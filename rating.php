<?php
session_start();
require_once 'scripts/class_scripts/db-connection.class.php';

if (!isset($_SESSION['id'])) {
    header("Location: signin.php");
    exit();
}
// Initialize the database connection using the existing class
$db = new DBconnection();
$conn = $db->connect();  // Assuming you're using the mysqli connection
if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Process the form submission
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];
    $user_id = $_SESSION['id'];

    // Connect to your database
    //$conn = new mysqli("localhost", "username", "password", "database_name");

    /* if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    } */

    $stmt = $conn->prepare("INSERT INTO feedback (user_id, rating, comment) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $rating, $comment);

    if ($stmt->execute()) {
        $feedback_message = "Thank you for your feedback!";
    } else {
        $feedback_message = "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StoryBot Feedback</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <style>
        .rating {
            unicode-bidi: bidi-override;
            direction: rtl;
            text-align: center;
        }
        .rating > span {
            display: inline-block;
            position: relative;
            width: 1.1em;
            font-size: 2em;
            color: #ccc;
            cursor: pointer;
        }
        .rating > span:hover:before,
        .rating > span:hover ~ span:before {
            content: "\2605";
            position: absolute;
            color: gold;
        }
        .rating > span.selected:before,
        .rating > span.selected ~ span:before {
            content: "\2605";
            position: absolute;
            color: gold;
        }
    </style>
</head>
<body>
<?php require_once(__DIR__ . '/resources/nav.php'); ?>
    <div class="container" style="max-width: max-content; padding: 3%;">
        <h1>StoryBot Feedback</h1>
        <?php if (isset($feedback_message)): ?>
            <p><?php echo $feedback_message; ?></p>
            <a href="chat.php">Return to Chat</a>
        <?php else: ?>
            <form method="POST" action="rating.php">
                <div class="rating">
                    <span data-rating="5">☆</span>
                    <span data-rating="4">☆</span>
                    <span data-rating="3">☆</span>
                    <span data-rating="2">☆</span>
                    <span data-rating="1">☆</span>
                </div>
                <input type="hidden" name="rating" id="rating" value="0">
                <textarea name="comment" rows="4" cols="50" placeholder="Please leave your comments here..."></textarea>
                <button type="submit">Submit Feedback</button>
            </form>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.rating > span').click(function() {
                var rating = $(this).data('rating');
                console.log(rating);
                $('#rating').val(rating);
                $('.rating > span').removeClass('selected');
                //$(this).addClass('selected').prevAll().addClass('selected');
                $(this).addClass('selected');

            });
        });
    </script>
<!--     <script src="script.js"></script>
 -->
</body>
</html>