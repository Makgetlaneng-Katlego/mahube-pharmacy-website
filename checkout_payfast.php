<?php
// checkout_payfast.php
session_start();
require_once 'payfast_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get cart data from POST
if (!isset($_POST['cart_data'])) {
    header('Location: cart.php');
    exit;
}
$cart = json_decode($_POST['cart_data'], true);
if (empty($cart)) {
    header('Location: cart.php');
    exit;
}

// Calculate total (including R20 service fee)
$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['qty'];
}
$total += 20; // service fee

// Generate a unique order reference (max 40 chars for PayFast)
$reference = 'MAHUBE-' . time() . '-' . rand(1000, 9999);

// Store cart and reference in session for later use
$_SESSION['pending_cart'] = $cart;
$_SESSION['pf_reference'] = $reference;

// Build PayFast data array
$data = [
    'merchant_id' => PF_MERCHANT_ID,
    'merchant_key' => PF_MERCHANT_KEY,
    'return_url' => SITE_URL . '/payfast_success.php',
    'cancel_url' => SITE_URL . '/payfast_cancel.php',
    'notify_url' => SITE_URL . '/payfast_notify.php',
    'name_first' => $_SESSION['fullname'] ?? 'Customer',
    'name_last' => '',
    'email_address' => $_SESSION['email'] ?? '',
    'm_payment_id' => $reference,
    'amount' => number_format($total, 2, '.', ''),
    'item_name' => 'Mahube Pharmacy Order #' . $reference,
    'item_description' => 'Pharmacy products and service fee',
    'custom_int1' => $_SESSION['user_id'], // pass user_id for later
    'custom_str1' => $reference,
];

// If you have a passphrase, add it and generate signature
if (PF_PASSPHRASE) {
    $data['passphrase'] = PF_PASSPHRASE;
}

// Generate signature (PayFast requires this)
$signature = generatePayFastSignature($data, PF_PASSPHRASE);
$data['signature'] = $signature;

// Now redirect to PayFast with a POST form
?>
<!DOCTYPE html>
<html>
<head>
    <title>Redirecting to PayFast...</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        .spinner { border: 4px solid #f3f3f3; border-top: 4px solid #f7931e; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin: 20px auto; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <h2>Redirecting to PayFast Secure Payment...</h2>
    <div class="spinner"></div>
    <p>Please wait, you will be redirected to complete your payment.</p>
    
    <form id="payfastForm" action="<?php echo PF_ACTION_URL; ?>" method="POST">
        <?php foreach ($data as $key => $value): ?>
            <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
        <?php endforeach; ?>
    </form>
    
    <script>
        document.getElementById('payfastForm').submit();
    </script>
</body>
</html>

<?php
/**
 * Generate PayFast signature
 */
function generatePayFastSignature($data, $passphrase = null) {
    // Remove signature if present
    unset($data['signature']);
    
    // Sort the array by key
    ksort($data);
    
    // Create query string
    $queryString = http_build_query($data);
    
    // Add passphrase if set
    if ($passphrase) {
        $queryString .= '&passphrase=' . urlencode($passphrase);
    }
    
    // Return MD5 hash
    return md5($queryString);
}
?>