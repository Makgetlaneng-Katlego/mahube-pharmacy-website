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

// Fetch all products ordered by name
$products = $pdo->query("SELECT product_id, product_name, price, image FROM products ORDER BY product_name")->fetchAll(PDO::FETCH_ASSOC);
$total_products = count($products);

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['fullname'] : '';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Products - Mahube Pharmacy</title>
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
<nav class="navbar"><a href="index.php">Home</a><a href="products.php" class="active">Products</a><a href="about.php">About</a><a href="services.php">Services</a><a href="prescription.php">Prescriptions</a><a href="consultation.php">Consultation</a></nav>
<div class="category-bar"><a href="products.php">Shop All</a><a href="products.php">Pain Relief</a><a href="products.php">Cold & Flu</a><a href="products.php">Vitamins</a><a href="services.php">Clinic Services</a></div>

<main class="wrapper">
<h1 class="page-title">Products</h1>
<div class="searchbar" style="margin-bottom:18px">
    <input onkeyup="filterProducts(this.value)" placeholder="Search medication, vitamins and health products">
    <button>🔍</button>
</div>

<div class="category-bar" style="padding:14px 0;background:transparent;box-shadow:none;border:0">
    <a href="#">Pain Relief</a>
    <a href="#">Cold & Flu</a>
    <a href="#">Allergy</a>
    <a href="#">Digestive Health</a>
    <a href="#">Vitamins</a>
    <a href="#">Prescription Support</a>
</div>

<section class="section">
    <div class="section-head">
        <h2>Medication & Health Products</h2>
        <span class="view"><?php echo $total_products; ?> products</span>
    </div>
    <div class="product-grid">
        <?php foreach ($products as $product): 
            $id = intval($product['product_id']);
            $name = htmlspecialchars($product['product_name']);
            $price = floatval($product['price']);
            $image = htmlspecialchars(!empty($product['image']) ? $product['image'] : 'images/logo.svg');
        ?>
        <div class="product">
            <span class="tag">MED</span>
            <img src="<?php echo $image; ?>" alt="<?php echo $name; ?>">
            <h3><?php echo $name; ?></h3>
            <p class="price">R<?php echo number_format($price, 2); ?></p>
            <button onclick="addToCart(<?php echo $id; ?>, '<?php echo addslashes($name); ?>', <?php echo $price; ?>, '<?php echo addslashes($image); ?>')">Add to cart</button>
        </div>
        <?php endforeach; ?>
        <?php if (empty($products)): ?>
            <p>No products found in database.</p>
        <?php endif; ?>
    </div>
</section>
</main>
<footer class="footer"><span>Mahube Pharmacy © 2026</span><div class="footer-links"><a href="about.php">About</a><a href="services.php">Services</a><a href="products.php">Products</a><a href="contact.php">Contact</a></div></footer>
<script src="app.js"></script>
<script>updateCartCount();</script>
</body>
</html>