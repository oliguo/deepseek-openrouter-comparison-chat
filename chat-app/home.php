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
