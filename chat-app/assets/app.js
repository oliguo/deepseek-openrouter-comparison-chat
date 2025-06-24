// Update the sendMessage function in app.js
async function sendMessage(provider) {
    const inputId = provider + '-input';
    const messagesId = provider + '-messages';
    const input = document.getElementById(inputId);
    const message = input.value.trim();
    
    if (!message) return;
    
    // Get model for OpenRouter
    let model = '';
    if (provider === 'openrouter') {
        model = document.getElementById('openrouter-model').value;
    }
    
    // Add user message to chat
    addMessage(messagesId, message, 'user');
    input.value = '';
    
    // Show thinking animation
    addThinkingAnimation(messagesId);
    
    try {
        const response = await fetch('api/send_message.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrf_token
            },
            body: JSON.stringify({
                message: message,
                provider: provider,
                model: model
            })
        });
        
        const data = await response.json();
        
        // Remove thinking animation
        removeThinkingAnimation(messagesId);
        
        if (data.success) {
            // Add AI response with typewriter effect
            addMessageWithTypewriter(messagesId, data.message, 'assistant', data.response_time, data.timestamp);
        } else {
            addMessage(messagesId, 'Error: ' + data.error, 'error');
        }
    } catch (error) {
        removeThinkingAnimation(messagesId);
        addMessage(messagesId, 'Network error: ' + error.message, 'error');
    }
}

// Helper functions for chat UI (addMessage, addThinkingAnimation, removeThinkingAnimation, addMessageWithTypewriter, clearChat)
function addMessage(messagesId, message, type) {
    const messages = document.getElementById(messagesId);
    const msgDiv = document.createElement('div');
    msgDiv.className = 'chat-message ' + type;
    msgDiv.textContent = message;
    messages.appendChild(msgDiv);
    messages.scrollTop = messages.scrollHeight;
}

function addThinkingAnimation(messagesId) {
    const messages = document.getElementById(messagesId);
    const thinkingDiv = document.createElement('div');
    thinkingDiv.className = 'chat-message thinking';
    thinkingDiv.id = messagesId + '-thinking';
    thinkingDiv.textContent = 'Thinking...';
    messages.appendChild(thinkingDiv);
    messages.scrollTop = messages.scrollHeight;
}

function removeThinkingAnimation(messagesId) {
    const thinkingDiv = document.getElementById(messagesId + '-thinking');
    if (thinkingDiv) thinkingDiv.remove();
}

function addMessageWithTypewriter(messagesId, message, type, responseTime, timestamp) {
    const messages = document.getElementById(messagesId);
    const msgDiv = document.createElement('div');
    msgDiv.className = 'chat-message ' + type;
    const meta = document.createElement('div');
    meta.className = 'chat-meta';
    meta.textContent = `⏱️ ${responseTime} ms | ${timestamp}`;
    msgDiv.appendChild(meta);
    const span = document.createElement('span');
    msgDiv.appendChild(span);
    messages.appendChild(msgDiv);
    messages.scrollTop = messages.scrollHeight;
    let i = 0;
    function typeWriter() {
        if (i < message.length) {
            span.textContent += message.charAt(i);
            i++;
            setTimeout(typeWriter, 15);
        }
    }
    typeWriter();
}

function clearChat(provider) {
    const messagesId = provider + '-messages';
    document.getElementById(messagesId).innerHTML = '';
}
