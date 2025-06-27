<?php
require_once __DIR__ . '/../config/db.php';

class Mark {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function getAll() {
        $sql = "SELECT m.*, s.name as student_name, sub.subject_name, e.exam_name, c.class_name 
                FROM marks m 
                LEFT JOIN students s ON m.student_id = s.id 
                LEFT JOIN subjects sub ON m.subject_id = sub.id 
                LEFT JOIN exams e ON m.exam_id = e.id 
                LEFT JOIN classes c ON s.class_id = c.id 
                ORDER BY e.exam_date DESC, s.name";
        return $this->db->fetchAll($sql);
    }

    public function getById($id) {
        $sql = "SELECT m.*, s.name as student_name, sub.subject_name, e.exam_name, c.class_name 
                FROM marks m 
                LEFT JOIN students s ON m.student_id = s.id 
                LEFT JOIN subjects sub ON m.subject_id = sub.id 
                LEFT JOIN exams e ON m.exam_id = e.id 
                LEFT JOIN classes c ON s.class_id = c.id 
                WHERE m.id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function create($data) {
        $sql = "INSERT INTO marks (student_id, subject_id, exam_id, marks_obtained, total_marks) 
                VALUES (?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $data['student_id'],
            $data['subject_id'],
            $data['exam_id'],
            $data['marks_obtained'],
            $data['total_marks'] ?? 100
        ]);
        
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE marks SET student_id = ?, subject_id = ?, exam_id = ?, 
                marks_obtained = ?, total_marks = ? WHERE id = ?";
        
        return $this->db->query($sql, [
            $data['student_id'],
            $data['subject_id'],
            $data['exam_id'],
            $data['marks_obtained'],
            $data['total_marks'] ?? 100,
            $id
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM marks WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function getByStudent($studentId) {
        $sql = "SELECT m.*, sub.subject_name, e.exam_name, e.exam_date 
                FROM marks m 
                LEFT JOIN subjects sub ON m.subject_id = sub.id 
                LEFT JOIN exams e ON m.exam_id = e.id 
                WHERE m.student_id = ? 
                ORDER BY e.exam_date DESC";
        return $this->db->fetchAll($sql, [$studentId]);
    }

    public function getByExam($examId) {
        $sql = "SELECT m.*, s.name as student_name, s.roll_number, sub.subject_name, c.class_name 
                FROM marks m 
                LEFT JOIN students s ON m.student_id = s.id 
                LEFT JOIN subjects sub ON m.subject_id = sub.id 
                LEFT JOIN classes c ON s.class_id = c.id 
                WHERE m.exam_id = ? 
                ORDER BY s.name, sub.subject_name";
        return $this->db->fetchAll($sql, [$examId]);
    }

    public function getByStudentAndExam($studentId, $examId) {
        $sql = "SELECT m.*, sub.subject_name 
                FROM marks m 
                LEFT JOIN subjects sub ON m.subject_id = sub.id 
                WHERE m.student_id = ? AND m.exam_id = ? 
                ORDER BY sub.subject_name";
        return $this->db->fetchAll($sql, [$studentId, $examId]);
    }

    public function getStudentAverage($studentId) {
        $sql = "SELECT AVG((marks_obtained / total_marks) * 100) as average_percentage 
                FROM marks 
                WHERE student_id = ?";
        $result = $this->db->fetch($sql, [$studentId]);
        return round($result['average_percentage'], 2);
    }

    public function getClassAverage($classId, $examId = null) {
        $sql = "SELECT AVG((m.marks_obtained / m.total_marks) * 100) as average_percentage 
                FROM marks m 
                LEFT JOIN students s ON m.student_id = s.id 
                WHERE s.class_id = ?";
        
        $params = [$classId];
        if ($examId) {
            $sql .= " AND m.exam_id = ?";
            $params[] = $examId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return round($result['average_percentage'], 2);
    }

    public function markExists($studentId, $subjectId, $examId) {
        $sql = "SELECT COUNT(*) as count FROM marks 
                WHERE student_id = ? AND subject_id = ? AND exam_id = ?";
        $result = $this->db->fetch($sql, [$studentId, $subjectId, $examId]);
        return $result['count'] > 0;
    }
}
?> 