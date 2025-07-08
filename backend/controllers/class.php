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
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $search = $_GET['search'] ?? '';
    
    $offset = ($page - 1) * $limit;
    
    try {
        $db = db();
        
        // Build search condition
        $searchCondition = '';
        $searchParams = [];
        
        if (!empty($search)) {
            $searchCondition = "WHERE c.name LIKE ? OR c.section LIKE ? OR c.room_number LIKE ? OR t.first_name LIKE ? OR t.last_name LIKE ?";
            $searchTerm = "%$search%";
            $searchParams = [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm];
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM classes c LEFT JOIN teachers t ON c.teacher_id = t.id $searchCondition";
        $countStmt = $db->query($countSql, $searchParams);
        $total = $countStmt->fetch()['total'];
        
        // Get classes with pagination and teacher info
        $sql = "SELECT c.*, CONCAT(t.first_name, ' ', t.last_name) as teacher_name 
                FROM classes c 
                LEFT JOIN teachers t ON c.teacher_id = t.id 
                $searchCondition 
                ORDER BY c.created_at DESC 
                LIMIT ? OFFSET ?";
        $params = array_merge($searchParams, [$limit, $offset]);
        $classes = $db->fetchAll($sql, $params);
        
        // Add status property to each class (since DB does not have it)
        foreach ($classes as &$cls) {
            $cls['status'] = 'active';
        }
        unset($cls);
        
        $totalPages = ceil($total / $limit);
        
        echo json_encode([
            'success' => true,
            'classes' => $classes,
            'total' => $total,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'items_per_page' => $limit
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
        
        // Get class with teacher info
        $sql = "SELECT c.*, CONCAT(t.first_name, ' ', t.last_name) as teacher_name 
                FROM classes c 
                LEFT JOIN teachers t ON c.teacher_id = t.id 
                WHERE c.id = ?";
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
    
    // Validate teacher_id if provided
    if (!empty($input['teacher_id'])) {
        $db = db();
        $teacherCheck = $db->fetch("SELECT id FROM teachers WHERE id = ? AND status = 'active'", [$input['teacher_id']]);
        if (!$teacherCheck) {
            echo json_encode(['success' => false, 'message' => 'Selected teacher not found or inactive']);
            return;
        }
    }
    
    try {
        // Prepare class data
        $classData = [
            'name' => trim($input['name']),
            'teacher_id' => $input['teacher_id'] ?? null,
            'room_number' => $input['room_number'] ?? null,
            'capacity' => $input['capacity'] ?? 30
        ];
        // Debug log
        error_log('AddClass input: ' . json_encode($classData));
        // Create class
        $classId = $classModel->create($classData);
        error_log('AddClass result classId: ' . print_r($classId, true));
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
    } catch (Exception $e) {
        error_log('AddClass Exception: ' . $e->getMessage());
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
    
    // Validate teacher_id if provided
    if (!empty($input['teacher_id'])) {
        $db = db();
        $teacherCheck = $db->fetch("SELECT id FROM teachers WHERE id = ? AND status = 'active'", [$input['teacher_id']]);
        if (!$teacherCheck) {
            echo json_encode(['success' => false, 'message' => 'Selected teacher not found or inactive']);
            return;
        }
    }
    
    try {
        // Prepare class data
        $classData = [
            'name' => trim($input['name']),
            'teacher_id' => $input['teacher_id'] ?? null,
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