<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../models/Attendance.php';
require_once __DIR__ . '/../models/Class.php';
require_once __DIR__ . '/../models/Teacher.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$attendance = new Attendance();
$class = new ClassModel();
$teacher = new Teacher();

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['action'])) {
            throw new Exception('Action is required');
        }
        
        switch ($input['action']) {
            case 'get_teacher_classes':
                if (!isset($input['teacher_id'])) {
                    throw new Exception('Teacher ID is required');
                }
                
                $classes = $class->getByTeacher($input['teacher_id']);
                echo json_encode([
                    'success' => true,
                    'data' => $classes
                ]);
                break;
                
            case 'get_students_by_class':
                if (!isset($input['class_id'])) {
                    throw new Exception('Class ID is required');
                }
                
                $students = $attendance->getStudentsByClass($input['class_id']);
                echo json_encode([
                    'success' => true,
                    'data' => $students
                ]);
                break;
                
            case 'save_attendance':
                if (!isset($input['class_id']) || !isset($input['date']) || !isset($input['teacher_id']) || !isset($input['attendance_data'])) {
                    throw new Exception('Class ID, date, teacher ID, and attendance data are required');
                }
                
                // Check if attendance already exists for this class and date
                if ($attendance->checkExistingAttendance($input['class_id'], $input['date'])) {
                    // Delete existing attendance
                    $attendance->deleteByClassAndDate($input['class_id'], $input['date']);
                }
                
                // Prepare attendance data
                $attendanceData = [];
                foreach ($input['attendance_data'] as $studentAttendance) {
                    $attendanceData[] = [
                        'student_id' => $studentAttendance['student_id'],
                        'class_id' => $input['class_id'],
                        'date' => $input['date'],
                        'status' => $studentAttendance['status'],
                        'marked_by' => $input['teacher_id']
                    ];
                }
                
                // Save attendance
                $attendance->createMultiple($attendanceData);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Attendance saved successfully'
                ]);
                break;
                
            case 'mark_all_present':
                if (!isset($input['class_id']) || !isset($input['date']) || !isset($input['teacher_id'])) {
                    throw new Exception('Class ID, date, and teacher ID are required');
                }
                
                // Get all students in the class
                $students = $attendance->getStudentsByClass($input['class_id']);
                
                // Check if attendance already exists for this class and date
                if ($attendance->checkExistingAttendance($input['class_id'], $input['date'])) {
                    // Delete existing attendance
                    $attendance->deleteByClassAndDate($input['class_id'], $input['date']);
                }
                
                // Prepare attendance data for all present
                $attendanceData = [];
                foreach ($students as $student) {
                    $attendanceData[] = [
                        'student_id' => $student['id'],
                        'class_id' => $input['class_id'],
                        'date' => $input['date'],
                        'status' => 'present',
                        'marked_by' => $input['teacher_id']
                    ];
                }
                
                // Save attendance
                $attendance->createMultiple($attendanceData);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'All students marked as present'
                ]);
                break;
                
            case 'get_attendance_stats':
                if (!isset($input['teacher_id'])) {
                    throw new Exception('Teacher ID is required');
                }
                
                $stats = $attendance->getAttendanceStats($input['teacher_id']);
                echo json_encode([
                    'success' => true,
                    'data' => $stats
                ]);
                break;
                
            case 'get_student_attendance_percent':
                if (!isset($input['student_id'])) {
                    throw new Exception('Student ID is required');
                }
                $studentId = $input['student_id'];
                $total = $attendance->getTotalAttendanceCount($studentId);
                $present = $attendance->getPresentAttendanceCount($studentId);
                $percent = ($total > 0) ? round(($present / $total) * 100) : 0;
                echo json_encode([
                    'success' => true,
                    'percent' => $percent
                ]);
                break;
                
            case 'get_by_class_and_date':
                if (!isset($input['class_id']) || !isset($input['date'])) {
                    throw new Exception('Class ID and date are required');
                }
                $records = $attendance->getByClassAndDate($input['class_id'], $input['date']);
                echo json_encode([
                    'success' => true,
                    'data' => $records
                ]);
                break;
                
            case 'get_stats_by_class_and_date':
                if (!isset($input['class_id']) || !isset($input['date'])) {
                    throw new Exception('Class ID and date are required');
                }
                // Get attendance records for the class and date
                $records = $attendance->getByClassAndDate($input['class_id'], $input['date']);
                $present = 0;
                $absent = 0;
                foreach ($records as $rec) {
                    if ($rec['status'] === 'present') $present++;
                    if ($rec['status'] === 'absent') $absent++;
                }
                // Get total students in class
                $students = $attendance->getStudentsByClass($input['class_id']);
                $total = count($students);
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'present' => $present,
                        'absent' => $absent,
                        'total' => $total
                    ]
                ]);
                break;
                
            case 'get_monthly_attendance_by_student':
                $student_id = $input['student_id'] ?? null;
                $months = isset($input['months']) ? (int)$input['months'] : 4;
                if (!$student_id) {
                    echo json_encode(['success' => false, 'message' => 'Student ID is required']);
                    return;
                }
                $db = db();
                $sql = "SELECT YEAR(date) as year, MONTH(date) as month, COUNT(*) as total_days, SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days
                        FROM attendance
                        WHERE student_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
                        GROUP BY YEAR(date), MONTH(date)
                        ORDER BY year DESC, month DESC
                        LIMIT ?";
                $rows = $db->fetchAll($sql, [$student_id, $months, $months]);
                $result = [];
                foreach ($rows as $row) {
                    $percent = $row['total_days'] > 0 ? round(($row['present_days'] / $row['total_days']) * 100) : 0;
                    $result[] = [
                        'year' => $row['year'],
                        'month' => $row['month'],
                        'present_days' => (int)$row['present_days'],
                        'total_days' => (int)$row['total_days'],
                        'percent' => $percent
                    ];
                }
                echo json_encode(['success' => true, 'data' => $result]);
                return;
                
            case 'get_recent_attendance_by_student':
                $student_id = $input['student_id'] ?? null;
                $days = isset($input['days']) ? (int)$input['days'] : 6;
                if (!$student_id) {
                    echo json_encode(['success' => false, 'message' => 'Student ID is required']);
                    return;
                }
                $db = db();
                $sql = "SELECT date, status FROM attendance WHERE student_id = ? AND (status = 'present' OR status = 'absent') ORDER BY date DESC LIMIT ?";
                $rows = $db->fetchAll($sql, [$student_id, $days]);
                echo json_encode(['success' => true, 'data' => $rows]);
                return;
                
            case 'get_student_attendance_stats':
                if (!isset($input['student_id'])) {
                    throw new Exception('Student ID is required');
                }
                $studentId = $input['student_id'];
                $total = $attendance->getTotalAttendanceCount($studentId);
                $present = $attendance->getPresentAttendanceCount($studentId);
                $absent = $total - $present;
                $percent = ($total > 0) ? round(($present / $total) * 100, 1) : 0;
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'total' => $total,
                        'present' => $present,
                        'absent' => $absent,
                        'percent' => $percent
                    ]
                ]);
                return;
                
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