<?php

if (!function_exists('escapeLike')) {
    /**
     * Escape special characters for use in a LIKE query.
     * This prevents SQL wildcards (%, _) in user input from affecting the query.
     */
    function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}

if (!function_exists('generateAifarmId')) {
    function generateAifarmId(): string {
        $alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $base = strlen($alphabet);

        // Get timestamp in milliseconds, limit to 32 bits (wraps in 2106)
        $timestamp = (int)(microtime(true) * 1000) & 0xFFFFFFFF;

        // 9 bits random (0 - 511)
        $randomBits = random_int(0, 511);

        // Shift timestamp 9 bits and OR with random
        $idInt = ($timestamp << 9) | $randomBits;

        // Encode to Base36 (uppercase only)
        return str_pad(baseEncode($idInt, $alphabet), 8, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('baseEncode')) {
    function baseEncode(int $num, string $alphabet): string {
        $base = strlen($alphabet);
        $str = '';
        do {
            $str = $alphabet[$num % $base] . $str;
            $num = intdiv($num, $base);
        } while ($num > 0);
        return $str;
    }
}
