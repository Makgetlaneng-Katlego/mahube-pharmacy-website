<?php
// ==================== DATABASE CONFIGURATION ====================
$db_host = '127.0.0.1';
$db_port = 3307;
$db_name = 'pharmacy';
$db_user = 'root';
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
                
                // Clear form fields
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
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Prescription Upload - Mahube Pharmacy</title>
<link rel="stylesheet" href="style.css">
<style>
    .lookup-section {
        margin-top: 40px;
        border-top: 1px solid #ddd;
        padding-top: 30px;
    }
    .response-card {
        background: #f8f9fa;
        border-left: 4px solid #2a9d8f;
        padding: 15px;
        margin-top: 15px;
        border-radius: 8px;
    }
    .prescription-detail {
        margin-bottom: 10px;
    }
    .prescription-detail strong {
        width: 140px;
        display: inline-block;
    }
    .id-box {
        background: #e8f4f0;
        border: 2px dashed #2a9d8f;
        padding: 12px;
        margin: 15px 0;
        text-align: center;
        border-radius: 10px;
    }
    .id-number {
        font-size: 32px;
        font-weight: bold;
        color: #2a9d8f;
        letter-spacing: 2px;
    }
    .id-label {
        font-size: 14px;
        text-transform: uppercase;
        color: #555;
    }
</style>
</head>
<body>
<div class="top-strip"><span>Mahube Pharmacy online health store</span><span>Healthcare products and pharmacist support</span></div>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>About - Mahube Pharmacy</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'header.php'; ?>
<nav class="navbar"><a href="index.php">Home</a><a href="products.php">Products</a><a href="about.php">About</a><a href="services.php">Services</a><a href="prescription.php" class="active">Prescriptions</a><a href="consultation.php">Consultation</a></nav>

<main class="wrapper">
<h1 class="page-title">Upload Prescription</h1>
<div class="hero" style="grid-template-columns:1fr 1fr">
    <div>
        <h1 style="font-size:42px">Send your prescription online</h1>
        <p>Upload a prescription image or PDF and add notes for the pharmacist.</p>
    </div>
    <img src="images/prescription_photo.svg" alt="Prescription upload">
</div>

<!-- Upload Form -->
<section class="content-card form">
    <?php if ($message): ?>
        <div id="prescriptionMsg" class="msg <?php echo $message_type; ?> show">
            <?php echo $message; ?>
            <?php if ($new_prescription_id): ?>
                <div class="id-box">
                    <div class="id-label">Your Prescription ID</div>
                    <div class="id-number"><?php echo $new_prescription_id; ?></div>
                    <p style="margin-top:8px; font-size:14px;">
                        📌 Save this ID. Use it to check the pharmacist's response below.
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

<!-- Lookup by Prescription ID -->
<section class="content-card lookup-section">
    <h2>Check Pharmacist Response</h2>
    <p>Enter your Prescription ID to see the pharmacist’s reply.</p>
    <form method="POST" action="">
        <input type="hidden" name="action" value="lookup">
        <div style="display: flex; gap: 10px;">
            <input type="number" name="prescription_id" placeholder="Prescription ID" style="flex:1;" required>
            <button type="submit" class="btn">Check Status</button>
        </div>
    </form>

    <?php if ($response_data): ?>
        <?php if (isset($response_data['error'])): ?>
            <div class="msg error show" style="margin-top:15px;"><?php echo $response_data['error']; ?></div>
        <?php else: ?>
            <div class="response-card">
                <h3>Prescription #<?php echo htmlspecialchars($_POST['prescription_id']); ?></h3>
                <div class="prescription-detail"><strong>Email:</strong> <?php echo htmlspecialchars($response_data['user_email']); ?></div>
                <div class="prescription-detail"><strong>Doctor:</strong> <?php echo htmlspecialchars($response_data['doctor_name']); ?></div>
                <div class="prescription-detail"><strong>Date Prescribed:</strong> <?php echo date('d M Y', strtotime($response_data['date_prescribed'])); ?></div>
                <div class="prescription-detail"><strong>Type:</strong> <?php echo htmlspecialchars($response_data['type']); ?></div>
                <div class="prescription-detail"><strong>Your Notes:</strong> <?php echo nl2br(htmlspecialchars($response_data['notes'] ?: 'None')); ?></div>
                <div class="prescription-detail"><strong>File:</strong> <?php echo $response_data['has_file'] ? 'Uploaded (confidential)' : 'Missing'; ?></div>
                <div class="prescription-detail" style="margin-top:10px; border-top:1px solid #ddd; padding-top:10px;">
                    <strong style="color:#2a9d8f;">Pharmacist's Response:</strong><br>
                    <?php echo !empty($response_data['response']) ? nl2br(htmlspecialchars($response_data['response'])) : '<em>Awaiting response from the pharmacy.</em>'; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>

</main>
<footer class="footer"><span>Mahube Pharmacy © 2026</span><div class="footer-links"><a href="about.php">About</a><a href="services.php">Services</a><a href="products.php">Products</a><a href="contact.php">Contact</a></div></footer>
<script src="app.js"></script>
</body>
</html>