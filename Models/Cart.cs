using System.ComponentModel.DataAnnotations.Schema;

namespace Mahube_Pharmacy.Models;


public class Cart
{
    [Column("id")]
    public int Id { get; set; }
    [Column("user_email")]
    public string? UserEmail { get; set; }
    [Column("product_id")]
    public string? ProductId { get; set; }  
    [Column("product_name")]
    public string? ProductName { get; set; }
    [Column("quantity")]
    public int? Quantity { get; set; }
    [Column("price")]
    public double? Price { get; set; }
}