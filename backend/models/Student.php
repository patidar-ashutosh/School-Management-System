<?php
require_once __DIR__ . '/../config/db.php';

class Student {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function getAll() {
        $sql = "SELECT s.*, c.class_name 
                FROM students s 
                LEFT JOIN classes c ON s.class_id = c.id 
                ORDER BY s.created_at DESC";
        return $this->db->fetchAll($sql);
    }

    public function getById($id) {
        $sql = "SELECT s.*, c.class_name 
                FROM students s 
                LEFT JOIN classes c ON s.class_id = c.id 
                WHERE s.id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function create($data) {
        $sql = "INSERT INTO students (name, roll_number, class_id, section, email, dob) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $data['name'],
            $data['roll_number'],
            $data['class_id'],
            $data['section'],
            $data['email'],
            $data['dob']
        ]);
        
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE students SET name = ?, roll_number = ?, class_id = ?, 
                section = ?, email = ?, dob = ? WHERE id = ?";
        
        return $this->db->query($sql, [
            $data['name'],
            $data['roll_number'],
            $data['class_id'],
            $data['section'],
            $data['email'],
            $data['dob'],
            $id
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM students WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function getByClass($classId) {
        $sql = "SELECT s.*, c.class_name 
                FROM students s 
                LEFT JOIN classes c ON s.class_id = c.id 
                WHERE s.class_id = ? 
                ORDER BY s.name";
        return $this->db->fetchAll($sql, [$classId]);
    }

    public function rollNumberExists($rollNumber, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM students WHERE roll_number = ?";
        if ($excludeId) {
            $sql .= " AND id != ?";
            $result = $this->db->fetch($sql, [$rollNumber, $excludeId]);
        } else {
            $result = $this->db->fetch($sql, [$rollNumber]);
        }
        return $result['count'] > 0;
    }

    public function getCount() {
        $sql = "SELECT COUNT(*) as count FROM students";
        $result = $this->db->fetch($sql);
        return $result['count'];
    }

    public function getCountByClass($classId) {
        $sql = "SELECT COUNT(*) as count FROM students WHERE class_id = ?";
        $result = $this->db->fetch($sql, [$classId]);
        return $result['count'];
    }
}
?> 