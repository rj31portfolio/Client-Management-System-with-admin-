<?php
require_once '../config/razorpay_config.php';
require_once __DIR__ . '/../../vendor/autoload.php';
require_once '../includes/functions.php';

use Razorpay\Api\Api;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    session_start(); // Ensure session is started
    $api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

    $razorpay_payment_id = $_POST['razorpay_payment_id'];
    $razorpay_order_id = $_POST['razorpay_order_id'];
    $razorpay_signature = $_POST['razorpay_signature'];
    $plan_id = $_POST['plan_id'];

    try {
        // Verify the payment signature
        $attributes = [
            'razorpay_order_id' => $razorpay_order_id,
            'razorpay_payment_id' => $razorpay_payment_id,
            'razorpay_signature' => $razorpay_signature
        ];
        $api->utility->verifyPaymentSignature($attributes);

        // Payment verified, activate subscription
        $user_id = $_SESSION['user_id'];
        $plan = $conn->query("SELECT * FROM plans WHERE id = $plan_id")->fetch_assoc();
        if (!$plan) {
            throw new Exception("Invalid plan ID.");
        }

        $start_date = date('Y-m-d');
        $expiry_date = date('Y-m-d', strtotime("+$plan[duration] days"));

        $query = "INSERT INTO subscriptions (user_id, plan_id, start_date, expiry_date) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iiss', $user_id, $plan_id, $start_date, $expiry_date);
        $stmt->execute();

        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        // Payment verification failed
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>