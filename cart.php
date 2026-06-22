<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['fullname'] : '';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cart - Mahube Pharmacy</title>
<link rel="stylesheet" href="style.css">
</head>
<body data-logged-in="<?php echo $isLoggedIn ? 'true' : 'false'; ?>">
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
<nav class="navbar"><a href="index.php">Home</a><a href="products.php">Products</a><a href="about.php">About</a><a href="services.php">Services</a><a href="prescription.php">Prescriptions</a><a href="consultation.php">Consultation</a></nav>
<div class="category-bar"><a href="products.php">Shop All</a><a href="products.php">Pain Relief</a><a href="products.php">Cold & Flu</a><a href="products.php">Vitamins</a><a href="services.php">Clinic Services</a></div>
<main class="wrapper">
<h1 class="page-title">My Cart</h1>
<div id="cartItems"></div>
<div class="content-card">
    <div class="total-row"><span>Subtotal:</span><strong id="subtotal">R0.00</strong></div>
    <div class="total-row"><span>Service Fee:</span><strong id="serviceFee">R0.00</strong></div>
    <div class="total-row grand"><span>Total:</span><strong id="total">R0.00</strong></div>
    <button class="btn" style="width:100%" onclick="checkoutOrder()">Proceed to checkout</button>
    <div id="cartMsg" class="msg"></div>
    <div id="receiptBox" class="receipt-box"></div>
</div>
</main>
<footer class="footer"><span>Mahube Pharmacy © 2026</span><div class="footer-links"><a href="about.php">About</a><a href="services.php">Services</a><a href="products.php">Products</a><a href="contact.php">Contact</a></div></footer>
<script src="app.js"></script>
<script>renderCart();</script>
</body>
</html>