<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

if (!isLoggedIn() || $_SESSION['role'] != 'client') {
    redirect('../auth/login.php');
}

$user_id = $_SESSION['user_id'];

// Fetch admin for chat (assuming one admin for simplicity)
$admin = $conn->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1")->fetch_assoc();
$admin_id = $admin['id'];

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message'])) {
    $message = $_POST['message'];
    $query = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iis', $user_id, $admin_id, $message);
    $stmt->execute();
    exit; // Stop further processing for AJAX requests
}

// Handle chat clearing
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['clear_chat'])) {
    $query = "DELETE FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iiii', $user_id, $admin_id, $admin_id, $user_id);
    $stmt->execute();
    exit; // Stop further processing for AJAX requests
}

// Handle AJAX request for fetching messages
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['fetch_messages'])) {
    $messages = $conn->query("SELECT m.*, u.username FROM messages m JOIN users u ON m.sender_id = u.id WHERE (m.sender_id = $user_id AND m.receiver_id = $admin_id) OR (m.sender_id = $admin_id AND m.receiver_id = $user_id) ORDER BY m.created_at ASC");
    while ($msg = $messages->fetch_assoc()) {
        echo "<p><strong>{$msg['username']}:</strong> {$msg['message']} <small class='text-muted'>({$msg['created_at']})</small></p>";
    }
    exit; // Stop further processing for AJAX requests
}

// Fetch chat history for initial page load
$messages = $conn->query("SELECT m.*, u.username FROM messages m JOIN users u ON m.sender_id = u.id WHERE (m.sender_id = $user_id AND m.receiver_id = $admin_id) OR (m.sender_id = $admin_id AND m.receiver_id = $user_id) ORDER BY m.created_at ASC");
?>

<div class="container mt-4">
    <h2 class="text-center mb-4">Live Chat with Admin</h2>
    <div id="chat-box" class="border rounded p-3 mb-3" style="height: 400px; overflow-y: scroll; background-color: #f8f9fa;">
        <?php while ($msg = $messages->fetch_assoc()): ?>
            <p>
                <strong><?php echo $msg['username']; ?>:</strong> 
                <?php echo $msg['message']; ?> 
                <small class="text-muted">(<?php echo $msg['created_at']; ?>)</small>
            </p>
        <?php endwhile; ?>
    </div>
    <form id="chat-form" method="POST">
        <div class="input-group mb-3">
            <input type="text" class="form-control" id="message" name="message" placeholder="Type your message..." required>
            <button type="submit" class="btn btn-primary">Send</button>
            <button type="button" id="clear-chat" class="btn btn-danger ms-2">Clear Chat</button>
        </div>
    </form>
</div>

<script>
// Function to fetch and update chat messages
function fetchMessages() {
    fetch('chat.php?fetch_messages=1') // Add a query parameter to indicate an AJAX request
        .then(response => response.text())
        .then(data => {
            document.getElementById('chat-box').innerHTML = data; // Update only the chat-box content
            let chatBox = document.getElementById('chat-box');
            chatBox.scrollTop = chatBox.scrollHeight; // Auto-scroll to bottom
        });
}

// Auto-reload messages every 3 seconds
setInterval(fetchMessages, 3000);

// Handle message sending
document.getElementById('chat-form').addEventListener('submit', function(e) {
    e.preventDefault();
    let message = document.getElementById('message').value;
    fetch('chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'message=' + encodeURIComponent(message)
    }).then(() => {
        document.getElementById('message').value = ''; // Clear input field
        fetchMessages(); // Refresh messages
    });
});

// Handle chat clearing
document.getElementById('clear-chat').addEventListener('click', function() {
    if (confirm('Are you sure you want to clear the chat?')) {
        fetch('chat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'clear_chat=1'
        }).then(() => {
            fetchMessages(); // Refresh messages
        });
    }
});

// Auto-scroll to bottom on page load
let chatBox = document.getElementById('chat-box');
chatBox.scrollTop = chatBox.scrollHeight;
</script>

<?php require_once '../includes/footer.php'; ?>
