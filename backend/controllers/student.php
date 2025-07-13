<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../models/Student.php';
require_once __DIR__ . '/../models/Class.php';
require_once __DIR__ . '/../config/session.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$student = new Student();
$classModel = new ClassModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';

    if ($action === 'get_profile') {
        requireRole('student');
        $studentId = getCurrentUserId();
        $profile = $student->getById($studentId);
        if ($profile) {
            // Get class name
            $profile['class_name'] = '-';
            if (!empty($profile['class_id'])) {
                $class = $classModel->getById($profile['class_id']);
                $profile['class_name'] = $class['name'] ?? '-';
            }
            unset($profile['password']);
            echo json_encode(['success' => true, 'profile' => $profile]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Student not found']);
        }
        exit;
    }
    if ($action === 'update_profile') {
        requireRole('student');
        $studentId = getCurrentUserId();
        
        // Get current student data to preserve existing values
        $current = $student->getById($studentId);
        if (!$current) {
            echo json_encode(['success' => false, 'message' => 'Student not found']);
            exit;
        }
        
        $fields = [
            'first_name', 'last_name', 'phone', 'address', 'gender', 'date_of_birth', 
            'parent_name', 'parent_phone', 'parent_email', 'blood_group', 'email'
        ];
        
        $data = [];
        foreach ($fields as $field) {
            $data[$field] = $input[$field] ?? null;
        }
        
        // Preserve existing class_id and admission_date if not provided
        $data['class_id'] = $current['class_id'];
        $data['admission_date'] = $current['admission_date'];
        
        // Email should not be updated, but is required by the update method signature
        $data['email'] = $current['email'];
        
        $result = $student->update($studentId, $data);
        if ($result !== false) {
            echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update profile']);
        }
        exit;
    }
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}
echo json_encode(['success' => false, 'message' => 'Method not allowed']); 