<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('../auth/login.php');
}

// Fetch summary data
$totalClients = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'client'")->fetch_assoc()['count'];
$activePlans = $conn->query("SELECT COUNT(*) as count FROM subscriptions WHERE status = 'active'")->fetch_assoc()['count'];
$expiringPlans = $conn->query("SELECT COUNT(*) as count FROM subscriptions WHERE expiry_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY) AND status = 'active'")->fetch_assoc()['count'];
?>

<h2 class="text-center my-4">Admin Dashboard</h2>
<div class="row g-4">
    <div class="col-md-4">
        <div class="card text-white bg-primary shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Total Clients</h5>
                        <p class="card-text fs-4"><?php echo $totalClients; ?></p>
                    </div>
                    <i class="bi bi-people-fill fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-success shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Active Plans</h5>
                        <p class="card-text fs-4"><?php echo $activePlans; ?></p>
                    </div>
                    <i class="bi bi-check-circle-fill fs-1"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card text-white bg-warning shadow">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title">Expiring Plans (7 Days)</h5>
                        <p class="card-text fs-4"><?php echo $expiringPlans; ?></p>
                    </div>
                    <i class="bi bi-exclamation-triangle-fill fs-1"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-5">
    <h4 class="text-center mb-4">Quick Actions</h4>
    <div class="d-flex flex-wrap justify-content-center gap-3">
        <a href="manage_clients.php" class="btn btn-outline-primary btn-lg"><i class="bi bi-person-lines-fill me-2"></i>Manage Clients</a>
        <a href="manage_plans.php" class="btn btn-outline-success btn-lg"><i class="bi bi-card-list me-2"></i>Manage Plans</a>
        <a href="upload_files.php" class="btn btn-outline-info btn-lg"><i class="bi bi-cloud-upload-fill me-2"></i>Upload Files</a>
        <a href="notifications.php" class="btn btn-outline-warning btn-lg"><i class="bi bi-bell-fill me-2"></i>Send Notifications</a>
        <a href="payment_history.php" class="btn btn-outline-secondary btn-lg"><i class="bi bi-cash-stack me-2"></i>Payment History</a>
        <a href="export_data.php" class="btn btn-outline-dark btn-lg"><i class="bi bi-file-earmark-arrow-down-fill me-2"></i>Export Data</a>
        <a href="chat.php" class="btn btn-outline-danger btn-lg"><i class="bi bi-chat-dots-fill me-2"></i>Live Chat</a>
    </div>
</div>

<?php
// Check if the 'name' column exists in the 'users' table
$columnCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'name'");
$clientNameColumn = $columnCheck->num_rows > 0 ? 'u.name' : 'u.username'; // Use 'username' as fallback

// Fetch all subscriptions
$subscriptions = $conn->query("
    SELECT s.id, $clientNameColumn AS client_name, p.name AS plan_name, s.start_date, s.expiry_date, s.status
    FROM subscriptions s
    JOIN users u ON s.user_id = u.id
    JOIN plans p ON s.plan_id = p.id
    ORDER BY s.id DESC
");
?>

<div class="mt-5">
    <h4 class="text-center mb-4">Manage Subscriptions</h4>
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Client</th>
                    <th>Plan</th>
                    <th>Start Date</th>
                    <th>Expiry Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($subscription = $subscriptions->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $subscription['id']; ?></td>
                        <td><?php echo $subscription['client_name']; ?></td>
                        <td><?php echo $subscription['plan_name']; ?></td>
                        <td><?php echo $subscription['start_date']; ?></td>
                        <td><?php echo $subscription['expiry_date']; ?></td>
                        <td>
                            <span class="badge bg-<?php echo $subscription['status'] === 'active' ? 'success' : 'warning'; ?>">
                                <?php echo ucfirst($subscription['status']); ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" action="update_subscription_status.php" class="d-inline">
                                <input type="hidden" name="subscription_id" value="<?php echo $subscription['id']; ?>">
                                <select name="status" class="form-select form-select-sm d-inline w-auto">
                                    <option value="active" <?php echo $subscription['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                    <option value="pending" <?php echo $subscription['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>