 
<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || $_SESSION['role'] != 'client') {
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];
$notifications = $conn->query("SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC");
?>

<h2>Notifications</h2>
<ul class="list-group">
    <?php while ($notif = $notifications->fetch_assoc()): ?>
        <li class="list-group-item"><?php echo $notif['message']; ?> <small>(<?php echo $notif['created_at']; ?>)</small></li>
    <?php endwhile; ?>
</ul>

<?php require_once '../includes/footer.php'; ?>