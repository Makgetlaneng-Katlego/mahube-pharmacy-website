using System.ComponentModel.DataAnnotations;
using System.ComponentModel.DataAnnotations.Schema;

namespace Mahube_Pharmacy.Models;

public class Consultation
{
    [Column("consultation_id")]
    public long ConsultationId { get; set; }

    [StringLength(50)]
    [Column("fullname")]
    public string? Fullname { get; set; }

    [StringLength(15)]
    [Column("phone_number")]
    public string? PhoneNumber { get; set; }
    [Column("preferred_date")]
    public DateTime? PreferredDate { get; set; }

    [StringLength(50)]
    [Column("consultation_type")]
    public string? ConsultationType { get; set; }
    [StringLength(350)]
    [Column("Description")]
    public string? Description { get; set; }

    [StringLength(50)]
    [Column("response")]
    public string? Response { get; set; }

    [StringLength(255)]
    [Column("email")]
    public string? Email { get; set; }
}