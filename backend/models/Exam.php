<?php
require_once __DIR__ . '/../config/db.php';

class Exam {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function getAll() {
        $sql = "SELECT e.*, c.name as class_name, s.name as subject_name
                FROM exams e
                LEFT JOIN classes c ON e.class_id = c.id
                LEFT JOIN subjects s ON e.subject_id = s.id
                ORDER BY e.date DESC";
        return $this->db->fetchAll($sql);
    }

    public function getById($id) {
        $sql = "SELECT e.*, c.name as class_name, s.name as subject_name
                FROM exams e
                LEFT JOIN classes c ON e.class_id = c.id
                LEFT JOIN subjects s ON e.subject_id = s.id
                WHERE e.id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function create($data) {
        $sql = "INSERT INTO exams (name, subject_id, class_id, date, start_time, end_time, total_marks, exam_type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $this->db->query($sql, [
            $data['name'],
            $data['subject_id'],
            $data['class_id'],
            $data['date'],
            $data['start_time'],
            $data['end_time'],
            $data['total_marks'],
            $data['exam_type'],
            $data['status']
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE exams SET name = ?, subject_id = ?, class_id = ?, date = ?, start_time = ?, end_time = ?, total_marks = ?, exam_type = ?, status = ? WHERE id = ?";
        return $this->db->query($sql, [
            $data['name'],
            $data['subject_id'],
            $data['class_id'],
            $data['date'],
            $data['start_time'],
            $data['end_time'],
            $data['total_marks'],
            $data['exam_type'],
            $data['status'],
            $id
        ]);
    }

    public function delete($id) {
        // Remove marks table check, just delete the exam
        $sql = "DELETE FROM exams WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function getByClass($classId) {
        $sql = "SELECT e.*, c.name as class_name, s.name as subject_name
                FROM exams e
                LEFT JOIN classes c ON e.class_id = c.id
                LEFT JOIN subjects s ON e.subject_id = s.id
                WHERE e.class_id = ?
                ORDER BY e.date DESC";
        return $this->db->fetchAll($sql, [$classId]);
    }

    public function getCount() {
        $sql = "SELECT COUNT(*) as count FROM exams";
        $result = $this->db->fetch($sql);
        return $result['count'];
    }

    public function getUpcomingExams() {
        $sql = "SELECT e.*, c.name as class_name, s.name as subject_name
                FROM exams e
                LEFT JOIN classes c ON e.class_id = c.id
                LEFT JOIN subjects s ON e.subject_id = s.id
                WHERE e.date >= CURDATE()
                ORDER BY e.date ASC
                LIMIT 5";
        return $this->db->fetchAll($sql);
    }

    public function getByTeacher($teacherId) {
        // Get all class_ids for this teacher from teacher_classes
        $classIds = $this->db->fetchAll("SELECT class_id FROM teacher_classes WHERE teacher_id = ?", [$teacherId]);
        if (!$classIds) return [];
        $classIdList = array_column($classIds, 'class_id');
        // Get teacher's subject_id
        $teacher = $this->db->fetch("SELECT subject_id FROM teachers WHERE id = ?", [$teacherId]);
        $subjectId = $teacher && isset($teacher['subject_id']) ? $teacher['subject_id'] : null;
        if (!$subjectId) return [];
        $in = str_repeat('?,', count($classIdList) - 1) . '?';
        $sql = "SELECT e.*, c.name as class_name, s.name as subject_name
                FROM exams e
                LEFT JOIN classes c ON e.class_id = c.id
                LEFT JOIN subjects s ON e.subject_id = s.id
                WHERE e.class_id IN ($in) AND e.subject_id = ?
                ORDER BY e.date DESC, e.id DESC";
        $params = array_merge($classIdList, [$subjectId]);
        return $this->db->fetchAll($sql, $params);
    }
}
?> 