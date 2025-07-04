<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../models/Mark.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Subject.php';
require_once __DIR__ . '/../models/Exam.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$mark = new Mark();
$student = new Student();
$subject = new Subject();
$exam = new Exam();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['action'])) {
            throw new Exception('Action is required');
        }
        
        switch ($input['action']) {
            case 'get_all':
                $marks = $mark->getAll();
                echo json_encode([
                    'success' => true,
                    'data' => $marks
                ]);
                break;
                
            case 'get_by_id':
                if (!isset($input['id'])) {
                    throw new Exception('Mark ID is required');
                }
                
                $markData = $mark->getById($input['id']);
                if ($markData) {
                    echo json_encode([
                        'success' => true,
                        'data' => $markData
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Mark not found'
                    ]);
                }
                break;
                
            case 'create':
                if (!isset($input['student_id']) || !isset($input['subject_id']) || !isset($input['exam_id']) || !isset($input['marks_obtained'])) {
                    throw new Exception('Student, subject, exam, and marks are required');
                }
                
                // Check if mark already exists for this student, subject, and exam
                if ($mark->markExists($input['student_id'], $input['subject_id'], $input['exam_id'])) {
                    throw new Exception('Mark already exists for this student, subject, and exam');
                }
                
                $markId = $mark->create($input);
                echo json_encode([
                    'success' => true,
                    'message' => 'Mark created successfully',
                    'id' => $markId
                ]);
                break;
                
            case 'update':
                if (!isset($input['id']) || !isset($input['student_id']) || !isset($input['subject_id']) || !isset($input['exam_id']) || !isset($input['marks_obtained'])) {
                    throw new Exception('ID, student, subject, exam, and marks are required');
                }
                
                $mark->update($input['id'], $input);
                echo json_encode([
                    'success' => true,
                    'message' => 'Mark updated successfully'
                ]);
                break;
                
            case 'delete':
                if (!isset($input['id'])) {
                    throw new Exception('Mark ID is required');
                }
                
                $mark->delete($input['id']);
                echo json_encode([
                    'success' => true,
                    'message' => 'Mark deleted successfully'
                ]);
                break;
                
            case 'get_by_student':
                if (!isset($input['student_id'])) {
                    throw new Exception('Student ID is required');
                }
                
                $marks = $mark->getByStudent($input['student_id']);
                echo json_encode([
                    'success' => true,
                    'data' => $marks
                ]);
                break;
                
            case 'get_by_exam':
                if (!isset($input['exam_id'])) {
                    throw new Exception('Exam ID is required');
                }
                
                $marks = $mark->getByExam($input['exam_id']);
                echo json_encode([
                    'success' => true,
                    'data' => $marks
                ]);
                break;
                
            case 'get_by_student_and_exam':
                if (!isset($input['student_id']) || !isset($input['exam_id'])) {
                    throw new Exception('Student ID and exam ID are required');
                }
                
                $marks = $mark->getByStudentAndExam($input['student_id'], $input['exam_id']);
                echo json_encode([
                    'success' => true,
                    'data' => $marks
                ]);
                break;
                
            case 'get_student_average':
                if (!isset($input['student_id'])) {
                    throw new Exception('Student ID is required');
                }
                
                $average = $mark->getStudentAverage($input['student_id']);
                echo json_encode([
                    'success' => true,
                    'average' => $average
                ]);
                break;
                
            case 'get_class_average':
                if (!isset($input['class_id'])) {
                    throw new Exception('Class ID is required');
                }
                
                $examId = $input['exam_id'] ?? null;
                $average = $mark->getClassAverage($input['class_id'], $examId);
                echo json_encode([
                    'success' => true,
                    'average' => $average
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