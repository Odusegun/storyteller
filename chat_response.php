<?php
require_once 'vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

// Database connection
$conn = new mysqli("localhost", "root", "", "chatbot");

if ($conn->connect_error) {
    die(json_encode(['error' => "Connection failed: " . $conn->connect_error]));
}

// Fetch request body
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';
$language = $data['language'] ?? 'en';

// Initialize Guzzle client
$client = new Client([
    'base_uri' => 'https://api.openai.com/v1/',
    'headers' => [
        'Authorization' => 'Bearer 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',
        'Content-Type'  => 'application/json',
    ],
]);

function callAIAPI($prompt, $language) {
    global $client;

    $languagePrompt = "Respond in $language. ";
    $fullPrompt = $languagePrompt . $prompt;
    $maxRetries = 5;
    $retryDelay = 1; // Initial delay in seconds

    for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
        try {
            $response = $client->post('chat/completions', [
                'json' => [
                    'model' => 'gpt-3.5-turbo', // Use the latest version
                    'messages' => [
                        ['role' => 'user', 'content' => $fullPrompt],
                    ],
                    'max_tokens' => 500,
                    'temperature' => 0.7,
                ]
            ]);

            $body = json_decode($response->getBody(), true);
            echo $body['choices'][0]['message']['content'];
            return $body['choices'][0]['message']['content'];
        } catch (RequestException $e) {
            if ($e->getResponse()->getStatusCode() == 429) {
                echo $e;
                // Handle rate limit exceeded
                error_log("Rate limit exceeded: " . $e->getMessage());
                if ($attempt < $maxRetries) {
                    sleep($retryDelay); // Wait before retrying
                    $retryDelay *= 2; // Exponential backoff
                } else {
                    return "An error occurred while generating content. Please try again later.". $e;
                }
            } else {
                // Handle other exceptions
                error_log("AI API Error: " . $e->getMessage());
                return "An error occurred while generating content.". $e->getMessage();
            }
        }
    }
}


switch ($action) {
    case 'getStory':
        getStory($conn, $data['storyType'], $language);
        break;
    case 'answerQuestion':
        answerQuestion($data['question'], $data['story'], $language);
        break;
    case 'generateQuestion':
        generateQuestion($data['story'], $language);
        break;
    case 'checkAnswer':
        checkAnswer($data['answer'], $data['story'], $language);
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
}

function getStory($conn, $storyType, $language) {
    $stmt = $conn->prepare("SELECT story FROM stories WHERE topic = ? AND language = ?");
    $stmt->bind_param('ss', $storyType, $language);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode(['story' => $row['story']]);
    } else {
        $prompt = "Generate a short story about $storyType. The story should be entertaining and suitable for all ages.";
        $generatedStory = callAIAPI($prompt, $language);
        
        $stmt = $conn->prepare("INSERT INTO stories (topic, language, story) VALUES (?, ?, ?)");
        $stmt->bind_param('sss', $storyType, $language, $generatedStory);
        $stmt->execute();
        
        echo json_encode(['story' => $generatedStory]);
    }
    $stmt->close();
}

function answerQuestion($question, $story, $language) {
    $prompt = "Based on the following story, answer this question: $question\n\nStory: $story";
    $answer = callAIAPI($prompt, $language);
    echo json_encode(['answer' => $answer]);
}

function generateQuestion($story, $language) {
    $prompt = "Based on the following story, generate an interesting question:\n\nStory: $story";
    $question = callAIAPI($prompt, $language);
    echo json_encode(['question' => $question]);
}

function checkAnswer($answer, $story, $language) {
    $prompt = "Given the following story and user's answer, evaluate if the answer is correct. Provide feedback.\n\nStory: $story\n\nUser's Answer: $answer";
    $response = callAIAPI($prompt, $language);
    echo json_encode(['response' => $response]);
}

$conn->close();
?>
