<?php
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['fullname'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cart - Mahube Pharmacy</title>
<link rel="icon" type="image/png" href="images/favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="theme.css">
<style>
    /* New UI Cart Layout */
    .cart-layout { display: grid; grid-template-columns: 1fr 360px; gap: 30px; align-items: start; }
    
    /* Styling the container where app.js injects the items */
    #cartItems { display: flex; flex-direction: column; gap: 16px; }
    
    /* Summary Sidebar */
    .cart-summary { background: #fff; border: 1px solid var(--line); border-radius: 16px; padding: 24px; position: sticky; top: 100px; }
    .cart-summary h2 { font-size: 20px; margin-bottom: 20px; border-bottom: 1px solid var(--line); padding-bottom: 12px; }
    
    .total-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 15px; color: var(--muted); }
    .total-row strong { color: var(--text); }
    .total-row.grand { font-size: 18px; font-weight: 700; border-top: 1px solid var(--line); padding-top: 16px; margin-top: 16px; }
    .total-row.grand strong { font-size: 22px; color: var(--green); }

    /* Receipt Box for success */
    .receipt-box { display: none; margin-top: 20px; background: #fff; border: 1px solid var(--line); border-radius: 10px; padding: 20px; text-align: center; }
    .receipt-box.show { display: block; }
    .receipt-success { background: #eef5e9; color: #2c5a16; border-radius: 12px; padding: 14px; font-weight: 600; font-size: 15px; }

    @media(max-width: 850px) {
        .cart-layout { grid-template-columns: 1fr; }
        .cart-summary { position: static; }
    }
</style>
</head>
<body data-page="cart" data-logged-in="<?php echo $isLoggedIn ? 'true' : 'false'; ?>">
<?php include 'header.php'; ?>

<main class="wrapper">
    <div class="breadcrumb"><a href="index.php">Home</a> / Cart</div>
    <h1 class="page-title">My Cart</h1>

    <div class="cart-layout">
        <!-- Left Side: Items (Rendered by app.js) -->
        <div id="cartItems">
            <!-- Loading state before JS kicks in -->
            <div class="content-card">
                <p>Loading your cart...</p>
            </div>
        </div>

        <!-- Right Side: Summary & Checkout -->
        <div class="cart-summary">
            <h2>Order Summary</h2>
            
            <div class="total-row">
                <span>Subtotal</span>
                <strong id="subtotal">R0.00</strong>
            </div>
            <div class="total-row">
                <span>Service Fee</span>
                <strong id="serviceFee">R0.00</strong>
            </div>
            <div class="total-row grand">
                <span>Total</span>
                <strong id="total">R0.00</strong>
            </div>
            
            <!-- Calls the exact same function from your old file -->
            <button class="btn" style="width:100%; margin-top:10px;" onclick="checkoutOrder()">
                Proceed to checkout <i class="fa-solid fa-arrow-right"></i>
            </button>
            
            <!-- Messages & Receipts -->
            <div id="cartMsg" class="msg" style="margin-top:15px; margin-bottom:0;"></div>
            <div id="receiptBox" class="receipt-box"></div>
        </div>
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
<script>
    // Renders the cart immediately when the page loads
    renderCart();
</script>
</body>
</html>