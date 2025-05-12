 
<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('../auth/login.php');
}

if (isset($_GET['export']) && $_GET['export'] == 'clients') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="clients.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Username', 'Email', 'Created At']);

    $clients = $conn->query("SELECT * FROM users WHERE role = 'client'");
    while ($client = $clients->fetch_assoc()) {
        fputcsv($output, [$client['id'], $client['username'], $client['email'], $client['created_at']]);
    }
    fclose($output);
    exit();
} elseif (isset($_GET['export']) && $_GET['export'] == 'payments') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="payments.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Client', 'Plan', 'Amount', 'Payment ID', 'Status', 'Date']);

    $payments = $conn->query("SELECT p.*, u.username, pl.name FROM payments p JOIN users u ON p.user_id = u.id JOIN plans pl ON p.plan_id = pl.id");
    while ($payment = $payments->fetch_assoc()) {
        fputcsv($output, [$payment['id'], $payment['username'], $payment['name'], $payment['amount'], $payment['razorpay_payment_id'], $payment['status'], $payment['created_at']]);
    }
    fclose($output);
    exit();
}
?>

<h2>Export Data</h2>
<a href="?export=clients" class="btn btn-primary">Export Clients (CSV)</a>
<a href="?export=payments" class="btn btn-primary">Export Payments (CSV)</a>

<?php require_once '../includes/footer.php'; ?>