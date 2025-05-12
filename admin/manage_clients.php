 
<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('../auth/login.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_client'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        $query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'client')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('sss', $username, $email, $password);
        $stmt->execute();
    } elseif (isset($_POST['delete_client'])) {
        $user_id = $_POST['user_id'];
        $query = "DELETE FROM users WHERE id = ? AND role = 'client'";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
    }
}

$clients = $conn->query("SELECT * FROM users WHERE role = 'client'");
?>

<h2>Manage Clients</h2>
<h4>Add Client</h4>
<form method="POST">
    <div class="mb-3">
        <label for="username" class="form-label">Username</label>
        <input type="text" class="form-control" id="username" name="username" required>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" class="form-control" id="password" name="password" required>
    </div>
    <button type="submit" name="add_client" class="btn btn-primary">Add Client</button>
</form>

<h4 class="mt-4">Client List</h4>
<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($client = $clients->fetch_assoc()): ?>
            <tr>
                <td><?php echo $client['id']; ?></td>
                <td><?php echo $client['username']; ?></td>
                <td><?php echo $client['email']; ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $client['id']; ?>">
                        <button type="submit" name="delete_client" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php require_once '../includes/footer.php'; ?>