using Mahube_Pharmacy.Models;

namespace Mahube_Pharmacy.ViewModels;

public class CheckoutViewModel
{
    public List<CheckoutItemVm> Items { get; set; } = new();
    public List<Address> Addresses { get; set; } = new();
    public int? SelectedAddressId { get; set; }

    public decimal Subtotal => Items.Sum(i => i.LineTotal);
    public decimal ServiceFee { get; set; } = 20.00m;
    public decimal Total => Subtotal + ServiceFee;
}

public class CheckoutItemVm
{
    public Product Product { get; set; } = null!;
    public int Quantity { get; set; }
    public decimal LineTotal => Product.Price * Quantity;
}