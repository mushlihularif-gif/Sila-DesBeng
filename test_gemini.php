<?php
$apiKey = 'AQ.Ab8RN6KUChtqwgdbLQ-Pq_S2dTocJxWkOuJg5tyXpiieDqXbJA';
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

$data = [
    'contents' => [
        ['role' => 'user', 'parts' => [['text' => 'Hello']]]
    ]
];

$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
        'ignore_errors' => true
    ]
];

$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);
echo substr($result, 0, 200);
?>
