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
$db_host = '192.168.0.128';
$db_port = 3307;
$db_name = 'pharmacy';
$db_user = 'Katlego';
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

    if (empty($street_address) || empty($city) || empty($province) || empty($postal_code)) {
        $message = 'Please fill in all required fields (street, city, province, postal code).';
        $message_type = 'error';
    } else {
        try {
            if ($address) {
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
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Address - Mahube Pharmacy</title>
<link rel="icon" type="image/png" href="images/favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="theme.css">
<style>
    .required { color: #b3261e; }
    .btn-group { margin-top: 20px; display: flex; gap: 10px; flex-wrap: wrap; }
    .msg.error   { background: #fbecea; color: #9b2c20; }
    .msg.success { background: #eef5e9; color: #2c5a16; }
</style>
</head>
<body data-page="address">
<?php include 'header.php'; ?>

<main class="wrapper">
    <div class="breadcrumb"><a href="index.php">Home</a> / <a href="profile.php">Profile</a> / Manage Address</div>
    <h1 class="page-title"><?php echo $address ? 'Edit' : 'Add'; ?> Shipping Address</h1>

    <?php if ($message): ?>
        <div class="msg <?php echo $message_type; ?> show"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <section class="content-card form">
        <form method="POST">
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
                <button type="submit" class="btn"><i class="fa-solid fa-floppy-disk"></i> Save address</button>
                <a href="profile.php" class="btn ghost">Cancel</a>
            </div>
        </form>
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
<script src="site.js"></script>
</body>
</html>