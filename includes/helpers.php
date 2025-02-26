<?php
session_start();

/**
 * Authentication and Session Helpers
 */

function isLoggedIn() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['session_id'])) {
        return false;
    }

    try {
        $db = Database::getInstance();
        $result = $db->query(
            "SELECT SessionId FROM Sessions 
            WHERE SessionId = ? 
            AND UserId = ? 
            AND ExpiresAt > NOW() 
            AND IsValid = TRUE",
            [$_SESSION['session_id'], $_SESSION['user_id']]
        );

        if (!$result) {
            // Session not found or invalid
            session_destroy();
            return false;
        }

        // Update last activity
        $db->execute(
            "UPDATE Sessions 
            SET LastActivity = NOW() 
            WHERE SessionId = ?",
            [$_SESSION['session_id']]
        );

        return true;
    } catch (Exception $e) {
        // Log error and fail safely
        error_log('Session validation error: ' . $e->getMessage());
        return false;
    }
}

function getUserRole() {
    return $_SESSION['user_role'] ?? null;
}

function requireLogin() {
    if (!isLoggedIn()) {
        $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') .
            "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('Location: /?return_url=' . urlencode($currentUrl));
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (getUserRole() !== 'Admin') {
        header('Location: /home/');
        exit();
    }
}

function requireUser() {
    requireLogin();
    if (getUserRole() !== 'User') {
        header('Location: /admin/');
        exit();
    }
}

function redirect($role, $return_url = null) {
    if ($return_url && isValidInternalUrl($return_url)) {
        header('Location: ' . $return_url);
    } else {
        if ($role === 'Admin') {
            header('Location: /admin/');
        } else {
            header('Location: /home/');
        }
    }
    exit();
}

function isValidInternalUrl($url) {
    // Parse the URL
    $parsedUrl = parse_url($url);
    
    // Check if it's a relative URL or matches our domain
    if (!isset($parsedUrl['host'])) {
        return true; // Relative URL is safe
    }
    
    $allowedDomains = [$_SERVER['HTTP_HOST']]; // Add any other allowed domains here
    return in_array($parsedUrl['host'], $allowedDomains);
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generateSessionId() {
    return bin2hex(random_bytes(32));
}

function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
