 
<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('../auth/login.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_plan'])) {
        $name = $_POST['name'];
        $features = $_POST['features'];
        $price = $_POST['price'];
        $duration = $_POST['duration'];
        $is_custom = isset($_POST['is_custom']) ? 1 : 0;

        $query = "INSERT INTO plans (name, features, price, duration, is_custom) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ssdis', $name, $features, $price, $duration, $is_custom);
        $stmt->execute();
    } elseif (isset($_POST['delete_plan'])) {
        $plan_id = $_POST['plan_id'];
        $query = "DELETE FROM plans WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $plan_id);
        $stmt->execute();
    }
}

$plans = $conn->query("SELECT * FROM plans");
?>

<h2>Manage Plans</h2>
<h4>Add Plan</h4>
<form method="POST">
    <div class="mb-3">
        <label for="name" class="form-label">Plan Name</label>
        <input type="text" class="form-control" id="name" name="name" required>
    </div>
    <div class="mb-3">
        <label for="features" class="form-label">Features</label>
        <textarea class="form-control" id="features" name="features" required></textarea>
    </div>
    <div class="mb-3">
        <label for="price" class="form-label">Price</label>
        <input type="number" step="0.01" class="form-control" id="price" name="price" required>
    </div>
    <div class="mb-3">
        <label for="duration" class="form-label">Duration (days)</label>
        <input type="number" class="form-control" id="duration" name="duration" required>
    </div>
    <div class="mb-3">
        <label for="is_custom" class="form-check-label">
            <input type="checkbox" class="form-check-input" id="is_custom" name="is_custom"> Custom Plan
        </label>
    </div>
    <button type="submit" name="add_plan" class="btn btn-primary">Add Plan</button>
</form>

<h4 class="mt-4">Plan List</h4>
<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Price</th>
            <th>Duration</th>
            <th>Custom</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($plan = $plans->fetch_assoc()): ?>
            <tr>
                <td><?php echo $plan['id']; ?></td>
                <td><?php echo $plan['name']; ?></td>
                <td><?php echo $plan['price']; ?></td>
                <td><?php echo $plan['duration']; ?></td>
                <td><?php echo $plan['is_custom'] ? 'Yes' : 'No'; ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                        <button type="submit" name="delete_plan" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php require_once '../includes/footer.php'; ?>