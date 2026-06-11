<?php
// includes/helpers.php

// =======================
// SECURE SESSION START
// =======================
if (session_status() === PHP_SESSION_NONE) {

    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS'])); // only HTTPS

    session_start();
}

// =======================
// CSRF TOKEN
// =======================
function csrf_token() {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

// =======================
// VERIFY CSRF
// =======================
function check_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        if (
            empty($_POST['_csrf']) ||
            empty($_SESSION['csrf']) ||
            !hash_equals($_SESSION['csrf'], $_POST['_csrf'])
        ) {
            http_response_code(403);
            die('❌ Invalid CSRF token');
        }
    }
}

// =======================
// REQUIRE LOGIN
// =======================
function require_login() {
    if (empty($_SESSION['user_id'])) {

      
            header("Location: /honey-management-php/index.php");
            exit;
        
    }
}
// =======================
// ESCAPE OUTPUT (XSS PROTECTION)
// =======================
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// =======================
// FLASH MESSAGE
// =======================
function flash_message($message, $type = 'info') {
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type
    ];
}

// =======================
// DISPLAY FLASH (SAFE)
// =======================
function display_flash() {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);

        echo "<div class='alert alert-".e($flash['type'])."'>";
        echo e($flash['message']);
        echo "</div>";
    }
}

// =======================
// SESSION TIMEOUT (AUTO LOGOUT)
// =======================
function session_timeout($minutes = 30) {

    $timeout = $minutes * 60;

    if (isset($_SESSION['LAST_ACTIVITY']) && 
        (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {

        session_unset();
        session_destroy();

        header("Location:  /honey-management-php/index.php?timeout=1");
        exit;
    }

    $_SESSION['LAST_ACTIVITY'] = time();
}

// =======================
// OPTIONAL: UPDATE EXPIRY
// =======================
function update_expiry_status($pdo) {
    $sql = "
        UPDATE expiring_batches
        SET status = 
          CASE 
            WHEN expiry_date <= CURDATE() THEN 'expired'
            WHEN expiry_date BETWEEN CURDATE() 
                 AND DATE_ADD(CURDATE(), INTERVAL 90 DAY) THEN 'near_expiry'
            ELSE 'active'
          END
    ";
    $pdo->exec($sql);
}