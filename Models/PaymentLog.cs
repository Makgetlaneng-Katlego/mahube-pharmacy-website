using Mahube_Pharmacy.Models;
using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace Mahube_Pharmacy.Models;

public class PaymentLog
{
  
    public int LogId { get; set; }

    public int? OrderId { get; set; }
    public Order? Order { get; set; }


    public string? ResponseData { get; set; }

    public DateTime? CreatedAt { get; set; }
}