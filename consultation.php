<?php
session_start();

// ==================== DATABASE CONFIGURATION ====================
$db_host = '127.0.0.1';
$db_port = 3307;
$db_name = 'pharmacy';
$db_user = 'root';
$db_pass = 'm4kg3tl4n3ng';

$message = '';
$message_type = '';
$response_data = null;
$new_consultation_id = null;

// Handle new consultation submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $consultation_type = trim($_POST['consultation_type'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $preferred_datetime = $_POST['preferred_datetime'] ?? '';

    // Validate required fields
    if (empty($fullname) || empty($email) || empty($phone_number) || empty($consultation_type) || empty($description)) {
        $message = 'Please fill in all required fields.';
        $message_type = 'error';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $message_type = 'error';
    } else {
        $preferred_date = !empty($preferred_datetime) ? date('Y-m-d', strtotime($preferred_datetime)) : null;
        try {
            $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $sql = "INSERT INTO consultation (fullname, email, phone_number, preferred_date, consultation_type, description, response) 
                    VALUES (:fullname, :email, :phone_number, :preferred_date, :consultation_type, :description, '')";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':fullname' => $fullname,
                ':email'    => $email,
                ':phone_number' => $phone_number,
                ':preferred_date' => $preferred_date,
                ':consultation_type' => $consultation_type,
                ':description' => $description
            ]);
            $new_consultation_id = $pdo->lastInsertId();
            $message = "Your consultation has been submitted successfully!";
            $message_type = 'success';
            // Clear form fields
            $fullname = $email = $phone_number = $consultation_type = $description = $preferred_datetime = '';
        } catch (PDOException $e) {
            $message = 'Database error: ' . $e->getMessage();
            $message_type = 'error';
        }
    }
}

// Handle response lookup by consultation ID
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'lookup') {
    $consultation_id = intval($_POST['consultation_id'] ?? 0);
    if ($consultation_id <= 0) {
        $response_data = ['error' => 'Please enter a valid Consultation ID.'];
    } else {
        try {
            $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $stmt = $pdo->prepare("SELECT fullname, email, consultation_type, description, response, preferred_date FROM consultation WHERE consultation_id = :id");
            $stmt->execute([':id' => $consultation_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($result) {
                $response_data = $result;
            } else {
                $response_data = ['error' => 'No consultation found with that ID.'];
            }
        } catch (PDOException $e) {
            $response_data = ['error' => 'Database error: ' . $e->getMessage()];
        }
    }
}

// Initial form values
$fullname = $email = $phone_number = $consultation_type = $description = $preferred_datetime = '';
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Consultation - Mahube Pharmacy</title>
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
    .consultation-detail {
        margin-bottom: 10px;
    }
    .consultation-detail strong {
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
<?php include 'header.php'; ?>
<nav class="navbar"><a href="index.php">Home</a><a href="products.php">Products</a><a href="about.php">About</a><a href="services.php">Services</a><a href="prescription.php">Prescriptions</a><a href="consultation.php" class="active">Consultation</a></nav>

<main class="wrapper">
<h1 class="page-title">Pharmacist Consultation</h1>
<div class="hero" style="grid-template-columns:1fr 1fr">
    <div>
        <h1 style="font-size:42px">Ask our pharmacist</h1>
        <p>Request advice about medication, symptoms or prescription instructions.</p>
    </div>
    <img src="images/consultation_photo.svg" alt="Consultation">
</div>

<!-- Submission Form -->
<section class="content-card form">
    <?php if ($message): ?>
        <div id="consultMsg" class="msg <?php echo $message_type; ?> show">
            <?php echo $message; ?>
            <?php if ($new_consultation_id): ?>
                <div class="id-box">
                    <div class="id-label">Your Consultation ID</div>
                    <div class="id-number"><?php echo $new_consultation_id; ?></div>
                    <p style="margin-top:8px; font-size:14px;">
                        📌 Save this ID. Use it to check the pharmacist's response below.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div id="consultMsg" class="msg"></div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="hidden" name="action" value="submit">

        <label>Full Name *</label>
        <input type="text" name="fullname" placeholder="Enter your full name" value="<?php echo htmlspecialchars($fullname); ?>" required>

        <label>Email Address *</label>
        <input type="email" name="email" placeholder="Enter your email address" value="<?php echo htmlspecialchars($email); ?>" required>

        <label>Phone Number *</label>
        <input type="text" name="phone_number" placeholder="Enter your phone number" value="<?php echo htmlspecialchars($phone_number); ?>" required>

        <label>Consultation Type *</label>
        <select name="consultation_type" required>
            <option value="Medication Advice" <?php echo $consultation_type == 'Medication Advice' ? 'selected' : ''; ?>>Medication Advice</option>
            <option value="Prescription Question" <?php echo $consultation_type == 'Prescription Question' ? 'selected' : ''; ?>>Prescription Question</option>
            <option value="Side Effects" <?php echo $consultation_type == 'Side Effects' ? 'selected' : ''; ?>>Side Effects</option>
            <option value="General Health Question" <?php echo $consultation_type == 'General Health Question' ? 'selected' : ''; ?>>General Health Question</option>
        </select>

        <label>Your Question *</label>
        <textarea name="description" placeholder="Describe what you need help with" required><?php echo htmlspecialchars($description); ?></textarea>

        <label>Preferred Appointment Time (optional)</label>
        <input type="datetime-local" name="preferred_datetime" value="<?php echo htmlspecialchars($preferred_datetime); ?>">

        <button class="btn" style="width:100%">Request Consultation</button>
    </form>
</section>

<!-- Lookup by Consultation ID -->
<section class="content-card lookup-section">
    <h2>Check Pharmacist Response</h2>
    <p>Enter your Consultation ID to see the pharmacist’s reply.</p>
    <form method="POST" action="">
        <input type="hidden" name="action" value="lookup">
        <div style="display: flex; gap: 10px;">
            <input type="number" name="consultation_id" placeholder="Consultation ID" style="flex:1;" required>
            <button type="submit" class="btn">Check Status</button>
        </div>
    </form>

    <?php if ($response_data): ?>
        <?php if (isset($response_data['error'])): ?>
            <div class="msg error show" style="margin-top:15px;"><?php echo $response_data['error']; ?></div>
        <?php else: ?>
            <div class="response-card">
                <h3>Consultation #<?php echo htmlspecialchars($_POST['consultation_id']); ?></h3>
                <div class="consultation-detail"><strong>Full Name:</strong> <?php echo htmlspecialchars($response_data['fullname']); ?></div>
                <div class="consultation-detail"><strong>Email:</strong> <?php echo htmlspecialchars($response_data['email']); ?></div>
                <div class="consultation-detail"><strong>Type:</strong> <?php echo htmlspecialchars($response_data['consultation_type']); ?></div>
                <div class="consultation-detail"><strong>Preferred Date:</strong> <?php echo $response_data['preferred_date'] ? date('d M Y', strtotime($response_data['preferred_date'])) : 'Not specified'; ?></div>
                <div class="consultation-detail"><strong>Your Question:</strong> <?php echo nl2br(htmlspecialchars($response_data['description'])); ?></div>
                <div class="consultation-detail" style="margin-top:10px; border-top:1px solid #ddd; padding-top:10px;">
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