<?php
require_once '../config/db.php';

$data = json_decode(file_get_contents("php://input"), true);

if ($data['action'] === 'get_classes_by_teacher' && isset($data['teacher_id'])) {
    $teacher_id = intval($data['teacher_id']);
    $db = db()->getConnection();
    $stmt = $db->prepare("SELECT c.id, c.name FROM teacher_classes tc JOIN classes c ON tc.class_id = c.id WHERE tc.teacher_id = ?");
    $stmt->execute([$teacher_id]);
    $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $classes]);
    exit;
}

// Optionally, handle other actions here

echo json_encode(['success' => false, 'message' => 'Invalid action or missing teacher_id']);
exit;
?> 