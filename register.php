<?php
session_start();
$error = '';
$success = '';


$db_host = '192.168.0.128';
$db_port = 3307;
$db_name = 'pharmacy';
$db_user = 'Katlego';
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

            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                $error = 'Email already registered. Please login.';
            } else {
                $role = 'customer';

                // Hash the password with bcrypt before storing.
                // PASSWORD_BCRYPT (not PASSWORD_DEFAULT) so a future PHP
                // upgrade can't silently switch to argon2id and break the
                // Android app's bcrypt verifier.
                $passwordHash = password_hash($password, PASSWORD_BCRYPT);

                $sql = "INSERT INTO users (fullname, email, password, role, phone_number) 
                        VALUES (:fullname, :email, :password, :role, :phone)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':fullname' => $fullname,
                    ':email'    => $email,
                    ':password' => $passwordHash,
                    ':role'     => $role,
                    ':phone'    => $phone
                ]);

                $success = 'Registration successful! You can now log in.';
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
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create account - Mahube Pharmacy</title>
<link rel="icon" type="image/png" href="images/favicon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="style.css">
<link rel="stylesheet" href="theme.css">
</head>
<body data-page="register">
<div class="auth-shell">
<div class="auth-card">
    <a class="brand" href="index.php"><img src="images/logo-mark.png" alt=""><span class="brand-text">Mahube<b>Pharmacy</b></span></a>
    <h1>Create an account</h1>
    <p class="sub">Join Mahube Pharmacy and take control of your health.</p>

    <?php if ($error): ?>
        <div class="auth-alert show"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($success): ?>
        <div class="auth-alert show" style="background:#eef5e9;color:#2c5a16;"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="POST" action="" novalidate>
        <div class="field" data-f="fullname">
            <label for="regName">Full name</label>
            <div class="field-box">
                <i class="lead fa-regular fa-user"></i>
                <input id="regName" type="text" name="fullname" placeholder="Your full name" autocomplete="name" value="<?php echo htmlspecialchars($fullname); ?>" required>
            </div>
            <div class="field-error"></div>
        </div>
        <div class="field" data-f="email">
            <label for="regEmail">Email</label>
            <div class="field-box">
                <i class="lead fa-regular fa-envelope"></i>
                <input id="regEmail" type="email" name="email" placeholder="you@example.com" autocomplete="email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="field-error"></div>
        </div>
        <div class="field" data-f="phone">
            <label for="regPhone">Phone number</label>
            <div class="field-box">
                <i class="lead fa-solid fa-phone"></i>
                <input id="regPhone" type="tel" name="phone" placeholder="e.g. 071 234 5678" autocomplete="tel" value="<?php echo htmlspecialchars($phone); ?>" required>
            </div>
            <div class="field-error"></div>
        </div>
        <div class="field" data-f="password">
            <label for="regPass">Password</label>
            <div class="field-box">
                <i class="lead fa-solid fa-lock"></i>
                <input id="regPass" type="password" name="password" placeholder="At least 6 characters" autocomplete="new-password" required>
                <button type="button" class="eye" onclick="togglePw(this)" aria-label="Show or hide password"><i class="fa-regular fa-eye"></i></button>
            </div>
            <div class="field-error"></div>
        </div>
        <div class="field" data-f="confirm_password">
            <label for="regPass2">Confirm password</label>
            <div class="field-box">
                <i class="lead fa-solid fa-lock"></i>
                <input id="regPass2" type="password" name="confirm_password" placeholder="Re-enter your password" autocomplete="new-password" required>
                <button type="button" class="eye" onclick="togglePw(this)" aria-label="Show or hide password"><i class="fa-regular fa-eye"></i></button>
            </div>
            <div class="field-error"></div>
        </div>

        <label class="check"><input type="checkbox" required> I agree to the <a href="#">Terms &amp; Conditions</a> and <a href="#">Privacy Policy</a></label>

        <button class="auth-main-btn" type="submit">Create account</button>
    </form>

    <p class="auth-switch">Already have an account? <a href="login.php">Log in</a></p>
</div>
<a class="auth-back" href="index.php"><i class="fa-solid fa-arrow-left"></i> Back to the shop</a>
</div>
<script>
function togglePw(btn){
    var input = btn.parentNode.querySelector('input');
    var icon = btn.querySelector('i');
    if (input.tyqpe === 'password') {
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