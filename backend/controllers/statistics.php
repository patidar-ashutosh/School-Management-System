<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../models/Principal.php';
require_once __DIR__ . '/../models/Teacher.php';
require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Class.php';
require_once __DIR__ . '/../models/Subject.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$principal = new Principal();
$teacher = new Teacher();
$student = new Student();
$class = new ClassModel();
$subject = new Subject();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['action'])) {
            throw new Exception('Action is required');
        }
        
        switch ($input['action']) {
            case 'get_dashboard_stats':
                $stats = $principal->getSchoolStatistics();
                echo json_encode([
                    'success' => true,
                    'stats' => $stats
                ]);
                break;
                
            case 'get_recent_activities':
                // Get recent activities from various tables
                $activities = [];
                
                // Recent students
                $recentStudents = $student->getAll();
                foreach (array_slice($recentStudents, 0, 5) as $student) {
                    $activities[] = [
                        'type' => 'student',
                        'action' => 'New student registered',
                        'name' => $student['first_name'] . ' ' . $student['last_name'],
                        'date' => $student['created_at'],
                        'status' => 'completed'
                    ];
                }
                
                // Recent teachers
                $recentTeachers = $teacher->getAll();
                foreach (array_slice($recentTeachers, 0, 5) as $teacher) {
                    $activities[] = [
                        'type' => 'teacher',
                        'action' => 'New teacher added',
                        'name' => $teacher['first_name'] . ' ' . $teacher['last_name'],
                        'date' => $teacher['created_at'],
                        'status' => 'completed'
                    ];
                }
                
                // Sort by date
                usort($activities, function($a, $b) {
                    return strtotime($b['date']) - strtotime($a['date']);
                });
                
                echo json_encode([
                    'success' => true,
                    'activities' => array_slice($activities, 0, 10)
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