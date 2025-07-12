<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Class.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    
    try {
        $classModel = new ClassModel();
        
        switch ($action) {
            case 'get_all':
                handleGetAllClasses($classModel);
                break;
            case 'get_by_id':
                handleGetClassById($classModel);
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['action'])) {
        echo json_encode(['success' => false, 'message' => 'Action not specified']);
        exit;
    }
    
    try {
        $classModel = new ClassModel();
        
        switch ($input['action']) {
            case 'create':
                handleCreateClass($classModel, $input);
                break;
            case 'update':
                handleUpdateClass($classModel, $input);
                break;
            case 'delete':
                handleDeleteClass($classModel, $input);
                break;
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}

function handleGetAllClasses($classModel) {
    try {
        $classes = $classModel->getAll();
        // Add status property to each class (for frontend compatibility)
        foreach ($classes as &$cls) {
            $cls['status'] = 'active';
        }
        unset($cls);
        echo json_encode([
            'success' => true,
            'classes' => $classes
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to load classes: ' . $e->getMessage()]);
    }
}

function handleGetClassById($classModel) {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Class ID is required']);
        return;
    }
    
    try {
        $db = db();
        
        // Get class
        $sql = "SELECT c.* FROM classes c WHERE c.id = ?";
        $class = $db->fetch($sql, [$id]);
        
        if ($class) {
            echo json_encode([
                'success' => true,
                'class' => $class
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Class not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to load class: ' . $e->getMessage()]);
    }
}

function handleCreateClass($classModel, $input) {
    // Validate required fields
    $requiredFields = ['name'];
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
            return;
        }
    }
    try {
        $classData = [
            'name' => strtolower(trim($input['name'])),
            'room_number' => $input['room_number'] ?? null,
            'capacity' => $input['capacity'] ?? 30
        ];
        $classId = $classModel->create($classData);
        if (!$classId) {
            echo json_encode([
                'success' => false,
                'message' => 'Failed to add class (DB error)'
            ]);
            return;
        }
        echo json_encode([
            'success' => true,
            'message' => 'Class added successfully',
            'class_id' => $classId
        ]);
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            echo json_encode(['success' => false, 'message' => 'Class with this name already exists.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to create class: ' . $e->getMessage()]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to create class: ' . $e->getMessage()]);
    }
}

function handleUpdateClass($classModel, $input) {
    $id = $input['id'] ?? null;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Class ID is required']);
        return;
    }
    
    // Validate required fields
    $requiredFields = ['name'];
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
            return;
        }
    }
    
    try {
        // Prepare class data
        $classData = [
            'name' => strtolower(trim($input['name'])),
            'room_number' => $input['room_number'] ?? null,
            'capacity' => $input['capacity'] ?? 30
        ];
        
        // Update class
        $classModel->update($id, $classData);
        
        echo json_encode([
            'success' => true,
            'message' => 'Class updated successfully'
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to update class: ' . $e->getMessage()]);
    }
}

function handleDeleteClass($classModel, $input) {
    $id = $input['id'] ?? null;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Class ID is required']);
        return;
    }
    
    try {
        // Check if class exists
        $class = $classModel->getById($id);
        if (!$class) {
            echo json_encode(['success' => false, 'message' => 'Class not found']);
            return;
        }
        
        // Check if class has students
        $db = db();
        $studentCheck = $db->fetch("SELECT COUNT(*) as count FROM students WHERE class_id = ?", [$id]);
        if ($studentCheck['count'] > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete class. It has students enrolled.']);
            return;
        }
        
        // Check if class has subjects (should check assignments and exams, not subjects)
        // This check includes all assignment types (quiz and project). To restrict, add AND type = 'project' or 'quiz'.
        $assignmentCheck = $db->fetch("SELECT COUNT(*) as count FROM assignments WHERE class_id = ?", [$id]);
        if ($assignmentCheck['count'] > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete class. It has assignments assigned (quiz or project).']);
            return;
        }
        $examCheck = $db->fetch("SELECT COUNT(*) as count FROM exams WHERE class_id = ?", [$id]);
        if ($examCheck['count'] > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete class. It has exams assigned.']);
            return;
        }
        
        // Delete class
        $classModel->delete($id);
        
        echo json_encode([
            'success' => true,
            'message' => 'Class deleted successfully'
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to delete class: ' . $e->getMessage()]);
    }
}
?> 