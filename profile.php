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

// Build initials for the avatar (e.g. "Pfano Dzivhani" -> "PD")
$initials = 'U';
$parts = preg_split('/\s+/', trim($fullname));
if (count($parts) >= 2) {
    $initials = strtoupper(mb_substr($parts[0], 0, 1) . mb_substr(end($parts), 0, 1));
} elseif (count($parts) === 1 && $parts[0] !== '') {
    $initials = strtoupper(mb_substr($parts[0], 0, 2));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Profile - Mahube Pharmacy</title>
<link rel="icon" type="image/png" href="images/favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="theme.css">
</head>
<body data-page="profile">
<?php include 'header.php'; ?>

<main class="wrapper">
    <div class="breadcrumb"><a href="index.php">Home</a> / Profile</div>
    <h1 class="page-title">My profile</h1>

    <div class="content-card profile-box">
        <div class="profile-avatar" id="pfAvatar"><?php echo htmlspecialchars($initials); ?></div>
        <div class="profile-info">
            <h2 id="pfName"><?php echo htmlspecialchars($fullname); ?></h2>
            <p class="pf-line"><i class="fa-regular fa-envelope"></i> <span id="pfEmail"><?php echo htmlspecialchars($email); ?></span></p>
            <p class="pf-line"><i class="fa-solid fa-phone"></i> <span id="pfPhone"><?php echo htmlspecialchars($phone); ?></span></p>
            <p class="pf-line muted"><i class="fa-solid fa-user-tag"></i> Role: <?php echo htmlspecialchars($role); ?></p>
            <span class="status-pill">Active account</span>
            <div class="pf-actions">
                <a href="register.php" class="btn ghost">Edit profile</a>
                <a href="address.php" class="btn ghost">Manage address</a>
                <a href="order_history.php" class="btn ghost">Order history</a>
            </div>
        </div>
    </div>

    <div class="menu-list">
       
        <a href="order_history.php"><i class="fa-solid fa-receipt"></i><span>Order history</span><i class="fa-solid fa-chevron-right go"></i></a>
        <a href="address.php"><i class="fa-solid fa-location-dot"></i><span>Manage address</span><i class="fa-solid fa-chevron-right go"></i></a>
        <a href="logout.php" class="danger"><i class="fa-solid fa-arrow-right-from-bracket"></i><span>Log out</span></a>
    </div>
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