<?php
// ==========================================
// PostgreSQL Database Connection
// ==========================================
$db_host = 'localhost';
$db_port = '5432';
$db_user = 'postgres'; // Set your PostgreSQL user
$db_pass = 'secret';   // Set your PostgreSQL password
$db_name = 'cryptic_db';

$conn_string = "host={$db_host} port={$db_port} dbname={$db_name} user={$db_user} password={$db_pass}";
$conn = @pg_connect($conn_string);
$db_connected = (bool)$conn;

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
// Form Processing & Database Insertion
// ==========================================
$cipher = $_POST['cipher'] ?? 'rot13';
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

        // Log to PostgreSQL
        if ($db_connected) {
            $query = "INSERT INTO cipher_logs (cipher_type, action_mode, input_text, output_text) VALUES ($1, 'encrypt', $2, $3)";
            pg_query_params($conn, $query, array($cipher, $encrypt_input, $encrypt_result));
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

        // Log to PostgreSQL
        if ($db_connected) {
            $query = "INSERT INTO cipher_logs (cipher_type, action_mode, input_text, output_text) VALUES ($1, 'decrypt', $2, $3)";
            pg_query_params($conn, $query, array($cipher, $decrypt_input, $decrypt_result));
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
    
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Press Start 2P', monospace;
            image-rendering: pixelated;
            image-rendering: crisp-edges;
        }

        body {
            margin: 0;
            padding: 20px;
            background-color: #0d0e15;
            background-image: 
                linear-gradient(45deg, #151828 25%, transparent 25%), 
                linear-gradient(-45deg, #151828 25%, transparent 25%), 
                linear-gradient(45deg, transparent 75%, #151828 75%), 
                linear-gradient(-45deg, transparent 75%, #151828 75%);
            background-size: 16px 16px;
            color: #00ffcc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        body::before {
            content: " ";
            display: block;
            position: fixed;
            top: 0; left: 0; bottom: 0; right: 0;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.35) 50%);
            background-size: 100% 4px;
            z-index: 99;
            pointer-events: none;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            width: 100%;
        }

        header {
            background: #1b1e2e;
            border: 6px solid #000000;
            box-shadow: 6px 6px 0 #00ffcc;
            padding: 20px;
            text-align: center;
            margin-bottom: 24px;
        }

        header h1 {
            color: #ff0055;
            font-size: 1.2rem;
            margin: 0 0 10px 0;
            text-shadow: 3px 3px 0 #000;
            line-height: 1.4;
        }

        header p {
            color: #ffcc00;
            font-size: 0.6rem;
            margin: 0;
        }

        .cipher-selector {
            background: #1b1e2e;
            border: 6px solid #000000;
            box-shadow: 6px 6px 0 #ff0055;
            padding: 16px;
            margin-bottom: 24px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .cipher-selector label {
            font-size: 0.65rem;
            color: #ffcc00;
        }

        select {
            width: 100%;
            background: #0d0e15;
            color: #00ffcc;
            border: 4px solid #000;
            padding: 12px;
            font-size: 0.75rem;
            outline: none;
            cursor: pointer;
            box-shadow: inset 3px 3px 0 #000;
        }

        select:focus {
            background: #151828;
            color: #ff0055;
        }

        .grid-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        @media (max-width: 768px) {
            .grid-container {
                grid-template-columns: 1fr;
            }
        }

        .grid-box {
            background: #1b1e2e;
            border: 6px solid #000000;
            box-shadow: 6px 6px 0 #000000;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .grid-box.encrypt-box {
            border-color: #000;
            box-shadow: 6px 6px 0 #00e676;
        }

        .grid-box.decrypt-box {
            border-color: #000;
            box-shadow: 6px 6px 0 #ff7b00;
        }

        .grid-box h2 {
            font-size: 0.8rem;
            margin: 0;
            padding: 8px;
            background: #000;
            text-align: center;
        }

        .encrypt-box h2 { color: #00e676; }
        .decrypt-box h2 { color: #ff7b00; }

        textarea {
            width: 100%;
            height: 120px;
            background: #0d0e15;
            border: 4px solid #000;
            color: #ffffff;
            padding: 10px;
            font-size: 0.65rem;
            line-height: 1.6;
            resize: vertical;
            outline: none;
            box-shadow: inset 3px 3px 0 #000;
        }

        textarea:focus {
            border-color: #ffcc00;
        }

        .result-box {
            background: #0d0e15;
            border: 4px solid #000;
            padding: 10px;
            min-height: 80px;
            font-size: 0.65rem;
            color: #ffcc00;
            word-break: break-all;
            line-height: 1.5;
        }

        .submit-container {
            margin-bottom: 24px;
        }

        .pixel-btn {
            width: 100%;
            background: #ff0055;
            color: #ffffff;
            border: 6px solid #000000;
            padding: 16px;
            font-size: 0.85rem;
            cursor: pointer;
            box-shadow: 6px 6px 0 #000000;
            text-transform: uppercase;
        }

        .pixel-btn:hover {
            background: #00e676;
            color: #000000;
            box-shadow: 6px 6px 0 #00ffcc;
        }

        .pixel-btn:active {
            transform: translate(4px, 4px);
            box-shadow: 2px 2px 0 #000000;
        }

        footer {
            background: #1b1e2e;
            border: 6px solid #000000;
            box-shadow: 6px 6px 0 #000000;
            padding: 14px;
            text-align: center;
            font-size: 0.55rem;
            color: #ffffff;
        }

        footer span {
            color: #ff0055;
        }
    </style>
</head>
<body>

<div class="container">
    <form method="POST" action="index.php">
        
        <header>
            <h1>CRYPTIC Encrypt Decrypt</h1>
            <p>[ POSTGRESQL TERMINAL V1.0 ]</p>
        </header>

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

        <div class="grid-container">
            
            <div class="grid-box encrypt-box">
                <h2>[ ENCRYPT GRID ]</h2>
                <label style="font-size: 0.55rem; color: #00e676;">INPUT PLAIN TEXT:</label>
                <textarea name="encrypt_input" placeholder="Type plain text to encrypt..."><?php echo htmlspecialchars($encrypt_input); ?></textarea>
                
                <label style="font-size: 0.55rem; color: #00e676;">ENCRYPTED RESULT:</label>
                <div class="result-box"><?php echo htmlspecialchars($encrypt_result ?: '> Waiting for input...'); ?></div>
            </div>

            <div class="grid-box decrypt-box">
                <h2>[ DECRYPT GRID ]</h2>
                <label style="font-size: 0.55rem; color: #ff7b00;">INPUT CIPHER TEXT:</label>
                <textarea name="decrypt_input" placeholder="Type cipher text to decrypt..."><?php echo htmlspecialchars($decrypt_input); ?></textarea>
                
                <label style="font-size: 0.55rem; color: #ff7b00;">DECRYPTED RESULT:</label>
                <div class="result-box"><?php echo htmlspecialchars($decrypt_result ?: '> Waiting for input...'); ?></div>
            </div>

        </div>

        <div class="submit-container">
            <button type="submit" class="pixel-btn">▶ EXECUTE CIPHER PROCESS</button>
        </div>

    </form>
</div>

<footer>
    TEXT CREDIT: <span>CRYPTIC ENCRYPTION ENGINE © 2026</span>
</footer>

</body>
</html>