<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || $_SESSION['role'] != 'client') {
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];
$files = $conn->query("SELECT * FROM uploaded_files WHERE user_id = $user_id");
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary">View Files</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
    </div>

    <?php if ($files->num_rows > 0): ?>
        <table class="table table-striped table-hover shadow-sm">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>File Type</th>
                    <th>File</th>
                    <th>Uploaded At</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($file = $files->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $file['id']; ?></td>
                        <td><?php echo ucfirst($file['file_type']); ?></td>
                        <td><a href="<?php echo $file['file_path']; ?>" class="btn btn-sm btn-primary" download><i class="bi bi-download"></i> Download</a></td>
                        <td><?php echo $file['uploaded_at']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning text-center" role="alert">
            No files available to view.
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>