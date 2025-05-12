 
<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

$token = $_GET['token'] ?? '';
if (!$token) {
    redirect('login.php');
}

$query = "SELECT * FROM password_resets WHERE token = ? AND created_at > NOW() - INTERVAL 1 HOUR";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $token);
$stmt->execute();
$reset = $stmt->get_result()->fetch_assoc();

if (!$reset) {
    $error = "Invalid or expired token.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $reset) {
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $query = "UPDATE users SET password = ? WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ss', $password, $reset['email']);
    $stmt->execute();

    $query = "DELETE FROM password_resets WHERE token = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $token);
    $stmt->execute();

    $success = "Password reset successfully. <a href='login.php'>Login</a>";
}
?>

<h2>Reset Password</h2>
<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php elseif (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php else: ?>
    <form method="POST">
        <div class="mb-3">
            <label for="password" class="form-label">New Password</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">Reset Password</button>
    </form>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>