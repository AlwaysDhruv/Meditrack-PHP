<?php
require "../auth/ai_helper.php";

header("Content-Type: application/json");

$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    echo json_encode(["analysis" => "No data received"]);
    exit();
}

$question = $input["question"] ?? null;

$prompt = "
Analyze this record safely. Do not diagnose.

Record:
" . json_encode($input, JSON_PRETTY_PRINT) . "

User question (if any):
$question

Provide a clean, structured answer.
";

echo json_encode([
    "analysis" => askGemini($prompt)
]);
?>
