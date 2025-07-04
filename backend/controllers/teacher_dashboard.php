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
            
            // Get total classes assigned to this teacher
            $sql = "SELECT COUNT(*) as count FROM classes WHERE teacher_id = ? AND status = 'active'";
            $result = $db->fetch($sql, [$teacherId]);
            $stats['total_classes'] = $result['count'];
            
            // Get total students in teacher's classes
            $sql = "SELECT COUNT(DISTINCT s.id) as count 
                    FROM students s 
                    INNER JOIN classes c ON s.class_id = c.id 
                    WHERE c.teacher_id = ? AND s.status = 'active'";
            $result = $db->fetch($sql, [$teacherId]);
            $stats['total_students'] = $result['count'];
            
            // Get total active assignments
            $sql = "SELECT COUNT(*) as count FROM assignments WHERE teacher_id = ? AND status = 'active'";
            $result = $db->fetch($sql, [$teacherId]);
            $stats['total_assignments'] = $result['count'];
            
            // Get average attendance rate for teacher's classes
            $sql = "SELECT AVG(attendance_percentage) as avg_rate 
                    FROM (
                        SELECT s.id, 
                               (COUNT(CASE WHEN a.status = 'present' THEN 1 END) * 100.0 / COUNT(a.id)) as attendance_percentage
                        FROM students s 
                        INNER JOIN classes c ON s.class_id = c.id 
                        LEFT JOIN attendance a ON s.id = a.student_id 
                        WHERE c.teacher_id = ? AND s.status = 'active'
                        GROUP BY s.id
                    ) as student_attendance";
            $result = $db->fetch($sql, [$teacherId]);
            $stats['attendance_rate'] = round($result['avg_rate'] ?? 0, 1);
            
            echo json_encode([
                'success' => true,
                'stats' => $stats
            ]);
            
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