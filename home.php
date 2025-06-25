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
    <div class="main-app-container">
        <header class="main-header">
            <div class="main-header-content">
                <span class="main-logo">🤖</span>
                <span class="main-title">AI Chat Demo</span>
            </div>
            <button id="logout-btn" class="btn btn--outline" onclick="logout()">Logout</button>
        </header>
        <main class="chat-rooms-wrapper">
            <div class="chat-room new-layout">
                <div class="chat-room-header">
                    <h3>DeepSeek Chat</h3>
                </div>
                <div class="chat-messages" id="deepseek-messages"></div>
                <form class="chat-input-container" onsubmit="event.preventDefault();sendMessage('deepseek');">
                    <div class="chat-input-group">
                        <input type="text" class="form-control chat-input" id="deepseek-input" placeholder="Type your message..." autocomplete="off">
                        <button type="submit" class="btn btn--primary send-btn">Send</button>
                        <button type="button" class="icon-btn clear-btn" title="Clear Chat" onclick="clearChat('deepseek')">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.5 9V14M10 9V14M12.5 9V14M3.333 5.833h13.334M5.833 5.833V15.833a1.667 1.667 0 001.667 1.667h5a1.667 1.667 0 001.667-1.667V5.833m-8.334 0V4.167A1.667 1.667 0 017.5 2.5h5a1.667 1.667 0 011.667 1.667v1.666" stroke="#888" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                </form>
            </div>
            <div class="chat-room new-layout">
                <div class="chat-room-header openrouter-header">
                    <h3>OpenRouter Chat</h3>
                </div>
                <div class="model-selector-row">
                    <div class="model-selector" style="position:relative;min-width:180px;">
                        <input type="text" id="openrouter-model-input" class="form-control" placeholder="Search model..." autocomplete="off">
                        <ul id="openrouter-model-list" class="autocomplete-list" style="display:none;"></ul>
                    </div>
                </div>
                <div class="chat-messages" id="openrouter-messages"></div>
                <form class="chat-input-container" onsubmit="event.preventDefault();sendMessage('openrouter');">
                    <div class="chat-input-group">
                        <input type="text" class="form-control chat-input" id="openrouter-input" placeholder="Type your message..." autocomplete="off">
                        <button type="submit" class="btn btn--primary send-btn">Send</button>
                        <button type="button" class="icon-btn clear-btn" title="Clear Chat" onclick="clearChat('openrouter')">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.5 9V14M10 9V14M12.5 9V14M3.333 5.833h13.334M5.833 5.833V15.833a1.667 1.667 0 001.667 1.667h5a1.667 1.667 0 001.667-1.667V5.833m-8.334 0V4.167A1.667 1.667 0 017.5 2.5h5a1.667 1.667 0 011.667 1.667v1.666" stroke="#888" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    </div>
                </form>
            </div>
            <div class="chat-room new-layout">
                <div class="chat-room-header gemini-header">
                    <h3>Google Gemini Chat</h3>
                </div>
                <div class="chat-messages" id="gemini-messages"></div>
                <form class="chat-input-container" onsubmit="event.preventDefault();sendMessage('gemini');">
                    <div class="chat-input-group">
                        <input type="text" class="form-control chat-input" id="gemini-input" placeholder="Type your message..." autocomplete="off">
                        <button type="submit" class="btn btn--primary send-btn">Send</button>
                        <button type="button" class="icon-btn clear-btn" title="Clear Chat" onclick="clearChat('gemini')">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M7.5 9V14M10 9V14M12.5 9V14M3.333 5.833h13.334M5.833 5.833V15.833a1.667 1.667 0 001.667 1.667h5a1.667 1.667 0 001.667-1.667V5.833m-8.334 0V4.167A1.667 1.667 0 017.5 2.5h5a1.667 1.667 0 011.667 1.667v1.666" stroke="#888" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
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
        document.getElementById('gemini-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') sendMessage('gemini');
        });
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'api/logout.php';
            }
        }
        // --- OpenRouter model autocomplete logic ---
        let openrouterModels = [];
        let selectedModelId = '';
        const modelInput = document.getElementById('openrouter-model-input');
        const modelList = document.getElementById('openrouter-model-list');
        function renderModelList(filter) {
            modelList.innerHTML = '';
            const filtered = openrouterModels.filter(m => m.name.toLowerCase().includes(filter.toLowerCase()));
            if (filtered.length === 0) {
                const li = document.createElement('li');
                li.textContent = 'No models found';
                li.className = 'autocomplete-item disabled';
                modelList.appendChild(li);
                modelList.style.display = 'block';
                return;
            }
            filtered.forEach(model => {
                const li = document.createElement('li');
                li.textContent = model.name;
                li.className = 'autocomplete-item';
                li.onclick = function() {
                    modelInput.value = model.name;
                    selectedModelId = model.id;
                    modelList.style.display = 'none';
                };
                modelList.appendChild(li);
            });
            modelList.style.display = 'block';
        }
        modelInput.addEventListener('input', function() {
            selectedModelId = '';
            if (this.value.trim() === '') {
                modelList.style.display = 'none';
                return;
            }
            renderModelList(this.value);
        });
        modelInput.addEventListener('focus', function() {
            if (this.value.trim() !== '') renderModelList(this.value);
        });
        document.addEventListener('click', function(e) {
            if (!modelInput.contains(e.target) && !modelList.contains(e.target)) {
                modelList.style.display = 'none';
            }
        });
        // Fetch OpenRouter models and populate autocomplete
        fetch('api/openrouter_models.php')
            .then(res => res.json())
            .then(data => {
                if (data.models && data.models.length > 0) {
                    openrouterModels = data.models;
                }
            });
        // --- END autocomplete logic ---
        // Patch sendMessage to use selectedModelId
        window.getOpenRouterSelectedModel = function() {
            // If user typed and didn't select, try to match by name
            if (!selectedModelId && modelInput.value.trim() !== '') {
                const match = openrouterModels.find(m => m.name.toLowerCase() === modelInput.value.trim().toLowerCase());
                if (match) {
                    selectedModelId = match.id;
                    console.log('Matched model by name:', match.name, 'ID:', match.id);
                }
            }
            
            // If still no model selected, use the first available model as fallback
            if (!selectedModelId && openrouterModels.length > 0) {
                selectedModelId = openrouterModels[0].id;
                modelInput.value = openrouterModels[0].name;
                console.log('Using fallback model:', openrouterModels[0].name, 'ID:', openrouterModels[0].id);
            }
            
            console.log('Selected OpenRouter Model ID:', selectedModelId);
            return selectedModelId || '';
        };
    </script>
</body>
</html>
