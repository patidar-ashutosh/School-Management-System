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
                
            case 'get_assignments':
                if (!isset($input['student_id'])) {
                    throw new Exception('Student ID is required');
                }
                $studentId = $input['student_id'];
                $status = $input['status'] ?? null;
                $type = $input['type'] ?? null;
                if ($type && !in_array($type, ['essays', 'reports', 'presentations'])) {
                    echo json_encode(['success' => false, 'message' => 'Invalid assignment type.']);
                    return;
                }
                $subject_id = $input['subject_id'] ?? null;
                $db = $student->getDb();
                $sql = "SELECT sa.*, a.title as assignment_title, a.description, a.type as assignment_type, a.due_date, a.total_marks, a.subject_id, a.class_id, a.status as assignment_status, sub.name as subject_name
                        FROM student_assignments sa
                        JOIN assignments a ON sa.assignment_id = a.id
                        LEFT JOIN subjects sub ON a.subject_id = sub.id
                        WHERE sa.student_id = ?";
                $params = [$studentId];
                if ($status) {
                    $sql .= " AND sa.status = ?";
                    $params[] = $status;
                }
                if ($type) {
                    $sql .= " AND a.type = ?";
                    $params[] = $type;
                }
                if ($subject_id) {
                    $sql .= " AND a.subject_id = ?";
                    $params[] = $subject_id;
                }
                $sql .= " ORDER BY a.due_date DESC, sa.id DESC";
                $rows = $db->fetchAll($sql, $params);
                echo json_encode(['success' => true, 'assignments' => $rows]);
                break;
                
            case 'get_assignment_stats':
                if (!isset($input['student_id'])) {
                    throw new Exception('Student ID is required');
                }
                $studentId = $input['student_id'];
                $db = $student->getDb();
                // Total assignments
                $total = $db->fetch("SELECT COUNT(*) as count FROM student_assignments WHERE student_id = ?", [$studentId])['count'];
                // Completed assignments (graded or submitted)
                $completed = $db->fetch("SELECT COUNT(*) as count FROM student_assignments WHERE student_id = ? AND status IN ('graded', 'submitted')", [$studentId])['count'];
                // Pending assignments
                $pending = $db->fetch("SELECT COUNT(*) as count FROM student_assignments WHERE student_id = ? AND status = 'pending'", [$studentId])['count'];
                // Overdue assignments (pending and due_date < today)
                $overdue = $db->fetch("SELECT COUNT(*) as count FROM student_assignments sa JOIN assignments a ON sa.assignment_id = a.id WHERE sa.student_id = ? AND sa.status = 'pending' AND a.due_date < CURDATE()", [$studentId])['count'];
                echo json_encode([
                    'success' => true,
                    'stats' => [
                        'total' => $total,
                        'completed' => $completed,
                        'pending' => $pending,
                        'overdue' => $overdue
                    ]
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