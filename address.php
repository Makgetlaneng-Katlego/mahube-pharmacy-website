<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';
$message_type = '';

// Database config
$db_host = '127.0.0.1';
$db_port = 3307;
$db_name = 'pharmacy';
$db_user = 'root';
$db_pass = 'm4kg3tl4n3ng';

try {
    $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// Fetch existing address (if any)
$address = null;
$stmt = $pdo->prepare("SELECT * FROM address WHERE user_id = ? LIMIT 1");
$stmt->execute([$user_id]);
$address = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_address'])) {
    $street_address = trim($_POST['street_address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $address_name = trim($_POST['address_name'] ?? '');
    $delivery_instruction = trim($_POST['delivery_instruction'] ?? '');

    // Basic validation
    if (empty($street_address) || empty($city) || empty($province) || empty($postal_code)) {
        $message = 'Please fill in all required fields (street, city, province, postal code).';
        $message_type = 'error';
    } else {
        try {
            if ($address) {
                // Update existing address
                $sql = "UPDATE address SET 
                            street_address = :street,
                            city = :city,
                            province = :province,
                            postal_code = :postal,
                            address_name = :name,
                            delivery_instruction = :instruction
                        WHERE user_id = :user_id";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':street' => $street_address,
                    ':city' => $city,
                    ':province' => $province,
                    ':postal' => $postal_code,
                    ':name' => $address_name,
                    ':instruction' => $delivery_instruction,
                    ':user_id' => $user_id
                ]);
                $message = 'Address updated successfully!';
                $message_type = 'success';
            } else {
                // Insert new address
                $sql = "INSERT INTO address (user_id, street_address, city, province, postal_code, address_name, delivery_instruction)
                        VALUES (:user_id, :street, :city, :province, :postal, :name, :instruction)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':user_id' => $user_id,
                    ':street' => $street_address,
                    ':city' => $city,
                    ':province' => $province,
                    ':postal' => $postal_code,
                    ':name' => $address_name,
                    ':instruction' => $delivery_instruction
                ]);
                $message = 'Address added successfully!';
                $message_type = 'success';
            }
            // Refresh address data
            $stmt = $pdo->prepare("SELECT * FROM address WHERE user_id = ? LIMIT 1");
            $stmt->execute([$user_id]);
            $address = $stmt->fetch(PDO::FETCH_ASSOC);
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
<title>Manage Address - Mahube Pharmacy</title>
<link rel="stylesheet" href="style.css">
<style>
    .address-form label { display: block; margin-top: 15px; font-weight: bold; }
    .address-form input, .address-form textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; }
    .address-form .required { color: red; }
    .btn-group { margin-top: 20px; display: flex; gap: 10px; }
</style>
</head>
<body>
<div class="top-strip"><span>Mahube Pharmacy online health store</span><span>Affordable healthcare products and pharmacist support</span></div>
<header class="main-header">
<a href="index.php"><img class="logo" src="images/logo.svg" alt="Mahube Pharmacy"></a>
<div class="searchbar"><input placeholder="Search..."><button>🔍</button></div>
<div class="header-actions">
    <span>Hello, <?php echo htmlspecialchars($_SESSION['fullname']); ?></span>
    <a href="logout.php">Logout</a>
    <a class="action-icon" href="profile.php">👤</a>
    <a class="action-icon" href="cart.php">🛒<span class="count cart-count">0</span></a>
</div>
</header>
<nav class="navbar"><a href="index.php">Home</a><a href="products.php">Products</a><a href="about.php">About</a><a href="services.php">Services</a><a href="prescription.php">Prescriptions</a><a href="consultation.php">Consultation</a></nav>
<div class="category-bar"><a href="products.php">Shop All</a><a href="products.php">Pain Relief</a><a href="products.php">Cold & Flu</a><a href="products.php">Vitamins</a><a href="services.php">Clinic Services</a></div>

<main class="wrapper">
<div class="breadcrumb"><a href="index.php">Home</a> / <a href="profile.php">Profile</a> / Manage Address</div>
<h1 class="page-title"><?php echo $address ? 'Edit' : 'Add'; ?> Shipping Address</h1>

<?php if ($message): ?>
    <div class="msg <?php echo $message_type; ?> show"><?php echo $message; ?></div>
<?php endif; ?>

<div class="content-card">
    <form method="POST" class="address-form">
        <!-- Hidden field to identify submission -->
        <input type="hidden" name="submit_address" value="1">

        <label>Address Name <span class="required">*</span> <small>(e.g., Home, Work)</small></label>
        <input type="text" name="address_name" value="<?php echo htmlspecialchars($address['address_name'] ?? ''); ?>" placeholder="e.g., Home">

        <label>Street Address <span class="required">*</span></label>
        <input type="text" name="street_address" value="<?php echo htmlspecialchars($address['street_address'] ?? ''); ?>" required>

        <label>City <span class="required">*</span></label>
        <input type="text" name="city" value="<?php echo htmlspecialchars($address['city'] ?? ''); ?>" required>

        <label>Province <span class="required">*</span></label>
        <select name="province" required>
            <option value="">Select Province</option>
            <?php
            $provinces = ['Eastern Cape', 'Free State', 'Gauteng', 'KwaZulu-Natal', 'Limpopo', 'Mpumalanga', 'North West', 'Northern Cape', 'Western Cape'];
            $current = $address['province'] ?? '';
            foreach ($provinces as $prov) {
                $selected = ($prov === $current) ? 'selected' : '';
                echo "<option value=\"$prov\" $selected>$prov</option>";
            }
            ?>
        </select>

        <label>Postal Code <span class="required">*</span></label>
        <input type="text" name="postal_code" value="<?php echo htmlspecialchars($address['postal_code'] ?? ''); ?>" required>

        <label>Delivery Instruction <small>(optional)</small></label>
        <textarea name="delivery_instruction" rows="3" placeholder="e.g., Leave at the gate, ring bell, etc."><?php echo htmlspecialchars($address['delivery_instruction'] ?? ''); ?></textarea>

        <div class="btn-group">
            <button type="submit" class="btn" style="background:#2a9d8f;">💾 Save Address</button>
            <a href="profile.php" class="btn" style="background:#6c757d;">Cancel</a>
        </div>
    </form>
</div>

</main>
<footer class="footer"><span>Mahube Pharmacy © 2026</span><div class="footer-links"><a href="about.php">About</a><a href="services.php">Services</a><a href="products.php">Products</a><a href="contact.php">Contact</a></div></footer>
<script src="app.js"></script>
</body>
</html>