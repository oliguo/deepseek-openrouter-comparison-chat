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
