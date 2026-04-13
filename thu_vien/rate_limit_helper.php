<?php
/**
 * RATE LIMITING HELPER - Chống Brute Force Attack
 * 
 * Cần chạy SQL sau trước để tạo table:
 * CREATE TABLE login_attempts (
 *     id INT PRIMARY KEY AUTO_INCREMENT,
 *     ip_address VARCHAR(45) NOT NULL,
 *     username VARCHAR(100),
 *     attempt_time DATETIME NOT NULL,
 *     status ENUM('fail', 'success'),
 *     INDEX idx_ip_time (ip_address, attempt_time),
 *     INDEX idx_username_time (username, attempt_time)
 * );
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// === ĐỊNH NGHĨA RATE LIMIT THÔNG SỐ ===
define('MAX_LOGIN_ATTEMPTS', 5);          // Tối đa 5 lần thử
define('LOGIN_ATTEMPT_WINDOW', 900);      // Trong 15 phút (900 giây)
define('LOCKOUT_DURATION', 1800);         // Khóa 30 phút (1800 giây)

/**
 * Lấy IP của user
 * @return string
 */
function getClientIP() {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
}

/**
 * Kiểm tra xem IP có đang bị khóa không (quá nhiều lần thử fail)
 * @param mysqli $conn
 * @param string $identifier - IP hoặc username
 * @return bool - true nếu bị khóa, false nếu bình thường
 */
function isRateLimited($conn, $identifier) {
    $now = new DateTime();
    $windowStart = $now->modify('-' . LOGIN_ATTEMPT_WINDOW . ' seconds')->format('Y-m-d H:i:s');
    $lockoutStart = $now->modify('-' . LOCKOUT_DURATION . ' seconds')->format('Y-m-d H:i:s');
    
    // Kiểm tra có bị lockout không (quá nhiều fail gần đây)
    $sql_check = "SELECT COUNT(*) as fail_count, MAX(attempt_time) as last_attempt
                  FROM login_attempts
                  WHERE (ip_address = ? OR username = ?)
                  AND status = 'fail'
                  AND attempt_time >= ?
                  LIMIT 1";
    
    $stmt = $conn->prepare($sql_check);
    if (!$stmt) return false;
    
    $stmt->bind_param("sss", $identifier, $identifier, $windowStart);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if ($row && $row['fail_count'] >= MAX_LOGIN_ATTEMPTS) {
        // Bị quá tải - lockout
        return true;
    }
    
    return false;
}

/**
 * Ghi lại lần thử login
 * @param mysqli $conn
 * @param string $username
 * @param string $status - 'success' hoặc 'fail'
 */
function recordLoginAttempt($conn, $username, $status = 'fail') {
    $ip = getClientIP();
    $now = date('Y-m-d H:i:s');
    
    $sql = "INSERT INTO login_attempts (ip_address, username, attempt_time, status)
            VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssss", $ip, $username, $now, $status);
        $stmt->execute();
        $stmt->close();
    }
    
    // Nếu login thành công, xóa các lần thử fail cũ
    if ($status === 'success') {
        $sql_clean = "DELETE FROM login_attempts 
                      WHERE username = ? AND status = 'fail'";
        $stmt = $conn->prepare($sql_clean);
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->close();
        }
    }
}

/**
 * Lấy thông tin rate limiting hiện tại (for admin view)
 * @param mysqli $conn
 * @param string $identifier
 * @return array - ['attempts' => số lần, 'last_attempt' => thời gian cuối, 'is_locked' => bool]
 */
function getRateLimitStatus($conn, $identifier) {
    $ip = getClientIP();
    $now = new DateTime();
    $windowStart = $now->modify('-' . LOGIN_ATTEMPT_WINDOW . ' seconds')->format('Y-m-d H:i:s');
    
    $sql = "SELECT COUNT(*) as attempt_count, MAX(attempt_time) as last_attempt
            FROM login_attempts
            WHERE (ip_address = ? OR username = ?)
            AND status = 'fail'
            AND attempt_time >= ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $ip, $identifier, $windowStart);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return [
        'attempts' => (int)($row['attempt_count'] ?? 0),
        'last_attempt' => $row['last_attempt'] ?? null,
        'is_locked' => isRateLimited($conn, $identifier),
        'remaining_attempts' => max(0, MAX_LOGIN_ATTEMPTS - ($row['attempt_count'] ?? 0))
    ];
}

/**
 * Reset rate limit untuk satu user (admin action)
 * @param mysqli $conn
 * @param string $username
 */
function resetLoginAttempts($conn, $username) {
    $sql = "DELETE FROM login_attempts WHERE username = ? AND status = 'fail'";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->close();
    }
}

/**
 * Clean up old records (chạy periodic)
 * @param mysqli $conn
 */
function cleanupOldAttempts($conn) {
    $cutoff = date('Y-m-d H:i:s', time() - (7 * 24 * 3600)); // 7 ngày
    
    $sql = "DELETE FROM login_attempts WHERE attempt_time < ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $cutoff);
        $stmt->execute();
        $stmt->close();
    }
}

?>
