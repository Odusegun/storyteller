let conversationState = 'greeting';
let currentStory = '';
let currentLanguage = 'en';


const chatBox = document.getElementById('chat-box');
const userInput = document.getElementById('user-input');
const sendButton = document.getElementById('send-button');
const sidebarToggle = document.getElementById('sidebar-toggle');
const languageSelect = document.getElementById('language');
const modeToggle = document.getElementById('mode-toggle-checkbox');

languageSelect.addEventListener('change', (event) => {
    currentLanguage = event.target.value;
    appendMessage('System', `Language changed to ${currentLanguage}`);
});

modeToggle.addEventListener('change', () => {
    document.body.classList.toggle('dark-mode');
});

sendButton.addEventListener('click', sendMessage);
userInput.addEventListener('keydown', (event) => {
    if (event.key === 'Enter') {
        sendMessage();
    }
});



document.addEventListener('DOMContentLoaded', function () {
    const newConversationBtn = document.getElementById('new-conversation-btn');
    const conversationContent = document.querySelector('.conversation-content');
    const chatContainer = document.querySelector('.chat-container');

    sidebarToggle.addEventListener('click', function () {
        const sidebar = document.querySelector('.sidebar');
        sidebar.classList.toggle('collapsed');
        chatContainer.style.width = sidebar.classList.contains('collapsed') ? '97%' : 'calc(100% - 300px)';
        if (!sidebar.classList.contains('collapsed')) {
            chatContainer.style.marginLeft = 'auto';
        } 
        else {
            chatContainer.style.marginLeft = '50px'; // Reset margin when collapsed
        }
    });

    newConversationBtn.addEventListener('click', function () {
        conversationContent.textContent = "New Conversation Started!";
        chatBox.innerHTML = '';
        conversationState = 'greeting';
        currentStory = '';
        getGreetingResponse(languageSelect.value);
    });

    modeToggle.addEventListener('change', function () {
        chatContainer.classList.toggle('light-mode');
        chatContainer.classList.toggle('dark-mode');
    });

    getGreetingResponse(languageSelect.value);
});

function sendMessage() {
    const message = userInput.value.trim();
    //const language = languageSelect.value;
    if (message !== '') {
        appendMessage('user', message);
        processUserInput(message, currentLanguage);
        userInput.value = '';
    }
}

/* function appendMessage(sender, message) {
    const p = document.createElement('p');
    p.textContent = `${sender}: ${message}`;
    chatBox.appendChild(p);
    chatBox.scrollTop = chatBox.scrollHeight;
}
 */
function appendMessage(sender, message) {
    const p = document.createElement('p');
    p.textContent = `${sender}: ${message}`;
    
    // Apply different classes based on the sender
    if (sender === 'user') {
        p.classList.add('user-message');
    } else {
        p.classList.add('bot-message');
    }
    
    chatBox.appendChild(p);
    chatBox.scrollTop = chatBox.scrollHeight;
}


function getGreetingResponse(language) {
    appendMessage('StoryBot', "Hello! What kind of story would you like to hear?");
    conversationState = 'storyType';
}

function processUserInput(message, language) {
    switch (conversationState) {
        case 'storyType':
            fetchStory(message, language);
            break;
        case 'question':
            askAIQuestion(message, language);
            break;
        case 'answer':
            checkUserAnswer(message, language);
            break;
            case 'chat':
            generalChat(message, language);
            break;
        default:
            answerUserQuestion(message, language);
    }
}

function generalChat(message, language) {
    $.ajax({
        url: 'chatResponse.php',
        method: 'POST',
        data: JSON.stringify({ action: 'generalChat', message: message, language: language }),
        contentType: 'application/json',
        dataType: 'json',
        timeout: 30000, // 30 seconds timeout
        success: function(data) {
            if (data.response) {
                appendMessage('StoryBot', data.response);
            } else {
                appendMessage('StoryBot', "I'm not sure how to respond to that. Can you try rephrasing?");
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("AJAX error:", textStatus, errorThrown);
            appendMessage('StoryBot', "I'm having trouble understanding right now. Can we try a different topic?");
        }
    });
}

/* function fetchStory(storyType, language) {
    $.ajax({
        url: 'chatResponse.php',
        method: 'POST',
        data: JSON.stringify({ action: 'getStory', storyType: storyType, language: language }),
        contentType: 'application/json',
        dataType: 'json',
        success: function(data) {
            if (data.story) {
                currentStory = data.story;
                appendMessage('StoryBot', data.story);
                appendMessage('StoryBot', "Do you have any questions about the story? Or should I ask you a question?");
                conversationState = 'question';
            } else {
                appendMessage('StoryBot', "Sorry, I couldn't find a story for that. Let's try something else!");
                conversationState = 'storyType';
            }
        },
        error: function() {
            appendMessage('StoryBot', "Sorry, there was an error fetching the story. Please try again.");
            conversationState = 'storyType';
        }
    });
} */


    function fetchStory(storyType, language, retries = 3) {
        $.ajax({
            url: 'chatResponse.php',
            method: 'POST',
            data: JSON.stringify({ action: 'getStory', storyType: storyType, language: language }),
            contentType: 'application/json',
            dataType: 'json',
            timeout: 30000, // 30 seconds timeout
            success: function(data) {
                console.log("Received data:", data);
                if (data.story) {
                    currentStory = data.story;
                    appendMessage('StoryBot', data.story);
                    appendMessage('StoryBot', "Do you have any questions about the story? Or should I ask you a question?");
                    conversationState = 'question';
                    saveConversation();
                } else if (data.error) {
                    console.error("Error from server:", data.error);
                    handleStoryError(storyType, language, retries);
                } else {
                    appendMessage('StoryBot', "Sorry, I couldn't find a story for that. Let's try something else!");
                    conversationState = 'storyType';
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error("AJAX error:", textStatus, errorThrown);
                handleStoryError(storyType, language, retries);
            }
        });
    }

    function handleStoryError(storyType, language, retries) {
        if (retries > 0) {
            appendMessage('StoryBot', "I'm having trouble generating a story. Let me try again...");
            setTimeout(() => fetchStory(storyType, language, retries - 1), 2000);
        } else {
            appendMessage('StoryBot', "I'm sorry, but I'm having difficulties generating a story right now. How about we chat about something else? What would you like to talk about?");
            conversationState = 'chat';
        }
    }

function saveConversation() {
    const messages = Array.from(chatBox.children).map(p => ({
        sender: p.classList.contains('user-message') ? 'user' : 'StoryBot',
        content: p.textContent.split(': ')[1]
    }));

    $.ajax({
        url: 'saveConversation.php',
        method: 'POST',
        data: JSON.stringify({ 
            action: 'saveConversation', 
            messages: messages, 
            language: currentLanguage 
        }),
        contentType: 'application/json',
        dataType: 'json',
        success: function(data) {
            console.log("Conversation saved:", data);
            updateSidebar();
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error saving conversation:", textStatus, errorThrown);
        }
    });
}

function updateSidebar() {
    $.ajax({
        url: 'getConversations.php',
        method: 'GET',
        dataType: 'json',
        success: function(data) {
            const conversationList = $('.conversation-list');
            conversationList.empty();
            data.conversations.forEach(conv => {
                const convElement = $('<button>').addClass('conversation ').text(conv.title);
                convElement.click(() => loadConversation(conv.id));
                conversationList.append(convElement);
            });
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error fetching conversations:", textStatus, errorThrown);
        }
    });
}

function loadConversation(conversationId) {
    $.ajax({
        url: 'getConversation.php',
        method: 'GET',
        data: { id: conversationId },
        dataType: 'json',
        success: function(data) {
            chatBox.innerHTML = '';
            data.messages.forEach(msg => {
                appendMessage(msg.sender, msg.content);
            });
            currentLanguage = data.language;
            languageSelect.value = currentLanguage;
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error("Error loading conversation:", textStatus, errorThrown);
        }
    });
}

function answerUserQuestion(question, language) {
    $.ajax({
        url: 'chatResponse.php',
        method: 'POST',
        data: JSON.stringify({ action: 'answerQuestion', question: question, story: currentStory, language: language }),
        contentType: 'application/json',
        dataType: 'json',
        success: function(data) {
            appendMessage('StoryBot', data.answer);
            appendMessage('StoryBot', "Do you have any more questions? Or should I ask you a question now?");
            conversationState = 'question';
        },
        error: function() {
            appendMessage('StoryBot', "Sorry, I couldn't answer that question. Can you try asking something else?");
        }
    });
}

function askAIQuestion(message, language) {
    if (message.toLowerCase().includes("ask") || message.toLowerCase().includes("question")) {
        $.ajax({
            url: 'chatResponse.php',
            method: 'POST',
            data: JSON.stringify({ action: 'generateQuestion', story: currentStory, language: language }),
            contentType: 'application/json',
            dataType: 'json',
            success: function(data) {
                appendMessage('StoryBot', data.question);
                conversationState = 'answer';
            },
            error: function() {
                appendMessage('StoryBot', "Sorry, I couldn't generate a question. Let's continue our conversation about the story.");
                conversationState = 'question';
            }
        });
    } else {
        answerUserQuestion(message, language);
    }
}

function checkUserAnswer(answer, language) {
    $.ajax({
        url: 'chatResponse.php',
        method: 'POST',
        data: JSON.stringify({ action: 'checkAnswer', answer: answer, story: currentStory, language: language }),
        contentType: 'application/json',
        dataType: 'json',
        success: function(data) {
            appendMessage('StoryBot', data.response);
            appendMessage('StoryBot', "Do you have any more questions about the story?");
            conversationState = 'question';
        },
        error: function() {
            appendMessage('StoryBot', "Sorry, I couldn't check your answer. Let's continue our conversation about the story.");
            conversationState = 'question';
        }
    });
}

let modalShown = false;

function showFeedbackModal() {
    if (!modalShown) {
        $('#feedbackModal').show();
        modalShown = true;
    }
}



$(document).ready(function() {
    updateSidebar();
    // Set timeout for feedback modal
    //setTimeout(showFeedbackModal, 1 * 60 * 1000); // 3 minutes
    // Show modal after 1 minute (first occurrence)
    setTimeout(function() {
        showFeedbackModal();  // Show the modal after 1 minute

        // Then, show the modal every 3 minutes
        setInterval(showFeedbackModal, 3 * 60 * 1000);  // 3 minutes interval
    }, 1 * 60 * 1000);  // 1 minute initial delay


    // Modal button handlers
    $('#yesBtn').click(function() {
        window.location.href = 'rating.php';
    });

   
    $('#noBtn').click(function() {
        $('#feedbackModal').hide();
    });
});