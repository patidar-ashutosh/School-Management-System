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
        // Support both JSON and multipart/form-data
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') !== false) {
            $input = $_POST;
            // For file upload, handle submitted_file
            if (isset($_FILES['submitted_file']) && $_FILES['submitted_file']['error'] === UPLOAD_ERR_OK) {
                // Save file to uploads/assignments/ (create dir if not exists)
                $uploadDir = __DIR__ . '/../../uploads/assignments/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
                $filename = uniqid('assignment_') . '_' . basename($_FILES['submitted_file']['name']);
                $targetPath = $uploadDir . $filename;
                if (move_uploaded_file($_FILES['submitted_file']['tmp_name'], $targetPath)) {
                    $input['submitted_file'] = 'uploads/assignments/' . $filename;
                } else {
                    throw new Exception('File upload failed');
                }
            }
        } else {
        $input = json_decode(file_get_contents('php://input'), true);
        }
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
                // If only id and status are provided, update only the status
                if (isset($input['id']) && isset($input['status']) && count($input) === 3 && isset($input['action'])) {
                    $db = $student->getDb();
                    $db->query("UPDATE students SET status = ? WHERE id = ?", [$input['status'], $input['id']]);
                    $msg = $input['status'] === 'inactive' ? 'Student deactivated successfully!' : 'Student status updated!';
                    echo json_encode([
                        'success' => true,
                        'message' => $msg
                    ]);
                    break;
                }
                // Otherwise, do a full update as before
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
                $msg = 'Student updated successfully';
                if (isset($input['status']) && $input['status'] === 'inactive') {
                    $msg = 'Student deactivated successfully!';
                }
                echo json_encode([
                    'success' => true,
                    'message' => $msg
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
                $subject_id = $input['subject_id'] ?? null;
                $db = $student->getDb();
                // First, try to fetch from student_assignments
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
                if (count($rows) > 0) {
                echo json_encode(['success' => true, 'assignments' => $rows]);
                    break;
                }
                // If no rows, fetch student's class_id
                $studentData = $student->getById($studentId);
                if (!$studentData || !isset($studentData['class_id'])) {
                    echo json_encode(['success' => true, 'assignments' => []]);
                    break;
                }
                $classId = $studentData['class_id'];
                // Fetch assignments for this class
                $sql2 = "SELECT a.*, sub.name as subject_name FROM assignments a LEFT JOIN subjects sub ON a.subject_id = sub.id WHERE a.class_id = ? ORDER BY a.due_date DESC, a.id DESC";
                $params2 = [$classId];
                if ($status) {
                    $sql2 .= " AND a.status = ?";
                    $params2[] = $status;
                }
                if ($type) {
                    $sql2 .= " AND a.type = ?";
                    $params2[] = $type;
                }
                if ($subject_id) {
                    $sql2 .= " AND a.subject_id = ?";
                    $params2[] = $subject_id;
                }
                $assignments = $db->fetchAll($sql2, $params2);
                // Map to expected output format (remove not_submitted logic)
                // Only return assignments that exist in student_assignments
                echo json_encode(['success' => true, 'assignments' => $rows]);
                break;
                
            case 'get_assignment_stats':
                if (!isset($input['student_id'])) {
                    throw new Exception('Student ID is required');
                }
                $studentId = $input['student_id'];
                $db = $student->getDb();
                // Total assignments (only those submitted or graded)
                $total = $db->fetch("SELECT COUNT(*) as count FROM student_assignments WHERE student_id = ?", [$studentId])['count'];
                // Completed assignments (graded or submitted)
                $completed = $db->fetch("SELECT COUNT(*) as count FROM student_assignments WHERE student_id = ? AND status IN ('graded', 'submitted')", [$studentId])['count'];
                // Overdue assignments (submitted/graded and due_date < today)
                $overdue = $db->fetch("SELECT COUNT(*) as count FROM student_assignments sa JOIN assignments a ON sa.assignment_id = a.id WHERE sa.student_id = ? AND a.due_date < CURDATE()", [$studentId])['count'];
                echo json_encode([
                    'success' => true,
                    'stats' => [
                        'total' => $total,
                        'completed' => $completed,
                        'overdue' => $overdue
                    ]
                ]);
                break;
                
            case 'submit_assignment':
                if (!isset($input['student_id']) || !isset($input['assignment_id'])) {
                    throw new Exception('Student ID and Assignment ID are required');
                }
                $studentId = $input['student_id'];
                $assignmentId = $input['assignment_id'];
                $submittedText = $input['submitted_text'] ?? null;
                $submittedFile = $input['submitted_file'] ?? null;
                $db = $student->getDb();
                // Check if row exists
                $row = $db->fetch("SELECT id FROM student_assignments WHERE student_id = ? AND assignment_id = ?", [$studentId, $assignmentId]);
                if ($row) {
                    // Update
                    $db->query("UPDATE student_assignments SET status = 'submitted', submitted_date = NOW(), submitted_text = ?, submitted_file = ? WHERE id = ?", [$submittedText, $submittedFile, $row['id']]);
                } else {
                    // Insert
                    $db->query("INSERT INTO student_assignments (student_id, assignment_id, status, submitted_date, submitted_text, submitted_file) VALUES (?, ?, 'submitted', NOW(), ?, ?)", [$studentId, $assignmentId, $submittedText, $submittedFile]);
                }
                echo json_encode(['success' => true, 'message' => 'Assignment submitted successfully!']);
                break;
                
            case 'get_class_assignments':
                if (!isset($input['student_id'])) {
                    throw new Exception('Student ID is required');
                }
                $studentId = $input['student_id'];
                $studentData = $student->getById($studentId);
                if (!$studentData || !isset($studentData['class_id'])) {
                    echo json_encode(['success' => true, 'assignments' => []]);
                    break;
                }
                $classId = $studentData['class_id'];
                $db = $student->getDb();
                $sql = "SELECT a.*, sub.name as subject_name FROM assignments a LEFT JOIN subjects sub ON a.subject_id = sub.id WHERE a.class_id = ? AND a.status IN ('coming', 'running') AND a.id NOT IN (SELECT assignment_id FROM student_assignments WHERE student_id = ?) ORDER BY a.due_date DESC, a.id DESC";
                $assignments = $db->fetchAll($sql, [$classId, $studentId]);
                echo json_encode(['success' => true, 'assignments' => $assignments]);
                break;
            case 'get_my_submissions':
                if (!isset($input['student_id'])) {
                    throw new Exception('Student ID is required');
                }
                $studentId = $input['student_id'];
                $db = $student->getDb();
                $sql = "SELECT sa.*, a.title as assignment_title, a.description, a.type as assignment_type, a.due_date, a.total_marks, a.subject_id, a.class_id, a.status as assignment_status, sub.name as subject_name FROM student_assignments sa JOIN assignments a ON sa.assignment_id = a.id LEFT JOIN subjects sub ON a.subject_id = sub.id WHERE sa.student_id = ? AND sa.status IN ('submitted', 'graded') ORDER BY sa.submitted_date DESC, sa.id DESC";
                $rows = $db->fetchAll($sql, [$studentId]);
                echo json_encode(['success' => true, 'assignments' => $rows]);
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