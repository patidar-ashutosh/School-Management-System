<?php
require_once __DIR__ . '/../config/db.php';

class Exam {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function getAll() {
        $sql = "SELECT e.*, c.class_name 
                FROM exams e 
                LEFT JOIN classes c ON e.class_id = c.id 
                ORDER BY e.exam_date DESC";
        return $this->db->fetchAll($sql);
    }

    public function getById($id) {
        $sql = "SELECT e.*, c.class_name 
                FROM exams e 
                LEFT JOIN classes c ON e.class_id = c.id 
                WHERE e.id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function create($data) {
        $sql = "INSERT INTO exams (exam_name, exam_date, class_id) VALUES (?, ?, ?)";
        
        $this->db->query($sql, [
            $data['exam_name'],
            $data['exam_date'],
            $data['class_id']
        ]);
        
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE exams SET exam_name = ?, exam_date = ?, class_id = ? WHERE id = ?";
        
        return $this->db->query($sql, [
            $data['exam_name'],
            $data['exam_date'],
            $data['class_id'],
            $id
        ]);
    }

    public function delete($id) {
        // Check if exam has marks
        $sql = "SELECT COUNT(*) as count FROM marks WHERE exam_id = ?";
        $result = $this->db->fetch($sql, [$id]);
        
        if ($result['count'] > 0) {
            throw new Exception("Cannot delete exam with existing marks");
        }
        
        $sql = "DELETE FROM exams WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function getByClass($classId) {
        $sql = "SELECT * FROM exams WHERE class_id = ? ORDER BY exam_date DESC";
        return $this->db->fetchAll($sql, [$classId]);
    }

    public function getCount() {
        $sql = "SELECT COUNT(*) as count FROM exams";
        $result = $this->db->fetch($sql);
        return $result['count'];
    }

    public function getUpcomingExams() {
        $sql = "SELECT e.*, c.class_name 
                FROM exams e 
                LEFT JOIN classes c ON e.class_id = c.id 
                WHERE e.exam_date >= CURDATE() 
                ORDER BY e.exam_date ASC 
                LIMIT 5";
        return $this->db->fetchAll($sql);
    }
}
?> 