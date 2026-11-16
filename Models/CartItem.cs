using System.ComponentModel.DataAnnotations.Schema;

namespace Mahube_Pharmacy.Models;

public class CartItem
{
    [Column("cart_items_id")]
    public int CartItemsId { get; set; }
    [Column("cart_id")]
    public int? CartId { get; set; }
    [Column("product_id")]
    public int? ProductId { get; set; }
    [Column("quantity")]
    public int? Quantity { get; set; }
    [Column("price")]
    public float? Price { get; set; }
    [Column("subtotal")]    
    public float? Subtotal { get; set; }
}