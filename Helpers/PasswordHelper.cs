namespace Mahube_Pharmacy.Helpers;

public static class PasswordHelper
{
    public const int MinLength = 8;

  
    public static bool IsStrong(string? password)
    {
        if (string.IsNullOrEmpty(password) || password.Length < MinLength)
            return false;
        if (!password.Any(char.IsDigit))
            return false;
        if (!password.Any(c => !char.IsLetterOrDigit(c)))
            return false;
        return true;
    }
}