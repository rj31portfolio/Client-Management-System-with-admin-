<?php
require_once '../includes/db_connection.php'; // Adjust this path if necessary
require_once '../includes/functions.php';

if (!isAdmin()) {
    redirect('../auth/login.php');
}

$admin_id = $_SESSION['user_id'];
$client_id = $_GET['client_id'] ?? 0;
$start_date = $_GET['start_date'] ?? null;
$end_date = $_GET['end_date'] ?? null;

if (!$client_id) {
    die("Invalid client ID.");
}

// Fetch chat messages
$query = "SELECT m.*, u.username FROM messages m JOIN users u ON m.sender_id = u.id WHERE ((m.sender_id = $admin_id AND m.receiver_id = $client_id) OR (m.sender_id = $client_id AND m.receiver_id = $admin_id))";
if ($start_date && $end_date) {
    $query .= " AND DATE(m.created_at) BETWEEN '$start_date' AND '$end_date'";
}
$query .= " ORDER BY m.created_at ASC";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    // Generate the chat export content
    $content = "Chat History\n";
    $content .= "=====================\n\n";
    while ($msg = $result->fetch_assoc()) {
        $content .= "[{$msg['created_at']}] {$msg['username']}: {$msg['message']}\n";
    }

    // Set headers for file download
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="chat_history.txt"');
    echo $content;
} else {
    die("No chat messages found for the selected date range.");
}
?>