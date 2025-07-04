<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../models/Subject.php';
require_once __DIR__ . '/../models/Class.php';
require_once __DIR__ . '/../models/Teacher.php';
require_once __DIR__ . '/../models/Student.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$subject = new Subject();
$class = new ClassModel();
$teacher = new Teacher();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['action'])) {
            throw new Exception('Action is required');
        }
        
        switch ($input['action']) {
            case 'get_all':
                $subjects = $subject->getAll();
                echo json_encode([
                    'success' => true,
                    'data' => $subjects
                ]);
                break;
                
            case 'get_by_id':
                if (!isset($input['id'])) {
                    throw new Exception('Subject ID is required');
                }
                
                $subjectData = $subject->getById($input['id']);
                if ($subjectData) {
                    echo json_encode([
                        'success' => true,
                        'data' => $subjectData
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Subject not found'
                    ]);
                }
                break;
                
            case 'create':
                if (!isset($input['name'])) {
                    throw new Exception('Subject name is required');
                }
                $subjectData = [
                    'name' => $input['name'],
                    'description' => $input['description'] ?? null,
                    'status' => $input['status'] ?? 'active'
                ];
                $subjectId = $subject->create($subjectData);
                echo json_encode([
                    'success' => true,
                    'message' => 'Subject created successfully',
                    'id' => $subjectId
                ]);
                break;
                
            case 'update':
                $subjectId = $input['id'] ?? null;
                if (!$subjectId && isset($input['code'])) {
                    $subjectRow = $subject->getByCode($input['code']);
                    if ($subjectRow && isset($subjectRow['id'])) {
                        $subjectId = $subjectRow['id'];
                    }
                }
                if (!$subjectId) {
                    throw new Exception('Subject ID is required');
                }
                if (!isset($input['name'])) {
                    throw new Exception('Subject name is required');
                }
                $subjectData = [
                    'name' => $input['name'],
                    'description' => $input['description'] ?? null,
                    'status' => $input['status'] ?? 'active'
                ];
                $subject->update($subjectId, $subjectData);
                echo json_encode([
                    'success' => true,
                    'message' => 'Subject updated successfully'
                ]);
                break;
                
            case 'delete':
                $subjectId = $input['id'] ?? null;
                if (!$subjectId && isset($input['code'])) {
                    $subjectRow = $subject->getByCode($input['code']);
                    if ($subjectRow && isset($subjectRow['id'])) {
                        $subjectId = $subjectRow['id'];
                    }
                }
                if (!$subjectId) {
                    throw new Exception('Subject ID is required');
                }
                $subject->delete($subjectId);
                echo json_encode([
                    'success' => true,
                    'message' => 'Subject deleted successfully'
                ]);
                break;
                
            case 'get_by_class':
                if (!isset($input['class_id'])) {
                    throw new Exception('Class ID is required');
                }
                
                $subjects = $subject->getByClass($input['class_id']);
                echo json_encode([
                    'success' => true,
                    'data' => $subjects
                ]);
                break;
                
            case 'get_by_teacher':
                if (!isset($input['teacher_id'])) {
                    throw new Exception('Teacher ID is required');
                }
                
                $subjects = $subject->getByTeacher($input['teacher_id']);
                echo json_encode([
                    'success' => true,
                    'data' => $subjects
                ]);
                break;
                
            case 'get_by_student':
                if (!isset($input['student_id'])) {
                    throw new Exception('Student ID is required');
                }
                
                // First get student's class, then get subjects for that class
                $student = new Student();
                $studentData = $student->getById($input['student_id']);
                
                if (!$studentData || !$studentData['class_id']) {
                    echo json_encode([
                        'success' => true,
                        'data' => []
                    ]);
                    break;
                }
                
                $subjects = $subject->getByClass($studentData['class_id']);
                echo json_encode([
                    'success' => true,
                    'data' => $subjects
                ]);
                break;
                
            case 'get_count':
                $count = $subject->getCount();
                echo json_encode([
                    'success' => true,
                    'count' => $count
                ]);
                break;
                
            case 'get_count_by_class':
                if (!isset($input['class_id'])) {
                    throw new Exception('Class ID is required');
                }
                
                $count = $subject->getCountByClass($input['class_id']);
                echo json_encode([
                    'success' => true,
                    'count' => $count
                ]);
                break;
                
            default:
                throw new Exception('Invalid action');
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $action = $_GET['action'] ?? '';
        
        switch ($action) {
            case 'get_all':
                $subjects = $subject->getAll();
                echo json_encode([
                    'success' => true,
                    'data' => $subjects
                ]);
                break;
                
            case 'get_by_id':
                $id = $_GET['id'] ?? null;
                if (!$id) {
                    throw new Exception('Subject ID is required');
                }
                
                $subjectData = $subject->getById($id);
                if ($subjectData) {
                    echo json_encode([
                        'success' => true,
                        'data' => $subjectData
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Subject not found'
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