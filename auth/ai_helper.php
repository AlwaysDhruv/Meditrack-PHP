<?php
require_once "../config/env.php";
loadEnv("../.env");

function askGemini($prompt) {

    $apiKey = $_ENV["GEMINI_API_KEY"];

    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-pro:generateContent?key=" . $apiKey;

    $payload = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $prompt]
                ]
            ]
        ]
    ];

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $res = json_decode($response, true);

    if (!isset($res["candidates"][0]["content"]["parts"][0]["text"]))
        return "AI response unavailable.";

    return $res["candidates"][0]["content"]["parts"][0]["text"];
}
?>
