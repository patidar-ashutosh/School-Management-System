<?php
require_once __DIR__ . '/../config/db.php';

class Lecturer {
    private $db;
    public function __construct() {
        $this->db = db();
    }

    public function getByTeacher($teacher_id) {
        $sql = "SELECT l.*, s.name as subject_name, c.name as class_name FROM lecturers l LEFT JOIN subjects s ON l.subject_id = s.id LEFT JOIN classes c ON l.class_id = c.id WHERE l.teacher_id = ? ORDER BY l.start_time DESC";
        return $this->db->fetchAll($sql, [$teacher_id]);
    }

    public function add($data) {
        $sql = "INSERT INTO lecturers (subject_id, teacher_id, class_id, start_time, end_time, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $this->db->query($sql, [
            $data['subject_id'],
            $data['teacher_id'],
            $data['class_id'],
            $data['start_time'],
            $data['end_time'],
            $data['status']
        ]);
        return $this->db->lastInsertId();
    }

    public function edit($id, $data) {
        $sql = "UPDATE lecturers SET subject_id = ?, teacher_id = ?, class_id = ?, start_time = ?, end_time = ?, status = ?, updated_at = NOW() WHERE id = ?";
        $this->db->query($sql, [
            $data['subject_id'],
            $data['teacher_id'],
            $data['class_id'],
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
} 