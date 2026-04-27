<?php
/**
 * وكيل شات Gemini — يُستدعى من المتصفح عبر POST JSON.
 *
 * المفتاح (أحد الخيارين):
 *   - متغير بيئي GEMINI_API_KEY
 *   - ملف .env في نفس المجلد (مُتجاهَل في Git) — أنشئه يدوياً ولا تضع المفتاح في الكود.
 *
 * اختياري: GEMINI_MODEL (افتراضي: gemini-2.5-flash)
 * اختياري: GEMINI_API_VERSION — v1 (افتراضي) أو v1beta
 */

declare(strict_types=1);

/**
 * تحميل متغيرات من .env المحلي دون استبدال ما هو معيّن مسبقاً في البيئة.
 */
/**
 * قراءة متغير بيئة بعد تحميل .env (getenv أحياناً لا يُحدَّث فوراً مع php -S).
 */
function gemini_env(string $name): string
{
    $v = getenv($name);
    if (is_string($v) && $v !== '') {
        return $v;
    }
    if (isset($_ENV[$name]) && is_string($_ENV[$name]) && $_ENV[$name] !== '') {
        return $_ENV[$name];
    }
    if (isset($_SERVER[$name]) && is_string($_SERVER[$name]) && $_SERVER[$name] !== '') {
        return $_SERVER[$name];
    }

    return '';
}

function gemini_load_dotenv(string $path): void
{
    if (!is_readable($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        return;
    }
    if ($lines !== [] && strncmp($lines[0], "\xEF\xBB\xBF", 3) === 0) {
        $lines[0] = substr($lines[0], 3);
    }
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || (isset($line[0]) && $line[0] === '#')) {
            continue;
        }
        $eq = strpos($line, '=');
        if ($eq === false) {
            continue;
        }
        $name = trim(substr($line, 0, $eq));
        $value = trim(substr($line, $eq + 1));
        if ($name === '') {
            continue;
        }
        $len = strlen($value);
        if ($len >= 2) {
            $q = $value[0];
            if (($q === '"' || $q === "'") && $value[$len - 1] === $q) {
                $value = substr($value, 1, -1);
            }
        }
        if (getenv($name) !== false) {
            continue;
        }
        if ($value !== '') {
            putenv($name . '=' . $value);
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

gemini_load_dotenv(__DIR__ . DIRECTORY_SEPARATOR . '.env');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'الطريقة غير مسموحة'], JSON_UNESCAPED_UNICODE);
    exit;
}

$apiKey = gemini_env('GEMINI_API_KEY');
if ($apiKey === '') {
    http_response_code(503);
    echo json_encode([
        'ok' => false,
        'error' => 'لم يُضبط GEMINI_API_KEY. افتح الملف ' . basename(__DIR__) . '/.env وألصق المفتاح بعد GEMINI_API_KEY= (أو نفّذ: export GEMINI_API_KEY="مفتاحك") ثم أعد تشغيل php -S.',
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$model = gemini_env('GEMINI_MODEL');
$model = $model !== '' ? $model : 'gemini-2.5-flash';
$model = preg_match('/^[a-zA-Z0-9._-]+$/', $model) ? $model : 'gemini-2.5-flash';

$apiVersion = gemini_env('GEMINI_API_VERSION');
$apiVersion = $apiVersion !== '' ? $apiVersion : 'v1';
$apiVersion = in_array($apiVersion, ['v1', 'v1beta'], true) ? $apiVersion : 'v1';

$raw = file_get_contents('php://input');
$data = is_string($raw) ? json_decode($raw, true) : null;
if (!is_array($data)) {
    echo json_encode(['ok' => false, 'error' => 'جسم الطلب غير صالح'], JSON_UNESCAPED_UNICODE);
    exit;
}

$message = isset($data['message']) ? trim((string) $data['message']) : '';
if ($message === '' || mb_strlen($message, 'UTF-8') > 8000) {
    echo json_encode(['ok' => false, 'error' => 'الرسالة فارغة أو طويلة جداً'], JSON_UNESCAPED_UNICODE);
    exit;
}

// لا نستخدم systemInstruction في JSON — بعض مسارات v1/v1beta ترفض الاسمين.
// ندمج التعليمات في أول دور user فقط (سلوك موحّد ومتوافق).
$systemText = 'أنت مساعد ذكي لمنصة عربية لمشاريع التخرج الجامعية. '
    . 'ساعد في: شرح كيفية استخدام المنصة، أفكار مشاريع، تقنيات برمجية عامة، وكتابة نصوص قصيرة. '
    . 'أجب بالعربية ما لم يُطلب غير ذلك. كن مختصراً ومهذباً. '
    . 'لا تخترع أرقاماً أو روابط مشاريع محددة في المنصة. '
    . 'لا تطلب كلمات مرور أو بيانات حساسة.';
$systemPrefix = "[تعليمات للمساعد — طبّقها في كل رد]\n" . $systemText . "\n---\n";

$historyIn = $data['history'] ?? [];
$contents = [];

if (is_array($historyIn)) {
    $slice = array_slice($historyIn, -24);
    foreach ($slice as $turn) {
        if (!is_array($turn) || !isset($turn['role'], $turn['text'])) {
            continue;
        }
        $role = $turn['role'] === 'model' ? 'model' : 'user';
        $text = mb_substr(trim((string) $turn['text']), 0, 8000, 'UTF-8');
        if ($text === '') {
            continue;
        }
        $contents[] = [
            'role' => $role,
            'parts' => [['text' => $text]],
        ];
    }
}

$contents[] = [
    'role' => 'user',
    'parts' => [['text' => $message]],
];

$systemInjected = false;
foreach ($contents as $i => $row) {
    if (($row['role'] ?? '') !== 'user' || !isset($row['parts'][0]['text'])) {
        continue;
    }
    $t = (string) $row['parts'][0]['text'];
    $contents[$i]['parts'][0]['text'] = $systemPrefix . $t;
    $systemInjected = true;
    break;
}
if (!$systemInjected && $contents !== []) {
    for ($j = count($contents) - 1; $j >= 0; $j--) {
        if (($contents[$j]['role'] ?? '') === 'user' && isset($contents[$j]['parts'][0]['text'])) {
            $contents[$j]['parts'][0]['text'] = $systemPrefix . (string) $contents[$j]['parts'][0]['text'];
            break;
        }
    }
}

$url = sprintf(
    'https://generativelanguage.googleapis.com/%s/models/%s:generateContent?key=%s',
    $apiVersion,
    rawurlencode($model),
    rawurlencode($apiKey)
);

$payload = [
    'contents' => $contents,
    'generationConfig' => [
        'maxOutputTokens' => 1024,
        'temperature' => 0.65,
    ],
];

$jsonBody = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
if ($jsonBody === false) {
    echo json_encode(['ok' => false, 'error' => 'تعذر ترميز الطلب'], JSON_UNESCAPED_UNICODE);
    exit;
}

$response = null;
$httpCode = 0;

if (function_exists('curl_init')) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json; charset=utf-8'],
        CURLOPT_POSTFIELDS => $jsonBody,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
    ]);
    $response = curl_exec($ch);
    $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
} else {
    $ctx = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json; charset=utf-8\r\n",
            'content' => $jsonBody,
            'timeout' => 60,
        ],
    ]);
    $response = @file_get_contents($url, false, $ctx);
    if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $m)) {
        $httpCode = (int) $m[1];
    }
}

if ($response === false || $response === '') {
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => 'فشل الاتصال بخدمة Gemini'], JSON_UNESCAPED_UNICODE);
    exit;
}

$decoded = json_decode($response, true);
if (!is_array($decoded)) {
    http_response_code(502);
    echo json_encode(['ok' => false, 'error' => 'استجابة غير متوقعة من الخادم'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($httpCode !== 200) {
    $err = (string) ($decoded['error']['message'] ?? ('رمز HTTP: ' . $httpCode));
    $errLc = strtolower($err);
    $quotaHit = str_contains($errLc, 'quota')
        || str_contains($errLc, 'rate limit')
        || str_contains($errLc, 'resource_exhausted')
        || str_contains($errLc, 'resource exhausted');
    if ($quotaHit) {
        $err = 'حصّة Gemini لهذا المفتاح أو النموذج (' . $model . ') غير متاحة الآن. '
            . 'انتظر قليلاً ثم أعد المحاولة، أو فعّل الفوترة من Google AI Studio، '
            . 'أو غيّر GEMINI_MODEL في ملف .env (مثلاً gemini-2.5-flash-lite إذا بقي الحدّ ضيقاً). '
            . 'https://ai.google.dev/gemini-api/docs/rate-limits';
    }
    http_response_code($httpCode === 429 ? 429 : 502);
    echo json_encode(['ok' => false, 'error' => $err], JSON_UNESCAPED_UNICODE);
    exit;
}

$text = '';
if (!empty($decoded['candidates'][0]['content']['parts'])) {
    foreach ($decoded['candidates'][0]['content']['parts'] as $part) {
        if (isset($part['text'])) {
            $text .= $part['text'];
        }
    }
}

$text = trim($text);
if ($text === '') {
    $reason = $decoded['candidates'][0]['finishReason'] ?? 'UNKNOWN';
    echo json_encode([
        'ok' => false,
        'error' => 'لم يُرجع النموذج نصاً صالحاً. السبب: ' . $reason,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['ok' => true, 'reply' => $text], JSON_UNESCAPED_UNICODE);
