<?php
// payfast_success.php
session_start();
require_once 'payfast_config.php';

// Check if we received a response
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['m_payment_id'])) {
    die('Invalid request.');
}

// 1. Validate signature
$pfData = $_POST;
unset($pfData['signature']); // Remove signature for validation
$signature = generatePayFastSignature($pfData, PF_PASSPHRASE);

if ($signature !== $_POST['signature']) {
    die('Signature verification failed.');
}

// 2. Check payment status
$paymentStatus = $_POST['payment_status'] ?? '';
if ($paymentStatus !== 'COMPLETE') {
    header('Location: payfast_cancel.php');
    exit;
}

// 3. Get the cart from session
$cart = $_SESSION['pending_cart'] ?? [];
if (empty($cart)) {
    die('Cart data missing.');
}

// 4. Create the order in the database
try {
    $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $total = 0;
    foreach ($cart as $item) {
        $total += $item['price'] * $item['qty'];
    }
    $total += 20;

    $pdo->beginTransaction();

    // Insert into orders
    $stmt = $pdo->prepare("
        INSERT INTO orders 
        (customer_id, order_date, total_amount, status, shipping_address, payment_method, payment_status, transaction_id)
        VALUES 
        (:customer_id, NOW(), :total, 'Completed', 0, 'PayFast', 'paid', :transaction_id)
    ");
    $stmt->execute([
        ':customer_id' => $_SESSION['user_id'],
        ':total' => $total,
        ':transaction_id' => $_POST['pf_payment_id'] ?? ''
    ]);
    $order_id = $pdo->lastInsertId();

    // Insert into order_items
    $stmt_item = $pdo->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price_at_time)
        VALUES (:order_id, :product_id, :quantity, :price_at_time)
    ");
    foreach ($cart as $item) {
        $stmt_item->execute([
            ':order_id' => $order_id,
            ':product_id' => $item['id'],
            ':quantity' => $item['qty'],
            ':price_at_time' => $item['price']
        ]);
    }

    // Insert into payment_logs
    $logData = json_encode($_POST, JSON_PRETTY_PRINT);
    $stmt_log = $pdo->prepare("
        INSERT INTO payment_logs (order_id, response_data, created_at)
        VALUES (:order_id, :response_data, NOW())
    ");
    $stmt_log->execute([
        ':order_id' => $order_id,
        ':response_data' => $logData
    ]);

    $pdo->commit();

    // Clear session and cart
    unset($_SESSION['pending_cart']);
    unset($_SESSION['pf_reference']);

    $order_num = $order_id;

} catch (Exception $e) {
    if (isset($pdo)) $pdo->rollBack();
    die('Order creation failed: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Mahube Pharmacy</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .success-box { max-width:600px; margin:80px auto; text-align:center; background:#fff; padding:40px; border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,0.1); }
        .success-box h1 { color:#2a9d8f; }
        .order-number { font-size:32px; font-weight:bold; color:#002f6c; margin:20px 0; }
    </style>
</head>
<body>
<div class="success-box">
    <h1>✅ Payment Successful!</h1>
    <p>Thank you for your order.</p>
    <div class="order-number">#<?php echo $order_num ?? ''; ?></div>
    <p>Your PayFast payment was successful. We will process your order shortly.</p>
    <a href="index.php" class="btn" style="display:inline-block; margin-top:20px;">Return to Home</a>
</div>
<script>
    // Clear local cart
    localStorage.removeItem('mahubeCart');
</script>
</body>
</html>

<?php
// Helper function (same as used in checkout)
function generatePayFastSignature($data, $passphrase = null) {
    unset($data['signature']);
    ksort($data);
    $queryString = http_build_query($data);
    if ($passphrase) {
        $queryString .= '&passphrase=' . urlencode($passphrase);
    }
    return md5($queryString);
}
?>