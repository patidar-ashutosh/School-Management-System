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
        day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday') NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        status ENUM('completed', 'incoming') DEFAULT 'incoming',
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
        due_date DATE,
        total_marks INT DEFAULT 100,
        teacher_id INT,
        type ENUM('quiz', 'project') NOT NULL DEFAULT 'quiz',
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
        feedback TEXT,
        submitted_file VARCHAR(255),
        status ENUM('pending', 'submitted', 'graded') DEFAULT 'pending',
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

// Insert sample data based on updated schema
try {
    // Insert principal admin
    $principalPassword = password_hash('priyasharma', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO principals (password, email, first_name, last_name) VALUES (?, ?, ?, ?)");
    $stmt->execute([$principalPassword, 'principal@school.com', 'Priya', 'Sharma']);
    echo "✓ Principal admin created\n";
    
    // Insert sample classes first
    $stmt = $pdo->prepare("INSERT IGNORE INTO classes (name, room_number, capacity) VALUES (?, ?, ?)");
    $stmt->execute(['class 10', 101, 35]);
    $stmt->execute(['class 9', 91, 32]);
    $stmt->execute(['class 11', 111, 30]);
    echo "✓ Sample classes created\n";

    // Get class IDs
    $stmt = $pdo->prepare("SELECT id FROM classes WHERE name = ?");
    $stmt->execute(['class 10']);
    $class10AId = $stmt->fetchColumn();
    $stmt->execute(['class 9']);
    $class9BId = $stmt->fetchColumn();
    $stmt->execute(['class 11']);
    $class11AId = $stmt->fetchColumn();

    // Insert sample subjects using class IDs
    $stmt = $pdo->prepare("INSERT IGNORE INTO subjects (name, code, description, class_id) VALUES (?, ?, ?, ?)");
    $stmt->execute(['mathematics', 'MATH101', 'Advanced Mathematics including Algebra and Calculus', $class10AId]);
    $stmt->execute(['english literature', 'ENG101', 'English Literature and Composition', $class10AId]);
    $stmt->execute(['physics', 'PHY101', 'Physics with Laboratory', $class9BId]);
    echo "✓ Sample subjects created\n";
    
    // Get subject IDs
    $stmt = $pdo->prepare("SELECT id FROM subjects WHERE code IN ('MATH101', 'ENG101', 'PHY101')");
    $stmt->execute();
    $subjectIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Insert teacher admins with subject and lecturer references
    $stmt = $pdo->prepare("INSERT IGNORE INTO teachers (password, email, first_name, last_name, phone, address, subject_id, qualification, experience_years, joining_date, salary, status, class_teacher_of) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([password_hash('amitsingh', PASSWORD_DEFAULT), 'teacher1@school.com', 'Amit', 'Singh', '9876543212', '789 Lake Road, Bangalore', $subjectIds[0], 'M.Tech in Mathematics', 8, '2016-07-01', 45000.00, 'active', null]);
    $stmt->execute([password_hash('nehapatel', PASSWORD_DEFAULT), 'teacher2@school.com', 'Neha', 'Patel', '9876543213', '321 Garden Street, Chennai', $subjectIds[1], 'M.A. in English Literature', 6, '2018-06-15', 42000.00, 'active', null]);
    $stmt->execute([password_hash('vikramgupta', PASSWORD_DEFAULT), 'teacher3@school.com', 'Vikram', 'Gupta', '9876543214', '654 Hill View, Kolkata', $subjectIds[2], 'M.Sc in Physics', 7, '2017-08-01', 48000.00, 'active', null]);
    echo "✓ Teacher records created\n";
    
    // Get teacher IDs
    $stmt = $pdo->prepare("SELECT id FROM teachers WHERE email IN (?, ?, ?)");
    $stmt->execute(['teacher1@school.com', 'teacher2@school.com', 'teacher3@school.com']);
    $teacherIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Insert sample exams
    $stmt = $pdo->prepare("INSERT IGNORE INTO exams (name, subject_id, class_id, date, start_time, end_time, total_marks, exam_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Mathematics Midterm', $subjectIds[0], $class10AId, '2024-01-30', '09:00:00', '11:00:00', 100, 'midterm']);
    $stmt->execute(['English Literature Final', $subjectIds[1], $class10AId, '2024-02-05', '10:00:00', '12:00:00', 50, 'final']);
    $stmt->execute(['Physics Final Exam', $subjectIds[2], $class9BId, '2024-02-15', '14:00:00', '16:00:00', 100, 'final']);
    echo "✓ Sample exams created\n";
    
    // Get exam IDs
    $stmt = $pdo->prepare("SELECT id FROM exams WHERE name = ?");
    $stmt->execute(['Mathematics Midterm']);
    $mathExamId = $stmt->fetchColumn();
    $stmt->execute(['English Literature Quiz']);
    $englishExamId = $stmt->fetchColumn();
    
    // Insert sample students
    $stmt = $pdo->prepare("INSERT IGNORE INTO students (password, email, first_name, last_name, date_of_birth, gender, address, phone, parent_name, parent_phone, parent_email, class_id, roll_number, admission_date, blood_group, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        password_hash('arjunreddy', PASSWORD_DEFAULT),
        'student1@school.com',
        'Arjun',
        'Reddy',
        '2007-05-10',
        'male',
        '123 Main Street, Hyderabad',
        '9876543210',
        'Ramesh Reddy',
        '9876543211',
        'parent1@school.com',
        $class10AId,
        1001,
        '2020-06-15',
        'B+',
        'active'
    ]);
    $stmt->execute([
        password_hash('zarakhan', PASSWORD_DEFAULT),
        'student2@school.com',
        'Zara',
        'Khan',
        '2008-08-22',
        'female',
        '456 Park Avenue, Mumbai',
        '9876543215',
        'Imran Khan',
        '9876543216',
        'parent2@school.com',
        $class9BId,
        1002,
        '2021-06-10',
        'O+',
        'active'
    ]);
    $stmt->execute([
        password_hash('ishaanverma', PASSWORD_DEFAULT),
        'student3@school.com',
        'Ishaan',
        'Verma',
        '2007-12-05',
        'male',
        '789 Green Lane, Delhi',
        '9876543217',
        'Suresh Verma',
        '9876543218',
        'parent3@school.com',
        $class11AId,
        1003,
        '2022-06-12',
        'A+',
        'active'
    ]);
    echo "✓ Sample students created\n";
    
    // Get student IDs
    $stmt = $pdo->prepare("SELECT id FROM students WHERE email = ?");
    $stmt->execute(['student1@school.com']);
    $student1Id = $stmt->fetchColumn();
    $stmt->execute(['student2@school.com']);
    $student2Id = $stmt->fetchColumn();
    
    // Insert sample attendance
    $stmt = $pdo->prepare("INSERT IGNORE INTO attendance (student_id, class_id, date, status, marked_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$student1Id, $class10AId, '2024-01-15', 'present', $teacherIds[0]]);
    $stmt->execute([$student2Id, $class10AId, '2024-01-15', 'present', $teacherIds[0]]);
    $stmt->execute([$student1Id, $class10AId, '2024-01-16', 'absent', $teacherIds[0]]);
    $stmt->execute([$student2Id, $class10AId, '2024-01-16', 'present', $teacherIds[0]]);
    echo "✓ Sample attendance created\n";
    
    // Insert sample assignments (updated to use teacher_id instead of created_by)
    $stmt = $pdo->prepare("INSERT IGNORE INTO assignments (title, description, subject_id, class_id, due_date, total_marks, teacher_id, type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Algebra Problem Set 1', 'Solving linear equations and inequalities', $subjectIds[0], $class10AId, '2024-01-20', 100, $teacherIds[0], 'quiz', 'completed']);
    $stmt->execute(['Shakespeare Essay', 'Analysis of Hamlet soliloquy', $subjectIds[1], $class10AId, '2024-01-25', 50, $teacherIds[1], 'project', 'running']);
    $stmt->execute(['Physics Lab Report', 'Experiment on Newton Laws', $subjectIds[2], $class9BId, '2024-01-18', 75, $teacherIds[2], 'project', 'coming']);
    echo "✓ Sample assignments created\n";
    
    // Get assignment IDs
    $stmt = $pdo->prepare("SELECT id FROM assignments WHERE title = ?");
    $stmt->execute(['Algebra Problem Set 1']);
    $mathAssignmentId = $stmt->fetchColumn();
    $stmt->execute(['Shakespeare Essay']);
    $englishAssignmentId = $stmt->fetchColumn();
    
    // Insert sample student assignments
    $stmt = $pdo->prepare("INSERT IGNORE INTO student_assignments (assignment_id, student_id, submitted_date, marks_obtained, feedback, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$mathAssignmentId, $student1Id, '2024-01-18 15:30:00', 85, 'Good work, but show more steps', 'graded']);
    $stmt->execute([$englishAssignmentId, $student2Id, '2024-01-23 20:15:00', 92, 'Excellent analysis and writing', 'graded']);
    echo "✓ Sample student assignments created\n";
    
    // Insert sample lecturers (now as weekly schedule)
    $stmt = $pdo->prepare("INSERT IGNORE INTO lecturers (subject_id, teacher_id, class_id, day_of_week, start_time, end_time, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$subjectIds[0], $teacherIds[0], $class10AId, 'Monday', '08:00:00', '09:00:00', 'incoming']);
    $stmt->execute([$subjectIds[1], $teacherIds[1], $class10AId, 'Tuesday', '09:00:00', '10:00:00', 'incoming']);
    $stmt->execute([$subjectIds[2], $teacherIds[2], $class9BId, 'Wednesday', '10:00:00', '11:00:00', 'incoming']);
    echo "✓ Sample lecturer sessions created\n";
    
    // After class IDs are fetched, update teachers to set class_teacher_of
    $stmt = $pdo->prepare("UPDATE teachers SET class_teacher_of = ? WHERE email = ?");
    $stmt->execute([$class10AId, 'teacher1@school.com']);
    $stmt->execute([$class9BId, 'teacher2@school.com']);
    $stmt->execute([$class11AId, 'teacher3@school.com']);
    echo "✓ Teachers updated with class_teacher_of\n";
    
} catch (PDOException $e) {
    echo "✗ Error inserting sample data: " . $e->getMessage() . "\n";
}

echo "\n🎉 Database setup completed successfully!\n\n";
echo "Database Structure:\n";
echo "- Principals table: Separate table for school principals\n";
echo "- Teachers table: Separate table for teachers with login credentials (using id as teacher ID)\n";
echo "- Students table: Separate table for students with login credentials (using id as student ID, roll_number auto-increment)\n";
echo "- Assignments table: Updated to use teacher_id instead of created_by\n";
echo "- Complete academic management system with attendance, grades, exams, etc.\n\n";
echo "Default login credentials:\n";
echo "Principal: principal@school.com / priyasharma\n";
echo "Teachers: teacher1@school.com / amitsingh, teacher2@school.com / nehapatel, teacher3@school.com / vikramgupta\n";
echo "Students: student1@school.com / arjunreddy, student2@school.com / zarakhan, student3@school.com / ishaanverma\n\n";
echo "Sample Data Features:\n";
echo "- Indian names: Arjun Reddy, Zara Khan, Ishaan Verma\n";
echo "- Teachers: Amit Singh, Neha Patel, Vikram Gupta\n";
echo "- Principal: Priya Sharma\n";
echo "- Subjects: Mathematics, English Literature, Physics\n";
echo "- Classes: Class 9, 10, 11 with sections\n";
echo "- Complete attendance, grades, exams, assignments, and schedule data\n";
echo "- Students use email for login, roll_number is auto-increment\n\n";
echo "You can now access the system at: http://localhost:8000/frontend/index.html\n";
?> 