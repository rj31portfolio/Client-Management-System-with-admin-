<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('../auth/login.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $file_type = $_POST['file_type'];
    $file = $_FILES['file'];

    $upload_dir = '../uploads/' . ($file_type == 'report' ? 'reports' : 'projects') . '/';
    $file_path = $upload_dir . basename($file['name']);

    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        $query = "INSERT INTO uploaded_files (user_id, file_type, file_path) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('iss', $user_id, $file_type, $file_path);
        $stmt->execute();
        $success = "File uploaded successfully.";
    } else {
        $error = "File upload failed.";
    }
}

$clients = $conn->query("SELECT * FROM users WHERE role = 'client'");
$uploaded_files = $conn->query("SELECT uf.*, u.username FROM uploaded_files uf JOIN users u ON uf.user_id = u.id ORDER BY uf.uploaded_at DESC");
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-primary">Upload Files</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $success; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif (isset($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $error; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- File Upload Form -->
    <form method="POST" enctype="multipart/form-data" class="shadow p-4 rounded bg-light mb-5">
        <div class="mb-3">
            <label for="user_id" class="form-label">Select Client</label>
            <select class="form-select" id="user_id" name="user_id" required>
                <option value="" disabled selected>Select a client</option>
                <?php while ($client = $clients->fetch_assoc()): ?>
                    <option value="<?php echo $client['id']; ?>"><?php echo $client['username']; ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="file_type" class="form-label">File Type</label>
            <select class="form-select" id="file_type" name="file_type" required>
                <option value="" disabled selected>Select file type</option>
                <option value="report">Report</option>
                <option value="project">Project</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="file" class="form-label">Upload File</label>
            <input type="file" class="form-control" id="file" name="file" accept=".pdf,.zip,.jpg,.png" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Upload File</button>
    </form>

    <!-- Uploaded Files List -->
    <h3 class="text-primary mb-4">Uploaded Files</h3>
    <?php if ($uploaded_files->num_rows > 0): ?>
        <table class="table table-striped table-hover shadow-sm">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>File Type</th>
                    <th>File</th>
                    <th>Uploaded At</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($file = $uploaded_files->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $file['id']; ?></td>
                        <td><?php echo $file['username']; ?></td>
                        <td><?php echo ucfirst($file['file_type']); ?></td>
                        <td><a href="<?php echo $file['file_path']; ?>" class="btn btn-sm btn-primary" download><i class="bi bi-download"></i> Download</a></td>
                        <td><?php echo $file['uploaded_at']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning text-center" role="alert">
            No files have been uploaded yet.
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>