using Mahube_Pharmacy.Data;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace Mahube_Pharmacy.Controllers;

public class HomeController : Controller
{
    private readonly AppDbContext _db;
    public HomeController(AppDbContext db) => _db = db;

    public async Task<IActionResult> Index()
    {
        var featured = await _db.Products
            .AsNoTracking()
            .OrderBy(p => p.ProductName)
            .Take(4)
            .ToListAsync();

        ViewData["Title"] = "Mahube Pharmacy";
        return View(featured);
    }

    [ResponseCache(Duration = 0, Location = ResponseCacheLocation.None, NoStore = true)]
    public IActionResult Error() => View();
}