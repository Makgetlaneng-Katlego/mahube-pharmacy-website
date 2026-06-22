<?php
session_start();
header('Content-Type: application/json');

// ==================== DATABASE CONFIGURATION ====================
$db_host = '127.0.0.1';
$db_port = 3307;
$db_name = 'pharmacy';
$db_user = 'root';
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

// 1. Fetch user's address (we assume one address per user for simplicity)
$stmt = $pdo->prepare("SELECT address_id FROM address WHERE user_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$address = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$address) {
    echo json_encode([
        'success' => false,
        'message' => 'No shipping address found. Please add an address in your <a href="address.php">profile</a> before placing an order.'
    ]);
    exit;
}

$address_id = $address['address_id'];

// 2. Calculate total (subtotal + service fee)
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += floatval($item['price']) * intval($item['quantity']);
}
$service_fee = 20; // flat fee, or percentage
$total = $subtotal + $service_fee;

// 3. Insert order
try {
    $pdo->beginTransaction();

    $sql = "INSERT INTO orders (user_id, total, shipping_address, status, created_at) 
            VALUES (?, ?, ?, 'pending', NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id, $total, $address_id]);
    $order_id = $pdo->lastInsertId();

    // 4. Insert order items
    $itemSql = "INSERT INTO order_items (order_id, product_id, name, price, quantity) 
                VALUES (?, ?, ?, ?, ?)";
    $itemStmt = $pdo->prepare($itemSql);
    foreach ($cart as $item) {
        $product_id = intval($item['id'] ?? 0);
        $name = $item['name'] ?? 'Product';
        $price = floatval($item['price'] ?? 0);
        $qty = intval($item['quantity'] ?? 1);
        $itemStmt->execute([$order_id, $product_id, $name, $price, $qty]);
    }

    $pdo->commit();

    echo json_encode(['success' => true, 'order_id' => $order_id, 'message' => 'Order placed successfully!']);

} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}