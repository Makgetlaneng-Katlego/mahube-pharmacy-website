<?php
session_start();
header('Content-Type: application/json');

// ==================== DATABASE CONFIGURATION ====================
$db_host = '192.168.0.128';
$db_port = 3307;
$db_name = 'pharmacy';
$db_user = 'Katlego';
$db_pass = 'm4kg3tl4n3ng';

try {
    $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed.']);
    exit;
}

// Check login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please log in to place an order.']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Get cart data from request
$input = json_decode(file_get_contents('php://input'), true);
$cart = $input['cart'] ?? [];

if (empty($cart)) {
    echo json_encode(['success' => false, 'message' => 'Your cart is empty.']);
    exit;
}

// 1. Fetch user's address (if they have one)
$stmt = $pdo->prepare("SELECT address_id FROM address WHERE user_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$address = $stmt->fetch(PDO::FETCH_ASSOC);
$address_id = $address ? $address['address_id'] : null;

// 2. Calculate total (subtotal + R20 service fee)
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += floatval($item['price']) * intval($item['quantity']);
}
$service_fee = 20; 
$total = $subtotal + $service_fee;

// 3. Insert order and items
try {
    $pdo->beginTransaction();

    // Fixed: Added payment_status and transaction_id to match your DB schema
    $sql = "INSERT INTO orders (customer_id, total_amount, shipping_address, status, order_date, payment_method, payment_status, transaction_id) 
            VALUES (?, ?, ?, 'Pending', NOW(), 'Cash on Delivery', 'PENDING', NULL)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $total, $address_id]);
    $order_id = $pdo->lastInsertId();

    // Insert order items (matches your order_items table schema)
    $itemSql = "INSERT INTO order_items (order_id, product_id, price_at_time, quantity) 
                VALUES (?, ?, ?, ?)";
    $itemStmt = $pdo->prepare($itemSql);
    
    foreach ($cart as $item) {
        $product_id = intval($item['id'] ?? 0);
        $price = floatval($item['price'] ?? 0);
        $qty = intval($item['quantity'] ?? 1);
        
        // Only insert if we have a valid product_id to avoid foreign key errors
        if ($product_id > 0) {
            $itemStmt->execute([$order_id, $product_id, $price, $qty]);
        }
    }

    $pdo->commit();

    echo json_encode(['success' => true, 'order_id' => $order_id, 'message' => 'Order placed successfully!']);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}