<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$fullname = $_SESSION['fullname'];
$email = $_SESSION['email'];
$phone = $_SESSION['phone_number'] ?? 'Not provided';
$role = $_SESSION['role'] ?? 'customer';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile - Mahube Pharmacy</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="top-strip"><span>Mahube Pharmacy online health store</span><span>Affordable healthcare products and pharmacist support</span></div>
<header class="main-header">
<a href="index.php"><img class="logo" src="images/logo.svg" alt="Mahube Pharmacy"></a>
<div class="searchbar"><input placeholder="Search for medicines, vitamins and health products"><button>🔍</button></div>
<div class="header-actions">
    <span>Hello, <?php echo htmlspecialchars($fullname); ?></span>
    <a href="logout.php">Logout</a>
    <a class="action-icon" href="profile.php">👤</a>
    <a class="action-icon" href="cart.php">🛒<span class="count cart-count">0</span></a>
</div>
</header>
<nav class="navbar"><a href="index.php">Home</a><a href="products.php">Products</a><a href="about.php">About</a><a href="services.php">Services</a><a href="prescription.php">Prescriptions</a><a href="consultation.php">Consultation</a></nav>
<div class="category-bar"><a href="products.php">Shop All</a><a href="products.php">Pain Relief</a><a href="products.php">Cold & Flu</a><a href="products.php">Vitamins</a><a href="services.php">Clinic Services</a></div>

<main class="wrapper">
<div class="breadcrumb"><a href="index.php">Home</a> / Profile</div>
<h1 class="page-title">My Profile</h1>

<div class="content-card profile-box">
    <div class="profile-avatar">👤</div>
    <div>
        <h2><?php echo htmlspecialchars($fullname); ?></h2>
        <p>Email: <?php echo htmlspecialchars($email); ?></p>
        <p>Phone: <?php echo htmlspecialchars($phone); ?></p>
        <p>Role: <?php echo htmlspecialchars($role); ?></p>
        <span class="status-pill">Active account</span>
        <br><br>
        <a href="register.php" class="btn">Edit profile</a>
        <a href="address.php" class="btn" style="margin-left:10px; background:#2a9d8f;">📍 Manage Address</a>
        <a href="order_history.php" class="btn" style="margin-left:10px">Order History</a>
    </div>
</div>

<div class="deals-row">
    <a class="deal-card" href="cart.php"><img src="images/secure.svg"><div><h3>My Cart</h3><p>View cart and checkout</p></div></a>
    <a class="deal-card" href="reminder.php"><img src="images/clock.svg"><div><h3>Medication Reminders</h3><p>Manage your reminders</p></div></a>
    <a class="deal-card" href="logout.php"><img src="images/return.svg"><div><h3>Logout</h3><p>Sign out of your account</p></div></a>
</div>
</main>
<footer class="footer"><span>Mahube Pharmacy © 2026</span><div class="footer-links"><a href="about.php">About</a><a href="services.php">Services</a><a href="products.php">Products</a><a href="contact.php">Contact</a></div></footer>
<script src="app.js"></script>
</body>
</html>