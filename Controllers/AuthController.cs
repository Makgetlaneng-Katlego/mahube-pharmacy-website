using System.Security.Claims;
using System.Text.Json;
using Google.Apis.Auth;
using Mahube_Pharmacy.Data;
using Mahube_Pharmacy.Helpers;
using Mahube_Pharmacy.Models;
using Mahube_Pharmacy.Services;
using Microsoft.AspNetCore.Authentication;
using Microsoft.AspNetCore.Authentication.Cookies;
using Microsoft.AspNetCore.Mvc;
using Microsoft.EntityFrameworkCore;

namespace Mahube_Pharmacy.Controllers;

public class AuthController : Controller
{
    private readonly AppDbContext _db;
    private readonly IConfiguration _config;
    private readonly IGoogleUserService _googleUsers;

    public AuthController(AppDbContext db, IConfiguration config, IGoogleUserService googleUsers)
    {
        _db = db;
        _config = config;
        _googleUsers = googleUsers;
    }

    private static string Normalize(string s) => s.Trim().ToLowerInvariant();

    // ---------------------------------------------------------------
    // GET: /Auth/Login
    // ---------------------------------------------------------------
    [HttpGet]
    public IActionResult Login(string? returnUrl = null)
    {
        if (User.Identity?.IsAuthenticated == true)
            return RedirectToAction("Index", "Home");

        ViewBag.ReturnUrl = returnUrl;
        ViewBag.GoogleClientId = _config["Authentication:Google:ClientId"];
        return View();
    }

    // ---------------------------------------------------------------
    // POST: /Auth/Login  (existing email/phone + password flow)
    // ---------------------------------------------------------------
    [HttpPost, ValidateAntiForgeryToken]
    public async Task<IActionResult> Login(string identifier, string password, string? returnUrl = null)
    {
        ViewBag.ReturnUrl = returnUrl;
        ViewBag.GoogleClientId = _config["Authentication:Google:ClientId"];

        if (string.IsNullOrWhiteSpace(identifier) || string.IsNullOrEmpty(password))
        {
            ViewBag.Error = "The email or phone number is incorrectly formatted.";
            return View();
        }

        identifier = identifier.Trim();

        User? user;
        if (identifier.Contains('@'))
        {
            if (!EmailHelper.IsValid(identifier))
            {
                ViewBag.Error = "The email or phone number is incorrectly formatted.";
                return View();
            }
            var email = Normalize(identifier);
            user = await _db.Users.FirstOrDefaultAsync(u => u.Email == email);
        }
        else
        {
            if (!PhoneHelper.TryNormalize(identifier, out var normalizedPhone))
            {
                ViewBag.Error = "The email or phone number is incorrectly formatted.";
                return View();
            }
            user = await _db.Users.FirstOrDefaultAsync(u => u.PhoneNumber == normalizedPhone);
        }

        if (user is not null)
        {
            // Google-only accounts have a unique placeholder phone "G-..." and a random hash,
            // so password verification will always fail. Show a helpful message instead.
            if (IsGoogleOnlyAccount(user))
            {
                ViewBag.Error = "This account uses Google Sign-In. Please use the 'Continue with Google' button below.";
                return View();
            }

            var (ok, needsSave) = VerifyPassword(user, password);

            if (ok)
            {
                if (needsSave)
                    await _db.SaveChangesAsync();

                await SignInAsync(user);
                if (!string.IsNullOrEmpty(returnUrl) && Url.IsLocalUrl(returnUrl))
                    return LocalRedirect(returnUrl);
                return RedirectToAction("Index", "Home");
            }
        }

        ViewBag.Error = "That email, phone number, or password didn't match.";
        return View();
    }

    // ---------------------------------------------------------------
    // POST: /Auth/GoogleSignIn  (called by JS on the Login page)
    // ---------------------------------------------------------------
    [HttpPost]
    [IgnoreAntiforgeryToken] // Google's JS SDK does not send ASP.NET anti-forgery tokens.
    public async Task<IActionResult> GoogleSignIn([FromBody] JsonElement body)
    {
        string? credential = null;
        string? returnUrl = null;

        try
        {
            if (body.TryGetProperty("credential", out var c)) credential = c.GetString();
            if (body.TryGetProperty("returnUrl", out var r)) returnUrl = r.GetString();
        }
        catch
        {
            // ignore
        }

        if (string.IsNullOrWhiteSpace(credential))
            return Json(new { success = false, error = "Missing Google credential." });

        try
        {
            var settings = new GoogleJsonWebSignature.ValidationSettings
            {
                Audience = new[] { _config["Authentication:Google:ClientId"] }
            };
            var payload = await GoogleJsonWebSignature.ValidateAsync(credential, settings);

            if (!payload.EmailVerified)
                return Json(new { success = false, error = "Google email is not verified." });

            var user = await _googleUsers.GetOrCreateAsync(payload.Email, payload.Name ?? "");
            await SignInAsync(user);

            var redirect = (!string.IsNullOrEmpty(returnUrl) && Url.IsLocalUrl(returnUrl))
                ? returnUrl!
                : Url.Action("Index", "Home")!;

            return Json(new { success = true, redirectUrl = redirect });
        }
        catch (InvalidJwtException)
        {
            return Json(new { success = false, error = "Invalid or expired Google token." });
        }
    }

    // ---------------------------------------------------------------
    // GET/POST: Register / Logout  (unchanged from your original)
    // ---------------------------------------------------------------
    [HttpGet]
    public IActionResult Register()
    {
        ViewBag.GoogleClientId = _config["Authentication:Google:ClientId"];
        return View();
    }

    [HttpPost, ValidateAntiForgeryToken]
    public async Task<IActionResult> Register(string fullname, string email, string phone,
                                              string password, string confirmPassword)
    {
        email = Normalize(email);

        if (!EmailHelper.IsValid(email) || !PhoneHelper.TryNormalize(phone, out var normalizedPhone))
        {
            ViewBag.Error = "The email or phone number is incorrectly formatted.";
            return View();
        }

        if (!PasswordHelper.IsStrong(password))
        {
            ViewBag.Error = "Password must be at least 8 characters and include a number and a special character.";
            return View();
        }

        if (password != confirmPassword)
        {
            ViewBag.Error = "Passwords do not match.";
            return View();
        }

        if (await _db.Users.AnyAsync(u => u.Email == email))
        {
            ViewBag.Error = "Email already registered.";
            return View();
        }

        if (await _db.Users.AnyAsync(u => u.PhoneNumber == normalizedPhone))
        {
            ViewBag.Error = "That phone number is already registered.";
            return View();
        }

        var newUser = new User
        {
            FullName = fullname.Trim(),
            Email = email,
            PhoneNumber = normalizedPhone,
            Password = "",
            PasswordHash = BCrypt.Net.BCrypt.HashPassword(password),
            Role = "CUSTOMER"
        };

        _db.Users.Add(newUser);
        try
        {
            await _db.SaveChangesAsync();
        }
        catch (DbUpdateException ex)
        {
            ViewBag.Error = "Registration failed: " + (ex.InnerException?.Message ?? ex.Message);
            return View();
        }

        await SignInAsync(newUser);
        return RedirectToAction("Index", "Home");
    }

    [HttpPost, ValidateAntiForgeryToken]
    public async Task<IActionResult> Logout()
    {
        await HttpContext.SignOutAsync(CookieAuthenticationDefaults.AuthenticationScheme);
        return RedirectToAction("Index", "Home");
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------
    private async Task SignInAsync(User user)
    {
        var claims = new List<Claim>
        {
            new(ClaimTypes.NameIdentifier, user.UserId.ToString()),
            new(ClaimTypes.Email, user.Email),
            new("FullName", user.FullName),
            new(ClaimTypes.Role, user.Role)
        };
        var identity = new ClaimsIdentity(claims, CookieAuthenticationDefaults.AuthenticationScheme);
        await HttpContext.SignInAsync(
            CookieAuthenticationDefaults.AuthenticationScheme,
            new ClaimsPrincipal(identity));
    }

    /// <summary>
    /// True when this user was created through Google Sign-In (placeholder phone + random hash).
    /// Used to show a friendly error when the user tries a password login.
    /// </summary>
    private static bool IsGoogleOnlyAccount(User user) =>
        user.PhoneNumber is not null
        && user.PhoneNumber.StartsWith("G-", StringComparison.Ordinal);

    private static (bool Success, bool NeedsSave) VerifyPassword(User user, string password)
    {
        if (LooksLikeBcrypt(user.PasswordHash))
        {
            if (!BCrypt.Net.BCrypt.Verify(password, user.PasswordHash!))
                return (false, false);

            if (!string.IsNullOrEmpty(user.Password) && !LooksLikeBcrypt(user.Password))
            {
                user.Password = "";
                return (true, true);
            }
            return (true, false);
        }

        if (LooksLikeBcrypt(user.Password))
        {
            if (!BCrypt.Net.BCrypt.Verify(password, user.Password!))
                return (false, false);

            user.PasswordHash = user.Password;
            user.Password = "";
            return (true, true);
        }

        if (!string.IsNullOrEmpty(user.Password) && user.Password == password)
        {
            user.PasswordHash = BCrypt.Net.BCrypt.HashPassword(password);
            user.Password = "";
            return (true, true);
        }

        return (false, false);
    }

    private static bool LooksLikeBcrypt(string? value) =>
        value is not null
        && value.Length >= 7
        && value[0] == '$' && value[1] == '2'
        && (value[2] == 'a' || value[2] == 'b' || value[2] == 'y' || value[2] == 'x')
        && value[3] == '$';
}