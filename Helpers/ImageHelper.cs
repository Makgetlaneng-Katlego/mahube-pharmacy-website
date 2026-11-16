namespace Mahube_Pharmacy.Helpers;

public static class ImageHelper
{
    public const string Placeholder = "/favicon.png";

    public static string GetProductImage(string? image)
    {
        if (string.IsNullOrWhiteSpace(image))
            return Placeholder;

        image = image.Trim();

        if (image.StartsWith("http://", StringComparison.OrdinalIgnoreCase) ||
            image.StartsWith("https://", StringComparison.OrdinalIgnoreCase) ||
            image.StartsWith("//") ||
            image.StartsWith("data:", StringComparison.OrdinalIgnoreCase))
            return image;

        if (image.StartsWith("~/") || image.StartsWith("/"))
            return image;

        if (image.StartsWith("images/", StringComparison.OrdinalIgnoreCase) ||
            image.StartsWith("uploads/", StringComparison.OrdinalIgnoreCase))
            return "~/" + image;

        return "~/images/" + image;
    }
}