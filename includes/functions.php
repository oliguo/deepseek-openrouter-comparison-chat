<?php
function makeApiRequest($url, $headers, $data)
{
    $startTime = microtime(true);

    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => $headers,
    ));


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

function sanitizeInput($input)
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateCSRFToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token)
{
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
