<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Class.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$student = new Student();
$class = new ClassModel();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['action'])) {
            throw new Exception('Action is required');
        }
        
        switch ($input['action']) {
            case 'get_all':
                $students = $student->getAll();
                echo json_encode([
                    'success' => true,
                    'data' => $students
                ]);
                break;
                
            case 'get_by_id':
                if (!isset($input['id'])) {
                    throw new Exception('Student ID is required');
                }
                
                $studentData = $student->getById($input['id']);
                if ($studentData) {
                    echo json_encode([
                        'success' => true,
                        'data' => $studentData
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Student not found'
                    ]);
                }
                break;
                
            case 'create':
                if (!isset($input['first_name']) || !isset($input['last_name']) || !isset($input['email']) || !isset($input['class_id'])) {
                    throw new Exception('First name, last name, email, and class are required');
                }
                
                // Check if email already exists
                if ($student->emailExists($input['email'])) {
                    throw new Exception('Email already exists');
                }
                
                // Set default status if not provided
                if (!isset($input['status'])) {
                    $input['status'] = 'active';
                }
                
                $studentId = $student->create($input);
                echo json_encode([
                    'success' => true,
                    'message' => 'Student created successfully',
                    'id' => $studentId
                ]);
                break;
                
            case 'update':
                if (!isset($input['id']) || !isset($input['first_name']) || !isset($input['last_name']) || !isset($input['email']) || !isset($input['class_id'])) {
                    throw new Exception('ID, first name, last name, email, and class are required');
                }
                
                // Check if email already exists (excluding current student)
                if ($student->emailExists($input['email'], $input['id'])) {
                    throw new Exception('Email already exists');
                }
                
                // Set default status if not provided
                if (!isset($input['status'])) {
                    $input['status'] = 'active';
                }
                
                $student->update($input['id'], $input);
                echo json_encode([
                    'success' => true,
                    'message' => 'Student updated successfully'
                ]);
                break;
                
            case 'delete':
                if (!isset($input['id'])) {
                    throw new Exception('Student ID is required');
                }
                
                $student->delete($input['id']);
                echo json_encode([
                    'success' => true,
                    'message' => 'Student deleted successfully'
                ]);
                break;
                
            case 'get_by_class':
                if (!isset($input['class_id'])) {
                    throw new Exception('Class ID is required');
                }
                
                $students = $student->getByClass($input['class_id']);
                echo json_encode([
                    'success' => true,
                    'data' => $students
                ]);
                break;
                
            case 'get_count':
                $count = $student->getCount();
                echo json_encode([
                    'success' => true,
                    'count' => $count
                ]);
                break;
                
            case 'get_recent':
                // Get recent students (last 5)
                $students = $student->getAll();
                $recentStudents = array_slice($students, 0, 5);
                echo json_encode([
                    'success' => true,
                    'data' => $recentStudents
                ]);
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