<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../models/Lecturer.php';
require_once __DIR__ . '/../models/Subject.php';

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
                if (!isset($input['teacher_id']) || !isset($input['subject_id']) || !isset($input['class_id']) || !isset($input['start_time']) || !isset($input['end_time']) || !isset($input['status']) || !isset($input['day_of_week'])) {
                    throw new Exception('All fields are required');
                }
                // Prevent duplicate: same subject, class, day
                if ($lecturer->existsForSubjectClassDay($input['subject_id'], $input['class_id'], $input['day_of_week'])) {
                    throw new Exception('A lecture for this subject, class, and day already exists.');
                }
                $lectureId = $lecturer->add($input);
                echo json_encode([
                    'success' => true,
                    'message' => 'Lecture added successfully',
                    'id' => $lectureId
                ]);
                break;
            case 'edit':
                if (!isset($input['id']) || !isset($input['teacher_id']) || !isset($input['subject_id']) || !isset($input['class_id']) || !isset($input['start_time']) || !isset($input['end_time']) || !isset($input['status']) || !isset($input['day_of_week'])) {
                    throw new Exception('All fields are required');
                }
                // Prevent duplicate on edit (exclude current id)
                if ($lecturer->existsForSubjectClassDay($input['subject_id'], $input['class_id'], $input['day_of_week'], $input['id'])) {
                    throw new Exception('A lecture for this subject, class, and day already exists.');
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
                $updatedCount = $lecturer->autoUpdateStatus();
                echo json_encode([
                    'success' => true,
                    'message' => "Updated $updatedCount lecture statuses",
                    'updated_count' => $updatedCount
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