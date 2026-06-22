<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Book Appointment - Mahube Pharmacy</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="top-strip"><span>Mahube Pharmacy online health store</span><span>Affordable healthcare products and pharmacist support</span></div>
<header class="main-header">
<a href="index.php"><img class="logo" src="images/logo.svg" alt="Mahube Pharmacy"></a>
<div class="searchbar"><input placeholder="Search for medicines, vitamins and health products"><button>🔍</button></div>
<div class="header-actions"><a href="login.php">Login</a><a href="register.php">Register</a><a class="action-icon" href="profile.php">👤</a><a class="action-icon" href="cart.php">🛒<span class="count cart-count">0</span></a></div>
</header>
<nav class="navbar"><a href="index.php" class="">Home</a><a href="products.php" class="">Products</a><a href="about.php" class="">About</a><a href="services.php" class="">Services</a><a href="prescription.php">Prescriptions</a><a href="consultation.php">Consultation</a></nav>
<div class="category-bar"><a href="products.php">Shop All</a><a href="products.php">Pain Relief</a><a href="products.php">Cold & Flu</a><a href="products.php">Vitamins</a><a href="services.php">Clinic Services</a></div>

<main class="wrapper">

<div class="breadcrumb"><a href="index.php">Home</a> / Book Appointment</div>
<h1 class="page-title">Book Appointment</h1>
<p class="page-lead">Choose a date and time to visit Mahube Pharmacy.</p>

<section class="content-card form">
    <div id="appointmentMsg" class="msg"></div>

    <form onsubmit="submitDemo('appointmentMsg','Appointment booked successfully! Pharmacy staff can view it on the staff dashboard.'); return false;">
        <label>Full Name</label>
        <input type="text" placeholder="Enter your full name" required>

        <label>Phone Number</label>
        <input type="text" placeholder="Enter your phone number" required>

        <label>Appointment Type</label>
        <select required>
            <option>Prescription Collection</option>
            <option>Consultation</option>
            <option>Medication Advice</option>
            <option>General Inquiry</option>
        </select>

        <label>Date</label>
        <input type="date" required>

        <label>Time</label>
        <input type="time" required>

        <button class="btn" style="width:100%">Book Appointment</button>
    </form>
</section>

</main>
<footer class="footer"><span>Mahube Pharmacy © 2026</span><div class="footer-links"><a href="about.php">About</a><a href="services.php">Services</a><a href="products.php">Products</a><a href="contact.php">Contact</a></div></footer>
<script src="app.js"></script>
</body>
</html>