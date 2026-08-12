<?php
session_start();

// Ma3lomat database dyalk exact
define('DB_HOST', '51.83.49.125');
define('DB_NAME', 's181011_db1779366624436');
define('DB_USER', 'u181011_b44viEdbWS');
define('DB_PASS', 'fJ5dK6e9dB48HWTwP9F=+Nhm'); // ⚠️ Bdel had l-password mni t-bdelha f l-host d l-server!

// Smiyat dyal l-jadawel (Tables) f l-database dyalk
define('TABLE_USERS', 'accounts');      // Jdwel dyal l-accounts (bhal accounts wla users)
define('COL_USER_ID', 'id');            // Column d l-ID dyal player
define('COL_USERNAME', 'Username');     // Column d l-smiya dyal player
define('COL_PASSWORD', 'Password');     // Column d l-password dyal player

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Khata2 f l-etisal: " . $e->getMessage());
}
?>