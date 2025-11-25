<?php

header('Content-Type: application/json');

$payload = json_decode(file_get_contents('php://input'), true);

$country = '';
if (is_array($payload) && isset($payload['country'])) {
    $country = trim((string) $payload['country']);
} elseif (isset($_POST['country'])) {
    $country = trim((string) $_POST['country']);
}

if ($country === '') {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'msg'   => 'Country is required.',
    ]);
    exit;
}

$dataFile = __DIR__ . '/../data/countries-states.json';

if (!file_exists($dataFile)) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'msg'   => 'States dataset not found.',
    ]);
    exit;
}

$raw = file_get_contents($dataFile);
$decoded = json_decode($raw, true);

if (!is_array($decoded)) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'msg'   => 'Failed to parse states dataset.',
    ]);
    exit;
}

$normalized = mb_strtolower($country);
$match = null;

foreach ($decoded as $entry) {
    if (isset($entry['name']) && mb_strtolower($entry['name']) === $normalized) {
        $match = $entry;
        break;
    }
}

if (!$match) {
    foreach ($decoded as $entry) {
        if (
            (isset($entry['code2']) && mb_strtolower($entry['code2']) === $normalized) ||
            (isset($entry['code3']) && mb_strtolower($entry['code3']) === $normalized)
        ) {
            $match = $entry;
            break;
        }
    }
}

if (!$match) {
    http_response_code(404);
    echo json_encode([
        'error' => true,
        'msg'   => 'Country not found in dataset.',
    ]);
    exit;
}

$states = array_map(
    static function (array $state): array {
        return [
            'code' => $state['code'] ?? null,
            'name' => $state['name'] ?? '',
        ];
    },
    $match['states'] ?? []
);

usort($states, static function (array $a, array $b): int {
    return strcasecmp($a['name'], $b['name']);
});

echo json_encode([
    'error' => false,
    'msg'   => 'success',
    'data'  => [
        'name'  => $match['name'],
        'code2' => $match['code2'] ?? null,
        'states'=> $states,
    ],
]);

