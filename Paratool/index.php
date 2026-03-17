<?php
// index.php - Simple AI Paraphraser Chatbot using PHP and Gemini API

$GEMINI_API_KEY = 'AIzaSyBPCbBwM69bstyFDaR5xwAZ__dLGKrU18E';

// ── List Models ──────────────────────────────────────────────────────────────

// ── Load prompt instructions ─────────────────────────────────────────────────
require_once __DIR__ . '/instructions.php';

// ── Paraphrase Function ───────────────────────────────────────────────────────
function paraphrase($text, $apiKey) {
    // FIX: use a text model, not gemini-pro-vision (vision is for images only)
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent';

    $instructions = get_paraphrase_instructions($text);

    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $instructions]
                ]
            ]
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-goog-api-key: ' . $apiKey
        ]);
    $response   = curl_exec($ch);
    $curl_error = curl_error($ch);
    $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curl_error) {
        return 'cURL Error: ' . $curl_error;
    }
    if ($http_code !== 200) {
        return 'HTTP Error ' . $http_code . ': ' . $response;
    }

    $result = json_decode($response, true);
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        return trim($result['candidates'][0]['content']['parts'][0]['text']);
    }

    return 'API Error: ' . $response;
}

// ── Handle POST ───────────────────────────────────────────────────────────────
$paraphrased = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['user_input'])) {
    $user_input  = $_POST['user_input'];
    $paraphrased = paraphrase($user_input, $GEMINI_API_KEY);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>WordSmithy</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>WordSmithy</h2>


        <form method="post">
            <label for="user_input">Enter text to paraphrase:</label><br>
            <textarea name="user_input" id="user_input" required><?php
                if (!empty($_POST['user_input'])) echo htmlspecialchars($_POST['user_input']);
            ?></textarea><br>
            <button type="submit">Paraphrase</button>
        </form>

        <?php if ($paraphrased): ?>
            <div class="result">
                <strong>Paraphrased:</strong><br>
                <?php echo nl2br(htmlspecialchars($paraphrased)); ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="footer">
        Program created by Engr. Exequiel M. Sabater
    </div>
</body>
</html>