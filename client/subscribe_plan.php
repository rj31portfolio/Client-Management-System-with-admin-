<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';
require_once '../config/razorpay_config.php';
require_once __DIR__ . '/../vendor/autoload.php';

use Razorpay\Api\Api;

if (!isLoggedIn() || $_SESSION['role'] != 'client') {
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];
$plans = $conn->query("SELECT * FROM plans");

$api = new Api(RAZORPAY_KEY_ID, RAZORPAY_KEY_SECRET);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $plan_id = $_POST['plan_id'];
    $plan = $conn->query("SELECT * FROM plans WHERE id = $plan_id")->fetch_assoc();

    if (!$plan) {
        die('Plan not found.');
    }

    $order_data = [
        'amount' => $plan['price'] * 100, // In paise
        'currency' => 'INR',
        'receipt' => 'order_' . time(),
        'payment_capture' => 1
    ];

    try {
        $order = $api->order->create($order_data);
        $_SESSION['order_id'] = $order->id;
        $_SESSION['plan_id'] = $plan_id;
    } catch (Exception $e) {
        die('Error creating Razorpay order: ' . $e->getMessage());
    }
}
?>

<h2>Subscribe to Plan</h2>
<div class="row">
    <?php while ($plan = $plans->fetch_assoc()): ?>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo $plan['name']; ?></h5>
                    <p><?php echo $plan['features']; ?></p>
                    <p>Price: <?php echo $plan['price']; ?></p>
                    <p>Duration: <?php echo $plan['duration']; ?> days</p>
                    <form method="POST">
                        <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<?php if (isset($_SESSION['order_id'])): ?>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        var options = {
            "key": "<?php echo RAZORPAY_KEY_ID; ?>",
            "amount": "<?php echo $plan['price'] * 100; ?>",
            "currency": "INR",
            "name": "Client Management System",
            "description": "Subscription for <?php echo $plan['name']; ?>",
            "order_id": "<?php echo $_SESSION['order_id']; ?>",
            "handler": function (response) {
                // Send payment details to server for verification
                fetch('verify_payment.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'razorpay_payment_id=' + response.razorpay_payment_id +
                          '&razorpay_order_id=' + response.razorpay_order_id +
                          '&razorpay_signature=' + response.razorpay_signature +
                          '&plan_id=<?php echo $_SESSION['plan_id']; ?>'
                }).then(response => response.json()).then(data => {
                    if (data.success) {
                        alert('Payment successful!');
                        window.location.href = 'dashboard.php';
                    } else {
                        alert('Payment verification failed!');
                    }
                });
            }
        };
        var rzp = new Razorpay(options);
        rzp.open();
    </script>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>