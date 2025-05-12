<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || $_SESSION['role'] != 'client') {
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];
$payments = $conn->query("SELECT p.*, pl.name FROM payments p JOIN plans pl ON p.plan_id = pl.id WHERE p.user_id = $user_id");
?>

<h2>Payment History</h2>
<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Plan</th>
            <th>Amount</th>
            <th>Payment ID</th>
            <th>Status</th>
            <th>Date</th>
            <th>Invoice</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($payment = $payments->fetch_assoc()): ?>
            <tr>
                <td><?php echo $payment['id']; ?></td>
                <td><?php echo $payment['name']; ?></td>
                <td><?php echo $payment['amount']; ?></td>
                <td><?php echo $payment['payment_id']; ?></td>
                <td><?php echo $payment['status']; ?></td>
                <td><?php echo date('d-m-Y', strtotime($payment['created_at'])); ?></td>
                <td>
                    <a href="generate_invoice.php?payment_id=<?php echo $payment['id']; ?>" class="btn btn-sm btn-primary">Download</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php require_once '../includes/footer.php'; ?>