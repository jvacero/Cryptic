<?php

declare(strict_types=1);

/**
 * Sends encryption logs to Supabase via cURL using Environment Variables.
 */
function log_to_supabase(string $cipher, string $mode, string $input, string $output): bool
{
    $supabaseUrl = getenv('SUPABASE_URL') ?: '';
    $supabaseKey = getenv('SUPABASE_ANON_KEY') ?: '';

    if (empty($supabaseUrl) || empty($supabaseKey)) {
        return false;
    }

    $endpoint = rtrim($supabaseUrl, '/') . '/rest/v1/cipher_logs';

    $payload = json_encode([
        'cipher_type' => $cipher,
        'action_mode' => $mode,
        'input_text'  => $input,
        'output_text' => $output
    ], JSON_THROW_ON_ERROR);

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'apikey: ' . $supabaseKey,
            'Authorization: Bearer ' . $supabaseKey,
            'Prefer: return=minimal'
        ],
        CURLOPT_TIMEOUT        => 5
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($httpCode >= 200 && $httpCode < 300);
}

