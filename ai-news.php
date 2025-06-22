<?php
header('Content-Type: application/json');

// --- הגדרות ---
$telegramChannels = [
    "https://t.me/s/divuhim1234",
    "https://t.me/s/abualiexpress",
    "https://t.me/s/amitsegal",
    "https://t.me/s/yedidya_epshteyn",
    "https://t.me/s/HallelBittonRosen",
    "https://t.me/s/kolahadasot",
    "https://t.me/s/Now14israel",
    "https://t.me/s/israel_news_telegram",
    "https://t.me/s/Yotamzimritelegram",
    "https://t.me/s/amirbohbot",
    "https://t.me/s/tamirmorag14",
    "https://t.me/bengvir",
    "https://t.me/s/tzap1"
];
$AI_API_KEY = 'AIzaSyD7paY_GeC_33FVTUAcune29JIxJWZ3Fl8';
$DELAY_MS = 1000;

// --- פונקציות עזר ---
function fetchTelegramHtml($url) {
    $opts = ["http" => ["header" => "User-Agent: Mozilla/5.0"]];
    return @file_get_contents($url, false, stream_context_create($opts));
}

function cleanText($text) {
    $text = preg_replace('/<br\\s*\\/?>/i', "\n", $text);
    $text = strip_tags($text);
    $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $patterns = [
        '/https?:\\/\\/\\S+/', '/להצטרפות.*?\\n/', '/עקבו אחרי.*?\\n/',
        '/לשיתוף.*?\\n/', '/פיד ימין.*?\\n/', '/בסטטוס.*?\\n/',
    ];
    return trim(preg_replace($patterns, '', $text));
}

function parseMessages($html) {
    preg_match_all('/<div class="tgme_widget_message_wrap js-widget_message_wrap"[\\s\\S]*?<\\/div><\\/div>/', $html, $blocks);
    $messages = [];
    foreach ($blocks[0] as $block) {
        preg_match('/data-post="[^"]*?\\/(\\d+)"/', $block, $idMatch);
        preg_match('/<div class="tgme_widget_message_text js-message_text"[^>]*>([\\s\\S]*?)<\\/div>/', $block, $textMatch);
        $id = $idMatch[1] ?? null;
        $text = isset($textMatch[1]) ? cleanText($textMatch[1]) : '';
        if ($id && $text) $messages[] = ['id' => (int)$id, 'text' => $text];
    }
    usort($messages, fn($a, $b) => $a['id'] <=> $b['id']);
    return $messages;
}

function fetchAIResponse($text, $apiKey) {
    $url = "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent?key=$apiKey";
    $payload = json_encode([
        "contents" => [["parts" => [["text" => "סכם את הטקסט הבא בקצרה: $text"]]]],
        "generationConfig" => ["temperature" => 0.7, "maxOutputTokens" => 200]
    ], JSON_UNESCAPED_UNICODE);
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => $payload
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    $json = json_decode($response, true);
    return $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
}

// --- שליפה וסיכום ---
$summaryPath = __DIR__ . '/summary.json';
$lastData = file_exists($summaryPath) ? json_decode(file_get_contents($summaryPath), true) : ["lastId" => []];
$results = [];

foreach ($telegramChannels as $url) {
    $html = fetchTelegramHtml($url);
    if (!$html) continue;
    $messages = parseMessages($html);
    foreach ($messages as $msg) {
        $lastId = $lastData["lastId"][$url] ?? 0;
        if ($msg["id"] > $lastId) {
            $aiSummary = fetchAIResponse($msg["text"], $AI_API_KEY);
            if ($aiSummary) {
                $results[] = $aiSummary;
                $lastData["lastId"][$url] = $msg["id"];
                usleep($DELAY_MS * 1000);
            }
        }
    }
}

if ($results) {
    $joined = implode("\n\n", $results);
    $lastData["text"] = $joined;
    $lastData["updatedAt"] = date("d/m/Y H:i:s");
    file_put_contents($summaryPath, json_encode($lastData, JSON_UNESCAPED_UNICODE));
}

echo json_encode([
    "text" => $lastData["text"] ?? "אין סיכומים זמינים כרגע.",
    "updatedAt" => $lastData["updatedAt"] ?? date("d/m/Y H:i:s")
], JSON_UNESCAPED_UNICODE);
?>
