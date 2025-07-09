<?php
require_once __DIR__ . '/../config/db.php';

class Lecturer {
    private $db;
    public function __construct() {
        $this->db = db();
    }

    public function getByTeacher($teacher_id) {
        $sql = "SELECT l.*, s.name as subject_name, c.name as class_name FROM lecturers l LEFT JOIN subjects s ON l.subject_id = s.id LEFT JOIN classes c ON l.class_id = c.id WHERE l.teacher_id = ? ORDER BY FIELD(l.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'), l.start_time";
        return $this->db->fetchAll($sql, [$teacher_id]);
    }

    public function add($data) {
        $sql = "INSERT INTO lecturers (subject_id, teacher_id, class_id, day_of_week, start_time, end_time, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $this->db->query($sql, [
            $data['subject_id'],
            $data['teacher_id'],
            $data['class_id'],
            $data['day_of_week'],
            $data['start_time'],
            $data['end_time'],
            $data['status']
        ]);
        return $this->db->lastInsertId();
    }

    public function edit($id, $data) {
        $sql = "UPDATE lecturers SET subject_id = ?, teacher_id = ?, class_id = ?, day_of_week = ?, start_time = ?, end_time = ?, status = ?, updated_at = NOW() WHERE id = ?";
        $this->db->query($sql, [
            $data['subject_id'],
            $data['teacher_id'],
            $data['class_id'],
            $data['day_of_week'],
            $data['start_time'],
            $data['end_time'],
            $data['status'],
            $id
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM lecturers WHERE id = ?";
        $this->db->query($sql, [$id]);
    }

    public function getById($id) {
        $sql = "SELECT l.*, s.name as subject_name, c.name as class_name FROM lecturers l LEFT JOIN subjects s ON l.subject_id = s.id LEFT JOIN classes c ON l.class_id = c.id WHERE l.id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function existsForSubjectClassDay($subject_id, $class_id, $day_of_week, $exclude_id = null) {
        $sql = "SELECT COUNT(*) as count FROM lecturers WHERE subject_id = ? AND class_id = ? AND day_of_week = ?";
        $params = [$subject_id, $class_id, $day_of_week];
        if ($exclude_id) {
            $sql .= " AND id != ?";
            $params[] = $exclude_id;
        }
        $result = $this->db->fetch($sql, $params);
        return $result['count'] > 0;
    }
} 