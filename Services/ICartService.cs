namespace Mahube_Pharmacy.Services;

public interface ICartService
{
    Dictionary<int, int> GetCart();
    void SaveCart(Dictionary<int, int> cart);
    void Clear();
}