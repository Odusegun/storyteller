<?php
session_start();
require_once 'scripts/class_scripts/db-connection.class.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}

// Initialize the database connection using the existing class
$db = new DBconnection();
$conn = $db->connect();  // Assuming you're using the mysqli connection
if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$user_id = $_SESSION['id'];
$messages = $input['messages'];
$language = $input['language'];

// Generate a title based on the first few messages
$title = generateTitle($messages);

$stmt = $conn->prepare("INSERT INTO conversations (user_id, title, language) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $user_id, $title, $language);
$stmt->execute();
$conversation_id = $stmt->insert_id;

foreach ($messages as $message) {
    $stmt = $conn->prepare("INSERT INTO messages (conversation_id, sender, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $conversation_id, $message['sender'], $message['content']);
    $stmt->execute();
}

echo json_encode(['success' => true, 'conversation_id' => $conversation_id]);

function generateTitle($messages) {
    // Use the first user message or a portion of it as the title
    foreach ($messages as $message) {
        if ($message['sender'] === 'user') {
            return substr($message['content'], 0, 50) . '...';
        }
    }
    return 'Untitled Conversation';
}
