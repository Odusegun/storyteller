<?php
session_start();
require_once 'scripts/class_scripts/db-connection.class.php';


header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}
//Initialize the database connection using the existing class
$db = new DBconnection();
$conn = $db->connect();  // Assuming you're using the mysqli connection
if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$user_id = $_SESSION['id'];
$conversation_id = $_GET['id'];

// Fetch conversation details
$stmt = $conn->prepare("SELECT title, language FROM conversations WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $conversation_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$conversation = $result->fetch_assoc();

if (!$conversation) {
    echo json_encode(['error' => 'Conversation not found']);
    exit;
}

// Fetch messages
$stmt = $conn->prepare("SELECT sender, content FROM messages WHERE conversation_id = ? ORDER BY created_at ASC");
$stmt->bind_param("i", $conversation_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

echo json_encode([
    'title' => $conversation['title'],
    'language' => $conversation['language'],
    'messages' => $messages
]);