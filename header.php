<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isLoggedIn = isset($_SESSION['user_id']);
$userName   = $isLoggedIn ? $_SESSION['fullname'] : '';
$current    = basename($_SERVER['PHP_SELF']);
?>
<div class="top-strip">
    <span>Order online, collect in store - skip the queue.</span>
    <span><i class="fa-solid fa-phone"></i>012 000 0000</span>
</div>
<header class="main-header">
    <a class="brand" href="index.php">
        <img src="images/logo-mark.png" alt="Mahube Pharmacy">
        <span class="brand-text">Mahube<b>Pharmacy</b></span>
    </a>
    <form class="searchbar" action="products.php" method="get" onsubmit="return (typeof siteSearch === 'function') ? siteSearch(this) : true;" role="search">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input name="q" placeholder="Search medicines, vitamins, brands" aria-label="Search products">
        <button type="submit">Search</button>
    </form>
    <div class="header-actions">
        <?php if ($isLoggedIn): ?>
            <a class="auth-link" href="profile.php">Hello, <?php echo htmlspecialchars($userName); ?></a>
            <a class="auth-link" href="logout.php">Logout</a>
        <?php else: ?>
            <a class="auth-link" data-guest href="login.php">Log in</a>
            <a class="btn-small" data-guest href="register.php">Create account</a>
        <?php endif; ?>
        <a class="action-icon" href="profile.php" aria-label="My profile"><i class="fa-regular fa-circle-user"></i></a>
        <a class="action-icon" href="cart.php" aria-label="Cart"><i class="fa-solid fa-bag-shopping"></i><span class="count cart-count">0</span></a>
    </div>
</header>

<nav class="navbar">
    <a href="index.php" class="<?php echo $current === 'index.php' ? 'active' : ''; ?>">Home</a>
    <a href="products.php" class="<?php echo $current === 'products.php' ? 'active' : ''; ?>">Products</a>
    <a href="services.php" class="<?php echo $current === 'services.php' ? 'active' : ''; ?>">Services</a>
   <a href="about.php" class="<?php echo $current === 'about.php' ? 'active' : ''; ?>">About</a>
</nav>

<div class="category-bar">
    <a href="products.php">Shop All</a>
    <a href="products.php">Pain Relief</a>
    <a href="products.php">Cold &amp; Flu</a>
    <a href="products.php">Vitamins</a>
    <a href="services.php">Clinic Services</a>
</div>