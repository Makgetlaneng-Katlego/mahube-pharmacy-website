using Mahube_Pharmacy.Data;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace Mahube_Pharmacy.Controllers;

public class ProductController : Controller
{
    private readonly AppDbContext _db;
    public ProductController(AppDbContext db) => _db = db;

    public async Task<IActionResult> Index(string? q)
    {
        var query = _db.Products.AsNoTracking();

        if (!string.IsNullOrWhiteSpace(q))
            query = query.Where(p => p.ProductName.Contains(q));

        ViewData["Title"] = "Products";
        ViewData["Search"] = q;
        return View(await query.OrderBy(p => p.ProductName).ToListAsync());
    }
}