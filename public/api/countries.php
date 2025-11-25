<?php

header('Content-Type: application/json');

$dataFile = __DIR__ . '/../data/countries-states.json';

if (!file_exists($dataFile)) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'msg'   => 'Countries dataset not found.',
    ]);
    exit;
}

$raw = file_get_contents($dataFile);
$decoded = json_decode($raw, true);

if (!is_array($decoded)) {
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'msg'   => 'Failed to parse countries dataset.',
    ]);
    exit;
}

$countries = array_map(
    static function (array $entry): array {
        return ['name' => $entry['name']];
    },
    $decoded
);

usort($countries, static function (array $a, array $b): int {
    return strcasecmp($a['name'], $b['name']);
});

echo json_encode([
    'error' => false,
    'msg'   => 'success',
    'data'  => $countries,
]);

