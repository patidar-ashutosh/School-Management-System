<?php
/**
 * Database Setup Script for School Management System
 * This script will create the database and tables with sample data
 * Based on the updated schema with principals table
 */

echo "School Management System - Database Setup\n";
echo "=========================================\n\n";

// Database configuration (same as db.php)
$host = '127.0.0.1';
$dbname = 'school_management';
$username = 'root';
$password = '12345';

try {
    // Connect to MySQL server (without database)
    $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✓ Connected to MySQL server\n";
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database '$dbname' created/verified\n";
    
    // Connect to the specific database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✓ Connected to database '$dbname'\n\n";
    
} catch (PDOException $e) {
    die("❌ Database connection failed: " . $e->getMessage() . "\n");
}

// Step 1: Create all tables WITHOUT foreign keys
$tables = [
    'principals' => "CREATE TABLE principals (
        id INT PRIMARY KEY AUTO_INCREMENT,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        address TEXT,
        qualification VARCHAR(100),
        joining_date DATE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",

    'classes' => "CREATE TABLE classes (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL UNIQUE,
        room_number INT(20),
        capacity INT DEFAULT 30,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",

    'subjects' => "CREATE TABLE subjects (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL, -- not unique, can repeat in different classes
        code VARCHAR(20) UNIQUE NOT NULL,
        description TEXT,
        class_id INT,
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",

    'teachers' => "CREATE TABLE teachers (
        id INT PRIMARY KEY AUTO_INCREMENT,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        phone VARCHAR(20),
        address TEXT,
        subject_id INT,
        qualification VARCHAR(100),
        experience_years INT DEFAULT 0,
        joining_date DATE,
        salary DECIMAL(10,2),
        status ENUM('active', 'inactive') DEFAULT 'active',
        class_teacher_of INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",

    'lecturers' => "CREATE TABLE lecturers (
        id INT PRIMARY KEY AUTO_INCREMENT,
        subject_id INT NOT NULL,
        teacher_id INT NOT NULL,
        class_id INT NOT NULL,
        date DATE NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        status ENUM('scheduled', 'running', 'completed') DEFAULT 'scheduled',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",

    'students' => "CREATE TABLE students (
        id INT PRIMARY KEY AUTO_INCREMENT,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        date_of_birth DATE,
        gender ENUM('male', 'female', 'other'),
        address TEXT,
        phone VARCHAR(20),
        parent_name VARCHAR(100),
        parent_phone VARCHAR(20),
        parent_email VARCHAR(100),
        class_id INT,
        roll_number INT UNIQUE,
        admission_date DATE,
        blood_group VARCHAR(5),
        status ENUM('active', 'inactive') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",

    'attendance' => "CREATE TABLE attendance (
        id INT PRIMARY KEY AUTO_INCREMENT,
        student_id INT,
        class_id INT,
        date DATE,
        status ENUM('present', 'absent') DEFAULT 'present',
        marked_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    'assignments' => "CREATE TABLE assignments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(200) NOT NULL,
        description TEXT,
        subject_id INT,
        class_id INT,
        start_date DATE,
        due_date DATE,
        total_marks INT DEFAULT 100,
        teacher_id INT,
        type ENUM('essays', 'reports', 'presentations') NOT NULL DEFAULT 'essays',
        status ENUM('coming', 'running', 'completed') DEFAULT 'coming',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",

    'student_assignments' => "CREATE TABLE student_assignments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        assignment_id INT,
        student_id INT,
        submitted_date DATETIME,
        marks_obtained DECIMAL(5,2),
        submitted_text TEXT,
        submitted_file VARCHAR(255),
        status ENUM('submitted', 'graded') DEFAULT 'submitted',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",

    'exams' => "CREATE TABLE exams (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(200) NOT NULL,
        subject_id INT,
        class_id INT,
        date DATE,
        start_time TIME,
        end_time TIME,
        total_marks INT DEFAULT 100,
        exam_type ENUM('midterm', 'final') NOT NULL,
        status ENUM('scheduled', 'ongoing', 'completed') DEFAULT 'scheduled',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )",

    'password_resets' => "CREATE TABLE password_resets (
        id INT PRIMARY KEY AUTO_INCREMENT,
        email VARCHAR(100) NOT NULL,
        user_type ENUM('principal', 'teacher', 'student') NOT NULL,
        token VARCHAR(64) UNIQUE NOT NULL,
        expires_at TIMESTAMP NOT NULL,
        used BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    // Add teacher_classes table for many-to-many teacher-class mapping
    'teacher_classes' => "CREATE TABLE teacher_classes (
        id INT PRIMARY KEY AUTO_INCREMENT,
        teacher_id INT NOT NULL,
        class_id INT NOT NULL,
        FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
        FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
    )"
];

foreach ($tables as $tableName => $sql) {
    try {
        $pdo->exec($sql);
        echo "✓ Table '$tableName' created\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'already exists') !== false) {
            echo "✓ Table '$tableName' already exists\n";
        } else {
            echo "✗ Error creating table '$tableName': " . $e->getMessage() . "\n";
        }
    }
}

// Step 2: Add all foreign keys after all tables are created
$foreignKeys = [
    // classes
    // REMOVED: "ALTER TABLE classes ADD CONSTRAINT fk_classes_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL",
    // subjects
    "ALTER TABLE subjects ADD CONSTRAINT fk_subjects_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL",
    // teachers
    "ALTER TABLE teachers ADD CONSTRAINT fk_teachers_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL",
    // teachers - class_teacher_of
    "ALTER TABLE teachers ADD CONSTRAINT fk_class_teacher_of FOREIGN KEY (class_teacher_of) REFERENCES classes(id) ON DELETE SET NULL",
    // lecturers
    "ALTER TABLE lecturers ADD CONSTRAINT fk_lecturers_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE",
    "ALTER TABLE lecturers ADD CONSTRAINT fk_lecturers_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE",
    "ALTER TABLE lecturers ADD CONSTRAINT fk_lecturers_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE",
    // students
    "ALTER TABLE students ADD CONSTRAINT fk_students_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL",
    // attendance
    "ALTER TABLE attendance ADD CONSTRAINT fk_attendance_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE",
    "ALTER TABLE attendance ADD CONSTRAINT fk_attendance_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE",
    "ALTER TABLE attendance ADD CONSTRAINT fk_attendance_marked_by FOREIGN KEY (marked_by) REFERENCES teachers(id) ON DELETE SET NULL",
    // assignments
    "ALTER TABLE assignments ADD CONSTRAINT fk_assignments_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE",
    "ALTER TABLE assignments ADD CONSTRAINT fk_assignments_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE",
    "ALTER TABLE assignments ADD CONSTRAINT fk_assignments_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL",
    // student_assignments
    "ALTER TABLE student_assignments ADD CONSTRAINT fk_student_assignments_assignment FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE",
    "ALTER TABLE student_assignments ADD CONSTRAINT fk_student_assignments_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE",
    // exams
    "ALTER TABLE exams ADD CONSTRAINT fk_exams_subject FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE",
    "ALTER TABLE exams ADD CONSTRAINT fk_exams_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE"
];

foreach ($foreignKeys as $fkSql) {
    try {
        $pdo->exec($fkSql);
        echo "✓ Foreign key added: $fkSql\n";
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), 'Duplicate') !== false || strpos($e->getMessage(), 'already exists') !== false) {
            echo "✓ Foreign key already exists: $fkSql\n";
        } else {
            echo "✗ Error adding foreign key: $fkSql: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n🎉 Database setup completed successfully!\n\n";
echo "Database Structure:\n";
echo "- Principals table: Separate table for school principals\n";
echo "- Teachers table: Separate table for teachers with login credentials (using id as teacher ID)\n";
echo "- Students table: Separate table for students with login credentials (using id as student ID, roll_number auto-increment)\n";
echo "- Assignments table: Updated to use teacher_id instead of created_by\n";
echo "- Complete academic management system with attendance, grades, exams, etc.\n";
echo "- teacher_classes table: Many-to-many mapping for teachers and classes they teach\n\n";
?> 