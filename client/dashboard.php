<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || $_SESSION['role'] != 'client') {
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];
$subscription = $conn->query("SELECT s.*, p.name, p.price FROM subscriptions s JOIN plans p ON s.plan_id = p.id WHERE s.user_id = $user_id AND s.status = 'active'")->fetch_assoc();
$notifications = $conn->query("SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5");
?>

<h2 class="text-center my-4">Welcome to Your Dashboard</h2>

<div class="row g-4">
    <!-- Active Plan Section -->
    <div class="col-md-6">
        <?php if ($subscription): ?>
            <div class="card shadow border-0">
                <div class="card-body">
                    <h5 class="card-title text-primary">Active Plan</h5>
                    <p class="fs-5"><strong>Plan Name:</strong> <?php echo $subscription['name']; ?></p>
                    <p class="fs-5"><strong>Price:</strong> $<?php echo $subscription['price']; ?></p>
                    <p class="fs-5"><strong>Expiry Date:</strong> <?php echo $subscription['expiry_date']; ?></p>
                    <p class="fs-5"><strong>Days Left:</strong> <?php echo ceil((strtotime($subscription['expiry_date']) - time()) / (60 * 60 * 24)); ?> days</p>
                    <p class="fs-5"><strong>Time Left:</strong> <span id="countdown"></span></p>
                </div>
            </div>
        <?php else: ?>
            <div class="card shadow border-0">
                <div class="card-body text-center">
                    <h5 class="card-title text-danger">No Active Plan</h5>
                    <p class="fs-5">You currently have no active subscription plan.</p>
                    <a href="subscribe_plan.php" class="btn btn-primary">Subscribe Now</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Notifications Section -->
    <div class="col-md-6">
        <div class="card shadow border-0">
            <div class="card-body">
                <h5 class="card-title text-warning">Recent Notifications</h5>
                <ul class="list-group list-group-flush">
                    <?php if ($notifications->num_rows > 0): ?>
                        <?php while ($notif = $notifications->fetch_assoc()): ?>
                            <li class="list-group-item">
                                <?php echo $notif['message']; ?>
                                <small class="text-muted d-block">(<?php echo $notif['created_at']; ?>)</small>
                            </li>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <li class="list-group-item text-center">No notifications available.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions Section -->
<div class="mt-5">
    <h4 class="text-center mb-4">Quick Actions</h4>
    <div class="d-flex flex-wrap justify-content-center gap-3">
        <a href="subscribe_plan.php" class="btn btn-outline-primary btn-lg"><i class="bi bi-card-list me-2"></i>Subscribe to Plan</a>
        <a href="view_files.php" class="btn btn-outline-info btn-lg"><i class="bi bi-folder-fill me-2"></i>View Files</a>
        <a href="notifications.php" class="btn btn-outline-warning btn-lg"><i class="bi bi-bell-fill me-2"></i>Notifications</a>
        <a href="payment_history.php" class="btn btn-outline-secondary btn-lg"><i class="bi bi-cash-stack me-2"></i>Payment History</a>
        <a href="chat.php" class="btn btn-outline-danger btn-lg"><i class="bi bi-chat-dots-fill me-2"></i>Live Chat</a>
    </div>
</div>

<script>
    // Countdown Timer
    <?php if ($subscription): ?>
    const expiryDate = new Date("<?php echo $subscription['expiry_date']; ?>").getTime();

    function updateCountdown() {
        const now = new Date().getTime();
        const timeLeft = expiryDate - now;

        if (timeLeft > 0) {
            const days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
            const hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

            document.getElementById("countdown").innerHTML = `${days}d ${hours}h ${minutes}m ${seconds}s`;
        } else {
            document.getElementById("countdown").innerHTML = "Expired";
        }
    }

    setInterval(updateCountdown, 1000);
    <?php endif; ?>
</script>

<?php require_once '../includes/footer.php'; ?>