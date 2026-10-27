<?php
session_start();
$message = '';
$message_type = '';
$response_text = '';
$consultation_date = '';
$consultation_type = '';
$description = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $phone = trim($_POST['phone_number'] ?? '');
    if (empty($phone)) {
        $message = 'Please enter your phone number.';
        $message_type = 'error';
    } else {
        $db_host = '192.168.0.128';
        $db_port = 3307;
        $db_name = 'pharmacy';
        $db_user = 'Katlego';
        $db_pass = 'm4kg3tl4n3ng';
        try {
            $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Fetch the most recent consultation for this phone number
            $stmt = $pdo->prepare("
                SELECT fullname, consultation_type, description, response, preferred_date, created_at 
                FROM consultation 
                WHERE phone_number = :phone 
                ORDER BY consultation_id DESC 
                LIMIT 1
            ");
            $stmt->execute([':phone' => $phone]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                $consultation_date = date('d M Y', strtotime($result['preferred_date'] ?? $result['created_at'] ?? 'now'));
                $consultation_type = htmlspecialchars($result['consultation_type']);
                $description = htmlspecialchars($result['description']);
                $response_text = !empty($result['response']) ? htmlspecialchars($result['response']) : 'No response yet. The pharmacist will review your request soon.';
                $fullname = htmlspecialchars($result['fullname']);
                $message = "Consultation found for $fullname";
                $message_type = 'success';
            } else {
                $message = 'No consultation found with that phone number. Please check and try again.';
                $message_type = 'error';
            }
        } catch (PDOException $e) {
            $message = 'Database error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Consultation Status - Mahube Pharmacy</title>
<link rel="stylesheet" href="style.css">
<style>
    .response-box {
        background: #f8f9fa;
        border-left: 4px solid #2a9d8f;
        padding: 15px;
        margin-top: 20px;
        border-radius: 8px;
    }
    .response-label {
        font-weight: bold;
        color: #2a9d8f;
        margin-bottom: 5px;
    }
    .consultation-detail {
        margin-top: 20px;
        padding: 15px;
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
    }
    .detail-row {
        margin-bottom: 10px;
        padding-bottom: 8px;
        border-bottom: 1px solid #eee;
    }
    .detail-label {
        font-weight: bold;
        width: 140px;
        display: inline-block;
    }
</style>
</head>
<body>
<div class="top-strip"><span>Mahube Pharmacy online health store</span><span>Healthcare products and pharmacist support</span></div>
<header class="main-header">
<a href="index.php"><img class="logo" src="images/logo.svg" alt="Mahube Pharmacy"></a>
<div class="searchbar"><input placeholder="Search for medicines, vitamins and health products"><button>🔍</button></div>
<div class="header-actions"><a href="login.php">Login</a><a href="register.php">Register</a><a class="action-icon" href="profile.php">👤</a><a class="action-icon" href="cart.php">🛒<span class="count cart-count">0</span></a></div>
</header>
<nav class="navbar"><a href="index.php">Home</a><a href="products.php">Products</a><a href="about.php">About</a><a href="services.php">Services</a><a href="prescription.php">Prescriptions</a><a href="consultation.php">Consultation</a><a href="consultation_status.php" class="active">Status</a></nav>

<main class="wrapper">
<h1 class="page-title">Check Consultation Response</h1>
<div class="hero" style="grid-template-columns:1fr 1fr">
    <div>
        <p>Enter the phone number you used when requesting a consultation. The pharmacist's response will be shown below.</p>
    </div>
    <img src="images/consultation_photo.svg" alt="Consultation Status">
</div>

<section class="content-card form">
    <?php if ($message): ?>
        <div class="msg <?php echo $message_type; ?> show" style="margin-bottom:20px;"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <label>Phone Number *</label>
        <input type="text" name="phone_number" placeholder="Enter your phone number (e.g., 0712345678)" required>
        <button class="btn" style="width:100%; margin-top:10px;">Check Status</button>
    </form>

    <?php if ($response_text !== ''): ?>
    <div class="consultation-detail">
        <h3>Your Consultation Details</h3>
        <div class="detail-row"><span class="detail-label">Full Name:</span> <?php echo $fullname; ?></div>
        <div class="detail-row"><span class="detail-label">Type:</span> <?php echo $consultation_type; ?></div>
        <div class="detail-row"><span class="detail-label">Preferred Date:</span> <?php echo $consultation_date; ?></div>
        <div class="detail-row"><span class="detail-label">Your Question:</span> <?php echo nl2br($description); ?></div>
        
        <div class="response-box">
            <div class="response-label">Pharmacist's Response:</div>
            <p><?php echo nl2br($response_text); ?></p>
        </div>
    </div>
    <?php endif; ?>
</section>
</main>
<footer class="footer"><span>Mahube Pharmacy © 2026</span><div class="footer-links"><a href="about.php">About</a><a href="services.php">Services</a><a href="products.php">Products</a><a href="contact.php">Contact</a></div></footer>
<script src="app.js"></script>
</body>
</html>