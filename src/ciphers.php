<?php

declare(strict_types=1);

// 1. ROT13
function process_rot13(string $text): string
{
    return str_rot13($text);
}

// 2. Caesar Cipher
function process_caesar(string $text, int $shift = 3, bool $decrypt = false): string
{
    $shift = $decrypt ? (26 - ($shift % 26)) : ($shift % 26);
    $output = '';

    foreach (str_split($text) as $char) {
        if (ctype_upper($char)) {
            $output .= chr((ord($char) - 65 + $shift) % 26 + 65);
        } elseif (ctype_lower($char)) {
            $output .= chr((ord($char) - 97 + $shift) % 26 + 97);
        } else {
            $output .= $char;
        }
    }

    return $output;
}

// 3. Atbash Cipher
function process_atbash(string $text): string
{
    $output = '';

    foreach (str_split($text) as $char) {
        if (ctype_upper($char)) {
            $output .= chr(ord('Z') - (ord($char) - ord('A')));
        } elseif (ctype_lower($char)) {
            $output .= chr(ord('z') - (ord($char) - ord('a')));
        } else {
            $output .= $char;
        }
    }

    return $output;
}

// 4. Symmetric Cipher (AES-128-CBC)
function process_symmetric(string $text, string $key = 'crypticpixelkey', bool $decrypt = false): string
{
    $method = 'AES-128-CBC';
    $iv = substr(hash('sha256', 'cryptic_iv_seed'), 0, 16);
    $hashedKey = substr(hash('sha256', $key), 0, 16);

    if (!$decrypt) {
        $encrypted = openssl_encrypt($text, $method, $hashedKey, 0, $iv);
        return $encrypted !== false ? base64_encode($encrypted) : '[ENCRYPTION FAILED]';
    }

    $decoded = base64_decode($text, true);
    if ($decoded === false) {
        return '[INVALID BASE64 PAYLOAD]';
    }

    $decrypted = openssl_decrypt($decoded, $method, $hashedKey, 0, $iv);
    return $decrypted !== false ? $decrypted : '[DECRYPTION FAILED]';
}

// 5. Asymmetric Cipher (RSA 1024)
function process_asymmetric(string $text, bool $decrypt = false): string
{
    $config = [
        'digest_alg' => 'sha512',
        'private_key_bits' => 1024,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ];

    $res = openssl_pkey_new($config);
    if ($res === false) {
        return $decrypt ? '[INVALID RSA PAYLOAD]' : base64_encode($text);
    }

    openssl_pkey_export($res, $privKey);
    $details = openssl_pkey_get_details($res);
    $pubKey = $details['key'] ?? '';

    if (!$decrypt) {
        $encrypted = '';
        $success = openssl_public_encrypt($text, $encrypted, $pubKey);
        return $success ? base64_encode($encrypted) : '[RSA ENCRYPT FAILED]';
    }

    $data = base64_decode($text, true);
    if ($data === false) {
        return '[INVALID BASE64 PAYLOAD]';
    }

    $decrypted = '';
    $success = openssl_private_decrypt($data, $decrypted, $privKey);
    return $success ? $decrypted : '[RSA DECRYPT FAILED]';
}

// 6. Base64
function process_base64(string $text, bool $decrypt = false): string
{
    if (!$decrypt) {
        return base64_encode($text);
    }
    
    $decoded = base64_decode($text, true);
    return $decoded !== false ? $decoded : '[INVALID BASE64]';
}

// 7. Morse Code
function process_morse(string $text, bool $decrypt = false): string
{
    $morseMap = [
        'A' => '.-',    'B' => '-...',  'C' => '-.-.',  'D' => '-..',   'E' => '.',
        'F' => '..-.',  'G' => '--.',   'H' => '....',  'I' => '..',    'J' => '.---',
        'K' => '-.-',   'L' => '.-..',  'M' => '--',    'N' => '-.',    'O' => '---',
        'P' => '.--.',  'Q' => '--.-',  'R' => '.-.',   'S' => '...',   'T' => '-',
        'U' => '..-',   'V' => '...-',  'W' => '.--',   'X' => '-..-',  'Y' => '-.--',
        'Z' => '--..',  '1' => '.----', '2' => '..---', '3' => '...--', '4' => '....-',
        '5' => '.....', '6' => '-....', '7' => '--...', '8' => '---..', '9' => '----.',
        '0' => '-----', ' ' => '/'
    ];

    if (!$decrypt) {
        $text = strtoupper($text);
        $result = [];
        foreach (str_split($text) as $char) {
            $result[] = $morseMap[$char] ?? $char;
        }
        return implode(' ', $result);
    }

    $reverseMap = array_flip($morseMap);
    $tokens = explode(' ', $text);
    $result = '';
    foreach ($tokens as $token) {
        $result .= $reverseMap[$token] ?? $token;
    }
    return $result;
}

