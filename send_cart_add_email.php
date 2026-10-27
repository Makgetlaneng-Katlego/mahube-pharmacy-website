<?php
header('Content-Type: application/json');

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['email']) || !isset($input['product'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$to = trim($input['email']);
$product = $input['product'];
$productName = htmlspecialchars($product['name'] ?? 'Product');
$productPrice = floatval($product['price'] ?? 0);
$quantity = intval($product['quantity'] ?? 1);
$totalItem = $productPrice * $quantity;


if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
    exit;
}

$subject = "Item added to your cart - Mahube Pharmacy";

$messageHtml = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #2a9d8f; color: white; padding: 15px; text-align: center; }
        .item { border-bottom: 1px solid #ddd; padding: 10px 0; }
        .total { font-weight: bold; font-size: 18px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>🛒 Item Added to Your Cart</h2>
        </div>
        <p>You added the following item to your shopping cart:</p>
        <div class='item'>
            <strong>{$productName}</strong><br>
            Price: R " . number_format($productPrice, 2) . "<br>
            Quantity: {$quantity}<br>
            <span class='total'>Item total: R " . number_format($totalItem, 2) . "</span>
        </div>
        <p>You can review your cart at any time: <a href='https://yourdomain.com/cart.php'>View Cart</a></p>
        <p>Thank you for choosing Mahube Pharmacy!</p>
    </div>
</body>
</html>
";

// Plain text alternative
$messageText = "Item Added to Your Cart\n\n";
$messageText .= "Product: {$productName}\n";
$messageText .= "Price: R " . number_format($productPrice, 2) . "\n";
$messageText .= "Quantity: {$quantity}\n";
$messageText .= "Item total: R " . number_format($totalItem, 2) . "\n\n";
$messageText .= "View your cart: https://yourdomain.com/cart.php\n";
$messageText .= "Thank you for choosing Mahube Pharmacy!";

// Headers
$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
$headers .= "From: Mahube Pharmacy <noreply@mahubepharmacy.com>\r\n";
$headers .= "Reply-To: support@mahubepharmacy.com\r\n";

// Send the email
$success = mail($to, $subject, $messageHtml, $headers);

if ($success) {
    echo json_encode(['success' => true, 'message' => 'Confirmation email sent to ' . $to]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to send email. Please try again.']);
}