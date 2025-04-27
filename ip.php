<?php
declare(strict_types=1);

ini_set('display_errors', '0');
error_reporting(E_ALL);

$historyFile = __DIR__ . '/history.json';

// Get client IP
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

// Load and save history
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

// Output JSON and exit
function outputJson($data): void {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

// Special case: history mode
if (isset($_GET['history'])) {
    $history = loadHistory($historyFile);
    outputJson($history);
}

// Create entry
$ip = getClientIP();
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
date_default_timezone_set('UTC');
$timestamptz = date('c');

$entry = [
    'ip' => $ip,
    'user_agent' => $userAgent,
    'timestamptz' => $timestamptz,
];

// Save entry to history
$history = loadHistory($historyFile);
array_unshift($history, $entry);
$history = array_slice($history, 0, 10);
saveHistory($history);

// Return current visitor data
outputJson($entry);