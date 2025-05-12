 
<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('../auth/login.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $message = $_POST['message'];

    $query = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('is', $user_id, $message);
    $stmt->execute();
    $success = "Notification sent successfully.";
}

$clients = $conn->query("SELECT * FROM users WHERE role = 'client'");
?>

<h2>Send Notifications</h2>
<?php if (isset($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>
<form method="POST">
    <div class="mb-3">
        <label for="user_id" class="form-label">Select Client</label>
        <select class="form-control" id="user_id" name="user_id" required>
            <?php while ($client = $clients->fetch_assoc()): ?>
                <option value="<?php echo $client['id']; ?>"><?php echo $client['username']; ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="mb-3">
        <label for="message" class="form-label">Message</label>
        <textarea class="form-control" id="message" name="message" required></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Send Notification</button>
</form>

<?php require_once '../includes/footer.php'; ?>