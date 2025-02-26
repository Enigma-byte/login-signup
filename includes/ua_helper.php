<?php

function getFriendlyUserAgent($userAgent) {
    // Browser detection
    $browser = "Unknown Browser";
    if (preg_match('/(Edge|Edg)\/([0-9]+)/', $userAgent, $matches)) {
        $browser = "Microsoft Edge";
    } elseif (preg_match('/Firefox\/([0-9]+)/', $userAgent, $matches)) {
        $browser = "Firefox";
    } elseif (preg_match('/Chrome\/([0-9]+)/', $userAgent, $matches)) {
        $browser = "Chrome";
    } elseif (preg_match('/Safari\/([0-9]+)/', $userAgent, $matches) && !strpos($userAgent, 'Chrome')) {
        $browser = "Safari";
    } elseif (preg_match('/OPR|Opera\/([0-9]+)/', $userAgent, $matches)) {
        $browser = "Opera";
    }

    // OS detection
    $os = "Unknown OS";
    if (strpos($userAgent, 'Windows') !== false) {
        $os = "Windows";
        if (strpos($userAgent, 'Windows NT 10.0') !== false) $os = "Windows 10/11";
        elseif (strpos($userAgent, 'Windows NT 6.3') !== false) $os = "Windows 8.1";
        elseif (strpos($userAgent, 'Windows NT 6.2') !== false) $os = "Windows 8";
        elseif (strpos($userAgent, 'Windows NT 6.1') !== false) $os = "Windows 7";
    } elseif (strpos($userAgent, 'Macintosh') !== false) {
        $os = "macOS";
    } elseif (strpos($userAgent, 'Linux') !== false) {
        $os = "Linux";
        if (strpos($userAgent, 'Android') !== false) {
            $os = "Android";
        }
    } elseif (strpos($userAgent, 'iPhone') !== false) {
        $os = "iOS";
    } elseif (strpos($userAgent, 'iPad') !== false) {
        $os = "iPadOS";
    }

    // Device type detection
    $device = "Desktop";
    if (strpos($userAgent, 'Mobile') !== false || strpos($userAgent, 'Android') !== false || strpos($userAgent, 'iPhone') !== false) {
        $device = "Mobile";
    } elseif (strpos($userAgent, 'Tablet') !== false || strpos($userAgent, 'iPad') !== false) {
        $device = "Tablet";
    }

    // Clean up the UA string by removing Mozilla prefix and truncating
    $cleanUA = $userAgent;
    $cleanUA = preg_replace('/^Mozilla\/\d\.\d\s*\(/', '', $cleanUA);
    $cleanUA = preg_replace('/\)\s*Mozilla\/\d\.\d.*?(?=\s*(?:Firefox|Chrome|Safari|Edge|Opera))/', '', $cleanUA);
    $cleanUA = preg_replace('/\s+/', ' ', trim($cleanUA));

    return [
        'browser' => $browser,
        'os' => $os,
        'device' => $device,
        'full' => "$browser on $os ($device)",
        'clean_ua' => $cleanUA
    ];
}
