using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace Mahube_Pharmacy.Models;

public class User
{

    [Column("user_id")]
    public int UserId { get; set; }

    [Required, StringLength(100)]
    [Column("fullname")]
    public string FullName { get; set; } = "";

    [Required, EmailAddress, StringLength(255)]
    [Column("email")]
    public string Email { get; set; } = "";

    [Required, StringLength(20)]
    [Column("phone_number")]
    public string PhoneNumber { get; set; } = "";

    [StringLength(200)]
    [Column("password_hash")]
    public string? PasswordHash { get; set; }

 
    [StringLength(200)]
    [Column("password")]
    public string? Password { get; set; }

    [Required, StringLength(20)]
    [Column("role")]
    public string Role { get; set; } = "CUSTOMER";

    public ICollection<Address> Addresses { get; set; } = new List<Address>();
    public ICollection<Order> Orders { get; set; } = new List<Order>();

  
}