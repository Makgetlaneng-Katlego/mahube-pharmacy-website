using Mahube_Pharmacy.Data;
using Mahube_Pharmacy.Models;
using Microsoft.AspNetCore.Mvc;

namespace Mahube_Pharmacy.Controllers;

public class PrescriptionsController : Controller
{
    private readonly AppDbContext _db;
    private const long MaxBytes = 5L * 1024 * 1024; // 5 MB — matches PHP limit

    public PrescriptionsController(AppDbContext db) => _db = db;

    [HttpGet]
    public IActionResult Create() => View(new Prescription());

    [HttpPost, ValidateAntiForgeryToken]
    [RequestSizeLimit(MaxBytes + 1024 * 1024)]
    public async Task<IActionResult> Create(
        [FromForm] Prescription model,
        IFormFile? prescription_file,
        [FromForm(Name = "action")] string? action)
    {
        // "lookup" action = user wants to query a prescription, not submit
        if (action == "lookup")
            return View(new Prescription());

        // ---- Required-field validation ----
        if (string.IsNullOrWhiteSpace(model.UserEmail) ||
            string.IsNullOrWhiteSpace(model.DoctorName) ||
            model.DatePrescribed == default ||
            string.IsNullOrWhiteSpace(model.Type))
        {
            TempData["Message"] = "Please fill in all required fields and upload a valid file.";
            TempData["MessageType"] = "error";
            return View(model);
        }

        // ---- File presence ----
        if (prescription_file == null || prescription_file.Length == 0)
        {
            TempData["Message"] = "Please upload a prescription image or PDF.";
            TempData["MessageType"] = "error";
            return View(model);
        }

        // ---- File size ----
        if (prescription_file.Length > MaxBytes)
        {
            TempData["Message"] = "File size exceeds 5MB limit.";
            TempData["MessageType"] = "error";
            return View(model);
        }

        // ---- Read file into byte[] for the longblob column ----
        using (var ms = new MemoryStream())
        {
            await prescription_file.CopyToAsync(ms);
            model.FileData = ms.ToArray();
        }

        // Default response value (column is varchar(50) NULL, empty string is fine)
        model.Response = "";

        // ---- Persist ----
        _db.Prescriptions.Add(model);
        await _db.SaveChangesAsync();

        // ---- Flash message (survives the redirect, one request only) ----
        TempData["Message"] = "Prescription uploaded successfully!";
        TempData["MessageType"] = "success";
        TempData["NewPrescriptionId"] = model.Id;   // populated after SaveChangesAsync()

        // ---- Return to Home/Index as requested ----
        return RedirectToAction("Index", "Home");
    }
}