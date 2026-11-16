using Mahube_Pharmacy.Data;
using Mahube_Pharmacy.Models;
using Microsoft.AspNetCore.Mvc;
using System.ComponentModel.DataAnnotations;

namespace Mahube_Pharmacy.Controllers;

public class ConsultationsController : Controller
{
    private readonly AppDbContext _db;
    public ConsultationsController(AppDbContext db) => _db = db;

    [HttpGet]
    public IActionResult Create() => View(new Consultation());

    [HttpPost, ValidateAntiForgeryToken]
    public async Task<IActionResult> Create(
        [FromForm] Consultation model,
        [FromForm(Name = "preferred_datetime")] string? preferredDatetime,
        [FromForm(Name = "action")] string? action)
    {
        if (action == "lookup")
            return View(new Consultation());

        if (string.IsNullOrWhiteSpace(model.Fullname) ||
            string.IsNullOrWhiteSpace(model.Email) ||
            string.IsNullOrWhiteSpace(model.PhoneNumber) ||
            string.IsNullOrWhiteSpace(model.ConsultationType) ||
            string.IsNullOrWhiteSpace(model.Description))
        {
            TempData["Message"] = "Please fill in all required fields.";
            TempData["MessageType"] = "error";
            return View(model);
        }

        if (!new EmailAddressAttribute().IsValid(model.Email))
        {
            TempData["Message"] = "Please enter a valid email address.";
            TempData["MessageType"] = "error";
            return View(model);
        }

        if (!string.IsNullOrWhiteSpace(preferredDatetime) &&
            DateTime.TryParse(preferredDatetime, out var dt))
        {
            model.PreferredDate = dt;
        }

        model.Response = "";
        _db.Consultations.Add(model);
        await _db.SaveChangesAsync();

        TempData["Message"] = "Your consultation has been submitted successfully!";
        TempData["MessageType"] = "success";

        // ConsultationId is long (bigint in DB) — TempData cannot serialize
        // Int64, so store it as a string.
        TempData["NewConsultationId"] = model.ConsultationId.ToString();

        return RedirectToAction(nameof(Create));
    }
}