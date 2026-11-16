using Mahube_Pharmacy.Data;
using Mahube_Pharmacy.Models;
using Mahube_Pharmacy.Services;
using Mahube_Pharmacy.ViewModels;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;
using System.Security.Claims;

namespace Mahube_Pharmacy.Controllers;

[Authorize]
public class CheckoutController : Controller
{
    private const decimal ServiceFee = 20.00m;

    private readonly AppDbContext _db;
    private readonly ICartService _cart;

    public CheckoutController(AppDbContext db, ICartService cart)
    {
        _db = db;
        _cart = cart;
    }

    private int CurrentUserId
    {
        get
        {
            var id = User.FindFirstValue(ClaimTypes.NameIdentifier);
            if (!int.TryParse(id, out var userId))
                throw new InvalidOperationException("User id claim missing.");
            return userId;
        }
    }

    // GET: /Checkout
    [HttpGet]
    public async Task<IActionResult> Index()
    {
        var userId = CurrentUserId;
        var cart = _cart.GetCart();

        if (cart.Count == 0)
        {
            TempData["Error"] = "Your cart is empty.";
            return RedirectToAction("Index", "Cart");
        }

        var productIds = cart.Keys.ToList();
        var products = await _db.Products
            .Where(p => productIds.Contains(p.ProductId))
            .ToListAsync();

        var addresses = await _db.Addresses
            .Where(a => a.UserId == userId)
            .ToListAsync();

        var vm = new CheckoutViewModel
        {
            ServiceFee = ServiceFee,
            Addresses = addresses,
            SelectedAddressId = addresses.FirstOrDefault()?.AddressId,
            Items = products.Select(p => new CheckoutItemVm
            {
                Product = p,
                Quantity = cart[p.ProductId]
            }).ToList()
        };

        return View(vm);
    }

    // POST: /Checkout/PlaceOrder
    [HttpPost]
    [ValidateAntiForgeryToken]
    public async Task<IActionResult> PlaceOrder(int? selectedAddressId)
    {
        var userId = CurrentUserId;
        var cart = _cart.GetCart();

        if (cart.Count == 0)
            return RedirectToAction("Index", "Cart");

        if (selectedAddressId is null)
        {
            TempData["Error"] = "Please select a delivery address.";
            return RedirectToAction(nameof(Index));
        }

        var address = await _db.Addresses
            .FirstOrDefaultAsync(a => a.AddressId == selectedAddressId && a.UserId == userId);

        if (address is null)
        {
            TempData["Error"] = "Invalid delivery address.";
            return RedirectToAction(nameof(Index));
        }

        var productIds = cart.Keys.ToList();
        var products = await _db.Products
            .Where(p => productIds.Contains(p.ProductId))
            .ToDictionaryAsync(p => p.ProductId);

        foreach (var (productId, qty) in cart)
        {
            if (!products.TryGetValue(productId, out var p) || p.StockQuantity < qty)
            {
                TempData["Error"] = $"Insufficient stock for {(p?.ProductName ?? "an item")}.";
                return RedirectToAction("Index", "Cart");
            }
        }

        var subtotal = cart.Sum(kv => products[kv.Key].Price * kv.Value);
        var total = subtotal + ServiceFee;

        await using var tx = await _db.Database.BeginTransactionAsync();
        try
        {
            var order = new Order
            {
                CustomerId = userId,
                TotalAmount = total,
                Status = "Pending",
                PaymentMethod = "Cash on Delivery",
                PaymentStatus = "PENDING",
                OrderDate = DateTime.UtcNow,
                AddressId = address.AddressId
            };

            _db.Orders.Add(order);
            await _db.SaveChangesAsync();

            foreach (var (productId, qty) in cart)
            {
                var p = products[productId];

                _db.OrderItems.Add(new OrderItem
                {
                    OrderId = order.OrderId,
                    ProductId = productId,
                    Quantity = qty,
                    PriceAtTime = p.Price
                });

                p.StockQuantity -= qty;
            }

            _db.PaymentLogs.Add(new PaymentLog
            {
                OrderId = order.OrderId,
                ResponseData = "Order placed (Cash on Delivery)",
                CreatedAt = DateTime.UtcNow
            });

            await _db.SaveChangesAsync();
            await tx.CommitAsync();

            _cart.Clear();

            return RedirectToAction(nameof(Confirmation), new { id = order.OrderId });
        }
        catch (DbUpdateConcurrencyException)
        {
            await tx.RollbackAsync();
            TempData["Error"] = "Stock changed while you were checking out. Please try again.";
            return RedirectToAction("Index", "Cart");
        }
        catch
        {
            await tx.RollbackAsync();
            throw;
        }
    }

    // GET: /Checkout/Confirmation/5
    [HttpGet]
    public async Task<IActionResult> Confirmation(int id)
    {
        var userId = CurrentUserId;

        var order = await _db.Orders
            .Include(o => o.OrderItems)
                .ThenInclude(oi => oi.Product)
            .Include(o => o.Address)
            .FirstOrDefaultAsync(o => o.OrderId == id && o.CustomerId == userId);

        if (order is null)
            return NotFound();

        order.Subtotal = order.OrderItems.Sum(oi => oi.PriceAtTime * oi.Quantity);
        order.ServiceFee = ServiceFee;
        order.DeliveryAddressSnapshot = order.Address is null
            ? null
            : $"{order.Address.StreetAddress}, {order.Address.City}, {order.Address.Province}, {order.Address.PostalCode}";

        return View(order);
    }
}