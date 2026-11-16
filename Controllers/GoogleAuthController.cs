using Google.Apis.Auth;
using Mahube_Pharmacy.Services;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Extensions.Configuration;
using System.Threading.Tasks;

namespace Mahube_Pharmacy.Controllers;

[ApiController]
[Route("api/googleauth")]
public class GoogleAuthController : ControllerBase
{
    private readonly IConfiguration _config;
    private readonly IGoogleUserService _googleUsers;

    public GoogleAuthController(IConfiguration config, IGoogleUserService googleUsers)
    {
        _config = config;
        _googleUsers = googleUsers;
    }

    public record VerifyRequest(string IdToken);

    public record VerifyResponse(
        bool Success,
        int UserId,
        string Email,
        string FullName,
        string PhoneNumber,
        string Role,
        string? Error = null);

    [HttpPost("verify-token")]
    [IgnoreAntiforgeryToken] // Stateless API — CSRF is not applicable for native mobile clients
    public async Task<IActionResult> VerifyToken([FromBody] VerifyRequest request)
    {
        if (string.IsNullOrWhiteSpace(request?.IdToken))
            return BadRequest(new VerifyResponse(false, 0, "", "", "", "", "idToken is required."));

        try
        {
            var settings = new GoogleJsonWebSignature.ValidationSettings
            {
                Audience = new[] { _config["Authentication:Google:ClientId"] }
            };

            GoogleJsonWebSignature.Payload payload =
                await GoogleJsonWebSignature.ValidateAsync(request.IdToken, settings);

            if (!payload.EmailVerified)
                return Unauthorized(new VerifyResponse(false, 0, "", "", "", "",
                    "Google account email is not verified."));

            var user = await _googleUsers.GetOrCreateAsync(payload.Email, payload.Name ?? "");

            return Ok(new VerifyResponse(
                Success: true,
                UserId: user.UserId,
                Email: user.Email,
                FullName: user.FullName,
                PhoneNumber: user.PhoneNumber,
                Role: user.Role));
        }
        catch (InvalidJwtException)
        {
            return Unauthorized(new VerifyResponse(false, 0, "", "", "", "",
                "Invalid or expired Google ID token."));
        }
    }
}