<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../models/Lecturer.php';
require_once __DIR__ . '/../models/Subject.php';

date_default_timezone_set('Asia/Kolkata');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$lecturer = new Lecturer();
$subject = new Subject();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['action'])) {
            throw new Exception('Action is required');
        }
        switch ($input['action']) {
            case 'get_by_teacher':
                if (!isset($input['teacher_id'])) {
                    throw new Exception('Teacher ID is required');
                }
                $lectures = $lecturer->getByTeacher($input['teacher_id']);
                echo json_encode([
                    'success' => true,
                    'data' => $lectures
                ]);
                break;
            case 'add':
                if (!isset($input['teacher_id']) || !isset($input['subject_id']) || !isset($input['class_id']) || !isset($input['start_time']) || !isset($input['end_time']) || !isset($input['status']) || !isset($input['date'])) {
                    throw new Exception('All fields are required');
                }
                // Validate date is in next week
                $lectureDate = $input['date'];
                $today = new DateTime();
                $today->setTime(0,0,0);
                $dayOfWeek = (int)$today->format('w'); // 0=Sunday, 1=Monday, ...
                $daysUntilNextMonday = (($dayOfWeek === 0) ? 1 : 8 - $dayOfWeek);
                $nextMonday = clone $today;
                $nextMonday->modify("+{$daysUntilNextMonday} days");
                $nextSunday = clone $nextMonday;
                $nextSunday->modify("+6 days");
                if ($lectureDate < $nextMonday->format('Y-m-d') || $lectureDate > $nextSunday->format('Y-m-d')) {
                    throw new Exception('Lecture date must be in next week (Monday to Sunday) only.');
                }
                // Prevent duplicate: same subject, class, date
                if ($lecturer->existsForSubjectClassDay($input['subject_id'], $input['class_id'], $input['date'])) {
                    throw new Exception('A lecture for this subject, class, and date already exists.');
                }
                $lectureId = $lecturer->add($input);
                echo json_encode([
                    'success' => true,
                    'message' => 'Lecture added successfully',
                    'id' => $lectureId
                ]);
                break;
            case 'edit':
                if (!isset($input['id']) || !isset($input['teacher_id']) || !isset($input['subject_id']) || !isset($input['class_id']) || !isset($input['start_time']) || !isset($input['end_time']) || !isset($input['status']) || !isset($input['date'])) {
                    throw new Exception('All fields are required');
                }
                // Validate date is in next week
                $lectureDate = $input['date'];
                $today = new DateTime();
                $today->setTime(0,0,0);
                $dayOfWeek = (int)$today->format('w');
                $daysUntilNextMonday = (($dayOfWeek === 0) ? 1 : 8 - $dayOfWeek);
                $nextMonday = clone $today;
                $nextMonday->modify("+{$daysUntilNextMonday} days");
                $nextSunday = clone $nextMonday;
                $nextSunday->modify("+6 days");
                if ($lectureDate < $nextMonday->format('Y-m-d') || $lectureDate > $nextSunday->format('Y-m-d')) {
                    throw new Exception('Lecture date must be in next week (Monday to Sunday) only.');
                }
                // Prevent duplicate on edit (exclude current id)
                if ($lecturer->existsForSubjectClassDay($input['subject_id'], $input['class_id'], $input['date'], $input['id'])) {
                    throw new Exception('A lecture for this subject, class, and date already exists.');
                }
                $lecturer->edit($input['id'], $input);
                echo json_encode([
                    'success' => true,
                    'message' => 'Lecture updated successfully'
                ]);
                break;
            case 'delete':
                if (!isset($input['id'])) {
                    throw new Exception('Lecture ID is required');
                }
                $lecturer->delete($input['id']);
                echo json_encode([
                    'success' => true,
                    'message' => 'Lecture deleted successfully'
                ]);
                break;
            case 'get':
                if (!isset($input['id'])) {
                    throw new Exception('Lecture ID is required');
                }
                $lecture = $lecturer->getById($input['id']);
                if ($lecture) {
                    echo json_encode([
                        'success' => true,
                        'data' => $lecture
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Lecture not found'
                    ]);
                }
                break;
            case 'auto_update_status':
                // Get all lectures with status not 'completed'
                $allLectures = $lecturer->getAll();
                $now = new DateTime('now');
                $updated = 0;
                
                // Debug: Log current time
                error_log("Auto update lectures - Current time: " . $now->format('Y-m-d H:i:s'));
                
                foreach ($allLectures as $lec) {
                    if (strtolower($lec['status']) === 'completed') {
                        continue;
                    }
                    
                    $lectureDate = $lec['date']; // Y-m-d
                    $startTime = $lec['start_time']; // H:i:s
                    $endTime = $lec['end_time']; // H:i:s
                    $status = $lec['status'];
                    $originalStatus = $status;

                    // Debug: Log lecture details
                    error_log("Lecture ID: {$lec['id']}, Date: $lectureDate, Start: $startTime, End: $endTime, Current Status: $status");

                    // Case 3: Current Date > Lecture Date
                    if ($now->format('Y-m-d') > $lectureDate) {
                        $status = 'completed';
                        error_log("Case 3: Date passed, setting to completed");
                    }
                    // Case 1 & 2: Current Date == Lecture Date
                    else if ($now->format('Y-m-d') == $lectureDate) {
                        $currentTime = $now->format('H:i:s');
                        if ($startTime && $endTime) {
                            if ($currentTime > $endTime) {
                                $status = 'completed';
                                error_log("Case 2: Time passed, setting to completed");
                            } else if ($currentTime >= $startTime && $currentTime < $endTime) {
                                $status = 'running';
                                error_log("Case 1: Within time range, setting to running");
                            }
                        }
                    }
                    
                    if ($status !== $originalStatus) {
                        error_log("Updating lecture {$lec['id']} from $originalStatus to $status");
                        $lecturer->edit($lec['id'], array_merge($lec, ['status' => $status]));
                        $updated++;
                    }
                }
                
                error_log("Auto update completed. Updated: $updated lectures");
                echo json_encode([
                    'success' => true,
                    'message' => "Lecture statuses auto-updated.",
                    'updated' => $updated
                ]);
                break;
            case 'test_auto_update':
                // Get current date and time for debugging
                $currentDate = date('Y-m-d');
                $currentTime = date('H:i:s');
                
                // Get all non-completed lectures using the model method
                $allLectures = $lecturer->getAll();
                $lectures = array_filter($allLectures, function($lec) {
                    return $lec['status'] !== 'completed';
                });
                
                $debugInfo = [
                    'current_date' => $currentDate,
                    'current_time' => $currentTime,
                    'total_lectures' => count($lectures),
                    'lectures' => []
                ];
                
                foreach ($lectures as $lecture) {
                    $lectureInfo = [
                        'id' => $lecture['id'],
                        'date' => $lecture['date'],
                        'start_time' => $lecture['start_time'],
                        'end_time' => $lecture['end_time'],
                        'status' => $lecture['status'],
                        'is_today' => ($lecture['date'] === $currentDate),
                        'time_between' => ($currentTime >= $lecture['start_time'] && $currentTime <= $lecture['end_time']),
                        'time_after' => ($currentTime > $lecture['end_time']),
                        'date_before' => ($lecture['date'] < $currentDate)
                    ];
                    $debugInfo['lectures'][] = $lectureInfo;
                }
                
                echo json_encode([
                    'success' => true,
                    'debug_info' => $debugInfo
                ]);
                break;
            case 'get_completed_by_week':
                if (!isset($input['teacher_id'])) {
                    throw new Exception('Teacher ID is required');
                }
                if (!isset($input['week_filter'])) {
                    throw new Exception('Week filter is required');
                }
                
                $teacherId = $input['teacher_id'];
                $weekFilter = $input['week_filter'];
                
                // Calculate date range based on week filter
                $today = new DateTime('now');
                $today->setTime(0, 0, 0);
                
                switch ($weekFilter) {
                    case 'last_week':
                        $startDate = clone $today;
                        $startDate->modify('last monday -1 week');
                        $endDate = clone $startDate;
                        $endDate->modify('+6 days');
                        break;
                    case 'current_week':
                        $startDate = clone $today;
                        $startDate->modify('last monday');
                        $endDate = clone $today;
                        break;
                    case 'two_weeks_ago':
                        $startDate = clone $today;
                        $startDate->modify('last monday -2 weeks');
                        $endDate = clone $startDate;
                        $endDate->modify('+6 days');
                        break;
                    case 'three_weeks_ago':
                        $startDate = clone $today;
                        $startDate->modify('last monday -3 weeks');
                        $endDate = clone $startDate;
                        $endDate->modify('+6 days');
                        break;
                    case 'all':
                        $startDate = null;
                        $endDate = null;
                        break;
                    default:
                        throw new Exception('Invalid week filter');
                }
                
                // Build SQL query
                $sql = "SELECT l.*, s.name as subject_name, c.name as class_name 
                        FROM lecturers l 
                        LEFT JOIN subjects s ON l.subject_id = s.id 
                        LEFT JOIN classes c ON l.class_id = c.id 
                        WHERE l.teacher_id = ? AND l.status = 'completed'";
                $params = [$teacherId];
                
                if ($startDate && $endDate) {
                    $sql .= " AND l.date BETWEEN ? AND ?";
                    $params[] = $startDate->format('Y-m-d');
                    $params[] = $endDate->format('Y-m-d');
                }
                
                $sql .= " ORDER BY l.date DESC, l.start_time";
                
                $lectures = $lecturer->db->fetchAll($sql, $params);
                
                echo json_encode([
                    'success' => true,
                    'data' => $lectures,
                    'week_filter' => $weekFilter,
                    'date_range' => $startDate && $endDate ? [
                        'start' => $startDate->format('Y-m-d'),
                        'end' => $endDate->format('Y-m-d')
                    ] : null
                ]);
                break;
            case 'get_completed_by_date_range':
                if (!isset($input['teacher_id'])) {
                    throw new Exception('Teacher ID is required');
                }
                if (!isset($input['start_date']) || !isset($input['end_date'])) {
                    throw new Exception('Start date and end date are required');
                }
                
                $teacherId = $input['teacher_id'];
                $startDate = $input['start_date'];
                $endDate = $input['end_date'];
                
                // Use the model method
                $lectures = $lecturer->getCompletedByDateRange($teacherId, $startDate, $endDate);
                
                echo json_encode([
                    'success' => true,
                    'data' => $lectures,
                    'date_range' => [
                        'start' => $startDate,
                        'end' => $endDate
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