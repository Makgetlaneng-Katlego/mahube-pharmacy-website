using System.ComponentModel.DataAnnotations;

namespace Mahube_Pharmacy.Models;

public class ProductCategory
{
    public int CategoryId { get; set; }

    [Required, StringLength(100)]
    public string CategoryName { get; set; } = "";

    public ICollection<Product> Products { get; set; } = new List<Product>();
}