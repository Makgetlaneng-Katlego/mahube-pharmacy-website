using System.Security.Claims;
using Mahube_Pharmacy.Data;
using Mahube_Pharmacy.Models;
using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;

namespace Mahube_Pharmacy.Controllers;

[Authorize]
public class AddressController : Controller
{
    private readonly AppDbContext _db;
    public AddressController(AppDbContext db) => _db = db;

    private int CurrentUserId =>
        int.TryParse(User.FindFirstValue(ClaimTypes.NameIdentifier), out var id)
            ? id
            : throw new InvalidOperationException("User id claim missing.");

    [HttpGet]
    public IActionResult Create(string? returnUrl = null)
    {
        ViewBag.ReturnUrl = returnUrl;
        return View(new Address());
    }

    [HttpPost, ValidateAntiForgeryToken]
    public async Task<IActionResult> Create(Address model, string? returnUrl = null)
    {
        if (!ModelState.IsValid)
        {
            ViewBag.ReturnUrl = returnUrl;
            return View(model);
        }

        model.UserId = CurrentUserId;
        model.User = null;
        _db.Addresses.Add(model);
        await _db.SaveChangesAsync();

        if (!string.IsNullOrEmpty(returnUrl) && Url.IsLocalUrl(returnUrl))
            return LocalRedirect(returnUrl);

        return RedirectToAction("Index", "Checkout");
    }
}