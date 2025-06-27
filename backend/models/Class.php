<?php
require_once __DIR__ . '/../config/db.php';

class ClassModel {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function getAll() {
        $sql = "SELECT * FROM classes ORDER BY class_name";
        return $this->db->fetchAll($sql);
    }

    public function getById($id) {
        $sql = "SELECT * FROM classes WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function create($data) {
        $sql = "INSERT INTO classes (class_name) VALUES (?)";
        $this->db->query($sql, [$data['class_name']]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE classes SET class_name = ? WHERE id = ?";
        return $this->db->query($sql, [$data['class_name'], $id]);
    }

    public function delete($id) {
        // Check if class has students
        $sql = "SELECT COUNT(*) as count FROM students WHERE class_id = ?";
        $result = $this->db->fetch($sql, [$id]);
        
        if ($result['count'] > 0) {
            throw new Exception("Cannot delete class with existing students");
        }
        
        $sql = "DELETE FROM classes WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function getCount() {
        $sql = "SELECT COUNT(*) as count FROM classes";
        $result = $this->db->fetch($sql);
        return $result['count'];
    }

    public function getWithStudentCount() {
        $sql = "SELECT c.*, COUNT(s.id) as student_count 
                FROM classes c 
                LEFT JOIN students s ON c.id = s.class_id 
                GROUP BY c.id 
                ORDER BY c.class_name";
        return $this->db->fetchAll($sql);
    }
}
?> 