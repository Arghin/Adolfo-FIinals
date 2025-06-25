<?php
/* ───── Database configuration ───── */
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'role_management';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/* ───── Helper: best-guess client IP ───── */
function getRealIpAddress()
{
    // 1️⃣ Proxy / client headers
    if (!empty($_SERVER['HTTP_CLIENT_IP']) &&
        strtolower($_SERVER['HTTP_CLIENT_IP']) !== 'unknown') {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) &&
        strtolower($_SERVER['HTTP_X_FORWARDED_FOR']) !== 'unknown') {
        $ips = array_map('trim', explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']));
        return $ips[0];
    }

    // 2️⃣ Direct remote address
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

    // 3️⃣ Convert localhost (::1 / 127.0.0.1) → LAN IP if possible
    if ($ip === '::1' || $ip === '127.0.0.1') {
        $lan = gethostbyname(gethostname());   // Often 192.168.x.x on Windows/Linux
        if ($lan && $lan !== '127.0.0.1' && $lan !== '::1') {
            return $lan;
        }
        // Fallback
        return '127.0.0.1';
    }
    return $ip;
}

/* ───── Helper: add row to logs table ───── */
function addLog($conn, $user_id, $username, $action, $status = 'success')
{
    $ip   = getRealIpAddress();
    $port = $_SERVER['REMOTE_PORT'] ?? '0';
    $ipWithPort = $ip . ':' . $port;

    $stmt = $conn->prepare(
        "INSERT INTO logs (user_id, username, action, status, ip_address, created_at)
         VALUES (?, ?, ?, ?, ?, NOW())"
    );
    $stmt->bind_param("issss",
        $user_id,
        $username,
        $action,
        $status,
        $ipWithPort
    );
    $stmt->execute();
}

try {
    /* ───── Connect ───── */
    $conn = new mysqli($host, $user, $pass, $dbname);
    $conn->set_charset('utf8mb4');

    /* ───── Auto-inactive rule ───── */
    $conn->query("
        UPDATE users
        SET status = 'inactive'
        WHERE status = 'active'
          AND (
                (last_login IS NOT NULL AND last_login < NOW() - INTERVAL 3 DAY)
             OR (last_login IS NULL    AND created_at < NOW() - INTERVAL 3 DAY)
          )
    ");

} catch (mysqli_sql_exception $e) {
    die('Database connection failed: ' . $e->getMessage());
}
?>
