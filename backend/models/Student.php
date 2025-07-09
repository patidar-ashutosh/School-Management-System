<?php
require_once __DIR__ . '/../config/db.php';

class Student {
    private $db;

    public function __construct() {
        $this->db = db();
    }

    public function getAll() {
        $sql = "SELECT s.*, c.name as class_name 
                FROM students s 
                LEFT JOIN classes c ON s.class_id = c.id 
                ORDER BY s.created_at DESC";
        return $this->db->fetchAll($sql);
    }

    public function getById($id) {
        $sql = "SELECT s.*, c.name as class_name 
                FROM students s 
                LEFT JOIN classes c ON s.class_id = c.id 
                WHERE s.id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function getByEmail($email) {
        $sql = "SELECT s.*, c.name as class_name 
                FROM students s 
                LEFT JOIN classes c ON s.class_id = c.id 
                WHERE s.email = ?";
        return $this->db->fetch($sql, [$email]);
    }

    public function getNextRollNumber() {
        $sql = "SELECT MAX(roll_number) as max_roll FROM students";
        $result = $this->db->fetch($sql);
        return ($result['max_roll'] ?? 0) + 1;
    }

    public function create($data) {
        // Get the next roll number
        $nextRollNumber = $this->getNextRollNumber();
        
        $sql = "INSERT INTO students (password, email, first_name, last_name, date_of_birth, gender, address, phone, parent_name, parent_phone, parent_email, class_id, roll_number, admission_date, blood_group, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        // Generate default password if not provided
        if (empty($data['password'])) {
            $data['password'] = strtolower($data['first_name'] . $data['last_name']);
        }
        
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $this->db->query($sql, [
            $hashedPassword,
            $data['email'],
            $data['first_name'],
            $data['last_name'],
            $data['date_of_birth'],
            $data['gender'],
            $data['address'],
            $data['phone'],
            $data['parent_name'],
            $data['parent_phone'],
            $data['parent_email'],
            $data['class_id'],
            $nextRollNumber,
            $data['admission_date'],
            $data['blood_group'] ?? null,
            $data['status'] ?? 'active'
        ]);
        
        return $this->db->lastInsertId();
    }

    public function update($id, $data) {
        $sql = "UPDATE students SET email = ?, first_name = ?, last_name = ?, date_of_birth = ?, 
                gender = ?, address = ?, phone = ?, parent_name = ?, parent_phone = ?, parent_email = ?, class_id = ?, admission_date = ?, blood_group = ?, status = ? WHERE id = ?";
        
        return $this->db->query($sql, [
            $data['email'],
            $data['first_name'],
            $data['last_name'],
            $data['date_of_birth'],
            $data['gender'],
            $data['address'],
            $data['phone'],
            $data['parent_name'],
            $data['parent_phone'],
            $data['parent_email'],
            $data['class_id'],
            $data['admission_date'],
            $data['blood_group'] ?? null,
            $data['status'] ?? 'active',
            $id
        ]);
    }

    public function updatePassword($id, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE students SET password = ? WHERE id = ?";
        return $this->db->query($sql, [$hashedPassword, $id]);
    }

    public function delete($id) {
        $sql = "DELETE FROM students WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    public function getByClass($classId) {
        $sql = "SELECT s.*, c.name as class_name 
                FROM students s 
                LEFT JOIN classes c ON s.class_id = c.id 
                WHERE s.class_id = ? 
                ORDER BY s.first_name, s.last_name";
        return $this->db->fetchAll($sql, [$classId]);
    }

    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM students WHERE email = ?";
        if ($excludeId) {
            $sql .= " AND id != ?";
            $result = $this->db->fetch($sql, [$email, $excludeId]);
        } else {
            $result = $this->db->fetch($sql, [$email]);
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

    public function getActiveStudents() {
        $sql = "SELECT s.*, c.name as class_name 
                FROM students s 
                LEFT JOIN classes c ON s.class_id = c.id 
                WHERE s.status = 'active' 
                ORDER BY s.first_name, s.last_name";
        return $this->db->fetchAll($sql);
    }

    public function getPendingAssignmentsStats($classId) {
        $sql = "SELECT type, COUNT(*) as count FROM assignments WHERE class_id = ? AND status = 'active' GROUP BY type";
        return $this->db->fetchAll($sql, [$classId]);
    }

    public function getDb() {
        return $this->db;
    }
}
?> 