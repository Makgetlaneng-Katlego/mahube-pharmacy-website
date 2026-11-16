using PhoneNumbers;

namespace Mahube_Pharmacy.Helpers;

public static class PhoneHelper
{
    private static readonly PhoneNumberUtil Util = PhoneNumberUtil.GetInstance();

 
    public static bool TryNormalize(string? input, out string normalized)
    {
        normalized = "";
        if (string.IsNullOrWhiteSpace(input)) return false;

        try
        {
            var number = Util.Parse(input, "ZA");

            if (!Util.IsValidNumber(number)) return false;

            var type = Util.GetNumberType(number);
            if (type != PhoneNumberType.MOBILE && type != PhoneNumberType.FIXED_LINE)
                return false;

            normalized = Util.Format(number, PhoneNumberFormat.E164); // e.g. +27821234567
            return true;
        }
        catch (NumberParseException)
        {
            return false;
        }
    }
}