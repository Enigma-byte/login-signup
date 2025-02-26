<?php
require_once 'configs/database.php';
require_once 'includes/helpers.php';

if (isLoggedIn()) {
    try {
        $db = Database::getInstance();
        $userId = $_SESSION['user_id'];
        $currentSessionId = session_id();

        // First, mark all sessions as invalid for this user
        $db->execute(
            "UPDATE Sessions SET IsValid = FALSE WHERE UserId = ?",
            [$userId]
        );

        // Then, delete all expired or invalid sessions for this user
        $db->execute(
            "DELETE FROM Sessions WHERE UserId = ? AND (ExpiresAt < NOW() OR IsValid = FALSE)",
            [$userId]
        );

        // Log the logout event
        $db->execute(
            "INSERT INTO LoginAttempts (UserId, Status, IPAddress, UserAgent, AttemptTime) 
            VALUES (?, 'Success', ?, ?, NOW())",
            [$userId, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]
        );

    } catch (Exception $e) {
        error_log("Logout error: " . $e->getMessage());
        // Continue with logout even if database operations fail
    }

    // Clear all session data
    $_SESSION = array();
    
    // Destroy the session cookie
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', [
            'expires' => time() - 3600,
            'path' => '/',
            'domain' => '',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    // Finally destroy the session
    session_destroy();
}

// Redirect to login page with a message
setFlashMessage('success', 'You have been successfully logged out');
header('Location: /index.php');
exit();
