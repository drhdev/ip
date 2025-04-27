<?php
declare(strict_types=1);

// Fehler nicht nach außen anzeigen
ini_set('display_errors', '0');
error_reporting(E_ALL);

// History-Datei festlegen
$historyFile = __DIR__ . '/history.json';

// IP-Adresse ermitteln
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

// History laden
function loadHistory(string $file): array {
    if (!file_exists($file)) {
        return [];
    }
    $json = file_get_contents($file);
    $data = json_decode($json, true);
    return is_array($data) ? $data : [];
}

// History speichern
function saveHistory(string $file, array $history): void {
    file_put_contents($file, json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

// Antwort als JSON ausgeben und beenden
function outputJson($data): void {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

// Eingehende Parameter absichern
$allowedParams = ['screen', 'history'];
foreach ($_GET as $key => $value) {
    if (!in_array($key, $allowedParams, true)) {
        http_response_code(400);
        outputJson(['error' => 'Ungültiger Parameter: ' . htmlspecialchars($key)]);
    }
}

// Bildschirmauflösung, nur erlaubte Zeichen (z.B. 1920x1080)
$screen = '';
if (isset($_GET['screen'])) {
    if (preg_match('/^\d{2,5}x\d{2,5}$/', $_GET['screen'])) {
        $screen = $_GET['screen'];
    }
}

// IP-Adresse und User-Agent holen
$ip = getClientIP();
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

// Zeitzone und Zeitstempel
date_default_timezone_set('UTC');
$timestamptz = date('c');

// Neuen Eintrag erstellen
$entry = [
    'ip' => $ip,
    'user_agent' => $userAgent,
    'screen_resolution' => $screen,
    'country' => '', // aktuell leer
    'city' => '',    // aktuell leer
    'timestamptz' => $timestamptz,
];

// Prüfen, ob `history`-Modus aktiv ist
if (isset($_GET['history'])) {
    $history = loadHistory($historyFile);
    outputJson($history);
}

// Standard-Modus: neuen Eintrag speichern
$history = loadHistory($historyFile);
array_unshift($history, $entry);
$history = array_slice($history, 0, 10);
saveHistory($history);

// Antwort senden
outputJson($entry);
