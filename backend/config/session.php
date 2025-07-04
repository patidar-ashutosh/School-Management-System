<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS

// Session timeout (30 minutes)
$session_timeout = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Function to check if user has specific role
function hasRole($role) {
    return isLoggedIn() && $_SESSION['role'] === $role;
}

// Function to check if user has any of the specified roles
function hasAnyRole($roles) {
    if (!isLoggedIn()) return false;
    return in_array($_SESSION['role'], $roles);
}

// Function to get current user ID
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Function to get current user role
function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

// Function to get current user data
function getCurrentUserData() {
    if (!isLoggedIn()) return null;
    
    return [
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'role' => $_SESSION['role'],
        'email' => $_SESSION['email'] ?? ''
    ];
}

// Function to require authentication
function requireAuth() {
    if (!isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Authentication required']);
        exit;
    }
}

// Function to require specific role
function requireRole($role) {
    requireAuth();
    if (!hasRole($role)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }
}

// Function to require any of the specified roles
function requireAnyRole($roles) {
    requireAuth();
    if (!hasAnyRole($roles)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied']);
        exit;
    }
}

// Function to set user session
function setUserSession($userData) {
    $_SESSION['user_id'] = $userData['id'];
    $_SESSION['role'] = $userData['role'];
    $_SESSION['email'] = $userData['email'] ?? '';
    
    // Handle username - use first_name + last_name if username not provided
    if (isset($userData['username'])) {
        $_SESSION['username'] = $userData['username'];
    } elseif (isset($userData['first_name']) && isset($userData['last_name'])) {
        $_SESSION['username'] = $userData['first_name'] . ' ' . $userData['last_name'];
    } else {
        $_SESSION['username'] = 'User';
    }
    
    $_SESSION['last_activity'] = time();
}

// Function to clear user session
function clearUserSession() {
    session_unset();
    session_destroy();
    session_start();
}
?> 