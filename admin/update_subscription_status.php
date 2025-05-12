<?php
require_once '../includes/functions.php';
require_once '../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subscription_id = intval($_POST['subscription_id']);
    $status = $_POST['status'];

    // Validate status
    if (!in_array($status, ['active', 'pending'])) {
        die('Invalid status.');
    }

    // Update subscription status
    $stmt = $conn->prepare("UPDATE subscriptions SET status = ? WHERE id = ?");
    $stmt->bind_param('si', $status, $subscription_id);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Subscription status updated successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to update subscription status.";
    }

    // Redirect back to the dashboard
    header('Location: dashboard.php');
    exit;
}
?>