<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/config.php'; 

$role = $_SESSION['role'] ?? 'student';

if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === 'YOUR_GEMINI_API_KEY_HERE' || empty(GEMINI_API_KEY)) {
    echo json_encode([
        "error" => "Gemini API key not configured. Please set it in includes/config.php.",
        "prediction" => "Mock Data: You are doing well.",
        "risk_factors" => ["Mock Data: Low attendance"],
        "recommendation" => "Mock Data: Attend more classes."
    ]);
    exit;
}

$prompt = "";
if ($role == 'faculty') {
    $prompt = "You are an AI academic advisor for a faculty member. Analyze this hypothetical data: 120 students in class, 10% have below 75% attendance. Recent quiz average was 65%. Provide a short JSON response exactly with these keys: 'prediction' (string), 'risk_factors' (array of strings), 'recommendation' (string). Do not use markdown blocks, just raw JSON. Example: {\"prediction\": \"value\", \"risk_factors\": [\"value1\"], \"recommendation\": \"value\"}";
} else {
    $prompt = "You are an AI academic advisor for a student. Analyze this hypothetical data: Student attendance is 78%, recent quiz scores are 70% and 85%. Provide a short JSON response exactly with these keys: 'prediction' (string), 'risk_factors' (array of strings), 'recommendation' (string). Do not use markdown blocks, just raw JSON. Example: {\"prediction\": \"value\", \"risk_factors\": [\"value1\"], \"recommendation\": \"value\"}";
}

$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . GEMINI_API_KEY;

$data = [
    'contents' => [
        [
            'parts' => [
                ['text' => $prompt]
            ]
        ]
    ],
    'generationConfig' => [
        'responseMimeType' => 'application/json'
    ]
];

// Use cURL for better reliability and handling local SSL issues
$ch = curl_init($url);
$payload = json_encode($data);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Disable SSL verification for local XAMPP environments
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$result = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if(curl_errno($ch)){
    echo json_encode([
        "error" => "cURL Error: " . curl_error($ch),
        "prediction" => "Network error.",
        "risk_factors" => [],
        "recommendation" => "Please check internet connection."
    ]);
    curl_close($ch);
    exit;
}
curl_close($ch);

if ($httpcode !== 200) {
    echo json_encode([
        "error" => "API returned error code: " . $httpcode . " - Raw response: " . $result,
        "prediction" => "API Request Failed.",
        "risk_factors" => [],
        "recommendation" => "Verify API key is valid and has quota."
    ]);
    exit;
}

$response_data = json_decode($result, true);

if (isset($response_data['candidates'][0]['content']['parts'][0]['text'])) {
    $ai_text = $response_data['candidates'][0]['content']['parts'][0]['text'];
    
    // Clean up markdown wrapping if Gemini ignores instructions
    $ai_text = str_replace(['```json', '```', "\n"], '', $ai_text);
    $ai_text = trim($ai_text);
    
    $ai_json = json_decode($ai_text, true);
    
    if (json_last_error() === JSON_ERROR_NONE) {
        echo json_encode($ai_json);
    } else {
        echo json_encode([
            "prediction" => "AI responded, but format was unexpected.",
            "raw_text" => substr($ai_text, 0, 50) . "...",
            "risk_factors" => ["Format parsing error"],
            "recommendation" => "Try again later."
        ]);
    }
} else {
    echo json_encode([
        "error" => "Unexpected structure from Gemini API.",
        "raw" => substr($result, 0, 100)
    ]);
}
?>
