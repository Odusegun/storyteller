<?php
session_start();
require_once 'scripts/class_scripts/db-connection.class.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['error' => 'User not logged in']);
    exit;
}// Initialize the database connection using the existing class
$db = new DBconnection();
$conn = $db->connect();  // Assuming you're using the mysqli connection
if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$user_id = $_SESSION['id'];

$stmt = $conn->prepare("SELECT id, title FROM conversations WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$conversations = [];
while ($row = $result->fetch_assoc()) {
    $conversations[] = $row;
}

echo json_encode(['conversations' => $conversations]);