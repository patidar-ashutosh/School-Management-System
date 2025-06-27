<?php
require_once __DIR__ . '/../config/db.php';

class Subject {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function getAll() {
        $sql = "SELECT s.*, c.class_name, t.name as teacher_name 
                FROM subjects s 
                LEFT JOIN classes c ON s.class_id = c.id 
                LEFT JOIN teachers t ON s.teacher_id = t.id 
                ORDER BY s.subject_name";
        return $this->db->fetchAll($sql);
    }

    public function getById($id) {
        $sql = "SELECT s.*, c.class_name, t.name as teacher_name 
                FROM subjects s 
                LEFT JOIN classes c ON s.class_id = c.id 
                LEFT JOIN teachers t ON s.teacher_id = t.id 
                WHERE s.id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function create($data) {
        $sql = "INSERT INTO subjects (subject_name, class_id, teacher_id) VALUES (?, ?, ?)";
        
        $this->db->query($sql, [
            $data['subject_name'],
            $data['class_id'],
            $data['teacher_id'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE subjects SET subject_name = ?, class_id = ?, teacher_id = ? WHERE id = ?";
        
        return $this->db->query($sql, [
            $data['subject_name'],
            $data['class_id'],
            $data['teacher_id'] ?? null,
            $id
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM subjects WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function getByClass($classId) {
        $sql = "SELECT s.*, t.name as teacher_name 
                FROM subjects s 
                LEFT JOIN teachers t ON s.teacher_id = t.id 
                WHERE s.class_id = ? 
                ORDER BY s.subject_name";
        return $this->db->fetchAll($sql, [$classId]);
    }

    public function getByTeacher($teacherId) {
        $sql = "SELECT s.*, c.class_name 
                FROM subjects s 
                LEFT JOIN classes c ON s.class_id = c.id 
                WHERE s.teacher_id = ? 
                ORDER BY s.subject_name";
        return $this->db->fetchAll($sql, [$teacherId]);
    }

    public function getCount() {
        $sql = "SELECT COUNT(*) as count FROM subjects";
        $result = $this->db->fetch($sql);
        return $result['count'];
    }

    public function getCountByClass($classId) {
        $sql = "SELECT COUNT(*) as count FROM subjects WHERE class_id = ?";
        $result = $this->db->fetch($sql, [$classId]);
        return $result['count'];
    }
}
?> 