<?php
session_start();

// Database configuration
$db_host = '192.168.0.128';
$db_port = 3307;
$db_name = 'pharmacy';
$db_user = 'Katlego';
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
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Products - Mahube Pharmacy</title>
<link rel="icon" type="image/png" href="images/favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="theme.css">
</head>
<body data-page="products">
<?php include 'header.php'; ?>

<main class="wrapper">
    <h1 class="page-title">Products</h1>

    <div class="searchbar" style="margin-bottom:18px">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input onkeyup="filterProducts(this.value)" placeholder="Search medication, vitamins and health products" aria-label="Filter products">
        <button type="button" aria-label="Search"><i class="fa-solid fa-magnifying-glass"></i></button>
    </div>

    <div class="category-bar" style="padding:14px 0;background:transparent;box-shadow:none;border:0">
        <a href="#">Pain Relief</a>
        <a href="#">Cold &amp; Flu</a>
        <a href="#">Allergy</a>
        <a href="#">Digestive Health</a>
        <a href="#">Vitamins</a>
        <a href="#">Prescription Support</a>
    </div>

    <section class="section">
        <div class="section-head">
            <h2>Medication &amp; Health Products</h2>
            <span class="view"><?php echo $total_products; ?> products</span>
        </div>
        <div class="product-grid">
            <?php foreach ($products as $product):
                $id    = intval($product['product_id']);
                $name  = htmlspecialchars($product['product_name']);
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
<script>if (typeof updateCartCount === 'function') updateCartCount();</script>
</body>
</html>