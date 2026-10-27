<?php
session_start();


$db_host = '192.168.0.128';
$db_port = 3307;
$db_name = 'pharmacy';
$db_user = 'Katlego';
$db_pass = 'm4kg3tl4n3ng';

$message = '';
$message_type = '';
$response_data = null;
$new_consultation_id = null;


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $consultation_type = trim($_POST['consultation_type'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $preferred_datetime = $_POST['preferred_datetime'] ?? '';

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
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Consultation - Mahube Pharmacy</title>
<link rel="icon" type="image/png" href="images/favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="theme.css">
<style>
    .lookup-section { margin-top: 40px; border-top: 1px solid #ddd; padding-top: 30px; }
    .response-card { background: #f8f9fa; border-left: 4px solid var(--green, #3a7d1e); padding: 15px; margin-top: 15px; border-radius: 8px; }
    .consultation-detail { margin-bottom: 10px; }
    .consultation-detail strong { width: 140px; display: inline-block; }
    .id-box { background: #eef5e9; border: 2px dashed var(--green, #3a7d1e); padding: 12px; margin: 15px 0; text-align: center; border-radius: 10px; }
    .id-number { font-size: 32px; font-weight: bold; color: var(--green, #3a7d1e); letter-spacing: 2px; }
    .id-label { font-size: 14px; text-transform: uppercase; color: #555; }
</style>
</head>
<body data-page="consultation">
<?php include 'header.php'; ?>

<main class="wrapper">
    <h1 class="page-title">Pharmacist Consultation</h1>

    <div class="hero" style="grid-template-columns:1fr 1fr">
        <div>
            <h1 style="font-size:42px">Ask our pharmacist</h1>
            <p>Request advice about medication, symptoms or prescription instructions.</p>
        </div>
        <img src="images/consultation_photo.svg" alt="Consultation">
    </div>

    <section class="content-card form">
        <?php if ($message): ?>
            <div id="consultMsg" class="msg <?php echo $message_type; ?> show">
                <?php echo $message; ?>
                <?php if ($new_consultation_id): ?>
                    <div class="id-box">
                        <div class="id-label">Your Consultation ID</div>
                        <div class="id-number"><?php echo $new_consultation_id; ?></div>
                        <p style="margin-top:8px; font-size:14px;">
                            <i class="fa-solid fa-thumbtack"></i> Save this ID. Use it to check the pharmacist's response below.
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