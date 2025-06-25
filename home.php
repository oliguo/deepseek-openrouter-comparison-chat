<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
    <script>
      const csrf_token = '<?php echo htmlspecialchars($_SESSION["csrf_token"]); ?>';
    </script>
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
                    <h3 style="font-weight:600;font-size:1.15rem;">DeepSeek Chat</h3>
                    <button class="btn btn--sm btn--secondary" onclick="clearChat('deepseek')">Clear Chat</button>
                </div>
                <div class="chat-messages" id="deepseek-messages"></div>
                <form class="chat-input-container" onsubmit="event.preventDefault();sendMessage('deepseek');">
                    <div class="chat-input-group">
                        <input type="text" class="form-control chat-input" id="deepseek-input" placeholder="Type your message..." autocomplete="off">
                        <button type="submit" class="btn btn--primary send-btn">Send</button>
                    </div>
                </form>
            </div>

            <!-- OpenRouter Chat Room -->
            <div class="chat-room">
                <div class="chat-room-header">
                    <h3 style="font-weight:600;font-size:1.15rem;">OpenRouter Chat</h3>
                    <div class="model-selector">
                        <select id="openrouter-model" class="form-control">
                            <!-- Options will be loaded dynamically -->
                        </select>
                    </div>
                    <button class="btn btn--sm btn--secondary" onclick="clearChat('openrouter')">Clear Chat</button>
                </div>
                <div class="chat-messages" id="openrouter-messages"></div>
                <form class="chat-input-container" onsubmit="event.preventDefault();sendMessage('openrouter');">
                    <div class="chat-input-group">
                        <input type="text" class="form-control chat-input" id="openrouter-input" placeholder="Type your message..." autocomplete="off">
                        <button type="submit" class="btn btn--primary send-btn">Send</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="assets/app.js"></script>
    <script>        
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

        // Fetch OpenRouter models and populate selector
        fetch('api/openrouter_models.php')
            .then(res => res.json())
            .then(data => {
                const select = document.getElementById('openrouter-model');
                select.innerHTML = '';
                if (data.models && data.models.length > 0) {
                    data.models.forEach(model => {
                        const opt = document.createElement('option');
                        opt.value = model.id;
                        opt.textContent = model.name;
                        select.appendChild(opt);
                    });
                } else {
                    const opt = document.createElement('option');
                    opt.value = '';
                    opt.textContent = 'No models available';
                    select.appendChild(opt);
                }
            })
            .catch(() => {
                const select = document.getElementById('openrouter-model');
                select.innerHTML = '<option value="">Failed to load models</option>';
            });
    </script>
</body>
</html>
