<?php
// Safe, minimal IP API compatible with PHP 5.5

error_reporting(E_ALL);
ini_set('display_errors', '0');

$historyFile = __DIR__ . '/history.json';

// Helper: Get client IP
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ipList[0]);
    } else {
        $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    }
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '';
}

// Helper: Load history
function loadHistory($file) {
    if (!file_exists($file)) {
        return array();
    }
    $json = file_get_contents($file);
    if ($json === false) {
        return array();
    }
    $data = json_decode($json, true);
    return is_array($data) ? $data : array();
}

// Helper: Save history
function saveHistory($file, $history) {
    @file_put_contents($file, json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

// Helper: Output JSON and stop
function outputJson($data) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

// If history requested
if (isset($_GET['history'])) {
    $history = loadHistory($historyFile);
    outputJson($history);
}

// Build current visitor info
$ip = getClientIP();
$userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
date_default_timezone_set('UTC');
$timestamptz = date('c');

$entry = array(
    'ip' => $ip,
    'user_agent' => $userAgent,
    'timestamptz' => $timestamptz
);

// Update history
$history = loadHistory($historyFile);
array_unshift($history, $entry);
$history = array_slice($history, 0, 10);
saveHistory($historyFile, $history);

// Always output current info
outputJson($entry);
?>