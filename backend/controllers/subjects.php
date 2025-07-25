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
                if (!isset($input['class_ids']) || !is_array($input['class_ids']) || count($input['class_ids']) === 0) {
                    throw new Exception('At least one class is required for subject creation');
                }
                $subjectData = [
                    'name' => strtolower(trim($input['name'])),
                    'description' => $input['description'] ?? null,
                    'status' => $input['status'] ?? 'active',
                    'class_ids' => $input['class_ids']
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
                if (!isset($input['class_ids']) || !is_array($input['class_ids']) || count($input['class_ids']) === 0) {
                    throw new Exception('At least one class is required for subject update');
                }
                $subjectData = [
                    'name' => strtolower(trim($input['name'])),
                    'description' => $input['description'] ?? null,
                    'status' => $input['status'] ?? 'active',
                    'class_ids' => $input['class_ids']
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
                
            case 'get_schedule_by_class':
                if (!isset($input['class_id'])) {
                    throw new Exception('Class ID is required');
                }
                $class_id = $input['class_id'];
                $db = db();
                $sql = "SELECT l.*, s.name as subject_name, t.first_name as teacher_first_name, t.last_name as teacher_last_name
                        FROM lecturers l
                        LEFT JOIN subjects s ON l.subject_id = s.id
                        LEFT JOIN teachers t ON l.teacher_id = t.id
                        WHERE l.class_id = ? AND l.status IN ('scheduled', 'running', 'completed')
                        ORDER BY l.date, l.start_time";
                $rows = $db->fetchAll($sql, [$class_id]);
                foreach ($rows as &$row) {
                    $row['day_of_week'] = date('l', strtotime($row['date']));
                }
                unset($row);
                echo json_encode(['success' => true, 'data' => $rows]);
                return;
                
            case 'get_by_teacher_and_class':
                if (!isset($input['teacher_id']) || !isset($input['class_id'])) {
                    throw new Exception('Teacher ID and Class ID are required');
                }
                $teacher_id = intval($input['teacher_id']);
                $class_id = intval($input['class_id']);
                // Only subjects for this class that the teacher teaches
                $db = db()->getConnection();
                $sql = "SELECT s.id, s.name FROM subjects s WHERE s.class_id = ? AND (s.id IN (SELECT t.subject_id FROM teachers t WHERE t.id = ?))";
                $stmt = $db->prepare($sql);
                $stmt->execute([$class_id, $teacher_id]);
                $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode(['success' => true, 'data' => $subjects]);
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