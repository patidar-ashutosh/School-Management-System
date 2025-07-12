<?php
require_once __DIR__ . '/../config/db.php';

class Subject {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function getAll() {
        $sql = "SELECT s.*, c.name as class_name FROM subjects s LEFT JOIN classes c ON s.class_id = c.id ORDER BY s.name";
        return $this->db->fetchAll($sql);
    }

    public function getById($id) {
        $sql = "SELECT s.*, c.name as class_name FROM subjects s LEFT JOIN classes c ON s.class_id = c.id WHERE s.id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function create($data) {
        // Always store subject name in lowercase
        $data['name'] = strtolower(trim($data['name']));
        // Check for duplicate name in the same class
        if ($this->nameExists($data['name'], $data['class_id'])) {
            throw new Exception('Subject name must be unique within the same class');
        }
        // Generate code from name if not provided
        if (!isset($data['code']) || empty($data['code'])) {
            $data['code'] = $this->generateCode($data['name']);
        }
        $sql = "INSERT INTO subjects (name, code, description, class_id, status) VALUES (?, ?, ?, ?, ?)";
        $this->db->query($sql, [
            $data['name'],
            $data['code'],
            $data['description'] ?? null,
            $data['class_id'],
            $data['status'] ?? 'active'
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        // Always store subject name in lowercase
        $data['name'] = strtolower(trim($data['name']));
        // Check for duplicate name in the same class (excluding current id)
        if ($this->nameExists($data['name'], $data['class_id'], $id)) {
            throw new Exception('Subject name must be unique within the same class');
        }
        // Generate code from name if not provided
        if (!isset($data['code']) || empty($data['code'])) {
            $data['code'] = $this->generateCode($data['name']);
        }
        $sql = "UPDATE subjects SET name = ?, code = ?, description = ?, class_id = ?, status = ? WHERE id = ?";
        return $this->db->query($sql, [
            $data['name'],
            $data['code'],
            $data['description'] ?? null,
            $data['class_id'],
            $data['status'] ?? 'active',
            $id
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM subjects WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function getByTeacher($teacherId) {
        $sql = "SELECT s.*, c.name as class_name FROM subjects s LEFT JOIN classes c ON s.class_id = c.id INNER JOIN teachers t ON t.subject_id = s.id WHERE t.id = ? AND s.status = 'active' ORDER BY s.name";
        return $this->db->fetchAll($sql, [$teacherId]);
    }

    public function getByClass($classId) {
        $sql = "SELECT s.id, s.code, s.name, s.description
                FROM subjects s
                WHERE s.class_id = ? AND s.status = 'active'
                ORDER BY s.name";
        return $this->db->fetchAll($sql, [$classId]);
    }

    public function getCount() {
        $sql = "SELECT COUNT(*) as count FROM subjects";
        $result = $this->db->fetch($sql);
        return $result['count'];
    }

    public function getActiveSubjects() {
        $sql = "SELECT s.*, c.name as class_name FROM subjects s LEFT JOIN classes c ON s.class_id = c.id WHERE s.status = 'active' ORDER BY s.name";
        return $this->db->fetchAll($sql);
    }

    public function codeExists($code, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM subjects WHERE code = ?";
        if ($excludeId) {
            $sql .= " AND id != ?";
            $result = $this->db->fetch($sql, [$code, $excludeId]);
        } else {
            $result = $this->db->fetch($sql, [$code]);
        }
        return $result['count'] > 0;
    }

    public function nameExists($name, $class_id, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM subjects WHERE name = ? AND class_id = ?";
        $params = [$name, $class_id];
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        $result = $this->db->fetch($sql, $params);
        return $result['count'] > 0;
    }

    private function generateCode($name) {
        // Remove special characters and convert to uppercase
        $code = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $name));
        
        // Take first 3-6 characters
        $code = substr($code, 0, 6);
        
        // Add a number if code already exists
        $baseCode = $code;
        $counter = 1;
        
        while ($this->codeExists($code)) {
            $code = $baseCode . $counter;
            $counter++;
        }
        
        return $code;
    }

    public function getByCode($code) {
        $sql = "SELECT * FROM subjects WHERE code = ?";
        return $this->db->fetch($sql, [$code]);
    }
}
?> 