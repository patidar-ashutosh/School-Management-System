<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../models/Class.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$class = new ClassModel();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['action'])) {
            throw new Exception('Action is required');
        }
        
        switch ($input['action']) {
            case 'get_all':
                $classes = $class->getAll();
                echo json_encode([
                    'success' => true,
                    'data' => $classes
                ]);
                break;
                
            case 'get_by_id':
                if (!isset($input['id'])) {
                    throw new Exception('Class ID is required');
                }
                
                $classData = $class->getById($input['id']);
                if ($classData) {
                    echo json_encode([
                        'success' => true,
                        'data' => $classData
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Class not found'
                    ]);
                }
                break;
                
            case 'create':
                if (!isset($input['class_name'])) {
                    throw new Exception('Class name is required');
                }
                
                $classId = $class->create($input);
                echo json_encode([
                    'success' => true,
                    'message' => 'Class created successfully',
                    'id' => $classId
                ]);
                break;
                
            case 'update':
                if (!isset($input['id']) || !isset($input['class_name'])) {
                    throw new Exception('ID and class name are required');
                }
                
                $class->update($input['id'], $input);
                echo json_encode([
                    'success' => true,
                    'message' => 'Class updated successfully'
                ]);
                break;
                
            case 'delete':
                if (!isset($input['id'])) {
                    throw new Exception('Class ID is required');
                }
                
                $class->delete($input['id']);
                echo json_encode([
                    'success' => true,
                    'message' => 'Class deleted successfully'
                ]);
                break;
                
            case 'get_with_student_count':
                $classes = $class->getWithStudentCount();
                echo json_encode([
                    'success' => true,
                    'data' => $classes
                ]);
                break;
                
            case 'get_count':
                $count = $class->getCount();
                echo json_encode([
                    'success' => true,
                    'count' => $count
                ]);
                break;
                
            case 'get_by_teacher':
                if (!isset($input['teacher_id'])) {
                    throw new Exception('Teacher ID is required');
                }
                
                $classes = $class->getByTeacher($input['teacher_id']);
                echo json_encode([
                    'success' => true,
                    'data' => $classes
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