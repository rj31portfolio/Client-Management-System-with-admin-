 
<?php
require_once 'config/db_connect.php';
require_once 'config/razorpay_config.php';
require_once 'vendor/autoload.php';

use Razorpay\Api\Api;

$api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

// Get webhook payload
$webhook_body = file_get_contents('php://input');
$webhook_signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

// Verify webhook signature
$webhook_secret = 'your_webhook_secret'; // Set in Razorpay dashboard
$expected_signature = hash_hmac('sha256', $webhook_body, $webhook_secret);

if ($webhook_signature !== $expected_signature) {
    http_response_code(400);
    error_log("Webhook signature verification failed.");
    exit();
}

$payload = json_decode($webhook_body, true);

if ($payload['event'] == 'payment.captured') {
    $payment_id = $payload['payload']['payment']['entity']['id'];
    $order_id = $payload['payload']['payment']['entity']['order_id'];
    $amount = $payload['payload']['payment']['entity']['amount'] / 100; // Convert paise to INR
    $user_id = $payload['payload']['payment']['entity']['notes']['user_id'] ?? null;
    $plan_id = $payload['payload']['payment']['entity']['notes']['plan_id'] ?? null;
    $subscription_id = $payload['payload']['payment']['entity']['notes']['subscription_id'] ?? null;

    if ($user_id && $plan_id && $subscription_id) {
        // Update payment record
        $query = "INSERT INTO payments (user_id, plan_id, razorpay_payment_id, amount, status) VALUES (?, ?, ?, ?, 'success') ON DUPLICATE KEY UPDATE status = 'success'";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iisd', $user_id, $plan_id, $payment_id, $amount);
        $stmt->execute();

        // Update subscription status
        $query = "UPDATE subscriptions SET status = 'active' WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $subscription_id);
        $stmt->execute();

        // Send confirmation email
        $user = $conn->query("SELECT email FROM users WHERE id = $user_id")->fetch_assoc();
        sendEmail($user['email'], "Payment Confirmation", "Your payment of INR $amount for plan ID $plan_id was successful. Transaction ID: $payment_id");
    }
}

http_response_code(200);
?>