<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multilingual Storytelling Chatbot</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
    .tooltip {
    opacity: 1 !important;  /* Override with !important */
    }
    </style>
</head>
<body>
    <?php 
    session_set_cookie_params(300); // 300 seconds = 5 minutes
    session_start();
    if (!isset($_SESSION['id'])) {
        // If session is not set, redirect to login page
        header("Location: signin.php");
        exit();
    }
    /* else{
        echo $_SESSION['username']."home";

    } */
   /*  if (!isset($_SESSION)){
        session_start();
    }
    else{
        $user_id = $_SESSION['id'] ;
        echo $_SESSION['id']."home";
    } */
    ?>
<?php require_once(__DIR__ . '/resources/nav.php'); ?>

    <div class="sidebar collapsed">
        <div class="tooltip">
            <span class="tooltiptext">Open Sidebar</span>
            <button id="sidebar-toggle"><i class="fas fa-chevron-right"></i></button>

        </div>
        <div class="sidebar-content">
        <button id="new-conversation-btn">Start a new Conversation</button>


        <form method="POST" action="logout.php">
                <button type="submit" class="logout" id="logout_btn">Logout</button>
        </form>

        <p class="conversation-text">Previous Conversations:</p>

            <div class="conversation-list">
                 <div class="conversation">
                    <p class="conversation-content">No conversation yet</p>
                </div> 
            </div>
           

        </div>
    </div>
    <div class="chat-container light-mode">
        <div class="chat-content">
            <div class="chat-header">
                <div class="language-selection" style="font-size: 15px;">
                    <label for="language">Select Language:</label>
                    <select id="language">
                        <option value="en">English</option>
                        <option value="zh">Chinese</option>
                        <option value="es">Spanish</option>
                        <option value="hi">Hindi</option>
                        <option value="ar">Arabic</option>
                        <option value="ru">Russian</option>
                    </select>
                </div>
                <div class="mode-toggle">
                    <label class="switch">
                        <input type="checkbox" id="mode-toggle-checkbox">
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
            
            <div class="chat-box" id="chat-box"></div>
            <div class="input-container">
                <input type="text" id="user-input" placeholder="Type your message...">
                <button id="send-button"><b>&uarr;</b></button>
            </div>
        </div>
    </div>


    <!-- Feedback Modal -->
    <div id="feedbackModal" class="modal" style="display: none;">
        <div class="modal-content" style="left: 30%; padding: 10px; width: 40%;">
            <h2>Feedback</h2>
            <p>Would you like to provide feedback on your experience with StoryBot?</p>
            <button id="yesBtn">Yes</button>
            <button id="noBtn">No</button>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js"></script>
</body>
</html>