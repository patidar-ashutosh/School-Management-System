<?php
require_once __DIR__ . '/../config/db.php';

class Teacher {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function getAll() {
        $sql = "SELECT t.*, s.subject_name 
                FROM teachers t 
                LEFT JOIN subjects s ON t.subject_id = s.id 
                ORDER BY t.created_at DESC";
        return $this->db->fetchAll($sql);
    }

    public function getById($id) {
        $sql = "SELECT t.*, s.subject_name 
                FROM teachers t 
                LEFT JOIN subjects s ON t.subject_id = s.id 
                WHERE t.id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function create($data) {
        $sql = "INSERT INTO teachers (name, email, phone, subject_id) VALUES (?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['subject_id'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE teachers SET name = ?, email = ?, phone = ?, subject_id = ? WHERE id = ?";
        
        return $this->db->query($sql, [
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['subject_id'] ?? null,
            $id
        ]);
    }

    public function delete($id) {
        $sql = "DELETE FROM teachers WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM teachers WHERE email = ?";
        if ($excludeId) {
            $sql .= " AND id != ?";
            $result = $this->db->fetch($sql, [$email, $excludeId]);
        } else {
            $result = $this->db->fetch($sql, [$email]);
        }
        return $result['count'] > 0;
    }

    public function getCount() {
        $sql = "SELECT COUNT(*) as count FROM teachers";
        $result = $this->db->fetch($sql);
        return $result['count'];
    }

    public function getBySubject($subjectId) {
        $sql = "SELECT * FROM teachers WHERE subject_id = ?";
        return $this->db->fetchAll($sql, [$subjectId]);
    }

    public function getAvailableTeachers() {
        $sql = "SELECT * FROM teachers WHERE subject_id IS NULL OR subject_id = 0";
        return $this->db->fetchAll($sql);
    }
}
?> 