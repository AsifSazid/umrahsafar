<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$cacheFile = EXCHANGE_CACHE_FILE;
$cacheDuration = EXCHANGE_CACHE_DURATION;

// Correct April 2026 approximate rates (base: SAR)
$fallback = [
    'SAR'          => 1,
    'USD'          => 0.2664,   // 1 SAR ≈ 0.2664 USD
    'BDT'          => 32.5,     // 1 SAR ≈ 32.5 BDT
    'last_updated' => date('Y-m-d H:i:s'),
    'source'       => 'fallback'
];

// Check cache
if (file_exists($cacheFile)) {
    $cached = json_decode(file_get_contents($cacheFile), true);
    if ($cached && (time() - strtotime($cached['last_updated'])) < $cacheDuration) {
        echo json_encode($cached);
        exit;
    }
}

// Try live API — DB-stored setting (Admin → Settings) takes priority over
// the hardcoded constant in config.php, per this project's usual convention.
$apiKey = getSetting('exchange_api_key', EXCHANGE_API_KEY);
$url    = "https://v6.exchangerate-api.com/v6/{$apiKey}/pair/SAR/BDT";

$rates = $fallback;

if ($apiKey !== 'YOUR_API_KEY_HERE') {
    $ctx = stream_context_create(['http' => ['timeout' => 5]]);
    $sarToBdt = @file_get_contents("https://v6.exchangerate-api.com/v6/{$apiKey}/pair/SAR/BDT", false, $ctx);
    $sarToUsd = @file_get_contents("https://v6.exchangerate-api.com/v6/{$apiKey}/pair/SAR/USD", false, $ctx);

    if ($sarToBdt && $sarToUsd) {
        $bdtData = json_decode($sarToBdt, true);
        $usdData = json_decode($sarToUsd, true);
        if (
            isset($bdtData['conversion_rate'], $usdData['conversion_rate']) &&
            $bdtData['result'] === 'success' &&
            $usdData['result'] === 'success'
        ) {
            $rates = [
                'SAR'          => 1,
                'USD'          => round($usdData['conversion_rate'], 4),
                'BDT'          => round($bdtData['conversion_rate'], 2),
                'last_updated' => date('Y-m-d H:i:s'),
                'source'       => 'live'
            ];
        }
    }
}

// Cache the result
@file_put_contents($cacheFile, json_encode($rates));

echo json_encode($rates);