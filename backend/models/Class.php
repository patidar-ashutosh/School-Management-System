<?php
require_once __DIR__ . '/../config/db.php';

class ClassModel {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function getAll() {
        $sql = "SELECT c.*, CONCAT(t.first_name, ' ', t.last_name) as teacher_name 
                FROM classes c 
                LEFT JOIN teachers t ON c.teacher_id = t.id 
                ORDER BY c.created_at DESC";
        return $this->db->fetchAll($sql);
    }

    public function getById($id) {
        $sql = "SELECT c.*, CONCAT(t.first_name, ' ', t.last_name) as teacher_name 
                FROM classes c 
                LEFT JOIN teachers t ON c.teacher_id = t.id 
                WHERE c.id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function create($data) {
        error_log('ClassModel::create SQL data: ' . json_encode($data));
        $dbName = $this->db->getConnection()->query('SELECT DATABASE()')->fetchColumn();
        error_log('ClassModel::create CURRENT DATABASE: ' . $dbName);
        $sql = "INSERT INTO classes (name, teacher_id, room_number, capacity) 
                VALUES (?, ?, ?, ?)";
        $this->db->query($sql, [
            $data['name'],
            $data['teacher_id'] ?? null,
            $data['room_number'] ?? null,
            $data['capacity'] ?? 30
        ]);
        $id = $this->db->lastInsertId();
        error_log('ClassModel::create lastInsertId: ' . print_r($id, true));
        return $id;
    }

    public function update($id, $data) {
        $sql = "UPDATE classes SET name = ?, teacher_id = ?, room_number = ?, capacity = ? WHERE id = ?";
        return $this->db->query($sql, [
            $data['name'],
            $data['teacher_id'] ?? null,
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
        $sql = "SELECT c.*, CONCAT(t.first_name, ' ', t.last_name) as teacher_name 
                FROM classes c 
                LEFT JOIN teachers t ON c.teacher_id = t.id 
                WHERE c.teacher_id = ? 
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