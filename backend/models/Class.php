<?php
require_once __DIR__ . '/../config/db.php';

class ClassModel {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function getAll() {
        $sql = "SELECT c.* FROM classes c ORDER BY c.created_at DESC";
        return $this->db->fetchAll($sql);
    }

    public function getById($id) {
        $sql = "SELECT c.* FROM classes c WHERE c.id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function create($data) {
        $sql = "INSERT INTO classes (name, room_number, capacity) VALUES (?, ?, ?)";
        $this->db->query($sql, [
            strtolower(trim($data['name'])),
            $data['room_number'] ?? null,
            $data['capacity'] ?? 30
        ]);
        $id = $this->db->lastInsertId();
        return $id;
    }

    public function update($id, $data) {
        $sql = "UPDATE classes SET name = ?, room_number = ?, capacity = ? WHERE id = ?";
        return $this->db->query($sql, [
            strtolower(trim($data['name'])),
            $data['room_number'] ?? null,
            $data['capacity'] ?? 30,
            $id
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM classes WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function getByTeacher($teacherId) {
        // Return all classes where the teacher is assigned as a lecturer (many-to-many)
        $sql = "SELECT DISTINCT c.*, CONCAT(t.first_name, ' ', t.last_name) as teacher_name 
                FROM classes c 
                LEFT JOIN teachers t ON c.teacher_id = t.id 
                INNER JOIN lecturers l ON l.class_id = c.id 
                WHERE l.teacher_id = ? 
                ORDER BY c.name";
        return $this->db->fetchAll($sql, [$teacherId]);
    }

    public function getActiveClasses() {
        $sql = "SELECT c.*, CONCAT(t.first_name, ' ', t.last_name) as teacher_name 
                FROM classes c 
                LEFT JOIN teachers t ON c.teacher_id = t.id 
                ORDER BY c.name";
        return $this->db->fetchAll($sql);
    }

    public function getCount() {
        $sql = "SELECT COUNT(*) as count FROM classes";
        $result = $this->db->fetch($sql);
        return $result['count'];
    }

    public function getCountByTeacher($teacherId) {
        $sql = "SELECT COUNT(*) as count FROM classes WHERE teacher_id = ?";
        $result = $this->db->fetch($sql, [$teacherId]);
        return $result['count'];
    }

    public function getStudentCount($classId) {
        $sql = "SELECT COUNT(*) as count FROM students WHERE class_id = ?";
        $result = $this->db->fetch($sql, [$classId]);
        return $result['count'];
    }

    public function nameExists($name, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM classes WHERE name = ?";
        $params = [$name];
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        $result = $this->db->fetch($sql, $params);
        return $result['count'] > 0;
    }
}
?> 