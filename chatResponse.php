<?php
require_once 'vendor/autoload.php';
session_start();

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;

function logError($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, 'debug.log');
}

$rawData = file_get_contents("php://input");
logError("Received raw data: " . $rawData);

$data = json_decode($rawData, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    logError("JSON decode error: " . json_last_error_msg());
    die(json_encode(['error' => 'Invalid JSON data']));
}

$action = $data['action'] ?? '';
$language = $data['language'] ?? 'en';

logError("Decoded data: " . print_r($data, true));
logError("Action: " . $action);
logError("Language: " . $language);

$client = new Client([
    'headers' => [
        'Authorization' => 'Bearer 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',
        'Content-Type'  => 'application/json',
        'HTTP-Referer' => 'http://127.0.0.1/AI_chat/chat.php',
        'X-Title' => 'StoryBot'
    ],
    'timeout' => 30,
    'connect_timeout' => 10
]);

function makeApiRequest($client, $messages) {
    try {
        $response = $client->post('https://openrouter.ai/api/v1/chat/completions', [
            'json' => [
                'model' => 'openai/gpt-3.5-turbo',
                'messages' => $messages
            ]
        ]);

        $result = json_decode($response->getBody(), true);
        return $result['choices'][0]['message']['content'] ?? null;
    } catch (RequestException $e) {
        logError("API Request Error: " . $e->getMessage());
        if ($e->hasResponse()) {
            logError("Response: " . $e->getResponse()->getBody());
        }
        return null;
    } catch (ConnectException $e) {
        logError("Connection Error: " . $e->getMessage());
        return null;
    }
}

switch ($action) {
    case 'getStory':
        $storyType = $data['storyType'] ?? '';
        $content = makeApiRequest($client, [
            ['role' => 'system', 'content' => "You are a storyteller. Generate a short story about $storyType in $language."],
            ['role' => 'user', 'content' => "Tell me a story about $storyType."]
        ]);
        
        if ($content) {
            echo json_encode(['story' => $content]);
        } else {
            echo json_encode(['error' => 'Failed to generate story']);
        }
        break;

    case 'answerQuestion':
        $question = $data['question'] ?? '';
        $story = $data['story'] ?? '';
        $content = makeApiRequest($client, [
            ['role' => 'system', 'content' => "You are answering questions about a story in $language."],
            ['role' => 'user', 'content' => "Story: $story\n\nQuestion: $question"]
        ]);
        
        if ($content) {
            echo json_encode(['answer' => $content]);
        } else {
            echo json_encode(['error' => 'Failed to answer question']);
        }
        break;

    case 'generateQuestion':
        $story = $data['story'] ?? '';
        $content = makeApiRequest($client, [
            ['role' => 'system', 'content' => "You are generating a question about a story in $language."],
            ['role' => 'user', 'content' => "Generate a question about this story: $story"]
        ]);
        
        if ($content) {
            echo json_encode(['question' => $content]);
        } else {
            echo json_encode(['error' => 'Failed to generate question']);
        }
        break;

    case 'checkAnswer':
        $answer = $data['answer'] ?? '';
        $story = $data['story'] ?? '';
        $content = makeApiRequest($client, [
            ['role' => 'system', 'content' => "You are checking an answer to a question about a story in $language."],
            ['role' => 'user', 'content' => "Story: $story\n\nAnswer: $answer\n\nIs this answer correct? Provide feedback."]
        ]);
        
        if ($content) {
            echo json_encode(['response' => $content]);
        } else {
            echo json_encode(['error' => 'Failed to check answer']);
        }
        break;

    case 'generalChat':
        $message = $data['message'] ?? '';
        $content = makeApiRequest($client, [
            ['role' => 'system', 'content' => "You are a friendly chatbot that can discuss various topics in $language."],
            ['role' => 'user', 'content' => $message]
        ]);
        
        if ($content) {
            echo json_encode(['response' => $content]);
        } else {
            echo json_encode(['error' => 'Failed to generate response']);
        }
        break;

    default:
        logError("Invalid action received: " . $action);
        echo json_encode(['error' => 'Invalid action']);
}
?>