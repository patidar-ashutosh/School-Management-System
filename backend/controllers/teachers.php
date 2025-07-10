<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../models/Teacher.php';
require_once __DIR__ . '/../models/Subject.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$teacher = new Teacher();
$subject = new Subject();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['action'])) {
            throw new Exception('Action is required');
        }
        
        switch ($input['action']) {
            case 'get_all':
                $teachers = $teacher->getAll();
                echo json_encode([
                    'success' => true,
                    'data' => $teachers
                ]);
                break;
                
            case 'get_by_id':
                if (!isset($input['id'])) {
                    throw new Exception('Teacher ID is required');
                }
                
                $teacherData = $teacher->getById($input['id']);
                if ($teacherData) {
                    echo json_encode([
                        'success' => true,
                        'data' => $teacherData
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Teacher not found'
                    ]);
                }
                break;
                
            case 'create':
                if (!isset($input['first_name']) || !isset($input['last_name']) || !isset($input['email'])) {
                    throw new Exception('First name, last name, and email are required');
                }
                
                // Check if email already exists
                if ($teacher->emailExists($input['email'])) {
                    throw new Exception('Email already exists');
                }
                
                $teacherId = $teacher->create($input);
                // Handle classes_taught
                if (isset($input['classes_taught']) && is_array($input['classes_taught'])) {
                    $teacher->setClassesTaught($teacherId, $input['classes_taught']);
                }
                echo json_encode([
                    'success' => true,
                    'message' => 'Teacher created successfully',
                    'id' => $teacherId
                ]);
                break;
                
            case 'update':
                if (!isset($input['id']) || !isset($input['first_name']) || !isset($input['last_name']) || !isset($input['email'])) {
                    throw new Exception('ID, first name, last name, and email are required');
                }
                
                // Check if email already exists (excluding current teacher)
                if ($teacher->emailExists($input['email'], $input['id'])) {
                    throw new Exception('Email already exists');
                }
                
                $teacher->update($input['id'], $input);
                // Handle classes_taught
                if (isset($input['classes_taught']) && is_array($input['classes_taught'])) {
                    $teacher->setClassesTaught($input['id'], $input['classes_taught']);
                }
                $msg = 'Teacher updated successfully';
                if (isset($input['status']) && $input['status'] === 'inactive') {
                    $msg = 'Teacher deactivated successfully!';
                }
                echo json_encode([
                    'success' => true,
                    'message' => $msg
                ]);
                break;
                
            case 'delete':
                if (!isset($input['id'])) {
                    throw new Exception('Teacher ID is required');
                }
                
                $teacher->delete($input['id']);
                echo json_encode([
                    'success' => true,
                    'message' => 'Teacher deleted successfully'
                ]);
                break;
                
            case 'get_by_subject':
                if (!isset($input['subject_id'])) {
                    throw new Exception('Subject ID is required');
                }
                
                $teachers = $teacher->getBySubject($input['subject_id']);
                echo json_encode([
                    'success' => true,
                    'data' => $teachers
                ]);
                break;
                
            case 'get_available':
                $teachers = $teacher->getAvailableTeachers();
                echo json_encode([
                    'success' => true,
                    'data' => $teachers
                ]);
                break;
                
            case 'get_count':
                $count = $teacher->getCount();
                echo json_encode([
                    'success' => true,
                    'count' => $count
                ]);
                break;
                
            case 'get_recent':
                // Get recent teachers (last 5)
                $teachers = $teacher->getAll();
                $recentTeachers = array_slice($teachers, 0, 5);
                echo json_encode([
                    'success' => true,
                    'data' => $recentTeachers
                ]);
                break;
                
            default:
                throw new Exception('Invalid action');
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'get_all':
                $teachers = $teacher->getAll();
                echo json_encode([
                    'success' => true,
                    'data' => $teachers
                ]);
                break;
                
            case 'get_by_id':
                $id = $_GET['id'] ?? null;
                if (!$id) {
                    throw new Exception('Teacher ID is required');
                }
                
                $teacherData = $teacher->getById($id);
                if ($teacherData) {
                    echo json_encode([
                        'success' => true,
                        'data' => $teacherData
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Teacher not found'
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