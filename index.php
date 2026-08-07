<?php
// ==========================================
// Database Connection
// ==========================================
$db_host = 'localhost';
$db_user = 'root';
$db_pass = ''; // Set your MySQL password
$db_name = 'cryptic_db';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
$db_connected = !$conn->connect_error;

// ==========================================
// Cipher Helpers
// ==========================================

// 1. ROT13
function process_rot13($text) {
    return str_rot13($text);
}

// 2. Caesar Cipher
function process_caesar($text, $shift = 3, $decrypt = false) {
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
function process_atbash($text) {
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
function process_symmetric($text, $key = 'crypticpixelkey', $decrypt = false) {
    $method = "AES-128-CBC";
    $iv = substr(hash('sha256', 'cryptic_iv_seed'), 0, 16);
    $hashed_key = substr(hash('sha256', $key), 0, 16);

    if (!$decrypt) {
        return base64_encode(openssl_encrypt($text, $method, $hashed_key, 0, $iv));
    } else {
        return openssl_decrypt(base64_decode($text), $method, $hashed_key, 0, $iv) ?: "[DECRYPTION FAILED]";
    }
}

// 5. Asymmetric Cipher (RSA Key Simulation / OpenSSL)
function process_asymmetric($text, $decrypt = false) {
    $config = array(
        "digest_alg" => "sha512",
        "private_key_bits" => 1024,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
    );
    
    // Static demo keys for continuous execution
    $res = openssl_pkey_new($config);
    if (!$res) return $decrypt ? "[INVALID RSA PAYLOAD]" : base64_encode($text);
    
    openssl_pkey_export($res, $privKey);
    $pubKey = openssl_pkey_get_details($res)["key"];

    if (!$decrypt) {
        openssl_public_encrypt($text, $encrypted, $pubKey);
        return base64_encode($encrypted);
    } else {
        $data = base64_decode($text);
        openssl_private_decrypt($data, $decrypted, $privKey);
        return $decrypted ?: "[RSA DECRYPT FAILED]";
    }
}

// 6. Base64
function process_base64($text, $decrypt = false) {
    return $decrypt ? base64_decode($text) : base64_encode($text);
}

// 7. Morse Code
function process_morse($text, $decrypt = false) {
    $morse_map = [
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
            $result[] = $morse_map[$char] ?? $char;
        }
        return implode(' ', $result);
    } else {
        $reverse_map = array_flip($morse_map);
        $tokens = explode(' ', $text);
        $result = '';
        foreach ($tokens as $token) {
            $result .= $reverse_map[$token] ?? $token;
        }
        return $result;
    }
}

// ==========================================
// Form Submission & Processing Logic
// ==========================================
$cipher = $_POST['cipher'] ?? 'rot13';
$mode = $_POST['mode'] ?? 'encrypt';
$encrypt_input = $_POST['encrypt_input'] ?? '';
$decrypt_input = $_POST['decrypt_input'] ?? '';

$encrypt_result = '';
$decrypt_result = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process Encryption Block
    if (!empty($encrypt_input)) {
        switch ($cipher) {
            case 'rot13': $encrypt_result = process_rot13($encrypt_input); break;
            case 'caesar': $encrypt_result = process_caesar($encrypt_input, 3, false); break;
            case 'atbash': $encrypt_result = process_atbash($encrypt_input); break;
            case 'symmetric': $encrypt_result = process_symmetric($encrypt_input, 'crypticpixelkey', false); break;
            case 'asymmetric': $encrypt_result = process_asymmetric($encrypt_input, false); break;
            case 'base64': $encrypt_result = process_base64($encrypt_input, false); break;
            case 'morse': $encrypt_result = process_morse($encrypt_input, false); break;
        }

        // Log to Database
        if ($db_connected) {
            $stmt = $conn->prepare("INSERT INTO cipher_logs (cipher_type, action_mode, input_text, output_text) VALUES (?, 'encrypt', ?, ?)");
            $stmt->bind_param("sss", $cipher, $encrypt_input, $encrypt_result);
            $stmt->execute();
        }
    }

    // Process Decryption Block
    if (!empty($decrypt_input)) {
        switch ($cipher) {
            case 'rot13': $decrypt_result = process_rot13($decrypt_input); break;
            case 'caesar': $decrypt_result = process_caesar($decrypt_input, 3, true); break;
            case 'atbash': $decrypt_result = process_atbash($decrypt_input); break;
            case 'symmetric': $decrypt_result = process_symmetric($decrypt_input, 'crypticpixelkey', true); break;
            case 'asymmetric': $decrypt_result = process_asymmetric($decrypt_input, true); break;
            case 'base64': $decrypt_result = process_base64($decrypt_input, true); break;
            case 'morse': $decrypt_result = process_morse($decrypt_input, true); break;
        }

        // Log to Database
        if ($db_connected) {
            $stmt = $conn->prepare("INSERT INTO cipher_logs (cipher_type, action_mode, input_text, output_text) VALUES (?, 'decrypt', ?, ?)");
            $stmt->bind_param("sss", $cipher, $decrypt_input, $decrypt_result);
            $stmt->execute();
        }
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
    <link rel="stylesheet" href="assets/index.css">
</head>
<body>

<div class="container">
    <form method="POST" action="index.php">
        
        <!-- HEADER -->
        <header>
            <h1>CRYPTIC Encrypt Decrypt</h1>
            <p>[ RETRO CIPHER TERMINAL V1.0 ]</p>
        </header>

        <!-- TOP DROPDOWN OPTION -->
        <div class="cipher-selector">
            <label for="cipher">SELECT CIPHER ALGORITHM:</label>
            <select name="cipher" id="cipher">
                <option value="rot13" <?php if($cipher==='rot13') echo 'selected'; ?>>ROT13</option>
                <option value="caesar" <?php if($cipher==='caesar') echo 'selected'; ?>>Caesar Cipher (Shift 3)</option>
                <option value="atbash" <?php if($cipher==='atbash') echo 'selected'; ?>>Atbash Cipher</option>
                <option value="symmetric" <?php if($cipher==='symmetric') echo 'selected'; ?>>Symmetric Cipher (AES-128-CBC)</option>
                <option value="asymmetric" <?php if($cipher==='asymmetric') echo 'selected'; ?>>Asymmetric Cipher (RSA 1024)</option>
                <option value="base64" <?php if($cipher==='base64') echo 'selected'; ?>>Base64</option>
                <option value="morse" <?php if($cipher==='morse') echo 'selected'; ?>>Morse Code</option>
            </select>
        </div>

        <!-- MIDDLE 2 GRID: DECRYPT & ENCRYPT -->
        <div class="grid-container">
            
            <!-- ENCRYPT GRID -->
            <div class="grid-box encrypt-box">
                <h2>[ ENCRYPT GRID ]</h2>
                <label style="font-size: 0.55rem; color: #00e676;">INPUT PLAIN TEXT:</label>
                <textarea name="encrypt_input" placeholder="Type plain text to encrypt..."><?php echo htmlspecialchars($encrypt_input); ?></textarea>
                
                <label style="font-size: 0.55rem; color: #00e676;">ENCRYPTED RESULT:</label>
                <div class="result-box"><?php echo htmlspecialchars($encrypt_result ?: '> Waiting for input...'); ?></div>
            </div>

            <!-- DECRYPT GRID -->
            <div class="grid-box decrypt-box">
                <h2>[ DECRYPT GRID ]</h2>
                <label style="font-size: 0.55rem; color: #ff7b00;">INPUT CIPHER TEXT:</label>
                <textarea name="decrypt_input" placeholder="Type cipher text to decrypt..."><?php echo htmlspecialchars($decrypt_input); ?></textarea>
                
                <label style="font-size: 0.55rem; color: #ff7b00;">DECRYPTED RESULT:</label>
                <div class="result-box"><?php echo htmlspecialchars($decrypt_result ?: '> Waiting for input...'); ?></div>
            </div>

        </div>

        <!-- BOTTOM SUBMIT -->
        <div class="submit-container">
            <button type="submit" class="pixel-btn">▶ EXECUTE CIPHER PROCESS</button>
        </div>

    </form>
</div>

<!-- FOOTER -->
<footer>
    TEXT CREDIT: <span>CRYPTIC ENCRYPTION ENGINE © 2026</span>
</footer>

</body>
</html>