using System.ComponentModel.DataAnnotations;

namespace Mahube_Pharmacy.Models;

public class Prescription
{
    public int Id { get; set; }

    [Required, StringLength(255)]
    public string UserEmail { get; set; } = "";

    [Required, StringLength(255)]
    public string DoctorName { get; set; } = "";

    public DateTime DatePrescribed { get; set; }

    [Required, StringLength(100)]
    public string Type { get; set; } = "";

    public string? Notes { get; set; }

    public byte[]? FileData { get; set; }   // longblob

    [StringLength(50)]
    public string? Response { get; set; }
}