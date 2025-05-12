<?php
require_once '../includes/header.php';
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('../auth/login.php');
}

$admin_id = $_SESSION['user_id'];

// Fetch clients for chat
$clients = $conn->query("SELECT id, username FROM users WHERE role = 'client'");
$selected_client_id = $_GET['client_id'] ?? 0;

// Handle message sending
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message']) && $selected_client_id) {
    $message = $_POST['message'];
    $query = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iis', $admin_id, $selected_client_id, $message);
    $stmt->execute();
    exit; // Stop further processing for AJAX requests
}

// Handle chat clearing
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['clear_chat']) && $selected_client_id) {
    $query = "DELETE FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iiii', $admin_id, $selected_client_id, $selected_client_id, $admin_id);
    $stmt->execute();
    exit; // Stop further processing for AJAX requests
}

// Handle AJAX request for fetching messages
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['fetch_messages']) && $selected_client_id) {
    $start_date = $_GET['start_date'] ?? null;
    $end_date = $_GET['end_date'] ?? null;

    $query = "SELECT m.*, u.username FROM messages m JOIN users u ON m.sender_id = u.id WHERE ((m.sender_id = $admin_id AND m.receiver_id = $selected_client_id) OR (m.sender_id = $selected_client_id AND m.receiver_id = $admin_id))";
    if ($start_date && $end_date) {
        $query .= " AND DATE(m.created_at) BETWEEN '$start_date' AND '$end_date'";
    }
    $query .= " ORDER BY m.created_at ASC";

    $messages = $conn->query($query);
    while ($msg = $messages->fetch_assoc()) {
        echo "<p><strong>{$msg['username']}:</strong> {$msg['message']} <small>({$msg['created_at']})</small></p>";
    }
    exit; // Stop further processing for AJAX requests
}

// Fetch chat history for initial page load
$messages = $selected_client_id ? $conn->query("SELECT m.*, u.username FROM messages m JOIN users u ON m.sender_id = u.id WHERE (m.sender_id = $admin_id AND m.receiver_id = $selected_client_id) OR (m.sender_id = $selected_client_id AND m.receiver_id = $admin_id) ORDER BY m.created_at ASC") : null;
?>

<h2>Live Chat with Clients</h2>
<div class="row">
    <div class="col-md-4">
        <h4>Clients</h4>
        <ul class="list-group">
            <?php while ($client = $clients->fetch_assoc()): ?>
                <li class="list-group-item">
                    <a href="?client_id=<?php echo $client['id']; ?>"><?php echo $client['username']; ?></a>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
    <div class="col-md-8">
        <?php if ($selected_client_id): ?>
            <h4>Chat with Client</h4>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <form id="filter-form" class="d-flex gap-2">
                    <input type="date" class="form-control" id="start_date" name="start_date">
                    <input type="date" class="form-control" id="end_date" name="end_date">
                    <button type="button" class="btn btn-outline-primary" onclick="fetchMessages()">Filter</button>
                </form>
                <div>
                    <button class="btn btn-outline-danger" onclick="clearChat()">Clear Chat</button>
                    <button class="btn btn-outline-success" onclick="exportChat()">Export Chat</button>
                </div>
            </div>
            <div id="chat-box" style="height: 400px; overflow-y: scroll; border: 1px solid #ccc; padding: 10px;">
                <?php if ($messages): while ($msg = $messages->fetch_assoc()): ?>
                    <p><strong><?php echo $msg['username']; ?>:</strong> <?php echo $msg['message']; ?> <small>(<?php echo $msg['created_at']; ?>)</small></p>
                <?php endwhile; endif; ?>
            </div>
            <form id="chat-form" method="POST">
                <div class="input-group mt-3">
                    <input type="text" class="form-control" id="message" name="message" placeholder="Type your message..." required>
                    <button type="submit" class="btn btn-primary">Send</button>
                </div>
            </form>
        <?php else: ?>
            <p>Select a client to start chatting.</p>
        <?php endif; ?>
    </div>
</div>

<script>
// Function to fetch and update chat messages
function fetchMessages() {
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    const url = `chat.php?fetch_messages=1&client_id=<?php echo $selected_client_id; ?>&start_date=${start_date}&end_date=${end_date}`;
    fetch(url)
        .then(response => response.text())
        .then(data => {
            const chatBox = document.getElementById('chat-box');
            chatBox.innerHTML = data;
            chatBox.scrollTop = chatBox.scrollHeight;
        });
}

// Auto-reload messages every 3 seconds
if (<?php echo $selected_client_id; ?>) {
    setInterval(fetchMessages, 3000);
}

// Handle message sending
document.getElementById('chat-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const message = document.getElementById('message').value;
    fetch('chat.php?client_id=<?php echo $selected_client_id; ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'message=' + encodeURIComponent(message)
    }).then(() => {
        document.getElementById('message').value = '';
        fetchMessages();
    });
});

// Clear chat
function clearChat() {
    if (confirm('Are you sure you want to clear the chat?')) {
        fetch('chat.php?client_id=<?php echo $selected_client_id; ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'clear_chat=1'
        }).then(() => {
            fetchMessages();
        });
    }
}

// Export chat
function exportChat() {
    const start_date = document.getElementById('start_date').value;
    const end_date = document.getElementById('end_date').value;
    const url = `export_chat.php?client_id=<?php echo $selected_client_id; ?>&start_date=${start_date}&end_date=${end_date}`;
    window.location.href = url;
}

// Auto-scroll to bottom on page load
const chatBox = document.getElementById('chat-box');
if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;
</script>

<?php require_once '../includes/footer.php'; ?>