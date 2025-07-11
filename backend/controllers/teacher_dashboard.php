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

// Require authentication
requireAuth();

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    try {
        $db = db();
        $teacherId = getCurrentUserId();
        
        if ($action === 'get_dashboard_stats') {
            // Get teacher dashboard statistics
            $stats = [];
            
            // Get total classes assigned to this teacher (via lecturers)
            $sql = "SELECT COUNT(DISTINCT l.class_id) as count FROM lecturers l WHERE l.teacher_id = ?";
            $result = $db->fetch($sql, [$teacherId]);
            $stats['total_classes'] = $result['count'];
            
            // Get total students in teacher's classes (via lecturers)
            $sql = "SELECT COUNT(DISTINCT s.id) as count
                    FROM students s
                    INNER JOIN lecturers l ON s.class_id = l.class_id
                    WHERE l.teacher_id = ? AND s.status = 'active'";
            $result = $db->fetch($sql, [$teacherId]);
            $stats['total_students'] = $result['count'];
            
            // Get total active assignments (for classes taught by this teacher)
            $sql = "SELECT COUNT(*) as count, type FROM assignments a
                    INNER JOIN lecturers l ON a.class_id = l.class_id
                    WHERE l.teacher_id = ? AND a.status = 'active' AND a.teacher_id = ?
                    GROUP BY type";
            $result = $db->fetchAll($sql, [$teacherId, $teacherId]);
            $stats['total_assignments'] = 0;
            $stats['assignments_by_type'] = [];
            foreach ($result as $row) {
                $stats['total_assignments'] += $row['count'];
                $stats['assignments_by_type'][$row['type']] = $row['count'];
            }
            
            // Get average attendance rate for teacher's classes (via lecturers)
            $sql = "SELECT AVG(attendance_percentage) as avg_rate
                    FROM (
                        SELECT s.id,
                               (COUNT(CASE WHEN a.status = 'present' THEN 1 END) * 100.0 / COUNT(a.id)) as attendance_percentage
                        FROM students s
                        INNER JOIN lecturers l ON s.class_id = l.class_id
                        LEFT JOIN attendance a ON s.id = a.student_id
                        WHERE l.teacher_id = ? AND s.status = 'active'
                        GROUP BY s.id
                    ) as student_attendance";
            $result = $db->fetch($sql, [$teacherId]);
            $stats['attendance_rate'] = round($result['avg_rate'] ?? 0, 1);
            
            echo json_encode([
                'success' => true,
                'stats' => $stats
            ]);
            
        } else if ($action === 'get_assignments') {
            // Fetch all assignments for the current teacher
            $sql = "SELECT a.id, a.title, a.description, a.type, a.start_date, a.due_date, a.total_marks, a.status, a.class_id, a.subject_id, c.name as class_name, s.name as subject_name
                    FROM assignments a
                    LEFT JOIN classes c ON a.class_id = c.id
                    LEFT JOIN subjects s ON a.subject_id = s.id
                    WHERE a.teacher_id = ?
                    ORDER BY a.due_date DESC, a.id DESC";
            $assignments = $db->fetchAll($sql, [$teacherId]);
            echo json_encode([
                'success' => true,
                'assignments' => $assignments
            ]);
        
        } else if ($action === 'create_assignment') {
            // Create a new assignment
            $title = trim($input['title'] ?? '');
            $description = trim($input['description'] ?? '');
            $class_id = $input['class_id'] ?? null;
            $subject_id = $input['subject_id'] ?? null;
            $start_date = $input['start_date'] ?? null;
            $due_date = $input['due_date'] ?? null;
            $total_marks = $input['total_marks'] ?? 100;
            $type = $input['type'] ?? 'essays';
            $status = $input['status'] ?? 'coming';
            $teacher_id = $teacherId;
            if (!$title || !$class_id || !$subject_id || !$start_date || !$due_date || !$type || !$status) {
                echo json_encode(['success' => false, 'message' => 'All fields are required.']);
                return;
            }
            $sql = "INSERT INTO assignments (title, description, subject_id, class_id, start_date, due_date, total_marks, teacher_id, type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $db->query($sql, [$title, $description, $subject_id, $class_id, $start_date, $due_date, $total_marks, $teacher_id, $type, $status]);
            echo json_encode(['success' => true, 'message' => 'Assignment created successfully.']);
        
        } else if ($action === 'update_assignment') {
            // Update an existing assignment
            $id = $input['id'] ?? null;
            $title = trim($input['title'] ?? '');
            $description = trim($input['description'] ?? '');
            $class_id = $input['class_id'] ?? null;
            $subject_id = $input['subject_id'] ?? null;
            $start_date = $input['start_date'] ?? null;
            $due_date = $input['due_date'] ?? null;
            $total_marks = $input['total_marks'] ?? 100;
            $type = $input['type'] ?? 'essays';
            $status = $input['status'] ?? 'coming';
            if (!$id || !$title || !$class_id || !$subject_id || !$start_date || !$due_date || !$type || !$status) {
                echo json_encode(['success' => false, 'message' => 'All fields are required.']);
                return;
            }
            // Only allow update if assignment belongs to this teacher
            $sql = "UPDATE assignments SET title=?, description=?, subject_id=?, class_id=?, start_date=?, due_date=?, total_marks=?, type=?, status=? WHERE id=? AND teacher_id=?";
            $stmt = $db->query($sql, [$title, $description, $subject_id, $class_id, $start_date, $due_date, $total_marks, $type, $status, $id, $teacherId]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Assignment updated successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No data updated.']);
            }
        
        } else if ($action === 'delete_assignment') {
            $id = $input['id'] ?? null;
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'Assignment ID is required.']);
                return;
            }
            // Only allow delete if assignment belongs to this teacher
            $sql = "DELETE FROM assignments WHERE id=? AND teacher_id=?";
            $stmt = $db->query($sql, [$id, $teacherId]);
            if ($stmt->rowCount() > 0) {
                echo json_encode(['success' => true, 'message' => 'Assignment deleted successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Assignment not found or you do not have permission.']);
            }
        
        } else if ($action === 'get_assignment_student_status') {
            $assignment_id = $input['assignment_id'] ?? null;
            $class_id = $input['class_id'] ?? null;
            if (!$assignment_id || !$class_id) {
                echo json_encode(['success' => false, 'message' => 'Assignment ID and Class ID are required.']);
                return;
            }
            // Get all students in the class
            $students = $db->fetchAll("SELECT id FROM students WHERE class_id = ? AND status = 'active'", [$class_id]);
            $studentIds = array_column($students, 'id');
            $total_students = count($studentIds);
            $not_submitted = $submitted = $graded = 0;
            if ($total_students > 0) {
                // Get all student_assignments for this assignment
                $rows = $db->fetchAll("SELECT student_id, status FROM student_assignments WHERE assignment_id = ? AND student_id IN (" . implode(",", array_fill(0, count($studentIds), '?')) . ")", array_merge([$assignment_id], $studentIds));
                $statusMap = [];
                foreach ($rows as $row) {
                    $statusMap[$row['student_id']] = $row['status'];
                }
                foreach ($studentIds as $sid) {
                    if (!isset($statusMap[$sid])) $not_submitted++;
                    else if ($statusMap[$sid] === 'submitted') $submitted++;
                    else if ($statusMap[$sid] === 'graded') $graded++;
                }
            }
            echo json_encode([
                'success' => true,
                'not_submitted' => $not_submitted,
                'submitted' => $submitted,
                'graded' => $graded,
                'total_students' => $total_students
            ]);
        
        } else if ($action === 'get_assignment_submissions') {
            $subject_id = $input['subject_id'] ?? null;
            $class_id = $input['class_id'] ?? null;
            $status = $input['status'] ?? null;
            $type = $input['type'] ?? null;
            $teacher_id = $teacherId;
            // Build query
            $sql = "SELECT sa.*, s.first_name, s.last_name, s.roll_number, a.title as assignment_title, a.description, a.type as assignment_type, a.total_marks, a.subject_id, a.class_id, a.status as assignment_status, sub.name as subject_name, c.name as class_name
                    FROM student_assignments sa
                    JOIN assignments a ON sa.assignment_id = a.id
                    JOIN students s ON sa.student_id = s.id
                    LEFT JOIN subjects sub ON a.subject_id = sub.id
                    LEFT JOIN classes c ON a.class_id = c.id
                    WHERE a.teacher_id = ?";
            $params = [$teacher_id];
            if ($subject_id) {
                $sql .= " AND a.subject_id = ?";
                $params[] = $subject_id;
            }
            if ($class_id) {
                $sql .= " AND a.class_id = ?";
                $params[] = $class_id;
            }
            if ($status) {
                $sql .= " AND sa.status = ?";
                $params[] = $status;
            }
            if ($type) {
                $sql .= " AND a.type = ?";
                $params[] = $type;
            }
            $sql .= " ORDER BY sa.submitted_date DESC, sa.id DESC";
            $rows = $db->fetchAll($sql, $params);
            echo json_encode(['success' => true, 'submissions' => $rows]);
        
        } else if ($action === 'auto_update_assignment_status') {
            // Auto update assignment statuses based on start_date and due_date
            $today = date('Y-m-d');
            // Set running: start_date <= today <= due_date, status != completed
            $sqlRunning = "UPDATE assignments SET status='running' WHERE start_date <= ? AND due_date >= ? AND status != 'completed'";
            $stmtRunning = $db->query($sqlRunning, [$today, $today]);
            $updatedRunning = $stmtRunning->rowCount();
            // Set completed: due_date < today, status != completed
            $sqlCompleted = "UPDATE assignments SET status='completed' WHERE due_date < ? AND status != 'completed'";
            $stmtCompleted = $db->query($sqlCompleted, [$today]);
            $updatedCompleted = $stmtCompleted->rowCount();
            echo json_encode([
                'success' => true,
                'updated_running' => $updatedRunning,
                'updated_completed' => $updatedCompleted
            ]);
        
        } else if ($action === 'assign_marks') {
            // Assign marks to a student assignment
            $student_assignment_id = $input['student_assignment_id'] ?? null;
            $marks = $input['marks'] ?? null;
            if (!$student_assignment_id || $marks === null) {
                echo json_encode(['success' => false, 'message' => 'Student assignment ID and marks are required.']);
                return;
            }
            // Only allow if this teacher owns the assignment
            $row = $db->fetch("SELECT sa.id FROM student_assignments sa JOIN assignments a ON sa.assignment_id = a.id WHERE sa.id = ? AND a.teacher_id = ?", [$student_assignment_id, $teacherId]);
            if (!$row) {
                echo json_encode(['success' => false, 'message' => 'Not allowed.']);
                return;
            }
            $db->query("UPDATE student_assignments SET marks_obtained = ?, status = 'graded' WHERE id = ?", [$marks, $student_assignment_id]);
            echo json_encode(['success' => true, 'message' => 'Marks assigned successfully.']);
        
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?> 