<?php
session_start();
$error = '';

// Database configuration (use your actual credentials)
$db_host = '127.0.0.1';
$db_port = 3307;
$db_name = 'pharmacy';
$db_user = 'root';
$db_pass = 'm4kg3tl4n3ng';

// If already logged in, go to index
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter email and password.';
    } else {
        try {
            $pdo = new PDO("mysql:host=$db_host;port=$db_port;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $pdo->prepare("SELECT user_id, fullname, email, password, role, phone_number FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Changed from password_verify() to a direct plain text comparison
            if ($user && $password === $user['password']) {
                // Correct credentials – set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['phone_number'] = $user['phone_number'];

                // Redirect to homepage
                $_SESSION['user_email'] = $user['email'];  // store email for cart
                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login - Mahube Pharmacy</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-clean">
    <img src="images/logo.svg" class="auth-logo" alt="Mahube Pharmacy">
    <h1>Welcome Back</h1>
    <p class="auth-subtitle">Login to access your Mahube Pharmacy account.</p>

    <?php if ($error): ?>
        <div class="msg error show" style="background:#f8d7da;color:#721c24;padding:10px;border-radius:8px;margin-bottom:20px;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form class="auth-form" method="POST" action="">
        <label>Email Address</label>
        <div class="input-wrap"><span>✉️</span><input name="email" type="email" placeholder="Enter your email address" required></div>

        <label>Password</label>
        <div class="input-wrap"><span>🔒</span><input name="password" type="password" placeholder="Enter your password" required></div>

        <button class="btn auth-main-btn" type="submit">Login</button>

        <div class="auth-bottom-link">Don’t have an account? <a href="register.php">Register</a></div>
        <div class="auth-bottom-link"><a href="index.php">Back to Home</a></div>
    </form>
</div>
<script src="app.js"></script>
</body>
</html>