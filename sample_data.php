<?php
/**
 * Sample Data Insertion Script for School Management System
 * Inserts comprehensive Indian school data into all tables for testing
 * Run this after setup_database.php
 */

$host = '127.0.0.1';
$dbname = 'school_management';
$username = 'root';
$password = '12345';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    echo "Connected to database.\n";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

// 1. Principals
$principalPassword = password_hash('priyasharma', PASSWORD_DEFAULT);
$pdo->prepare("INSERT IGNORE INTO principals (password, email, first_name, last_name, phone, address, qualification, joining_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
    ->execute([$principalPassword, 'principal@school.com', 'Priya', 'Sharma', '9876543200', 'Delhi Public School, Sector 45, Gurgaon', 'M.Ed, PhD', '2015-04-01']);

// 2. Classes (10 classes: Class 1 to Class 10)
$classes = [
    ['Class 1', 101, 40],
    ['Class 2', 102, 38],
    ['Class 3', 103, 42],
    ['Class 4', 104, 39],
    ['Class 5', 105, 41],
    ['Class 6', 106, 40],
    ['Class 7', 107, 38],
    ['Class 8', 108, 40],
    ['Class 9', 109, 37],
    ['Class 10', 110, 39],
];
foreach ($classes as $c) {
    $pdo->prepare("INSERT IGNORE INTO classes (name, room_number, capacity) VALUES (?, ?, ?)")->execute($c);
}

// Fetch class IDs
$classIds = [];
foreach ($classes as $c) {
    $stmt = $pdo->prepare("SELECT id FROM classes WHERE name = ?");
    $stmt->execute([$c[0]]);
    $classIds[$c[0]] = $stmt->fetchColumn();
}

// 3. Subjects (7 subjects)
$subjects = [
    ['Mathematics', 'MATH', 'Mathematics for all classes'],
    ['English', 'ENG', 'English for all classes'],
    ['Science', 'SCI', 'Science for all classes'],
    ['Hindi', 'HIN', 'Hindi for all classes'],
    ['Social Studies', 'SST', 'Social Studies for all classes'],
    ['Computer Science', 'CS', 'Computer Science for all classes'],
    ['Business Education', 'BE', 'Business Education for all classes'], // replaced Physical Education
];
foreach ($subjects as $s) {
    $pdo->prepare("INSERT IGNORE INTO subjects (name, code, description) VALUES (?, ?, ?)")->execute($s);
}

// Fetch subject IDs
$subjectIds = [];
foreach ($subjects as $s) {
    $stmt = $pdo->prepare("SELECT id FROM subjects WHERE code = ?");
    $stmt->execute([$s[1]]);
    $subjectIds[$s[1]] = $stmt->fetchColumn();
}

// Custom subject-class mapping
$classNameToId = array_flip($classIds); // for reverse lookup
$subjectClassMap = [
    'MATH' => ['Class 1', 'Class 2', 'Class 3', 'Class 4'],
    'ENG' => ['Class 1', 'Class 2', 'Class 3', 'Class 4', 'Class 8', 'Class 9', 'Class 10'],
    'HIN' => ['Class 1', 'Class 2', 'Class 3', 'Class 4'],
    'SST' => ['Class 5', 'Class 6', 'Class 7', 'Class 8'],
    'SCI' => ['Class 8', 'Class 9', 'Class 10'],
    'CS'  => ['Class 8', 'Class 9', 'Class 10'],
    'BE'  => ['Class 8', 'Class 9', 'Class 10'],
];
foreach ($subjectClassMap as $subjectCode => $classNames) {
    $subjectId = $subjectIds[$subjectCode];
    foreach ($classNames as $className) {
        $classId = $classIds[$className];
        $pdo->prepare("INSERT IGNORE INTO subject_classes (subject_id, class_id) VALUES (?, ?)")->execute([$subjectId, $classId]);
    }
}

// 4. Teachers (10 teachers with specific class assignments)
$teachers = [
    ['teacher1@school.com', 'amitsingh', 'Amit', 'Singh', '9876543212', 'Bangalore', $subjectIds['MATH'], 'B.Sc Mathematics', 5, '2018-06-01', 35000, 'active', $classIds['Class 1']], // Teacher 1: Class 1, 2, 3
    ['teacher2@school.com', 'nehapatel', 'Neha', 'Patel', '9876543213', 'Chennai', $subjectIds['ENG'], 'B.A. English', 6, '2017-07-15', 36000, 'active', $classIds['Class 2']], // Teacher 2: Class 2, 4
    ['teacher3@school.com', 'vikramgupta', 'Vikram', 'Gupta', '9876543214', 'Kolkata', $subjectIds['SCI'], 'B.Sc Science', 7, '2016-08-01', 37000, 'active', $classIds['Class 3']], // Teacher 3: Class 3, 5
    ['teacher4@school.com', 'sunitajain', 'Sunita', 'Jain', '9876543215', 'Mumbai', $subjectIds['HIN'], 'B.A. Hindi', 8, '2015-05-10', 38000, 'active', $classIds['Class 4']], // Teacher 4: Class 4, 6
    ['teacher5@school.com', 'rajivmehra', 'Rajiv', 'Mehra', '9876543216', 'Delhi', $subjectIds['SST'], 'B.A. Social', 9, '2014-09-20', 39000, 'active', $classIds['Class 5']], // Teacher 5: Class 5, 7
    ['teacher6@school.com', 'priyagupta', 'Priya', 'Gupta', '9876543217', 'Pune', $subjectIds['CS'], 'B.Tech Computer Science', 10, '2013-06-01', 40000, 'active', $classIds['Class 6']], // Teacher 6: Class 6, 8
    ['teacher7@school.com', 'manishkumar', 'Manish', 'Kumar', '9876543218', 'Lucknow', $subjectIds['BE'], 'B.P.Ed', 11, '2012-07-15', 41000, 'active', $classIds['Class 7']], // Teacher 7: Class 7, 9
    ['teacher8@school.com', 'deepashah', 'Deepa', 'Shah', '9876543219', 'Ahmedabad', $subjectIds['MATH'], 'M.Sc Mathematics', 12, '2011-08-01', 42000, 'active', $classIds['Class 8']], // Teacher 8: Class 8, 10
    ['teacher9@school.com', 'sureshchandra', 'Suresh', 'Chandra', '9876543220', 'Hyderabad', $subjectIds['ENG'], 'M.A. English', 13, '2010-05-10', 43000, 'active', $classIds['Class 9']], // Teacher 9: Class 9
    ['teacher10@school.com', 'anitaroy', 'Anita', 'Roy', '9876543221', 'Jaipur', $subjectIds['SCI'], 'M.Sc Physics', 14, '2009-09-20', 44000, 'active', $classIds['Class 10']], // Teacher 10: Class 10
];
foreach ($teachers as $t) {
    $pdo->prepare("INSERT IGNORE INTO teachers (password, email, first_name, last_name, phone, address, subject_id, qualification, experience_years, joining_date, salary, status, class_teacher_of) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
        ->execute([
            password_hash($t[1], PASSWORD_DEFAULT), $t[0], $t[2], $t[3], $t[4], $t[5], $t[6], $t[7], $t[8], $t[9], $t[10], $t[11], $t[12]
        ]);
}
// Fetch teacher IDs
$teacherIds = [];
foreach ($teachers as $t) {
    $stmt = $pdo->prepare("SELECT id FROM teachers WHERE email = ?");
    $stmt->execute([$t[0]]);
    $teacherIds[$t[0]] = $stmt->fetchColumn();
}

// 5. Students (5 students per class + 10 extra for Class 1, admission dates: today to minus 5 days)
$students = [];
$firstNames = ['Arjun', 'Zara', 'Ishaan', 'Megha', 'Rohan', 'Simran', 'Kabir', 'Aanya', 'Dev', 'Tara', 'Yash', 'Riya', 'Aarav', 'Anaya', 'Vivaan', 'Diya', 'Aditya', 'Saanvi', 'Krishna', 'Myra', 'Dhruv', 'Kiara', 'Aryan', 'Navya', 'Parth', 'Rahul', 'Priya', 'Amit', 'Neha', 'Vikram', 'Sunita', 'Rajiv', 'Deepa', 'Suresh', 'Anita', 'Alok', 'Meena', 'Prakash', 'Kavita', 'Sanjay', 'Rekha', 'Mohan', 'Geeta', 'Ramesh', 'Lakshmi', 'Suresh', 'Radha', 'Gopal', 'Sita', 'Ram', 'Krishna', 'Radha', 'Balram', 'Subhadra', 'Jagannath', 'Lakshmi', 'Saraswati', 'Ganesh', 'Durga', 'Kali', 'Hanuman', 'Vishnu', 'Shiva', 'Brahma', 'Indra', 'Agni', 'Varuna', 'Vayu', 'Surya', 'Chandra', 'Mangal', 'Budh', 'Guru', 'Shukra', 'Shani', 'Rahu', 'Ketu'];
$lastNames = ['Reddy', 'Khan', 'Verma', 'Sharma', 'Joshi', 'Singh', 'Patel', 'Gupta', 'Jain', 'Mehra', 'Roy', 'Chandra', 'Kumar', 'Shah', 'Chopra', 'Bansal', 'Kapoor', 'Malhotra', 'Rastogi', 'Saxena', 'Agarwal', 'Srivastava', 'Pandey', 'Tripathi', 'Dubey', 'Mishra', 'Tiwari', 'Yadav', 'Kaur', 'Gill', 'Randhawa', 'Dhillon', 'Sidhu', 'Malhotra', 'Rastogi', 'Saxena', 'Agarwal', 'Srivastava', 'Pandey', 'Tripathi', 'Dubey', 'Mishra', 'Tiwari', 'Yadav', 'Kaur', 'Gill', 'Randhawa', 'Dhillon', 'Sidhu', 'Malhotra', 'Rastogi', 'Saxena', 'Agarwal', 'Srivastava', 'Pandey', 'Tripathi', 'Dubey', 'Mishra', 'Tiwari', 'Yadav', 'Kaur', 'Gill', 'Randhawa', 'Dhillon', 'Sidhu', 'Malhotra', 'Rastogi', 'Saxena', 'Agarwal', 'Srivastava', 'Pandey', 'Tripathi', 'Dubey', 'Mishra', 'Tiwari', 'Yadav', 'Kaur', 'Gill', 'Randhawa', 'Dhillon', 'Sidhu'];

$studentCounter = 1;

// Generate 5 students for each class (Class 1 to Class 10)
foreach ($classes as $classIndex => $class) {
    $className = $class[0];
    $classId = $classIds[$className];
    
    for ($i = 1; $i <= 5; $i++) {
        $first = $firstNames[($studentCounter-1) % count($firstNames)];
        $last = $lastNames[($studentCounter-1) % count($lastNames)];
        $email = strtolower($first.$last."$studentCounter@school.com");
        $username = strtolower($first.$last);
        $gender = ($studentCounter%2==0) ? 'female' : 'male';
        $dob = date('Y-m-d', strtotime("2005-01-01 +".($studentCounter*120).' days'));
        
        // Admission date: today to minus 5 days
        $admissionDaysAgo = ($studentCounter-1) % 6; // 0 to 5 days ago
        $admission = date('Y-m-d', strtotime("-$admissionDaysAgo days"));
        
        $students[] = [
            $email,
            $username,
            $first,
            $last,
            $dob,
            $gender,
            'City '.$studentCounter,
            '9876543'.str_pad($studentCounter,3,'0',STR_PAD_LEFT),
            'Parent '.$first,
            '9876543'.str_pad($studentCounter+100,3,'0',STR_PAD_LEFT),
            'parent'.$studentCounter.'@school.com',
            $classId,
            1000 + $studentCounter,
            $admission,
            'A+',
            'active',
        ];
        $studentCounter++;
    }
}

// Add 10 extra students for Class 1
$class1Id = $classIds['Class 1'];
for ($i = 1; $i <= 10; $i++) {
    $first = $firstNames[($studentCounter-1) % count($firstNames)];
    $last = $lastNames[($studentCounter-1) % count($lastNames)];
    $email = strtolower($first.$last."$studentCounter@school.com");
    $username = strtolower($first.$last);
    $gender = ($studentCounter%2==0) ? 'female' : 'male';
    $dob = date('Y-m-d', strtotime("2005-01-01 +".($studentCounter*120).' days'));
    
    // Admission date: today to minus 5 days
    $admissionDaysAgo = ($studentCounter-1) % 6; // 0 to 5 days ago
    $admission = date('Y-m-d', strtotime("-$admissionDaysAgo days"));
    
    $students[] = [
        $email,
        $username,
        $first,
        $last,
        $dob,
        $gender,
        'City '.$studentCounter,
        '9876543'.str_pad($studentCounter,3,'0',STR_PAD_LEFT),
        'Parent '.$first,
        '9876543'.str_pad($studentCounter+100,3,'0',STR_PAD_LEFT),
        'parent'.$studentCounter.'@school.com',
        $class1Id, // All extra students go to Class 1
        1000 + $studentCounter,
        $admission,
        'A+',
        'active',
    ];
    $studentCounter++;
}

foreach ($students as $s) {
    $pdo->prepare("INSERT IGNORE INTO students (password, email, first_name, last_name, date_of_birth, gender, address, phone, parent_name, parent_phone, parent_email, class_id, roll_number, admission_date, blood_group, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")
        ->execute([
            password_hash($s[1], PASSWORD_DEFAULT), $s[0], $s[2], $s[3], $s[4], $s[5], $s[6], $s[7], $s[8], $s[9], $s[10], $s[11], $s[12], $s[13], $s[14], $s[15]
        ]);
}
// Fetch student IDs
$studentIds = [];
foreach ($students as $s) {
    $stmt = $pdo->prepare("SELECT id FROM students WHERE email = ?");
    $stmt->execute([$s[0]]);
    $studentIds[$s[0]] = $stmt->fetchColumn();
}

// 6. Attendance (sample attendance for all students)
$attendance = [];
$currentDate = date('Y-m-d');

// Generate attendance for past 5 days for all students
for ($dayOffset = 5; $dayOffset >= 1; $dayOffset--) {
    $attendanceDate = date('Y-m-d', strtotime("-$dayOffset days"));
    
    foreach ($students as $student) {
        $studentEmail = $student[0];
        if (isset($studentIds[$studentEmail])) {
            $studentId = $studentIds[$studentEmail];
            $classId = $student[11]; // class_id
            
            // Find the class teacher for this class
            $classTeacherId = null;
            foreach ($teachers as $teacher) {
                if ($teacher[12] == $classId) { // class_teacher_of
                    $teacherEmail = $teacher[0];
                    $classTeacherId = $teacherIds[$teacherEmail];
                    break;
                }
            }
            
            if ($classTeacherId) {
                // Randomly assign present/absent (80% present, 20% absent)
                $status = (rand(1, 100) <= 80) ? 'present' : 'absent';
                
                $attendance[] = [
                    $studentId,
                    $classId,
                    $attendanceDate,
                    $status,
                    $classTeacherId
                ];
            }
        }
    }
}

foreach ($attendance as $a) {
    $pdo->prepare("INSERT IGNORE INTO attendance (student_id, class_id, date, status, marked_by) VALUES (?, ?, ?, ?, ?)")->execute($a);
}

// 7. Assignments (sample assignments for all classes)
$assignments = [];
$assignmentTitles = [
    'Mathematics Assignment', 'English Essay', 'Science Project', 'Hindi Literature', 
    'Social Studies Report', 'Computer Science Lab', 'Physical Education Test'
];

foreach ($classes as $classIndex => $class) {
    $className = $class[0];
    $classId = $classIds[$className];
    
    // Find the class teacher for this class
    $classTeacherId = null;
    foreach ($teachers as $teacher) {
        if ($teacher[12] == $classId) { // class_teacher_of
            $teacherEmail = $teacher[0];
            $classTeacherId = $teacherIds[$teacherEmail];
            break;
        }
    }
    
    if ($classTeacherId) {
        // Create assignments for each subject
        foreach ($subjects as $subjectIndex => $subject) {
            $subjectId = $subjectIds[$subject[1]]; // subject code
            $title = $assignmentTitles[$subjectIndex] . ' - ' . $className;
            $description = $subject[0] . ' assignment for ' . $className;
            $startDate = date('Y-m-d', strtotime('-2 days'));
            $dueDate = date('Y-m-d', strtotime('+5 days'));
            $totalMarks = 50 + ($subjectIndex * 10);
            $type = ($subjectIndex % 2 == 0) ? 'essays' : 'reports';
            $status = ($subjectIndex % 3 == 0) ? 'completed' : (($subjectIndex % 3 == 1) ? 'running' : 'coming');
            
            $assignments[] = [
                $title,
                $description,
                $subjectId,
                $classId,
                $startDate,
                $dueDate,
                $totalMarks,
                $classTeacherId,
                $type,
                $status
            ];
        }
    }
}

foreach ($assignments as $a) {
    $pdo->prepare("INSERT IGNORE INTO assignments (title, description, subject_id, class_id, start_date, due_date, total_marks, teacher_id, type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")->execute($a);
}

// 8. Student Assignments (sample submissions)
$studentAssignments = [];
foreach ($assignments as $assignmentIndex => $assignment) {
    // Get students for this class
    $classId = $assignment[3]; // class_id from assignment
    $classStudents = array_filter($students, function($student) use ($classId) {
        return $student[11] == $classId;
    });
    
    // Take first 3 students from each class for submissions
    $count = 0;
    foreach ($classStudents as $student) {
        if ($count >= 3) break;
        
        $studentEmail = $student[0];
        if (isset($studentIds[$studentEmail])) {
            $studentId = $studentIds[$studentEmail];
            $submittedDate = date('Y-m-d H:i:s', strtotime('-1 day'));
            $marksObtained = rand(60, 95);
            $feedback = 'Good work on ' . $assignment[0];
            $status = 'graded';
            
            $studentAssignments[] = [
                $assignmentIndex + 1, // assignment_id (assuming sequential insertion)
                $studentId,
                $submittedDate,
                $marksObtained,
                null, // No text submission
                null, // No file submission
                $status
            ];
        }
        $count++;
    }
}

foreach ($studentAssignments as $sa) {
    $pdo->prepare("INSERT IGNORE INTO student_assignments (assignment_id, student_id, submitted_date, marks_obtained, submitted_text, submitted_file, status) VALUES (?, ?, ?, ?, ?, ?, ?)")->execute($sa);
}

// 9. Exams (1 exam per class for today + 5 extra exams for Class 1)
$exams = [];
$currentTime = date('H:i:s');
$startTime = date('H:i:s', strtotime('+5 minutes'));
$endTime = date('H:i:s', strtotime('+30 minutes'));
$today = date('Y-m-d');

// 1 exam per class for today
foreach ($classes as $classIndex => $class) {
    $className = $class[0];
    $classId = $classIds[$className];
    $subjectId = $subjectIds['MATH']; // Default to Mathematics
    $examType = ($classIndex % 2 == 0) ? 'midterm' : 'final';
    $totalMarks = 50 + ($classIndex * 5);
    
    $exams[] = [
        'Mathematics ' . ucfirst($examType) . ' - ' . $className,
        $subjectId,
        $classId,
        $today,
        $startTime,
        $endTime,
        $totalMarks,
        $examType,
        'ongoing'
    ];
}

// 5 extra exams for Class 1 (from tomorrow onwards)
$class1Id = $classIds['Class 1'];
$class1SubjectIds = [$subjectIds['MATH'], $subjectIds['ENG'], $subjectIds['SCI'], $subjectIds['HIN'], $subjectIds['SST']];
$class1ExamTypes = ['midterm', 'final', 'midterm', 'final', 'midterm'];

for ($i = 1; $i <= 5; $i++) {
    $examDate = date('Y-m-d', strtotime("+$i days"));
    $subjectId = $class1SubjectIds[($i-1) % count($class1SubjectIds)];
    $examType = $class1ExamTypes[($i-1) % count($class1ExamTypes)];
    $subjectName = '';
    
    // Get subject name
    foreach ($subjects as $subject) {
        if ($subjectIds[$subject[1]] == $subjectId) {
            $subjectName = $subject[0];
            break;
        }
    }
    
    $exams[] = [
        $subjectName . ' ' . ucfirst($examType) . ' - Class 1',
        $subjectId,
        $class1Id,
        $examDate,
        $startTime,
        $endTime,
        50 + ($i * 5),
        $examType,
        'scheduled'
    ];
}

foreach ($exams as $e) {
    $pdo->prepare("INSERT IGNORE INTO exams (name, subject_id, class_id, date, start_time, end_time, total_marks, exam_type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")->execute($e);
}

// 10. Lecturers (weekly schedule)
$lecturers = [];
$currentWeekStart = date('Y-m-d', strtotime('monday this week'));
$currentWeekEnd = date('Y-m-d', strtotime('friday this week'));

// Generate lectures for each class for the current week
foreach ($classes as $classIndex => $class) {
    $className = $class[0];
    $classId = $classIds[$className];
    
    // Find the class teacher
    $classTeacherId = null;
    foreach ($teachers as $teacher) {
        if ($teacher[12] == $classId) { // class_teacher_of
            $teacherEmail = $teacher[0];
            $classTeacherId = $teacherIds[$teacherEmail];
            break;
        }
    }
    
    if ($classTeacherId) {
        // Generate lectures for each day of the week
        for ($dayOffset = 0; $dayOffset < 5; $dayOffset++) {
            $lectureDate = date('Y-m-d', strtotime("monday this week +$dayOffset days"));
            
            // Skip today's date for scheduled lectures
            if ($lectureDate <= $today) {
                $lectureDate = date('Y-m-d', strtotime("monday next week +$dayOffset days"));
            }
            
            $startHour = 8 + ($dayOffset % 3); // 8, 9, 10, 8, 9
            $startTime = sprintf('%02d:00:00', $startHour);
            $endTime = sprintf('%02d:00:00', $startHour + 1);
            
            $lecturers[] = [
                $subjectIds['MATH'], // Default subject
                $classTeacherId,
                $classId,
                $lectureDate,
                $startTime,
                $endTime,
                'scheduled'
            ];
        }
    }
}

foreach ($lecturers as $l) {
    $pdo->prepare("INSERT IGNORE INTO lecturers (subject_id, teacher_id, class_id, date, start_time, end_time, status) VALUES (?, ?, ?, ?, ?, ?, ?)")->execute($l);
}

// 11. Teacher Classes (many-to-many relationships)
$teacherClasses = [];

// Teacher 1 teaches Class 1, 2, 3
$teacherClasses[] = [$teacherIds['teacher1@school.com'], $classIds['Class 1']];
$teacherClasses[] = [$teacherIds['teacher1@school.com'], $classIds['Class 2']];
$teacherClasses[] = [$teacherIds['teacher1@school.com'], $classIds['Class 3']];

// Teacher 2 teaches Class 2, 4
$teacherClasses[] = [$teacherIds['teacher2@school.com'], $classIds['Class 2']];
$teacherClasses[] = [$teacherIds['teacher2@school.com'], $classIds['Class 4']];

// Teacher 3 teaches Class 3, 5
$teacherClasses[] = [$teacherIds['teacher3@school.com'], $classIds['Class 3']];
$teacherClasses[] = [$teacherIds['teacher3@school.com'], $classIds['Class 5']];

// Teacher 4 teaches Class 4, 6
$teacherClasses[] = [$teacherIds['teacher4@school.com'], $classIds['Class 4']];
$teacherClasses[] = [$teacherIds['teacher4@school.com'], $classIds['Class 6']];

// Teacher 5 teaches Class 5, 7
$teacherClasses[] = [$teacherIds['teacher5@school.com'], $classIds['Class 5']];
$teacherClasses[] = [$teacherIds['teacher5@school.com'], $classIds['Class 7']];

// Teacher 6 teaches Class 6, 8
$teacherClasses[] = [$teacherIds['teacher6@school.com'], $classIds['Class 6']];
$teacherClasses[] = [$teacherIds['teacher6@school.com'], $classIds['Class 8']];

// Teacher 7 teaches Class 7, 9
$teacherClasses[] = [$teacherIds['teacher7@school.com'], $classIds['Class 7']];
$teacherClasses[] = [$teacherIds['teacher7@school.com'], $classIds['Class 9']];

// Teacher 8 teaches Class 8, 10
$teacherClasses[] = [$teacherIds['teacher8@school.com'], $classIds['Class 8']];
$teacherClasses[] = [$teacherIds['teacher8@school.com'], $classIds['Class 10']];

// Teacher 9 teaches Class 9
$teacherClasses[] = [$teacherIds['teacher9@school.com'], $classIds['Class 9']];

// Teacher 10 teaches Class 10
$teacherClasses[] = [$teacherIds['teacher10@school.com'], $classIds['Class 10']];

// Add class_teacher_of assignments to teacher_classes table
foreach ($teachers as $t) {
    $teacherEmail = $t[0];
    $classTeacherOf = $t[12]; // class_teacher_of value
    if ($classTeacherOf) {
        // Add this teacher-class combination to teacher_classes table
        $teacherClasses[] = [$teacherIds[$teacherEmail], $classTeacherOf];
    }
}

foreach ($teacherClasses as $tc) {
    $pdo->prepare("INSERT IGNORE INTO teacher_classes (teacher_id, class_id) VALUES (?, ?)")->execute($tc);
}

echo "Sample data inserted successfully!\n";
echo "\nDefault login credentials:\n";
echo "Principal: principal@school.com / priyasharma\n";
echo "Teachers: teacher1@school.com / amitsingh, teacher2@school.com / nehapatel, teacher3@school.com / vikramgupta, teacher4@school.com / sunitajain, teacher5@school.com / rajivmehra\n";
echo "Students: Various students with email format: firstnamelastname@school.com\n\n";
echo "Sample Data Features:\n";
echo "- 10 Classes: Class 1 to Class 10\n";
echo "- 5 students per class + 10 extra students for Class 1 (total 60 students)\n";
echo "- Admission dates: today to minus 5 days\n";
echo "- 7 Subjects: Mathematics, English, Science, Hindi, Social Studies, Computer Science, Physical Education\n";
echo "- 10 Teachers with specific class assignments\n";
echo "- Teacher 1 teaches Class 1, 2, and 3\n";
echo "- 1 exam per class for today with current time + 5 min start, + 30 min end\n";
echo "- 5 extra exams for Class 1 from tomorrow onwards\n";
echo "- Complete attendance, assignments, and schedule data\n";
echo "- Students use email for login, roll_number is unique\n\n"; 