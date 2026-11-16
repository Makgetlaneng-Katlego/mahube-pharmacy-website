using Mahube_Pharmacy.Data;
using Mahube_Pharmacy.Models;
using Mahube_Pharmacy.Services;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace Mahube_Pharmacy.Controllers;

public class CartController : Controller
{
    private readonly AppDbContext _db;
    private readonly ICartService _cart;

    public CartController(AppDbContext db, ICartService cart)
    {
        _db = db;
        _cart = cart;
    }
    [HttpPost, ValidateAntiForgeryToken]
    public IActionResult UpdateQuantity(int productId, int delta)
    {
        var cart = _cart.GetCart();
        if (cart.TryGetValue(productId, out var q))
        {
            var newQty = q + delta;
            if (newQty <= 0) cart.Remove(productId);
            else cart[productId] = newQty;
            _cart.SaveCart(cart);
        }
        return RedirectToAction(nameof(Index));
    }

    public async Task<IActionResult> Index()
    {
        var cart = _cart.GetCart();
        var ids = cart.Keys.ToList();

        var products = await _db.Products
            .Where(p => ids.Contains(p.ProductId))
            .ToDictionaryAsync(p => p.ProductId);

        var rows = new List<(Product Product, int Quantity)>();
        foreach (var (productId, qty) in cart)
            if (products.TryGetValue(productId, out var p))
                rows.Add((p, qty));

        ViewData["Title"] = "Cart";
        return View(rows);
    }

    [HttpPost, ValidateAntiForgeryToken]
    public IActionResult Add(int productId)
    {
        var cart = _cart.GetCart();
        cart[productId] = cart.TryGetValue(productId, out var q) ? q + 1 : 1;
        _cart.SaveCart(cart);
        return RedirectToAction(nameof(Index));
    }

    [HttpPost, ValidateAntiForgeryToken]
    public IActionResult Remove(int productId)
    {
        var cart = _cart.GetCart();
        cart.Remove(productId);
        _cart.SaveCart(cart);
        return RedirectToAction(nameof(Index));
    }
}