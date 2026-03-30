<?php
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['reply' => 'Invalid request.']);
    exit;
}

$userMessage = trim($_POST['chatbot_message'] ?? '');
if (empty($userMessage)) {
    echo json_encode(['reply' => 'Please type a message.']);
    exit;
}

if (!function_exists('curl_init')) {
    echo json_encode(['reply' => 'ERROR: cURL is not enabled in PHP.']);
    exit;
}

$history = json_decode($_POST['history'] ?? '[]', true);
if (!is_array($history)) $history = [];

$messages = [];
foreach ($history as $h) {
    if (!empty($h['role']) && !empty($h['content'])) {
        $messages[] = ['role' => $h['role'], 'content' => $h['content']];
    }
}
$messages[] = ['role' => 'user', 'content' => $userMessage];

$systemPrompt = "You are Rita, the friendly AI shopping assistant for RetailHub — Sri Lanka's premium online retail store.

You help customers with:
- Finding products (electronics, fashion, home & living, sports & fitness)
- Order tracking and status inquiries
- Delivery information (2-3 business days island-wide, same day delivery in selected areas for orders before 2 PM)
- Returns & exchanges (within 7 days at physical outlets)
- Payment methods (Cash on Delivery, Debit/Credit Card, PayPal, Bank Transfer)
- Account & wishlist help
- General shopping advice

Key facts about RetailHub:
- Based in Sri Lanka, delivers island-wide
- Free delivery on orders over LKR 2,500
- 24/7 customer support
- Secure & encrypted checkout
- Categories: Electronics, Fashion & Footwear, Home & Living, Sports & Fitness
- Trusted brands: Apple, Samsung, Nike, Adidas, Sony, Bose, Casio, Canon, LG

Keep responses concise, friendly and helpful. Use emojis sparingly.
If asked about specific order details, ask them to log in and check their dashboard.
Do not make up product prices or stock levels.";

$apiKey = 'sk-ant-YOUR_REAL_KEY_HERE'; // ← REPLACE THIS

$payload = json_encode([
    'model'      => 'claude-haiku-4-5-20251001',
    'max_tokens' => 400,
    'system'     => $systemPrompt,
    'messages'   => $messages,
]);

$ch = curl_init('https://api.anthropic.com/v1/messages');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'x-api-key: ' . $apiKey,
        'anthropic-version: 2023-06-01',
    ],
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_SSL_VERIFYPEER => false,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

if ($curlErr) {
    echo json_encode(['reply' => 'cURL error: ' . $curlErr]);
    exit;
}

$reply = "Sorry, I'm having trouble connecting right now. Please try again! 🙏";

if ($httpCode === 200 && $response) {
    $data  = json_decode($response, true);
    $reply = $data['content'][0]['text'] ?? $reply;
} elseif ($httpCode === 401) {
    $reply = 'API key is invalid. Please contact support.';
} elseif ($httpCode === 429) {
    $reply = 'Too many requests — please wait a moment.';
} elseif ($httpCode === 400) {
    $reply = 'Bad request — error code 400.';
} else {
    $reply = 'Server error: HTTP ' . $httpCode;
}

echo json_encode(['reply' => $reply]);
exit;