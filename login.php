<?php
session_start();
$error = '';

// Database configuration
$db_host = '192.168.0.128';
$db_port = 3307;
$db_name = 'pharmacy';
$db_user = 'Katlego';
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

            if ($user) {
                $stored = $user['password'];
                // Check if the stored password is a hash (starts with $)
                $isHashed = substr($stored, 0, 1) === '$';
                $ok = false;

                if ($isHashed) {
                    // Verify modern bcrypt/argon2 hash
                    $ok = password_verify($password, $stored);
                } else {
                    // Legacy plaintext fallback
                    $ok = hash_equals($stored, $password);
                    if ($ok) {
                        // Transparently upgrade the user to bcrypt
                        $newHash = password_hash($password, PASSWORD_BCRYPT);
                        $upd = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                        $upd->execute([$newHash, $user['user_id']]);
                    }
                }

                if ($ok) {
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['fullname'] = $user['fullname'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['phone_number'] = $user['phone_number'];
                    $_SESSION['user_email'] = $user['email'];

                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'That email or password didn\'t match. Please try again.';
                }
            } else {
                $error = 'That email or password didn\'t match. Please try again.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Log in - Mahube Pharmacy</title>
<link rel="icon" type="image/png" href="images/favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="theme.css">
</head>
<body data-page="login">
<div class="auth-shell">
<div class="auth-card">
    <a class="brand" href="index.php"><img src="images/logo-mark.png" alt=""><span class="brand-text">Mahube<b>Pharmacy</b></span></a>
    <h1>Welcome back</h1>
    <p class="sub">Log in to see your orders, prescriptions and reminders.</p>

    <?php if ($error): ?>
        <div class="auth-alert show"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="" novalidate>
        <div class="field" data-f="email">
            <label for="loginEmail">Email</label>
            <div class="field-box">
                <i class="lead fa-regular fa-envelope"></i>
                <input id="loginEmail" type="email" name="email" placeholder="you@example.com" autocomplete="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
            </div>
            <div class="field-error"></div>
        </div>
        <div class="field" data-f="password">
            <label for="loginPass">Password</label>
            <div class="field-box">
                <i class="lead fa-solid fa-lock"></i>
                <input id="loginPass" type="password" name="password" placeholder="Your password" autocomplete="current-password" required>
                <button type="button" class="eye" onclick="togglePw(this)" aria-label="Show or hide password"><i class="fa-regular fa-eye"></i></button>
            </div>
            <div class="field-error"></div>
        </div>

        <div class="row-between">
            <label class="check" style="margin:0"><input type="checkbox" name="remember"> Remember me</label>
            <a href="contact.php">Need help signing in?</a>
        </div>
        <button class="auth-main-btn" type="submit">Log in</button>
    </form>

    <p class="auth-switch">New to Mahube? <a href="register.php">Create an account</a></p>
</div>
<a class="auth-back" href="index.php"><i class="fa-solid fa-arrow-left"></i> Back to the shop</a>
</div>
<script>
function togglePw(btn){
    var input = btn.parentNode.querySelector('input');
    var icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fa-regular fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fa-regular fa-eye';
    }
}
</script>
</body>
</html>