<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Subject.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$student = new Student();
$subject = new Subject();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['action'])) {
            throw new Exception('Action is required');
        }
        
        switch ($input['action']) {
            case 'get_dashboard_data':
                if (!isset($input['student_id'])) {
                    throw new Exception('Student ID is required');
                }
                
                $studentId = $input['student_id'];
                
                // Get student data
                $studentData = $student->getById($studentId);
                if (!$studentData) {
                    throw new Exception('Student not found');
                }
                
                // Get subjects for student's class
                $subjects = [];
                if ($studentData['class_id']) {
                    $subjects = $subject->getByClass($studentData['class_id']);
                }
                
                // Count pending assignments by type
                $pendingAssignments = 0;
                $pendingAssignmentsByType = ['quiz' => 0, 'project' => 0];
                if ($studentData['class_id']) {
                    $result = $student->getPendingAssignmentsStats($studentData['class_id']);
                    foreach ($result as $row) {
                        $pendingAssignments += $row['count'];
                        $pendingAssignmentsByType[$row['type']] = $row['count'];
                    }
                }
                $stats = [
                    'total_subjects' => count($subjects),
                    'attendance_percentage' => 0, // Placeholder - implement based on attendance table
                    'pending_assignments' => $pendingAssignments,
                    'pending_assignments_by_type' => $pendingAssignmentsByType
                ];
                
                echo json_encode([
                    'success' => true,
                    'student' => $studentData,
                    'stats' => $stats
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