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

// Fetch 4 products for featured section
$featured = $pdo->query("SELECT product_id, product_name, price, image FROM products LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);

$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['fullname'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Home - Mahube Pharmacy</title>
<link rel="icon" type="image/png" href="images/favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="theme.css">
</head>
<body data-page="home">
<?php include 'header.php'; ?>

<main class="wrapper">

    <section class="hero">
        <div>
            <h1>Medicine and advice,<br><span>without the queue.</span></h1>
            <p>Order online, upload your prescription, and collect when it is ready. A pharmacist is a message away if you are not sure what to take.</p>
            <div class="hero-actions">
                <a href="products.php" class="btn">Shop medicines</a>
                <a href="prescription.php" class="btn ghost">Upload a prescription</a>
            </div>
        </div>
        <img src="images/hero.svg" alt="">
    </section>

    <section class="promo-banner">
        <strong>This week</strong>
        <span>Selected pain relief and vitamins are on special in store and online.</span>
    </section>

    <section class="section">
        <div class="deals-row">
            <a href="prescription.php" class="deal-card">
                <img src="images/rx.svg" alt="Prescription">
                <div><h3>Upload a prescription</h3><p>We check it and get your order ready</p></div>
            </a>
            <a href="consultation.php" class="deal-card">
                <img src="images/consult.svg" alt="Consultation">
                <div><h3>Talk to a pharmacist</h3><p>Book a quick consultation</p></div>
            </a>
            <a href="reminder.php" class="deal-card">
                <img src="images/clock.svg" alt="Reminder">
                <div><h3>Medication reminders</h3><p>Get a nudge when it is time</p></div>
            </a>
        </div>
    </section>

    <section class="section">
        <div class="section-head">
            <h2>Popular right now</h2>
            <a class="view" href="products.php">View all</a>
        </div>
        <div class="product-grid">
            <?php foreach ($featured as $product):
                $id    = intval($product['product_id']);
                $name  = htmlspecialchars($product['product_name']);
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
        </div>
    </section>

    <section class="info-band">
        <div class="info-item"><img src="images/secure.svg" alt="Secure"><div><h4>Pay the way you prefer</h4><p>Card, EFT, scan to pay or at collection</p></div></div>
        <div class="info-item"><img src="images/support.svg" alt="Support"><div><h4>Real pharmacists</h4><p>Ask before you buy</p></div></div>
        <div class="info-item"><img src="images/return.svg" alt="Returns"><div><h4>Collect when ready</h4><p>Scan your code at the counter</p></div></div>
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