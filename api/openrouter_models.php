<?php
require_once 'config.php';

header('Content-Type: application/json');

$curl = curl_init();
curl_setopt_array($curl, array(
    CURLOPT_URL => OPENROUTER_API_URL . '/api/v1/models',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'GET',
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . OPENROUTER_API_KEY
    ),
));

$response = curl_exec($curl);
$error = curl_error($curl);
curl_close($curl);

if ($error) {
    http_response_code(500);
    echo json_encode(['error' => 'cURL Error: ' . $error]);
    exit();
}

$data = json_decode($response, true);
if (!isset($data['data'])) {
    http_response_code(500);
    echo json_encode(['error' => 'Invalid response from OpenRouter API']);
    exit();
}

$models = [];
foreach ($data['data'] as $model) {
    if (isset($model['id']) && isset($model['name'])) {
        $models[] = [
            'id' => $model['id'],
            'name' => $model['name']
        ];
    }
}
echo json_encode(['models' => $models]);
