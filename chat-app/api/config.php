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
