<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../models/Exam.php';
require_once __DIR__ . '/../models/Class.php';

date_default_timezone_set('Asia/Kolkata');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$exam = new Exam();
$class = new ClassModel();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['action'])) {
            throw new Exception('Action is required');
        }
        
        switch ($input['action']) {
            case 'get_all':
                $exams = $exam->getAll();
                echo json_encode([
                    'success' => true,
                    'data' => $exams
                ]);
                break;
                
            case 'get_by_id':
                if (!isset($input['id'])) {
                    throw new Exception('Exam ID is required');
                }
                
                $examData = $exam->getById($input['id']);
                if ($examData) {
                    echo json_encode([
                        'success' => true,
                        'data' => $examData
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Exam not found'
                    ]);
                }
                break;
                
            case 'create':
                if (!isset($input['name']) || !isset($input['date']) || !isset($input['class_id'])) {
                    throw new Exception('Exam name, date, and class are required');
                }
                
                if (!in_array($input['exam_type'], ['midterm', 'final'])) {
                    throw new Exception('Invalid exam type. Only midterm and final are allowed.');
                }
                $examId = $exam->create($input);
                echo json_encode([
                    'success' => true,
                    'message' => 'Exam created successfully',
                    'id' => $examId
                ]);
                break;
                
            case 'update':
                if (!isset($input['id']) || !isset($input['name']) || !isset($input['date']) || !isset($input['class_id'])) {
                    throw new Exception('ID, exam name, date, and class are required');
                }
                
                if (!in_array($input['exam_type'], ['midterm', 'final'])) {
                    throw new Exception('Invalid exam type. Only midterm and final are allowed.');
                }
                $exam->update($input['id'], $input);
                echo json_encode([
                    'success' => true,
                    'message' => 'Exam updated successfully'
                ]);
                break;
                
            case 'delete':
                if (!isset($input['id'])) {
                    throw new Exception('Exam ID is required');
                }
                
                $exam->delete($input['id']);
                echo json_encode([
                    'success' => true,
                    'message' => 'Exam deleted successfully'
                ]);
                break;
                
            case 'get_by_class':
                if (!isset($input['class_id'])) {
                    throw new Exception('Class ID is required');
                }
                
                $exams = $exam->getByClass($input['class_id']);
                echo json_encode([
                    'success' => true,
                    'data' => $exams
                ]);
                break;
                
            case 'get_upcoming':
                $exams = $exam->getUpcomingExams();
                echo json_encode([
                    'success' => true,
                    'data' => $exams
                ]);
                break;
                
            case 'get_count':
                $count = $exam->getCount();
                echo json_encode([
                    'success' => true,
                    'count' => $count
                ]);
                break;
                
            case 'test_timezone':
                $now = new DateTime('now');
                echo json_encode([
                    'success' => true,
                    'current_time' => $now->format('Y-m-d H:i:s'),
                    'timezone' => date_default_timezone_get(),
                    'timestamp' => time()
                ]);
                break;
                
            case 'auto_update_status':
                // Get all exams with status 'ONGOING' or 'SCHEDULED'
                $allExams = $exam->getAll();
                $now = new DateTime('now');
                $updated = 0;
                
                // Debug: Log current time
                error_log("Auto update - Current time: " . $now->format('Y-m-d H:i:s'));
                
                foreach ($allExams as $ex) {
                    if (!in_array(strtolower($ex['status']), ['ongoing', 'scheduled'])) {
                        continue;
                    }
                    
                    $examDate = $ex['date']; // Y-m-d
                    $startTime = $ex['start_time']; // H:i:s
                    $endTime = $ex['end_time']; // H:i:s
                    $status = $ex['status'];
                    $originalStatus = $status;

                    // Debug: Log exam details
                    error_log("Exam ID: {$ex['id']}, Date: $examDate, Start: $startTime, End: $endTime, Current Status: $status");

                    // Case 3: Current Date > Exam Date
                    if ($now->format('Y-m-d') > $examDate) {
                        $status = 'COMPLETED';
                        error_log("Case 3: Date passed, setting to COMPLETED");
                    }
                    // Case 1 & 2: Current Date == Exam Date
                    else if ($now->format('Y-m-d') == $examDate) {
                        $currentTime = $now->format('H:i:s');
                        if ($startTime && $endTime) {
                            if ($currentTime > $endTime) {
                                $status = 'COMPLETED';
                                error_log("Case 2: Time passed, setting to COMPLETED");
                            } else if ($currentTime >= $startTime && $currentTime < $endTime) {
                                $status = 'ONGOING';
                                error_log("Case 1: Within time range, setting to ONGOING");
                            }
                        }
                    }
                    
                    if ($status !== $originalStatus) {
                        error_log("Updating exam {$ex['id']} from $originalStatus to $status");
                        $exam->update($ex['id'], array_merge($ex, ['status' => $status]));
                        $updated++;
                    }
                }
                
                error_log("Auto update completed. Updated: $updated exams");
                echo json_encode([
                    'success' => true,
                    'message' => "Exam statuses auto-updated.",
                    'updated' => $updated
                ]);
                break;
                
            case 'get_by_teacher':
                if (!isset($input['teacher_id'])) {
                    throw new Exception('Teacher ID is required');
                }
                $exams = $exam->getByTeacher($input['teacher_id']);
                echo json_encode([
                    'success' => true,
                    'data' => $exams
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