<?php
session_start();
$error = '';
$success = '';

// Database configuration
$db_host = '127.0.0.1';
$db_port = 3307;
$db_name = 'pharmacy';
$db_user = 'root';
$db_pass = 'm4kg3tl4n3ng';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($fullname) || empty($email) || empty($phone) || empty($password) || empty($confirm)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        try {
            $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Check if email already exists
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                $error = 'Email already registered. Please login.';
            } else {
                // Hash password
               
                $role = 'customer'; // default role

                $sql = "INSERT INTO users (fullname, email, password, role, phone_number) 
                        VALUES (:fullname, :email, :password, :role, :phone)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':fullname' => $fullname,
                    ':email' => $email,
                    ':password' => $password, 
                    ':role' => $role,
                    ':phone' => $phone
                ]);

                $success = 'Registration successful! You can now login.';
                // Clear POST data
                $fullname = $email = $phone = '';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
} else {
    $fullname = $email = $phone = '';
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register - Mahube Pharmacy</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-clean">
    <img src="images/logo.svg" class="auth-logo" alt="Mahube Pharmacy">
    <h1>Create Account</h1>
    <p class="auth-subtitle">Join Mahube Pharmacy and take control of your health.</p>

    <?php if ($error): ?>
        <div class="msg error show" style="background:#f8d7da;color:#721c24;padding:10px;border-radius:8px;margin-bottom:20px;"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($success): ?>
        <div class="msg success show" style="background:#d4edda;color:#155724;padding:10px;border-radius:8px;margin-bottom:20px;"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form class="auth-form" method="POST" action="">
        <label>Full Name</label>
        <div class="input-wrap"><span>👤</span><input name="fullname" type="text" placeholder="Enter your full name" value="<?php echo htmlspecialchars($fullname); ?>" required></div>

        <label>Email Address</label>
        <div class="input-wrap"><span>✉️</span><input name="email" type="email" placeholder="Enter your email address" value="<?php echo htmlspecialchars($email); ?>" required></div>

        <label>Phone Number</label>
        <div class="input-wrap"><span>📞</span><input name="phone" type="text" placeholder="Enter your phone number" value="<?php echo htmlspecialchars($phone); ?>" required></div>

        <label>Password</label>
        <div class="input-wrap"><span>🔒</span><input name="password" type="password" placeholder="Create a password" required></div>

        <label>Confirm Password</label>
        <div class="input-wrap"><span>🔒</span><input name="confirm_password" type="password" placeholder="Confirm your password" required></div>

        <div class="terms-row"><input type="checkbox" required><p>I agree to the <a href="#">Terms & Conditions</a> and <a href="#">Privacy Policy</a></p></div>

        <button class="btn auth-main-btn" type="submit">Register</button>

        <div class="auth-bottom-link">Already have an account? <a href="login.php">Login</a></div>
    </form>
</div>
<script src="app.js"></script>
</body>
</html>