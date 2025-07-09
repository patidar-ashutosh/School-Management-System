<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/session.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Check authentication status
    echo json_encode([
        'loggedIn' => isLoggedIn(),
        'user' => getCurrentUserData()
    ]);
    exit;
}

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['action']) && $input['action'] === 'logout') {
        // Handle logout
        clearUserSession();
        echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
        exit;
    }
    
    // Handle login
    if (isset($input['email']) && isset($input['password'])) {
        $email = $input['email'];
        $password = $input['password'];
        $role = $input['role'] ?? '';
        
        try {
            $db = db();
            
            if ($role === 'principal') {
                // Principal login
                $stmt = $db->query("SELECT * FROM principals WHERE email = ?", [$email]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password'])) {
                    $userData = [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'role' => 'principal',
                        'first_name' => $user['first_name'],
                        'last_name' => $user['last_name']
                    ];
                    
                    setUserSession($userData);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Login successful',
                        'role' => 'principal',
                        'user' => $userData
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
                }
            } elseif ($role === 'teacher') {
                // Teacher login
                $stmt = $db->query("SELECT * FROM teachers WHERE email = ? AND status = 'active'", [$email]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password'])) {
                    $userData = [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'role' => 'teacher',
                        'first_name' => $user['first_name'],
                        'last_name' => $user['last_name']
                    ];
                    
                    setUserSession($userData);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Login successful',
                        'role' => 'teacher',
                        'user' => $userData
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
                }
            } elseif ($role === 'student') {
                // Student login
                $stmt = $db->query("SELECT * FROM students WHERE email = ? AND status = 'active'", [$email]);
                $student = $stmt->fetch();
                
                if ($student && password_verify($password, $student['password'])) {
                    // Prepare student data for session
                    $studentSessionData = [
                        'id' => $student['id'],
                        'email' => $student['email'],
                        'role' => 'student',
                        'first_name' => $student['first_name'],
                        'last_name' => $student['last_name'],
                        'roll_number' => $student['roll_number']
                    ];
                    
                    setUserSession($studentSessionData);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Login successful',
                        'role' => 'student',
                        'user' => [
                            'id' => $student['id'],
                            'roll_number' => $student['roll_number'],
                            'first_name' => $student['first_name'],
                            'last_name' => $student['last_name'],
                            'role' => 'student'
                        ]
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid role specified']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?> 