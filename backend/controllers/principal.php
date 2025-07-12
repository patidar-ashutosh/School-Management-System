<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../models/Principal.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    if ($action === 'get_profile') {
        requireRole('principal');
        $principalId = getCurrentUserId();
        $principalModel = new Principal();
        $profile = $principalModel->getById($principalId);
        if ($profile) {
            unset($profile['password']); // Don't expose password hash
            echo json_encode(['success' => true, 'profile' => $profile]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Principal not found']);
        }
        exit;
    }
    
    if ($action === 'update_profile') {
        requireRole('principal');
        $principalId = getCurrentUserId();
        $principalModel = new Principal();
        $fields = [
            'first_name', 'last_name', 'phone', 'address', 'qualification', 'joining_date', 'email'
        ];
        $data = [];
        foreach ($fields as $field) {
            $data[$field] = $input[$field] ?? null;
        }
        $current = $principalModel->getById($principalId);
        if (!$current) {
            echo json_encode(['success' => false, 'message' => 'Principal not found']);
            exit;
        }
        $data['email'] = $current['email'];
        // Always use the current joining_date from DB (not editable in UI)
        $data['joining_date'] = $current['joining_date'];
        $result = $principalModel->update($principalId, $data);
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