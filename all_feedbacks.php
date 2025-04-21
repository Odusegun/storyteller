<?php
require_once 'scripts/class_scripts/db-connection.class.php';

/* session_start();
if (!isset($_SESSION['id'])) {
    header("Location: signin.php");
    exit();
} */

// Database connection
$db = new DBconnection();
$conn = $db->connect();  // Assuming you're using the mysqli connection
if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}
//$conn = new mysqli("localhost", "username", "password", "database_name");

/* if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} */

// Pagination
$results_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start_from = ($page - 1) * $results_per_page;

// Fetch total number of feedbacks
$total_query = "SELECT COUNT(*) AS total FROM feedback";
$total_result = $conn->query($total_query);
$total_row = $total_result->fetch_assoc();
$total_pages = ceil($total_row['total'] / $results_per_page);

// Fetch feedbacks for current page
$sql = "SELECT f.id, f.rating, f.comment, f.created_at, u.username 
        FROM feedback f 
        JOIN users u ON f.user_id = u.id 
        ORDER BY f.created_at DESC 
        LIMIT $start_from, $results_per_page";

$result = $conn->query($sql);

$feedbacks = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $feedbacks[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Feedbacks and Reviews - StoryBot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="style.css" rel="stylesheet">
</head>
<body>
<?php require_once(__DIR__ . '/resources/nav.php'); ?>

    <!-- <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="index.php">StoryBot</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="chat.php">Chat</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="all_feedbacks.php">Feedbacks</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav> -->

    <div class="container mt-5">
        <h1 class="mb-4">All Feedbacks and Reviews</h1>
        
        <?php foreach ($feedbacks as $feedback): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="card-title"><?php echo htmlspecialchars($feedback['username']); ?></h5>
                        <small class="text-muted"><?php echo date('F j, Y, g:i a', strtotime($feedback['created_at'])); ?></small>
                    </div>
                    <p class="card-text"><?php echo htmlspecialchars($feedback['comment']); ?></p>
                    <div class="text-warning">
                        <?php
                        for ($i = 0; $i < 5; $i++) {
                            if ($i < $feedback['rating']) {
                                echo '<i class="fas fa-star"></i>';
                            } else {
                                echo '<i class="far fa-star"></i>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Pagination -->
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>

    <footer class="bg-light text-center py-3 mt-5">
        <p>&copy; 2024 StoryBot. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>