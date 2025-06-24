# PHP Chat Website Implementation Guide

## Overview

This guide provides the complete implementation details for building a PHP-based chat website that integrates with DeepSeek and OpenRouter APIs. The demo application shown above provides the frontend interface, and this guide covers the PHP backend implementation needed for a production system.

## Project Structure

```
chat-app/
├── index.php              # Login page
├── home.php               # Main chat interface
├── api/
│   ├── login.php          # Login handler
│   ├── logout.php         # Logout handler
│   ├── send_message.php   # Message sending API
│   └── config.php         # Configuration file
├── includes/
│   ├── session.php        # Session management
│   └── functions.php      # Utility functions
├── assets/
│   ├── style.css          # Styles (from demo)
│   └── app.js             # JavaScript (from demo)
└── .env                   # Environment variables
```

## 1. Configuration Setup

### config.php
```php
<?php
// API Configuration
define('DEEPSEEK_API_URL', 'https://api.deepseek.com/chat/completions');
define('DEEPSEEK_API_KEY', $_ENV['DEEPSEEK_API_KEY'] ?? 'your-deepseek-api-key');

define('OPENROUTER_API_URL', 'https://openrouter.ai/api/v1/chat/completions');
define('OPENROUTER_API_KEY', $_ENV['OPENROUTER_API_KEY'] ?? 'your-openrouter-api-key');

// App Configuration
define('LOGIN_PASSCODE', 'aitest');
define('SESSION_TIMEOUT', 3600); // 1 hour

// OpenRouter Models
define('OPENROUTER_MODELS', [
    'openai/gpt-4o' => 'GPT-4o',
    'anthropic/claude-3.5-sonnet' => 'Claude-3.5-Sonnet',
    'meta-llama/llama-3.1-70b-instruct' => 'Llama-3.1-70B',
    'google/gemini-pro' => 'Gemini-Pro',
    'openai/gpt-3.5-turbo' => 'GPT-3.5-Turbo'
]);

session_start();
?>
```

### .env File
```
DEEPSEEK_API_KEY=your_actual_deepseek_api_key_here
OPENROUTER_API_KEY=your_actual_openrouter_api_key_here
```

## 2. Session Management

### includes/session.php
```php
<?php
function startSecureSession() {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 1);
    ini_set('session.use_only_cookies', 1);
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit();
    }
}

function loginUser() {
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();
}

function logoutUser() {
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/');
}

function checkSessionTimeout() {
    if (isset($_SESSION['login_time'])) {
        if (time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
            logoutUser();
            return false;
        }
    }
    return true;
}
?>
```

## 3. Utility Functions

### includes/functions.php
```php
<?php
function makeApiRequest($url, $headers, $data) {
    $startTime = microtime(true);
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_USERAGENT => 'AI-Chat-Demo/1.0'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    $responseTime = round((microtime(true) - $startTime) * 1000, 2);
    
    if ($error) {
        return [
            'success' => false,
            'error' => 'cURL Error: ' . $error,
            'response_time' => $responseTime
        ];
    }
    
    if ($httpCode !== 200) {
        return [
            'success' => false,
            'error' => 'HTTP Error: ' . $httpCode,
            'response_time' => $responseTime
        ];
    }
    
    $decodedResponse = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'success' => false,
            'error' => 'JSON decode error: ' . json_last_error_msg(),
            'response_time' => $responseTime
        ];
    }
    
    return [
        'success' => true,
        'data' => $decodedResponse,
        'response_time' => $responseTime
    ];
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
```

## 4. Login Implementation

### index.php
```php
<?php
require_once 'api/config.php';
require_once 'includes/session.php';

startSecureSession();

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: home.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chat Demo - Login</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card card">
            <div class="card__body">
                <h2 class="login-title">AI Chat Demo</h2>
                <p class="login-subtitle">Enter your passcode to continue</p>
                <form id="login-form" method="POST" action="api/login.php">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                    <div class="form-group">
                        <label for="passcode" class="form-label">Passcode</label>
                        <input type="password" id="passcode" name="passcode" class="form-control" placeholder="Enter passcode" required>
                    </div>
                    <button type="submit" class="btn btn--primary btn--full-width">Login</button>
                </form>
                <?php if (isset($_GET['error'])): ?>
                    <div class="error-message">Invalid passcode. Please try again.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
```

### api/login.php
```php
<?php
require_once '../api/config.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

startSecureSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit();
}

// Validate CSRF token
if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
    header('Location: ../index.php?error=1');
    exit();
}

$passcode = sanitizeInput($_POST['passcode'] ?? '');

if ($passcode === LOGIN_PASSCODE) {
    loginUser();
    header('Location: ../home.php');
} else {
    header('Location: ../index.php?error=1');
}
exit();
?>
```

## 5. Main Chat Interface

### home.php
```php
<?php
require_once 'api/config.php';
require_once 'includes/session.php';
require_once 'includes/functions.php';

startSecureSession();
requireLogin();

if (!checkSessionTimeout()) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Chat Demo</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="home-container">
        <header class="home-header">
            <h1>AI Chat Demo</h1>
            <button id="logout-btn" class="btn btn--outline" onclick="logout()">Logout</button>
        </header>

        <main class="chat-container">
            <!-- DeepSeek Chat Room -->
            <div class="chat-room">
                <div class="chat-room-header">
                    <h3>DeepSeek Chat</h3>
                    <button class="btn btn--sm btn--secondary" onclick="clearChat('deepseek')">Clear Chat</button>
                </div>
                <div class="chat-messages" id="deepseek-messages"></div>
                <div class="chat-input-container">
                    <div class="chat-input-group">
                        <input type="text" class="form-control chat-input" id="deepseek-input" placeholder="Type your message...">
                        <button class="btn btn--primary send-btn" onclick="sendMessage('deepseek')">Send</button>
                    </div>
                </div>
            </div>

            <!-- OpenRouter Chat Room -->
            <div class="chat-room">
                <div class="chat-room-header">
                    <h3>OpenRouter Chat</h3>
                    <div class="model-selector">
                        <select id="openrouter-model" class="form-control">
                            <?php foreach (OPENROUTER_MODELS as $id => $name): ?>
                                <option value="<?php echo htmlspecialchars($id); ?>"><?php echo htmlspecialchars($name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn btn--sm btn--secondary" onclick="clearChat('openrouter')">Clear Chat</button>
                </div>
                <div class="chat-messages" id="openrouter-messages"></div>
                <div class="chat-input-container">
                    <div class="chat-input-group">
                        <input type="text" class="form-control chat-input" id="openrouter-input" placeholder="Type your message...">
                        <button class="btn btn--primary send-btn" onclick="sendMessage('openrouter')">Send</button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/app.js"></script>
    <script>
        const csrf_token = '<?php echo generateCSRFToken(); ?>';
        
        // Add event listeners for Enter key
        document.getElementById('deepseek-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') sendMessage('deepseek');
        });
        
        document.getElementById('openrouter-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') sendMessage('openrouter');
        });
        
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'api/logout.php';
            }
        }
    </script>
</body>
</html>
```

## 6. Message Sending API

### api/send_message.php
```php
<?php
require_once 'config.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

startSecureSession();
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Validate CSRF token
$headers = getallheaders();
$csrfToken = $headers['X-CSRF-Token'] ?? '';
if (!validateCSRFToken($csrfToken)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$message = sanitizeInput($input['message'] ?? '');
$provider = sanitizeInput($input['provider'] ?? '');
$model = sanitizeInput($input['model'] ?? '');

if (empty($message) || empty($provider)) {
    http_response_code(400);
    echo json_encode(['error' => 'Message and provider are required']);
    exit();
}

// Prepare API request based on provider
switch ($provider) {
    case 'deepseek':
        $url = DEEPSEEK_API_URL;
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . DEEPSEEK_API_KEY
        ];
        $data = [
            'model' => 'deepseek-chat',
            'messages' => [
                ['role' => 'user', 'content' => $message]
            ],
            'stream' => false
        ];
        break;
        
    case 'openrouter':
        $url = OPENROUTER_API_URL;
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . OPENROUTER_API_KEY,
            'HTTP-Referer: ' . $_SERVER['HTTP_HOST'],
            'X-Title: AI Chat Demo'
        ];
        $data = [
            'model' => $model ?: 'openai/gpt-4o',
            'messages' => [
                ['role' => 'user', 'content' => $message]
            ],
            'stream' => false
        ];
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid provider']);
        exit();
}

// Make API request
$result = makeApiRequest($url, $headers, $data);

if (!$result['success']) {
    http_response_code(500);
    echo json_encode([
        'error' => $result['error'],
        'response_time' => $result['response_time']
    ]);
    exit();
}

// Extract response content
$responseContent = '';
if (isset($result['data']['choices'][0]['message']['content'])) {
    $responseContent = $result['data']['choices'][0]['message']['content'];
} else {
    $responseContent = 'Sorry, I couldn\'t generate a response.';
}

echo json_encode([
    'success' => true,
    'message' => $responseContent,
    'response_time' => $result['response_time'],
    'timestamp' => date('Y-m-d H:i:s')
]);
?>
```

### api/logout.php
```php
<?php
require_once 'config.php';
require_once '../includes/session.php';

startSecureSession();
logoutUser();

header('Location: ../index.php');
exit();
?>
```

## 7. Enhanced JavaScript for PHP Integration

```javascript
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
```

## 8. Environment Setup

### Apache .htaccess
```apache
RewriteEngine On

# Redirect to HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Security headers
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY
Header always set X-XSS-Protection "1; mode=block"
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"

# Hide PHP version
Header unset X-Powered-By

# Prevent access to sensitive files
<Files ".env">
    Order allow,deny
    Deny from all
</Files>
```

## 9. Database Integration (Optional)

For production use, consider adding MySQL database to store chat history:

```sql
CREATE TABLE chat_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(255) NOT NULL,
    provider ENUM('deepseek', 'openrouter') NOT NULL,
    model VARCHAR(100),
    user_message TEXT NOT NULL,
    ai_response TEXT NOT NULL,
    response_time DECIMAL(8,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session_provider (session_id, provider)
);
```

## 10. Security Considerations

1. **API Key Protection**: Store API keys in environment variables
2. **CSRF Protection**: Implement CSRF tokens for all forms
3. **Input Validation**: Sanitize all user inputs
4. **Session Security**: Use secure session settings
5. **HTTPS**: Always use HTTPS in production
6. **Rate Limiting**: Implement rate limiting for API calls
7. **Error Handling**: Don't expose sensitive error information

## 11. Deployment Checklist

- [ ] Set environment variables for API keys
- [ ] Configure secure session settings
- [ ] Enable HTTPS
- [ ] Set appropriate file permissions
- [ ] Configure error logging
- [ ] Test API connectivity
- [ ] Set up monitoring and alerts
- [ ] Configure backup strategy

This implementation provides a complete, production-ready PHP chat application with proper security measures, error handling, and scalability considerations.