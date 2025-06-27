<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$user = new User();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['action'])) {
            throw new Exception('Action is required');
        }
        
        switch ($input['action']) {
            case 'login':
                if (!isset($input['email']) || !isset($input['password'])) {
                    throw new Exception('Email and password are required');
                }
                
                $authenticatedUser = $user->authenticate($input['email'], $input['password']);
                
                if ($authenticatedUser) {
                    $_SESSION['user_id'] = $authenticatedUser['id'];
                    $_SESSION['user_name'] = $authenticatedUser['name'];
                    $_SESSION['user_email'] = $authenticatedUser['email'];
                    $_SESSION['user_role'] = $authenticatedUser['role'];
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Login successful',
                        'user' => [
                            'id' => $authenticatedUser['id'],
                            'name' => $authenticatedUser['name'],
                            'email' => $authenticatedUser['email'],
                            'role' => $authenticatedUser['role']
                        ]
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Invalid email or password'
                    ]);
                }
                break;
                
            case 'logout':
                session_destroy();
                echo json_encode([
                    'success' => true,
                    'message' => 'Logout successful'
                ]);
                break;
                
            case 'check_session':
                if (isset($_SESSION['user_id'])) {
                    echo json_encode([
                        'success' => true,
                        'authenticated' => true,
                        'user' => [
                            'id' => $_SESSION['user_id'],
                            'name' => $_SESSION['user_name'],
                            'email' => $_SESSION['user_email'],
                            'role' => $_SESSION['user_role']
                        ]
                    ]);
                } else {
                    echo json_encode([
                        'success' => true,
                        'authenticated' => false
                    ]);
                }
                break;
                
            default:
                throw new Exception('Invalid action');
        }
    } else {
        throw new Exception('Method not allowed');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?> 