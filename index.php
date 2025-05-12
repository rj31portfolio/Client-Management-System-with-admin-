 
<?php
require_once 'includes/header.php';
require_once 'includes/functions.php';
?>

<h2>Welcome to Client Management System</h2>
<p>Manage your clients and subscriptions efficiently.</p>
<?php if (!isLoggedIn()): ?>
    <a href="auth/login.php" class="btn btn-primary">Login</a>
    <a href="auth/register.php" class="btn btn-secondary">Register</a>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>