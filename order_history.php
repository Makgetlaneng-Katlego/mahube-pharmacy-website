<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Database config
$db_host = '10.73.233.192';
$db_port = 3307;
$db_name = 'pharmacy';
$db_user = 'Katlego';
$db_pass = 'm4kg3tl4n3ng';

try {
    $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Fetch all orders for this customer, newest first
$stmt = $pdo->prepare("
    SELECT o.order_id, o.total_amount, o.status, o.order_date, o.shipping_address, o.payment_method,
           a.address_name, a.street_address, a.city, a.province, a.postal_code
    FROM orders o
    LEFT JOIN address a ON a.address_id = o.shipping_address
    WHERE o.customer_id = ?
    ORDER BY o.order_date DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch items for all orders in one query, joining products for the name
$order_items = [];
if (!empty($orders)) {
    $order_ids = array_column($orders, 'order_id');
    $placeholders = implode(',', array_fill(0, count($order_ids), '?'));
    
    $stmt = $pdo->prepare("
        SELECT oi.order_id, oi.product_id, COALESCE(p.product_name, 'Product') AS name, 
               oi.price_at_time AS price, oi.quantity 
        FROM order_items oi
        LEFT JOIN products p ON p.product_id = oi.product_id
        WHERE oi.order_id IN ($placeholders)
    ");
    $stmt->execute($order_ids);
    
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $order_items[(int)$row['order_id']][] = $row;
    }
}

// Map status -> label + css class
function order_status_meta($status) {
    $status = strtolower((string)$status);
    $map = [
        'pending'    => ['Pending',    'pending'],
        'processing' => ['Processing', 'review'],
        'shipped'    => ['Shipped',    'shipped'],
        'ready'      => ['Ready',      'ready'],
        'delivered'  => ['Delivered',  'ready'],
        'collected'  => ['Collected',  'ready'],
        'cancelled'  => ['Cancelled',  'cancelled'],
        'canceled'   => ['Cancelled',  'cancelled'],
    ];
    if (isset($map[$status])) {
        return $map[$status];
    }
    return [ucfirst($status ?: 'Unknown'), 'review'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order history - Mahube Pharmacy</title>
<link rel="icon" type="image/png" href="images/favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="theme.css">
<style>
    .order-card { background:#fff; border:1px solid var(--line); border-radius:16px; margin-bottom:18px; overflow:hidden; }
    .order-head { display:flex; justify-content:space-between; align-items:flex-start; gap:16px; padding:20px 24px; border-bottom:1px solid var(--line); flex-wrap:wrap; }
    .order-head h3 { font-size:16px; margin-bottom:4px; }
    .order-head .order-meta { color:var(--muted); font-size:13.5px; }
    .order-head .order-total { font-size:18px; font-weight:700; color:var(--text); text-align:right; white-space:nowrap; }
    .order-head .order-total small { display:block; font-size:12.5px; color:var(--muted); font-weight:500; margin-top:2px; }
    
    .order-status { display:inline-block; padding:4px 10px; border-radius:999px; font-size:12.5px; font-weight:600; }
    .order-status.pending   { background:#fff4df; color:#a56600; }
    .order-status.review    { background:#eaf3ff; color:#0057b8; }
    .order-status.shipped   { background:#f3e8ff; color:#6b21a8; }
    .order-status.ready     { background:#eef5e9; color:#2c5a16; }
    .order-status.cancelled { background:#fbecea; color:#9b2c20; }

    .order-items { padding:6px 24px 18px; }
    .order-item-row { display:grid; grid-template-columns: 1fr auto auto; gap:18px; align-items:center; padding:10px 0; border-bottom:1px dashed var(--line); font-size:14.5px; }
    .order-item-row:last-child { border-bottom:0; }
    .order-item-row .name { color:var(--text); }
    .order-item-row .qty { color:var(--muted); font-size:13.5px; }
    .order-item-row .price { color:var(--text); font-weight:600; text-align:right; white-space:nowrap; }
    .order-foot { padding:0 24px 18px; color:var(--muted); font-size:13.5px; display:flex; flex-direction:column; gap:6px;}
    .order-foot i { color:var(--green); margin-right:6px; width:14px; text-align:center;}
    
    .orders-empty { text-align:center; padding:70px 24px; }
    .orders-empty i { font-size:44px; color:#c9d3c4; margin-bottom:14px; display:block; }
    .orders-empty h2 { margin-bottom:8px; }
    .orders-empty p { color:var(--muted); margin-bottom:22px; }
    @media(max-width:600px){
        .order-item-row { grid-template-columns: 1fr auto; }
        .order-item-row .qty { grid-column:1 / -1; font-size:12.5px; }
        .order-head .order-total { text-align:left; }
    }
</style>
</head>
<body data-page="orders">
<?php include 'header.php'; ?>

<main class="wrapper">
    <div class="breadcrumb"><a href="index.php">Home</a> / <a href="profile.php">Profile</a> / Order history</div>
    <h1 class="page-title">Order history</h1>

    <?php if (empty($orders)): ?>
        <div class="content-card orders-empty">
            <i class="fa-solid fa-receipt"></i>
            <h2>No orders yet</h2>
            <p>When you place an order, it will appear here with its status and items.</p>
            <a href="products.php" class="btn">Browse products</a>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order):
            $oid    = (int)$order['order_id'];
            $status = (string)$order['status'];
            [$status_label, $status_class] = order_status_meta($status);
            $created = $order['order_date'] ? date('d M Y, H:i', strtotime($order['order_date'])) : '—';
            $items   = $order_items[$oid] ?? [];
            
            $addr_bits = array_filter([
                $order['address_name'] ?? '',
                $order['street_address'] ?? '',
                $order['city'] ?? '',
                $order['province'] ?? '',
                $order['postal_code'] ?? '',
            ]);
            $addr_text = implode(', ', $addr_bits);
        ?>
        <div class="order-card">
            <div class="order-head">
                <div>
                    <h3>Order #<?php echo $oid; ?></h3>
                    <div class="order-meta">Placed <?php echo htmlspecialchars($created); ?> &middot; <?php echo htmlspecialchars($order['payment_method'] ?? 'Payment'); ?></div>
                    <div style="margin-top:8px;">
                        <span class="order-status <?php echo $status_class; ?>"><?php echo htmlspecialchars($status_label); ?></span>
                    </div>
                </div>
                <div class="order-total">
                    R<?php echo number_format((float)$order['total_amount'], 2); ?>
                    <small>Total</small>
                </div>
            </div>

            <?php if (!empty($items)): ?>
            <div class="order-items">
                <?php foreach ($items as $it):
                    $line_total = (float)$it['price'] * (int)$it['quantity'];
                ?>
                <div class="order-item-row">
                    <div class="name"><?php echo htmlspecialchars($it['name']); ?></div>
                    <div class="qty">&times;<?php echo (int)$it['quantity']; ?></div>
                    <div class="price">R<?php echo number_format($line_total, 2); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="order-foot">
                <?php if ($addr_text): ?>
                    <div><i class="fa-solid fa-location-dot"></i><?php echo htmlspecialchars($addr_text); ?></div>
                <?php endif; ?>
                <?php if (!empty($order['transaction_id'])): ?>
                    <div><i class="fa-solid fa-receipt"></i>Txn ID: <?php echo htmlspecialchars($order['transaction_id']); ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<footer class="footer">
    <div class="footer-grid">
        <div>
            <a class="brand" href="index.php"><img src="images/logo-mark.png" alt=""><span class="brand-text">Mahube<b>Pharmacy</b></span></a>
            <p class="about">Your local pharmacy, online. Order ahead, collect in store, and ask a pharmacist when you need to.</p>
        </div>
        <div>
            <h5>Shop</h5>
            <a href="products.php">All products</a>
            <a href="prescription.php">Upload a prescription</a>
            <a href="cart.php">Your cart</a>
        </div>
        <div>
            <h5>Help</h5>
            <a href="consultation.php">Book a consultation</a>
            <a href="reminder.php">Medication reminders</a>
            <a href="about.php">About us</a>
            <a href="contact.php">Contact</a>
        </div>
        <div>
            <h5>Visit us</h5>
            <p>Mahube Valley, South Africa</p>
            <p>012 000 0000</p>
            <p>info@mahubepharmacy.co.za</p>
        </div>
    </div>
    <div class="footer-bottom">
        <span>&copy; 2026 Mahube Pharmacy</span>
    </div>
</footer>

<script src="app.js"></script>
<script src="site.js"></script>
</body>
</html>