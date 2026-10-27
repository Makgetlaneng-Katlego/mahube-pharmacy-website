<?php
// ==================== DATABASE CONFIGURATION ====================
$db_host = '192.168.0.128';
$db_port = 3307;
$db_name = 'pharmacy';
$db_user = 'Katlego';
$db_pass = 'm4kg3tl4n3ng';

$message = '';
$message_type = '';
$new_prescription_id = null;
$response_data = null;

// Handle prescription upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload') {
    $user_email = trim($_POST['user_email'] ?? '');
    $doctor_name = trim($_POST['doctor_name'] ?? '');
    $date_prescribed = $_POST['date_prescribed'] ?? '';
    $type = trim($_POST['type'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (empty($user_email) || empty($doctor_name) || empty($date_prescribed) || empty($type) || !isset($_FILES['prescription_file']) || $_FILES['prescription_file']['error'] != UPLOAD_ERR_OK) {
        $message = 'Please fill in all required fields and upload a valid file.';
        $message_type = 'error';
    } else {
        $file_tmp = $_FILES['prescription_file']['tmp_name'];
        $file_data = file_get_contents($file_tmp);

        if ($_FILES['prescription_file']['size'] > 5 * 1024 * 1024) {
            $message = 'File size exceeds 5MB limit.';
            $message_type = 'error';
        } else {
            try {
                $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $sql = "INSERT INTO prescription (user_email, doctor_name, date_prescribed, type, notes, file_data, response) 
                        VALUES (:user_email, :doctor_name, :date_prescribed, :type, :notes, :file_data, '')";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':user_email' => $user_email,
                    ':doctor_name' => $doctor_name,
                    ':date_prescribed' => $date_prescribed,
                    ':type' => $type,
                    ':notes' => $notes,
                    ':file_data' => $file_data
                ]);
                $new_prescription_id = $pdo->lastInsertId();
                $message = 'Prescription uploaded successfully!';
                $message_type = 'success';

                $user_email = $doctor_name = $date_prescribed = $type = $notes = '';
            } catch (PDOException $e) {
                $message = 'Database error: ' . $e->getMessage();
                $message_type = 'error';
            }
        }
    }
}

// Handle response lookup by prescription ID
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'lookup') {
    $prescription_id = intval($_POST['prescription_id'] ?? 0);
    if ($prescription_id <= 0) {
        $response_data = ['error' => 'Please enter a valid Prescription ID.'];
    } else {
        try {
            $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $pdo->prepare("SELECT user_email, doctor_name, date_prescribed, type, notes, response, file_data IS NOT NULL as has_file FROM prescription WHERE id = :id");
            $stmt->execute([':id' => $prescription_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $response_data = $result;
            } else {
                $response_data = ['error' => 'No prescription found with that ID.'];
            }
        } catch (PDOException $e) {
            $response_data = ['error' => 'Database error: ' . $e->getMessage()];
        }
    }
}

// Initial empty values
$user_email = $doctor_name = $date_prescribed = $type = $notes = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Prescription Upload - Mahube Pharmacy</title>
<link rel="icon" type="image/png" href="images/favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="theme.css">
<style>
    .lookup-section { margin-top: 40px; border-top: 1px solid #ddd; padding-top: 30px; }
    .response-card { background: #f8f9fa; border-left: 4px solid var(--green, #3a7d1e); padding: 15px; margin-top: 15px; border-radius: 8px; }
    .prescription-detail { margin-bottom: 10px; }
    .prescription-detail strong { width: 140px; display: inline-block; }
    .id-box { background: #eef5e9; border: 2px dashed var(--green, #3a7d1e); padding: 12px; margin: 15px 0; text-align: center; border-radius: 10px; }
    .id-number { font-size: 32px; font-weight: bold; color: var(--green, #3a7d1e); letter-spacing: 2px; }
    .id-label { font-size: 14px; text-transform: uppercase; color: #555; }
</style>
</head>
<body data-page="prescription">
<?php include 'header.php'; ?>

<main class="wrapper">
    <h1 class="page-title">Upload Prescription</h1>

    <div class="hero" style="grid-template-columns:1fr 1fr">
        <div>
            <h1 style="font-size:42px">Send your prescription online</h1>
            <p>Upload a prescription image or PDF and add notes for the pharmacist.</p>
        </div>
        <img src="images/prescription_photo.svg" alt="Prescription upload">
    </div>

    <section class="content-card form">
        <?php if ($message): ?>
            <div id="prescriptionMsg" class="msg <?php echo $message_type; ?> show">
                <?php echo $message; ?>
                <?php if ($new_prescription_id): ?>
                    <div class="id-box">
                        <div class="id-label">Your Prescription ID</div>
                        <div class="id-number"><?php echo $new_prescription_id; ?></div>
                        <p style="margin-top:8px; font-size:14px;">
                            <i class="fa-solid fa-thumbtack"></i> Save this ID. Use it to check the pharmacist's response below.
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div id="prescriptionMsg" class="msg"></div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload">

            <label>Your Email *</label>
            <input type="email" name="user_email" placeholder="Enter your email address" value="<?php echo htmlspecialchars($user_email); ?>" required>

            <label>Doctor's Name *</label>
            <input type="text" name="doctor_name" placeholder="Full name of the prescribing doctor" value="<?php echo htmlspecialchars($doctor_name); ?>" required>

            <label>Date Prescribed *</label>
            <input type="date" name="date_prescribed" value="<?php echo htmlspecialchars($date_prescribed); ?>" required>

            <label>Prescription Type *</label>
            <select name="type" required>
                <option value="New Prescription" <?php echo $type == 'New Prescription' ? 'selected' : ''; ?>>New Prescription</option>
                <option value="Refill" <?php echo $type == 'Refill' ? 'selected' : ''; ?>>Refill</option>
                <option value="Chronic Medication" <?php echo $type == 'Chronic Medication' ? 'selected' : ''; ?>>Chronic Medication</option>
                <option value="Other" <?php echo $type == 'Other' ? 'selected' : ''; ?>>Other</option>
            </select>

            <label>Upload Prescription (image or PDF) *</label>
            <input type="file" name="prescription_file" accept="image/*,.pdf" required>

            <label>Notes for Pharmacist (optional)</label>
            <textarea name="notes" placeholder="Example: I need a refill for my monthly medication"><?php echo htmlspecialchars($notes); ?></textarea>

            <button class="btn" style="width:100%">Submit Prescription</button>
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