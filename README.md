# CRYPTIC Encryption Engine — Technical Documentation

![alt text](Cryptic\assets\images\homepage.png)

## Overview
**CRYPTIC Encryption Engine** is a retro 8-bit CRT arcade-themed web application built with PHP, MySQL, CSS3, and JavaScript/HTML5. The system provides multi-cipher text processing (encryption and decryption) using classic substitution algorithms, encoding formats, and cryptographic schemes, alongside database transaction logging.

---

## File Directory & Architecture

<details>
<summary>View File Directories</summary>

```text
Directory structure:
└── jvacero-cryptic/
    ├── about_devs.php
    ├── index.php
    ├── api/
    │   └── index.php
    ├── assets/
    │   ├── devs.css
    │   └── index.css
    ├── config/
    │   └── mysqli_connect.php
    └── database/
        └── schema.sql
```
</details>

---

## Database Schema (`database/schema.sql`)

### Database: `cryptic_db`
- **Table:** `cipher_logs`
  - Stores all encryption and decryption operations executed by users via the web interface.

| Column Name   | Data Type    | Constraints                 | Description                                |
| :------------ | :----------- | :-------------------------- | :----------------------------------------- |
| `id`          | `INT`        | `AUTO_INCREMENT`, `PRIMARY` | Unique log entry identifier               |
| `cipher_type` | `VARCHAR(50)`| `NOT NULL`                  | Algorithm used (e.g., `rot13`, `caesar`)   |
| `action_mode` | `VARCHAR(10)`| `NOT NULL`                  | Action executed (`encrypt` or `decrypt`)   |
| `input_text`  | `TEXT`       | `NOT NULL`                  | User-provided source string               |
| `output_text` | `TEXT`       | `NOT NULL`                  | Resulting output from cipher processing    |
| `created_at`  | `TIMESTAMP`  | `DEFAULT CURRENT_TIMESTAMP` | System timestamp of execution              |

---

## Component Breakdown

### 1. Configuration (`config/mysqli_connect.php`)
* **Purpose:** Establishes a connection to the local MySQL server instance (`cryptic_db`).
* **Connection Variables:**
  * `$db_host`: `localhost`
  * `$db_user`: `root`
  * `$db_pass`: `""`
  * `$db_name`: `cryptic_db`
* **Handling:** Returns an explicit connection error message and halts script execution if the connection fails.

---

### 2. Main Interface & Engine (`index.php`)
* **Purpose:** Acts as the primary terminal interface for selecting, running, and logging cipher processes.
* **Supported Cipher Algorithms:**
  1. **ROT13:** Applies a 13-character letter substitution using PHP's native `str_rot13()`.
  2. **Caesar Cipher:** Shifts ASCII alphabetic characters by a fixed key of `3`.
  3. **Atbash Cipher:** Reverses the alphabet mapping (`A` ↔ `Z`, `a` ↔ `z`).
  4. **Symmetric Cipher:** AES-128-CBC encryption using `openssl_encrypt()` and `openssl_decrypt()` with a predefined key (`crypticpixelkey`) and IV seed (`cryptic_iv_seed`).
  5. **Asymmetric Cipher:** RSA 1024-bit key-pair simulation generated via `openssl_pkey_new()`.
  6. **Base64:** Standard binary-to-text base64 encoding/decoding.
  7. **Morse Code:** Custom mapping for alphanumeric characters to standard Morse dots (`.`) and dashes (`-`).

* **Workflow:**
  1. Accepts `POST` payload containing selected algorithm (`cipher`), plain text input (`encrypt_input`), and cipher text input (`decrypt_input`).
  2. Executes corresponding transformation function for active inputs.
  3. Inserts operation details into `cipher_logs` via prepared SQL statements (`$conn->prepare()`).
  4. Renders retro CRT styled interface with updated result blocks.

---

### 3. Developer Credits (`about_devs.php`)
* **Purpose:** Displays system contributor profiles in a retro gaming card layout.
* **Features:**
  * Dynamic dataset rendering using a PHP array (`$developers`).
  * XSS prevention via explicit string escaping function (`escape_html()` wrapping `htmlspecialchars()`).
  * Status and rank indicators for each contributor with accent color variations (`accent-cyan`, `accent-green`, `accent-orange`).

---

### 4. Stylesheets (`assets/index.css` & `assets/devs.css`)
* **Theme:** Retro 8-bit CRT Arcade Monitor Aesthetic.
* **Design Features:**
  * Font Family: `'Press Start 2P'`, Monospace (via Google Fonts).
  * Canvas Background: Dark checkerboard pattern (#0d0e15 / #151828) paired with pixelated rendering (`image-rendering: pixelated`).
  * Scanline Overlay: Pseudo-element (`body::before`) creating a CRT line grid effect over the viewport.
  * Border Styling: Solid 4px–6px pixelated high-contrast offset borders and offset box shadows (`6px 6px 0`).