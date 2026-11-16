using System.Text.RegularExpressions;

namespace Mahube_Pharmacy.Helpers;

public static class EmailHelper
{

    private static readonly Regex EmailRegex = new(
        @"^[^@\s]+@[^@\s]+\.[A-Za-z]{2,}$",
        RegexOptions.Compiled | RegexOptions.IgnoreCase);

    public static bool IsValid(string? email) =>
        !string.IsNullOrWhiteSpace(email) && EmailRegex.IsMatch(email.Trim());
}