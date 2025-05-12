 <?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

if (isLoggedIn()) {
    redirect($_SESSION['role'] == 'admin' ? '../admin/dashboard.php' : '../client/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'client')";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('sss', $username, $email, $password);

    if ($stmt->execute()) {
        redirect('login.php');
    } else {
        $error = "Registration failed. Email or username may already exist.";
    }
}
?>

<h2>Register</h2>
<?php if (isset($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>
<form method="POST">
    <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" id="username" name="username" required>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <button type="submit" class="btn btn-primary">Register</button>
</form>
<p class="mt-3">Already have an account? <a href="login.php">Login</a></p>

<?php require_once '../includes/footer.php'; ?>
