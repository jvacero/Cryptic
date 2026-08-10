<?php

declare(strict_types=1);

require_once __DIR__ . '/../src/ciphers.php';
require_once __DIR__ . '/../src/supabase.php';

$cipher = $_POST['cipher'] ?? 'rot13';
$encryptInput = $_POST['encrypt_input'] ?? '';
$decryptInput = $_POST['decrypt_input'] ?? '';

$encryptResult = '';
$decryptResult = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process Encryption Block
    if ($encryptInput !== '') {
        $encryptResult = match ($cipher) {
            'rot13'      => process_rot13($encryptInput),
            'caesar'     => process_caesar($encryptInput, 3, false),
            'atbash'     => process_atbash($encryptInput),
            'symmetric'  => process_symmetric($encryptInput, 'crypticpixelkey', false),
            'asymmetric' => process_asymmetric($encryptInput, false),
            'base64'     => process_base64($encryptInput, false),
            'morse'      => process_morse($encryptInput, false),
            default      => ''
        };

        log_to_supabase($cipher, 'encrypt', $encryptInput, $encryptResult);
    }

    // Process Decryption Block
    if ($decryptInput !== '') {
        $decryptResult = match ($cipher) {
            'rot13'      => process_rot13($decryptInput),
            'caesar'     => process_caesar($decryptInput, 3, true),
            'atbash'     => process_atbash($decryptInput),
            'symmetric'  => process_symmetric($decryptInput, 'crypticpixelkey', true),
            'asymmetric' => process_asymmetric($decryptInput, true),
            'base64'     => process_base64($decryptInput, true),
            'morse'      => process_morse($decryptInput, true),
            default      => ''
        };

        log_to_supabase($cipher, 'decrypt', $decryptInput, $decryptResult);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRYPTIC Encrypt Decrypt</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/style.css">
</head>
<body>

<div class="container">
    <form method="POST" action="/api/index.php">
        
        <header>
            <h1>CRYPTIC Encrypt Decrypt</h1>
            <p>[ SUPABASE version CLOUD TERMINAL V1.0 ]</p>
        </header>

        <div class="cipher-selector">
            <label for="cipher">SELECT CIPHER ALGORITHM:</label>
            <select name="cipher" id="cipher">
                <option value="rot13" <?= $cipher === 'rot13' ? 'selected' : '' ?>>ROT13</option>
                <option value="caesar" <?= $cipher === 'caesar' ? 'selected' : '' ?>>Caesar Cipher (Shift 3)</option>
                <option value="atbash" <?= $cipher === 'atbash' ? 'selected' : '' ?>>Atbash Cipher</option>
                <option value="symmetric" <?= $cipher === 'symmetric' ? 'selected' : '' ?>>Symmetric Cipher (AES-128-CBC)</option>
                <option value="asymmetric" <?= $cipher === 'asymmetric' ? 'selected' : '' ?>>Asymmetric Cipher (RSA 1024)</option>
                <option value="base64" <?= $cipher === 'base64' ? 'selected' : '' ?>>Base64</option>
                <option value="morse" <?= $cipher === 'morse' ? 'selected' : '' ?>>Morse Code</option>
            </select>
        </div>

        <div class="grid-container">
            <div class="grid-box encrypt-box">
                <h2>[ ENCRYPT GRID ]</h2>
                <label class="label-encrypt">INPUT PLAIN TEXT:</label>
                <textarea name="encrypt_input" placeholder="Type plain text to encrypt..."><?= htmlspecialchars($encryptInput) ?></textarea>
                
                <label class="label-encrypt">ENCRYPTED RESULT:</label>
                <div class="result-box"><?= htmlspecialchars($encryptResult !== '' ? $encryptResult : '> Waiting for input...') ?></div>
            </div>

            <div class="grid-box decrypt-box">
                <h2>[ DECRYPT GRID ]</h2>
                <label class="label-decrypt">INPUT CIPHER TEXT:</label>
                <textarea name="decrypt_input" placeholder="Type cipher text to decrypt..."><?= htmlspecialchars($decryptInput) ?></textarea>
                
                <label class="label-decrypt">DECRYPTED RESULT:</label>
                <div class="result-box"><?= htmlspecialchars($decryptResult !== '' ? $decryptResult : '> Waiting for input...') ?></div>
            </div>
        </div>

        <div class="submit-container">
            <button type="submit" class="pixel-btn">▶ EXECUTE CIPHER PROCESS</button>
        </div>

    </form>
</div>

<footer>
    RECBSITM71 <span>CRYPTIC ENCRYPTION ENGINE © 2026</span> <a href="#"></a>
</footer>

</body>
</html>

