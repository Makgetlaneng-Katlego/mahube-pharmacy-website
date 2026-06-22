<?php
session_start();

// Database configuration
$db_host = '127.0.0.1';
$db_port = 3307;
$db_name = 'pharmacy';
$db_user = 'root';
$db_pass = 'm4kg3tl4n3ng';

try {
    $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch 4 products for featured section
$featured = $pdo->query("SELECT product_id, product_name, price, image FROM products LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['fullname'] : '';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Home - Mahube Pharmacy</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="top-strip"><span>Mahube Pharmacy online health store</span><span>Affordable healthcare products and pharmacist support</span></div>
<header class="main-header">
<a href="index.php"><img class="logo" src="images/logo.svg" alt="Mahube Pharmacy"></a>
<div class="searchbar"><input placeholder="Search for medicines, vitamins and health products"><button>🔍</button></div>
<div class="header-actions">
    <?php if ($isLoggedIn): ?>
        <span>Hello, <?php echo htmlspecialchars($userName); ?></span>
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    <?php endif; ?>
    <a class="action-icon" href="profile.php">👤</a>
    <a class="action-icon" href="cart.php">🛒<span class="count cart-count">0</span></a>
</div>
</header>
<nav class="navbar"><a href="index.php" class="active">Home</a><a href="products.php">Products</a><a href="about.php">About</a><a href="services.php">Services</a><a href="prescription.php">Prescriptions</a><a href="consultation.php">Consultation</a></nav>
<div class="category-bar"><a href="products.php">Shop All</a><a href="products.php">Pain Relief</a><a href="products.php">Cold & Flu</a><a href="products.php">Vitamins</a><a href="services.php">Clinic Services</a></div>
<main class="wrapper">
<section class="hero"><div><h1>Your Health,<br><span>Our Priority</span></h1><p>Shop medicine, wellness products and pharmacy services in one simple place.</p><a href="products.php" class="btn">Shop now</a></div><img src="images/hero.svg" alt="Hero"></section>
<section class="promo-banner"><strong>Weekly pharmacy deals</strong><span>Affordable healthcare support without delivery services.</span></section>
<section class="section"><div class="deals-row">
<a href="prescription.php" class="deal-card"><img src="images/rx.svg" alt="Prescription"><div><h3>Upload Prescription</h3><p>Send your prescription for review</p></div></a>
<a href="consultation.php" class="deal-card"><img src="images/consult.svg" alt="Consultation"><div><h3>Consultation</h3><p>Ask our pharmacists</p></div></a>
<a href="reminder.php" class="deal-card"><img src="images/clock.svg" alt="Reminder"><div><h3>Medication Reminder</h3><p>Never miss your dose</p></div></a>
</div></section>
<section class="section"><div class="section-head"><h2>Featured products</h2><a class="view" href="products.php">View all</a></div><div class="product-grid">
    <?php foreach ($featured as $product): 
        $id = intval($product['product_id']);
        $name = htmlspecialchars($product['product_name']);
        $price = floatval($product['price']);
        $image = htmlspecialchars(!empty($product['image']) ? $product['image'] : 'images/logo.svg');
    ?>
    <div class="product">
        <span class="tag">DEAL</span>
        <img src="<?php echo $image; ?>" alt="<?php echo $name; ?>">
        <h3><?php echo $name; ?></h3>
        <p class="price">R<?php echo number_format($price, 2); ?></p>
        <button onclick="addToCart(<?php echo $id; ?>, '<?php echo addslashes($name); ?>', <?php echo $price; ?>, '<?php echo addslashes($image); ?>')">Add to cart</button>
    </div>
    <?php endforeach; ?>
    <?php if (empty($featured)): ?>
        <p>No products found.</p>
    <?php endif; ?>
</div></section>
<section class="info-band"><div class="info-item"><img src="images/secure.svg" alt="Secure"><div><h4>Secure checkout</h4><p>Safe order processing</p></div></div><div class="info-item"><img src="images/support.svg" alt="Support"><div><h4>Expert support</h4><p>Get advice from pharmacists</p></div></div><div class="info-item"><img src="images/return.svg" alt="Returns"><div><h4>Easy returns</h4><p>Simple return process</p></div></div></section>
</main>
<footer class="footer"><span>Mahube Pharmacy © 2026</span><div class="footer-links"><a href="about.php">About</a><a href="services.php">Services</a><a href="products.php">Products</a><a href="contact.php">Contact</a></div></footer>
<script src="app.js"></script>
<script>updateCartCount();</script>
</body>
</html>