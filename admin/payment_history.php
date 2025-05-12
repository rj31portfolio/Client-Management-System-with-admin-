 
<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('../auth/login.php');
}

$payments = $conn->query("SELECT p.*, u.username, pl.name FROM payments p JOIN users u ON p.user_id = u.id JOIN plans pl ON p.plan_id = pl.id");
?>

<h2>Payment History</h2>
<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Client</th>
            <th>Plan</th>
            <th>Amount</th>
            <th>Payment ID</th>
            <th>Status</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($payment = $payments->fetch_assoc()): ?>
            <tr>
                <td><?php echo $payment['id']; ?></td>
                <td><?php echo $payment['username']; ?></td>
                <td><?php echo $payment['name']; ?></td>
                <td><?php echo $payment['amount']; ?></td>
                <td><?php echo $payment['razorpay_payment_id']; ?></td>
                <td><?php echo $payment['status']; ?></td>
                <td><?php echo $payment['created_at']; ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php require_once '../includes/footer.php'; ?>