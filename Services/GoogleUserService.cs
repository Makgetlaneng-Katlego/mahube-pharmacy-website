using Mahube_Pharmacy.Data;
using Mahube_Pharmacy.Models;
using Microsoft.EntityFrameworkCore;

namespace Mahube_Pharmacy.Services;

public interface IGoogleUserService
{
    Task<User> GetOrCreateAsync(string email, string fullName);
    Task<User?> FindByEmailAsync(string email);
}

public class GoogleUserService : IGoogleUserService
{
    private readonly AppDbContext _db;
    public GoogleUserService(AppDbContext db) => _db = db;

    public Task<User?> FindByEmailAsync(string email)
    {
        var normalized = email.Trim().ToLowerInvariant();
        return _db.Users.FirstOrDefaultAsync(u => u.Email == normalized);
    }

    public async Task<User> GetOrCreateAsync(string email, string fullName)
    {
        var normalized = email.Trim().ToLowerInvariant();
        var existing = await _db.Users.FirstOrDefaultAsync(u => u.Email == normalized);
        if (existing is not null) return existing;

        
        var placeholderPhone = "G-" + Guid.NewGuid().ToString("N").Substring(0, 13);

        var newUser = new User
        {
            FullName = string.IsNullOrWhiteSpace(fullName)
                ? normalized.Split('@')[0]
                : fullName.Trim(),
            Email = normalized,
            PhoneNumber = placeholderPhone,
            Password = "",   
            PasswordHash = BCrypt.Net.BCrypt.HashPassword(Guid.NewGuid().ToString()),
            Role = "CUSTOMER"
        };

        _db.Users.Add(newUser);
        await _db.SaveChangesAsync();
        return newUser;
    }
}