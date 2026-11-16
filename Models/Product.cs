using Mahube_Pharmacy.Models;
using System.ComponentModel.DataAnnotations;

namespace Mahube_Pharmacy.Models;

public class Product
{
    public int ProductId { get; set; }

    [Required, StringLength(200)]
    public string ProductName { get; set; } = "";

    [StringLength(1000)]
    public string? Description { get; set; }

    public decimal Price { get; set; }

    public int StockQuantity { get; set; }
    public int CategoryId { get; set; }

    [StringLength(300)]
    public string? Image { get; set; }

    public ProductCategory? Category { get; set; }
    public ICollection<OrderItem> OrderItems { get; set; } = new List<OrderItem>();
}