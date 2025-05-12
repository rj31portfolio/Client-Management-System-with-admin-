 
<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user) {
        $token = bin2hex(random_bytes(32));
        $query = "INSERT INTO password_resets (email, token) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ss', $email, $token);
        $stmt->execute();

        $resetLink = "http://yourdomain.com/auth/reset_password.php?token=$token";
        sendEmail($email, "Password Reset", "Click here to reset your password: $resetLink");
        $success = "Password reset link sent to your email.";
    } else {
        $error = "Email not found.";
    }
}
?>

<h2>Forgot Password</h2>
<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php elseif (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>
<form method="POST">
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required>
    </div>
    <button type="submit" class="btn btn-primary">Send Reset Link</button>
</form>

<?php require_once '../includes/footer.php'; ?>