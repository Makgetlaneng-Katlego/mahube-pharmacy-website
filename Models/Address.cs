using Mahube_Pharmacy.Models;
using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace Mahube_Pharmacy.Models;

public class Address
{
    [Column("address_id")]
    public int AddressId { get; set; }
    [Column("user_id")]
    public int? UserId { get; set; }

    [StringLength(50)]
    [Column("address_name")]
    public string? AddressName { get; set; }

    [StringLength(50)]
    [Column("street_address")]
    public string? StreetAddress { get; set; }

    [StringLength(50)]
    [Column("city")]
    public string? City { get; set; }

    [StringLength(50)]
    [Column("province")]
    public string? Province { get; set; }

    [StringLength(15)]
    [Column("postal_code")]
    public string? PostalCode { get; set; }

    [StringLength(150)]
    [Column("delivery_instruction")]
    public string? DeliveryInstruction { get; set; }

    public User? User { get; set; }
}