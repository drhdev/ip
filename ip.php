<?php
declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);

$historyFile = __DIR__ . '/history.json';

function getClientIP(): string {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return filter_var($_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP) ?: '';
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return filter_var(trim($ipList[0]), FILTER_VALIDATE_IP) ?: '';
    }
    return filter_var($_SERVER['REMOTE_ADDR'] ?? '', FILTER_VALIDATE_IP) ?: '';
}

function loadHistory(string $file): array {
    if (!file_exists($file)) {
        return [];
    }
    $json = file_get_contents($file);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

function saveHistory(string $file, array $history): void {
    file_put_contents($file, json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function outputJson($data): void {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

$allowedParams = ['screen', 'history'];
foreach ($_GET as $key => $value) {
    if (!in_array($key, $allowedParams, true)) {
        http_response_code(400);
        outputJson(['error' => 'Invalid parameter: ' . htmlspecialchars($key)]);
    }
}

$screen = '';
if (isset($_GET['screen'])) {
    if (preg_match('/^\d{2,5}x\d{2,5}$/', $_GET['screen'])) {
        $screen = $_GET['screen'];
    }
}

$ip = getClientIP();
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

date_default_timezone_set('UTC');
$timestamptz = date('c');

$entry = [
    'ip' => $ip,
    'user_agent' => $userAgent,
    'screen_resolution' => $screen,
    'country' => '',
    'city' => '',
    'timestamptz' => $timestamptz,
];

if (isset($_GET['history'])) {
    $history = loadHistory($historyFile);
    outputJson($history);
}

$history = loadHistory($historyFile);
array_unshift($history, $entry);
$history = array_slice($history, 0, 10);
saveHistory($history);

outputJson($entry);
