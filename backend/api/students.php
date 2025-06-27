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
                if (!isset($input['name']) || !isset($input['roll_number']) || !isset($input['class_id'])) {
                    throw new Exception('Name, roll number, and class are required');
                }
                
                // Check if roll number already exists
                if ($student->rollNumberExists($input['roll_number'])) {
                    throw new Exception('Roll number already exists');
                }
                
                $studentId = $student->create($input);
                echo json_encode([
                    'success' => true,
                    'message' => 'Student created successfully',
                    'id' => $studentId
                ]);
                break;
                
            case 'update':
                if (!isset($input['id']) || !isset($input['name']) || !isset($input['roll_number']) || !isset($input['class_id'])) {
                    throw new Exception('ID, name, roll number, and class are required');
                }
                
                // Check if roll number already exists (excluding current student)
                if ($student->rollNumberExists($input['roll_number'], $input['id'])) {
                    throw new Exception('Roll number already exists');
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