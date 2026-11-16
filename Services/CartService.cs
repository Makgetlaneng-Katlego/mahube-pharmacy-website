using System.Text.Json;

namespace Mahube_Pharmacy.Services;

public class CartService : ICartService
{
    private const string CartKey = "Cart";
    private const string CountKey = "CartCount";

    private readonly IHttpContextAccessor _accessor;

    public CartService(IHttpContextAccessor accessor) => _accessor = accessor;

    private ISession Session =>
        _accessor.HttpContext?.Session
        ?? throw new InvalidOperationException("Session is not configured.");

    public Dictionary<int, int> GetCart()
    {
        var json = Session.GetString(CartKey);
        if (string.IsNullOrEmpty(json)) return new();
        return JsonSerializer.Deserialize<Dictionary<int, int>>(json) ?? new();
    }

    public void SaveCart(Dictionary<int, int> cart)
    {
        Session.SetString(CartKey, JsonSerializer.Serialize(cart));
        Session.SetInt32(CountKey, cart.Values.Sum());
    }

    public void Clear()
    {
        Session.Remove(CartKey);
        Session.SetInt32(CountKey, 0);
    }
}