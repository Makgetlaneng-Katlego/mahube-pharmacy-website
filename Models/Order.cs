using Mahube_Pharmacy.Models;
using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace Mahube_Pharmacy.Models;

[Table("orders")]
public class Order
{
    [Key]
    [Column("order_id")]
    public int OrderId { get; set; }

    [Column("customer_id")]
    public int? CustomerId { get; set; }

    [ForeignKey("CustomerId")]
    public User? Customer { get; set; }

    [Column("order_date")]
    public DateTime? OrderDate { get; set; }

    [Column("total_amount")]
    public decimal TotalAmount { get; set; }

    [Required, StringLength(50)]
    [Column("status")]
    public string Status { get; set; } = "Pending";

    [StringLength(50)]
    [Column("payment_method")]
    public string? PaymentMethod { get; set; }

    [StringLength(50)]
    [Column("payment_status")]
    public string? PaymentStatus { get; set; }

    [StringLength(100)]
    [Column("transaction_id")]
    public string? TransactionId { get; set; }

    // Note: The mapping for AddressId to "shipping_address" is already 
    // handled in your AppDbContext.cs, so we don't need [Column] here.
    public int? AddressId { get; set; }

    [ForeignKey("AddressId")]
    public Address? Address { get; set; }

    public ICollection<OrderItem> OrderItems { get; set; } = new List<OrderItem>();
    public ICollection<PaymentLog> PaymentLogs { get; set; } = new List<PaymentLog>();

    [NotMapped]
    public decimal Subtotal { get; set; }

    [NotMapped]
    public decimal ServiceFee { get; set; }

    [NotMapped]
    public string? DeliveryAddressSnapshot { get; set; }
}