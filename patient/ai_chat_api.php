<?php
require "../auth/ai_helper.php";

header("Content-Type: application/json");

$input = json_decode(file_get_contents("php://input"), true);

if (!$input || !isset($input["question"])) {
    echo json_encode(["analysis" => "Please enter a message to begin chatting."]);
    exit();
}

$question = trim($input["question"]);

/* ============================================================
   HEALTH-ONLY AI PROMPT (No diagnosis, no medical analysis)
============================================================ */
$prompt = "
You are a safe AI health companion.
Your job is to give helpful, friendly, non-diagnostic guidance.

IMPORTANT RULES:
- NEVER diagnose any disease.
- NEVER mention illnesses directly.
- NEVER tell the user they 'have' something.
- ALWAYS give general lifestyle, wellness, diet, sleep, hydration,
  stress relief, and mental health tips.
- ALWAYS tell the user to consult a real doctor if symptoms sound concerning.

User Question:
$question

Provide a clean and structured response with:
- Friendly guidance
- Practical tips
- Optional bullet points
- Clear and simple explanation
- Safety notes if needed
";

/* ============================================================
   CALL GEMINI
============================================================ */
$response = askGemini($prompt);

/* ============================================================
   RETURN
============================================================ */
echo json_encode([
    "analysis" => $response
]);
?>
