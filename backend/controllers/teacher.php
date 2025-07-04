<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Teacher.php';

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? '';
    
    try {
        $teacherModel = new Teacher();
        
        switch ($action) {
            case 'get_all':
                handleGetAllTeachers($teacherModel);
                break;
            case 'get_by_id':
                handleGetTeacherById($teacherModel);
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
        $teacherModel = new Teacher();
        
        switch ($input['action']) {
            case 'create':
                handleCreateTeacher($teacherModel, $input);
                break;
            case 'update':
                handleUpdateTeacher($teacherModel, $input);
                break;
            case 'delete':
                handleDeleteTeacher($teacherModel, $input);
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

function handleGetAllTeachers($teacherModel) {
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
            $searchCondition = "WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ?";
            $searchTerm = "%$search%";
            $searchParams = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
        }
        
        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM teachers $searchCondition";
        $countStmt = $db->query($countSql, $searchParams);
        $total = $countStmt->fetch()['total'];
        
        // Get teachers with pagination
        $sql = "SELECT * FROM teachers $searchCondition ORDER BY created_at DESC LIMIT ? OFFSET ?";
        $params = array_merge($searchParams, [$limit, $offset]);
        $teachers = $db->fetchAll($sql, $params);
        
        $totalPages = ceil($total / $limit);
        
        echo json_encode([
            'success' => true,
            'teachers' => $teachers,
            'total' => $total,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'items_per_page' => $limit
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to load teachers: ' . $e->getMessage()]);
    }
}

function handleGetTeacherById($teacherModel) {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Teacher ID is required']);
        return;
    }
    
    try {
        $teacher = $teacherModel->getById($id);
        
        if ($teacher) {
            echo json_encode([
                'success' => true,
                'teacher' => $teacher
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Teacher not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to load teacher: ' . $e->getMessage()]);
    }
}

function handleCreateTeacher($teacherModel, $input) {
    // Validate required fields
    $requiredFields = ['first_name', 'last_name', 'email'];
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
            return;
        }
    }
    
    // Validate email format
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        return;
    }
    
    // Check if email already exists
    if ($teacherModel->emailExists($input['email'])) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        return;
    }
    
    try {
        // Prepare teacher data
        $teacherData = [
            'first_name' => trim($input['first_name']),
            'last_name' => trim($input['last_name']),
            'email' => trim($input['email']),
            'phone' => $input['phone'] ?? null,
            'address' => $input['address'] ?? null,
            'qualification' => $input['qualification'] ?? null,
            'experience_years' => $input['experience_years'] ?? 0,
            'joining_date' => $input['joining_date'] ?? null,
            'salary' => $input['salary'] ?? null,
            'status' => $input['status'] ?? 'active'
        ];
        
        // Create teacher (password will be auto-generated)
        $teacherId = $teacherModel->create($teacherData);
        
        echo json_encode([
            'success' => true,
            'message' => 'Teacher added successfully',
            'teacher_id' => $teacherId
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to create teacher: ' . $e->getMessage()]);
    }
}

function handleUpdateTeacher($teacherModel, $input) {
    $id = $input['id'] ?? null;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Teacher ID is required']);
        return;
    }
    
    // Validate required fields
    $requiredFields = ['first_name', 'last_name', 'email'];
    foreach ($requiredFields as $field) {
        if (empty($input[$field])) {
            echo json_encode(['success' => false, 'message' => ucfirst(str_replace('_', ' ', $field)) . ' is required']);
            return;
        }
    }
    
    // Validate email format
    if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        return;
    }
    
    // Check if email already exists (excluding current teacher)
    if ($teacherModel->emailExists($input['email'], $id)) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        return;
    }
    
    try {
        // Prepare teacher data
        $teacherData = [
            'first_name' => trim($input['first_name']),
            'last_name' => trim($input['last_name']),
            'email' => trim($input['email']),
            'phone' => $input['phone'] ?? null,
            'address' => $input['address'] ?? null,
            'qualification' => $input['qualification'] ?? null,
            'experience_years' => $input['experience_years'] ?? 0,
            'joining_date' => $input['joining_date'] ?? null,
            'salary' => $input['salary'] ?? null,
            'status' => $input['status'] ?? 'active'
        ];
        
        // Update teacher
        $teacherModel->update($id, $teacherData);
        
        echo json_encode([
            'success' => true,
            'message' => 'Teacher updated successfully'
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to update teacher: ' . $e->getMessage()]);
    }
}

function handleDeleteTeacher($teacherModel, $input) {
    $id = $input['id'] ?? null;
    
    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'Teacher ID is required']);
        return;
    }
    
    try {
        // Check if teacher exists
        $teacher = $teacherModel->getById($id);
        if (!$teacher) {
            echo json_encode(['success' => false, 'message' => 'Teacher not found']);
            return;
        }
        
        // Check if teacher is assigned to any classes
        $db = db();
        $classCheck = $db->fetch("SELECT COUNT(*) as count FROM classes WHERE teacher_id = ?", [$id]);
        if ($classCheck['count'] > 0) {
            echo json_encode(['success' => false, 'message' => 'Cannot delete teacher. They are assigned to classes.']);
            return;
        }
        
        // Delete teacher
        $teacherModel->delete($id);
        
        echo json_encode([
            'success' => true,
            'message' => 'Teacher deleted successfully'
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Failed to delete teacher: ' . $e->getMessage()]);
    }
}
?> 