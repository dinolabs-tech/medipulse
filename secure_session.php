<?php
function secure_session_start($conn, $timeout = 3600, $regen_interval = 1800) {
    // Cookie settings (looser) - Must be called before session_start()
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => $cookieParams['path'],
        'domain'   => $cookieParams['domain'],
        'secure'   => isset($_SERVER['HTTPS']), // still secure on HTTPS
        'httponly' => true,
        'samesite' => 'Lax' // less strict than "Strict"
    ]);

    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $ip   = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ua   = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    $uid  = $_SESSION['user_id'] ?? null;
    $branch_id = $_SESSION['branch_id'] ?? null;

    // Helper for logging events
    $log_event = function($type) use ($conn, $uid, $branch_id, $ip, $ua) {
        $stmt = $conn->prepare("INSERT INTO session_logs (user_id, branch_id, event_type, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $uid, $branch_id, $type, $ip, $ua);
        $stmt->execute();
        $stmt->close();
    };

    // -----------------
    // 1. Hijack Detection (looser)
    // -----------------
    $fingerprint = $ua; // only User-Agent (ignore IP to reduce false kicks)
    if (!isset($_SESSION['fingerprint'])) {
        $_SESSION['fingerprint'] = $fingerprint;
    } elseif ($_SESSION['fingerprint'] !== $fingerprint) {
        $log_event('hijack-warning'); // log only, don’t kill session
        // Optionally: regenerate ID instead of destroying
        session_regenerate_id(true);
    }

    // -----------------
    // 2. Inactivity Timeout (longer)
    // -----------------
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        $log_event('timeout');
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['timeout'] = true;
    }
    $_SESSION['last_activity'] = time();

    // -----------------
    // 3. Periodic Regeneration (less frequent)
    // -----------------
    if (!isset($_SESSION['last_regen'])) {
        $_SESSION['last_regen'] = time();
    } elseif (time() - $_SESSION['last_regen'] > $regen_interval) {
        session_regenerate_id(true);
        $_SESSION['last_regen'] = time();
    }

    return $log_event;
}
