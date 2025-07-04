<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/db.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['action'])) {
        echo json_encode(['success' => false, 'message' => 'Action not specified']);
        exit;
    }
    
    try {
        $db = db();
        
        switch ($input['action']) {
            case 'forgot_password':
                handleForgotPassword($db, $input);
                break;
            case 'reset_password':
                handleResetPassword($db, $input);
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

function handleForgotPassword($db, $input) {
    if (!isset($input['email']) || !isset($input['userType'])) {
        echo json_encode(['success' => false, 'message' => 'Email and user type are required']);
        return;
    }
    
    $email = trim($input['email']);
    $userType = $input['userType'];
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        return;
    }
    
    // Check if user exists based on user type
    $user = null;
    $tableName = '';
    
    switch ($userType) {
        case 'principal':
            $tableName = 'principals';
            $stmt = $db->query("SELECT * FROM principals WHERE email = ? AND status = 'active'", [$email]);
            $user = $stmt->fetch();
            break;
        case 'teacher':
            $tableName = 'teachers';
            $stmt = $db->query("SELECT * FROM teachers WHERE email = ? AND status = 'active'", [$email]);
            $user = $stmt->fetch();
            break;
        case 'student':
            $tableName = 'students';
            $stmt = $db->query("SELECT * FROM students WHERE email = ? AND status = 'active'", [$email]);
            $user = $stmt->fetch();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid user type']);
            return;
    }
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'No account found with this email address']);
        return;
    }
    
    // Generate reset token
    $token = bin2hex(random_bytes(32));
    $expiresAt = gmdate('Y-m-d H:i:s', time() + 3600);
    
    // Store reset token in database
    $stmt = $db->query("INSERT INTO password_resets (email, user_type, token, expires_at) VALUES (?, ?, ?, NOW() + INTERVAL 1 HOUR)", 
        [$email, $userType, $token]);
    
    if ($stmt) {
        // In a real application, you would send an email here
        // For now, we'll just return the token (in production, send via email)
        echo json_encode([
            'success' => true, 
            'message' => 'Password reset link has been generated. Please check your email.',
            'token' => $token, // Remove this in production
            'email' => $email,
            'userType' => $userType
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to generate reset token']);
    }
}

function handleResetPassword($db, $input) {
    if (!isset($input['token']) || !isset($input['email']) || !isset($input['userType']) || !isset($input['newPassword'])) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        return;
    }
    
    $token = $input['token'];
    $email = $input['email'];
    $userType = $input['userType'];
    $newPassword = $input['newPassword'];
    
    // Validate password strength
    if (strlen($newPassword) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long']);
        return;
    }
    
    if (!preg_match('/[A-Z]/', $newPassword)) {
        echo json_encode(['success' => false, 'message' => 'Password must contain at least one uppercase letter']);
        return;
    }
    
    if (!preg_match('/[a-z]/', $newPassword)) {
        echo json_encode(['success' => false, 'message' => 'Password must contain at least one lowercase letter']);
        return;
    }
    
    if (!preg_match('/\d/', $newPassword)) {
        echo json_encode(['success' => false, 'message' => 'Password must contain at least one number']);
        return;
    }
    
    // Verify token
    $stmt = $db->query("SELECT * FROM password_resets WHERE token = ? AND email = ? AND user_type = ? AND expires_at > NOW() AND used = 0", 
        [$token, $email, $userType]);
    $resetRecord = $stmt->fetch();
    
    if (!$resetRecord) {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired reset token']);
        return;
    }
    
    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password in appropriate table
    $tableName = '';
    switch ($userType) {
        case 'principal':
            $tableName = 'principals';
            break;
        case 'teacher':
            $tableName = 'teachers';
            break;
        case 'student':
            $tableName = 'students';
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid user type']);
            return;
    }
    
    $stmt = $db->query("UPDATE $tableName SET password = ? WHERE email = ?", [$hashedPassword, $email]);
    
    if ($stmt) {
        // Mark token as used
        $db->query("UPDATE password_resets SET used = 1 WHERE token = ?", [$token]);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Password has been reset successfully. You can now login with your new password.'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update password']);
    }
}
?> 