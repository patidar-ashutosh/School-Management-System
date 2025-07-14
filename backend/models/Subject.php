<?php
require_once __DIR__ . '/../config/db.php';

class Subject {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function getAll() {
        // Fetch all subjects with their mapped classes
        $sql = "SELECT s.*, GROUP_CONCAT(c.name ORDER BY c.name SEPARATOR ', ') as class_names, GROUP_CONCAT(c.id ORDER BY c.name) as class_ids
                FROM subjects s
                LEFT JOIN subject_classes sc ON s.id = sc.subject_id
                LEFT JOIN classes c ON sc.class_id = c.id
                GROUP BY s.id
                ORDER BY s.name";
        return $this->db->fetchAll($sql);
    }

    public function getById($id) {
        $sql = "SELECT s.*, GROUP_CONCAT(c.name ORDER BY c.name SEPARATOR ', ') as class_names, GROUP_CONCAT(c.id ORDER BY c.name) as class_ids
                FROM subjects s
                LEFT JOIN subject_classes sc ON s.id = sc.subject_id
                LEFT JOIN classes c ON sc.class_id = c.id
                WHERE s.id = ?
                GROUP BY s.id";
        return $this->db->fetch($sql, [$id]);
    }

    public function create($data) {
        // Always store subject name in lowercase
        $data['name'] = strtolower(trim($data['name']));
        // Check for duplicate name in the same classes
        if (!empty($data['class_ids'])) {
            foreach ($data['class_ids'] as $class_id) {
                if ($this->nameExists($data['name'], $class_id)) {
                    throw new Exception('Subject name must be unique within the same class');
                }
            }
        }
        // Generate code from name if not provided
        if (!isset($data['code']) || empty($data['code'])) {
            $data['code'] = $this->generateCode($data['name']);
        }
        $sql = "INSERT INTO subjects (name, code, description, status) VALUES (?, ?, ?, ?)";
        $this->db->query($sql, [
            $data['name'],
            $data['code'],
            $data['description'] ?? null,
            $data['status'] ?? 'active'
        ]);
        $subjectId = $this->db->lastInsertId();
        // Insert into subject_classes mapping
        if (!empty($data['class_ids'])) {
            foreach ($data['class_ids'] as $class_id) {
                $this->addClassMapping($subjectId, $class_id);
            }
        }
        return $subjectId;
    }

    public function update($id, $data) {
        $data['name'] = strtolower(trim($data['name']));
        // Check for duplicate name in the same classes (excluding current id)
        if (!empty($data['class_ids'])) {
            foreach ($data['class_ids'] as $class_id) {
                if ($this->nameExists($data['name'], $class_id, $id)) {
                    throw new Exception('Subject name must be unique within the same class');
                }
            }
        }
        if (!isset($data['code']) || empty($data['code'])) {
            $data['code'] = $this->generateCode($data['name']);
        }
        $sql = "UPDATE subjects SET name = ?, code = ?, description = ?, status = ? WHERE id = ?";
        $this->db->query($sql, [
            $data['name'],
            $data['code'],
            $data['description'] ?? null,
            $data['status'] ?? 'active',
            $id
        ]);
        // Update subject_classes mapping
        $this->removeAllClassMappings($id);
        if (!empty($data['class_ids'])) {
            foreach ($data['class_ids'] as $class_id) {
                $this->addClassMapping($id, $class_id);
            }
        }
        return true;
    }

    public function delete($id) {
        // Remove all mappings first
        $this->removeAllClassMappings($id);
        $sql = "DELETE FROM subjects WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function getByTeacher($teacherId) {
        $sql = "SELECT s.*, GROUP_CONCAT(c.name ORDER BY c.name SEPARATOR ', ') as class_names
                FROM subjects s
                INNER JOIN subject_classes sc ON s.id = sc.subject_id
                INNER JOIN classes c ON sc.class_id = c.id
                INNER JOIN teacher_classes tc ON tc.class_id = c.id AND tc.teacher_id = ?
                WHERE s.status = 'active'
                GROUP BY s.id
                ORDER BY s.name";
        return $this->db->fetchAll($sql, [$teacherId]);
    }

    public function getByClass($classId) {
        $sql = "SELECT s.id, s.code, s.name, s.description
                FROM subjects s
                INNER JOIN subject_classes sc ON s.id = sc.subject_id
                WHERE sc.class_id = ? AND s.status = 'active'
                ORDER BY s.name";
        return $this->db->fetchAll($sql, [$classId]);
    }

    public function getCount() {
        $sql = "SELECT COUNT(*) as count FROM subjects";
        $result = $this->db->fetch($sql);
        return $result['count'];
    }

    public function getActiveSubjects() {
        $sql = "SELECT s.*, GROUP_CONCAT(c.name ORDER BY c.name SEPARATOR ', ') as class_names
                FROM subjects s
                LEFT JOIN subject_classes sc ON s.id = sc.subject_id
                LEFT JOIN classes c ON sc.class_id = c.id
                WHERE s.status = 'active'
                GROUP BY s.id
                ORDER BY s.name";
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
        $sql = "SELECT COUNT(*) as count FROM subjects s INNER JOIN subject_classes sc ON s.id = sc.subject_id WHERE s.name = ? AND sc.class_id = ?";
        $params = [$name, $class_id];
        if ($excludeId) {
            $sql .= " AND s.id != ?";
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

    // --- New helper methods for subject_classes mapping ---
    private function addClassMapping($subjectId, $classId) {
        $sql = "INSERT IGNORE INTO subject_classes (subject_id, class_id) VALUES (?, ?)";
        $this->db->query($sql, [$subjectId, $classId]);
    }

    private function removeAllClassMappings($subjectId) {
        $sql = "DELETE FROM subject_classes WHERE subject_id = ?";
        $this->db->query($sql, [$subjectId]);
    }
}
?> 