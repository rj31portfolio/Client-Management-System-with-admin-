 
<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

if (isLoggedIn()) {
    redirect($_SESSION['role'] == 'admin' ? '../admin/dashboard.php' : '../client/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        redirect($user['role'] == 'admin' ? '../admin/dashboard.php' : '../client/dashboard.php');
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<h2>Login</h2>
<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>
<form method="POST">
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <button type="submit" class="btn btn-primary">Login</button>
    <a href="forgot_password.php" class="ms-2">Forgot Password?</a>
</form>
<p class="mt-3">Don't have an account? <a href="register.php">Register</a></p>

<?php require_once '../includes/footer.php'; ?>