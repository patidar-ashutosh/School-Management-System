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

// 2. Classes (expanded to 15)
$classes = [
    ['Class 1A', 101, 40],
    ['Class 1B', 102, 38],
    ['Class 2A', 103, 42],
    ['Class 2B', 104, 39],
    ['Class 3A', 105, 41],
    ['Class 4A', 106, 40],
    ['Class 5A', 107, 38],
    ['Class 6A', 108, 40],
    ['Class 7A', 109, 37],
    ['Class 8A', 110, 39],
    ['Class 9A', 111, 42],
    ['Class 10A', 112, 40],
    ['Class 10B', 113, 38],
    ['Class 11 Science', 114, 35],
    ['Class 12 Commerce', 115, 30],
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

// 3. Subjects (expanded to 15)
$subjects = [
    ['Mathematics', 'MATH1A', 'Math for Class 1A', $classIds['Class 1A']],
    ['English', 'ENG1A', 'English for Class 1A', $classIds['Class 1A']],
    ['Science', 'SCI2A', 'Science for Class 2A', $classIds['Class 2A']],
    ['Hindi', 'HIN3A', 'Hindi for Class 3A', $classIds['Class 3A']],
    ['Social Studies', 'SST4A', 'Social Studies for Class 4A', $classIds['Class 4A']],
    ['Mathematics', 'MATH5A', 'Math for Class 5A', $classIds['Class 5A']],
    ['English', 'ENG6A', 'English for Class 6A', $classIds['Class 6A']],
    ['Science', 'SCI7A', 'Science for Class 7A', $classIds['Class 7A']],
    ['Hindi', 'HIN8A', 'Hindi for Class 8A', $classIds['Class 8A']],
    ['Social Studies', 'SST9A', 'Social Studies for Class 9A', $classIds['Class 9A']],
    ['Mathematics', 'MATH10A', 'Math for Class 10A', $classIds['Class 10A']],
    ['English', 'ENG10B', 'English for Class 10B', $classIds['Class 10B']],
    ['Physics', 'PHY11S', 'Physics for Class 11 Science', $classIds['Class 11 Science']],
    ['Accountancy', 'ACC12C', 'Accountancy for Class 12 Commerce', $classIds['Class 12 Commerce']],
    ['Business Studies', 'BST12C', 'Business Studies for Class 12 Commerce', $classIds['Class 12 Commerce']],
];
foreach ($subjects as $s) {
    $pdo->prepare("INSERT IGNORE INTO subjects (name, code, description, class_id) VALUES (?, ?, ?, ?)")->execute($s);
}

// Fetch subject IDs
$subjectIds = [];
foreach ($subjects as $s) {
    $stmt = $pdo->prepare("SELECT id FROM subjects WHERE code = ?");
    $stmt->execute([$s[1]]);
    $subjectIds[$s[1]] = $stmt->fetchColumn();
}

// 4. Teachers (expanded to 12)
$teachers = [
    ['teacher1@school.com', 'amitsingh', 'Amit', 'Singh', '9876543212', 'Bangalore', $subjectIds['MATH1A'], 'B.Sc Mathematics', 5, '2018-06-01', 35000, 'active', $classIds['Class 1A']],
    ['teacher2@school.com', 'nehapatel', 'Neha', 'Patel', '9876543213', 'Chennai', $subjectIds['ENG1A'], 'B.A. English', 6, '2017-07-15', 36000, 'active', $classIds['Class 1A']],
    ['teacher3@school.com', 'vikramgupta', 'Vikram', 'Gupta', '9876543214', 'Kolkata', $subjectIds['SCI2A'], 'B.Sc Science', 7, '2016-08-01', 37000, 'active', $classIds['Class 2A']],
    ['teacher4@school.com', 'sunitajain', 'Sunita', 'Jain', '9876543215', 'Mumbai', $subjectIds['HIN3A'], 'B.A. Hindi', 8, '2015-05-10', 38000, 'active', $classIds['Class 3A']],
    ['teacher5@school.com', 'rajivmehra', 'Rajiv', 'Mehra', '9876543216', 'Delhi', $subjectIds['SST4A'], 'B.A. Social', 9, '2014-09-20', 39000, 'active', $classIds['Class 4A']],
    ['teacher6@school.com', 'priyagupta', 'Priya', 'Gupta', '9876543217', 'Pune', $subjectIds['MATH5A'], 'B.Sc Mathematics', 10, '2013-06-01', 40000, 'active', $classIds['Class 5A']],
    ['teacher7@school.com', 'manishkumar', 'Manish', 'Kumar', '9876543218', 'Lucknow', $subjectIds['ENG6A'], 'B.A. English', 11, '2012-07-15', 41000, 'active', $classIds['Class 6A']],
    ['teacher8@school.com', 'deepashah', 'Deepa', 'Shah', '9876543219', 'Ahmedabad', $subjectIds['SCI7A'], 'B.Sc Science', 12, '2011-08-01', 42000, 'active', $classIds['Class 7A']],
    ['teacher9@school.com', 'sureshchandra', 'Suresh', 'Chandra', '9876543220', 'Hyderabad', $subjectIds['HIN8A'], 'B.A. Hindi', 13, '2010-05-10', 43000, 'active', $classIds['Class 8A']],
    ['teacher10@school.com', 'anitaroy', 'Anita', 'Roy', '9876543221', 'Jaipur', $subjectIds['SST9A'], 'B.A. Social', 14, '2009-09-20', 44000, 'active', $classIds['Class 9A']],
    ['teacher11@school.com', 'alokverma', 'Alok', 'Verma', '9876543222', 'Bhopal', $subjectIds['MATH10A'], 'M.Sc Mathematics', 15, '2014-06-01', 50000, 'active', $classIds['Class 10A']],
    ['teacher12@school.com', 'meenasingh', 'Meena', 'Singh', '9876543223', 'Surat', $subjectIds['PHY11S'], 'M.Sc Physics', 16, '2013-09-20', 52000, 'active', $classIds['Class 11 Science']],
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

// 5. Students (expanded to 25)
$students = [];
for ($i = 1; $i <= 25; $i++) {
    $classIndex = ($i - 1) % count($classes);
    $className = $classes[$classIndex][0];
    $classId = $classIds[$className];
    $roll = 1000 + $i;
    $firstNames = ['Arjun', 'Zara', 'Ishaan', 'Megha', 'Rohan', 'Simran', 'Kabir', 'Aanya', 'Dev', 'Tara', 'Yash', 'Riya', 'Aarav', 'Anaya', 'Vivaan', 'Diya', 'Aditya', 'Saanvi', 'Krishna', 'Myra', 'Dhruv', 'Kiara', 'Aryan', 'Navya', 'Parth'];
    $lastNames = ['Reddy', 'Khan', 'Verma', 'Sharma', 'Joshi', 'Singh', 'Patel', 'Gupta', 'Jain', 'Mehra', 'Roy', 'Chandra', 'Kumar', 'Shah', 'Chopra', 'Bansal', 'Kapoor', 'Malhotra', 'Rastogi', 'Saxena', 'Agarwal', 'Srivastava', 'Pandey', 'Tripathi', 'Dubey'];
    $first = $firstNames[($i-1)%count($firstNames)];
    $last = $lastNames[($i-1)%count($lastNames)];
    $email = strtolower($first.$last."$i@school.com");
    $username = strtolower($first.$last);
    $gender = ($i%2==0) ? 'female' : 'male';
    $dob = date('Y-m-d', strtotime("2005-01-01 +".($i*120).' days'));
    $admission = date('Y-m-d', strtotime("2018-06-01 +".($i*30).' days'));
    $students[] = [
        $email,
        $username,
        $first,
        $last,
        $dob,
        $gender,
        'City '.$i,
        '9876543'.str_pad($i,3,'0',STR_PAD_LEFT),
        'Parent '.$first,
        '9876543'.str_pad($i+100,3,'0',STR_PAD_LEFT),
        'parent'.$i.'@school.com',
        $classId,
        $roll,
        $admission,
        'A+',
        'active',
    ];
}

// Add 16 students specifically to Class 1A
$class1AId = $classIds['Class 1A'];
$additionalFirstNames = ['Rahul', 'Priya', 'Amit', 'Neha', 'Vikram', 'Sunita', 'Rajiv', 'Deepa', 'Suresh', 'Anita', 'Alok', 'Meena', 'Prakash', 'Kavita', 'Sanjay', 'Rekha'];
$additionalLastNames = ['Malhotra', 'Rastogi', 'Saxena', 'Agarwal', 'Srivastava', 'Pandey', 'Tripathi', 'Dubey', 'Mishra', 'Tiwari', 'Yadav', 'Kaur', 'Gill', 'Randhawa', 'Dhillon', 'Sidhu'];

for ($i = 26; $i <= 41; $i++) {
    $first = $additionalFirstNames[$i-26];
    $last = $additionalLastNames[$i-26];
    $email = strtolower($first.$last."$i@school.com");
    $username = strtolower($first.$last);
    $gender = ($i%2==0) ? 'female' : 'male';
    $dob = date('Y-m-d', strtotime("2005-01-01 +".($i*120).' days'));
    $admission = date('Y-m-d', strtotime("2018-06-01 +".($i*30).' days'));
    $students[] = [
        $email,
        $username,
        $first,
        $last,
        $dob,
        $gender,
        'City '.$i,
        '9876543'.str_pad($i,3,'0',STR_PAD_LEFT),
        'Parent '.$first,
        '9876543'.str_pad($i+100,3,'0',STR_PAD_LEFT),
        'parent'.$i.'@school.com',
        $class1AId, // All 16 additional students go to Class 1A
        1000 + $i,
        $admission,
        'A+',
        'active',
    ];
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

// 6. Attendance (varied: present/absent, different days, different teachers)
$attendance = [
    [$studentIds['arjunreddy1@school.com'], $classIds['Class 10A'], '2024-07-01', 'present', $teacherIds['teacher1@school.com']],
    [$studentIds['zarakhan2@school.com'], $classIds['Class 10B'], '2024-07-01', 'absent', $teacherIds['teacher2@school.com']],
    [$studentIds['ishaanverma3@school.com'], $classIds['Class 9A'], '2024-07-01', 'present', $teacherIds['teacher3@school.com']],
    [$studentIds['meghasharma4@school.com'], $classIds['Class 11 Science'], '2024-07-01', 'present', $teacherIds['teacher5@school.com']],
    [$studentIds['rohanjoshi5@school.com'], $classIds['Class 12 Commerce'], '2024-07-01', 'absent', $teacherIds['teacher4@school.com']],
];

// Add attendance data for current date and past 5 days for Class 1A students
$currentDate = date('Y-m-d');

// Filter only Class 1A students from the students array
$class1AStudents = [];
foreach ($students as $student) {
    if ($student[11] == $classIds['Class 1A']) { // class_id is at index 11
        $class1AStudents[] = $student;
    }
}

// Get Class 1A teacher ID (teacher1@school.com)
$class1ATeacherId = $teacherIds['teacher1@school.com'];

// Generate attendance for past 5 days (excluding current date)
for ($dayOffset = 5; $dayOffset >= 1; $dayOffset--) {
    $attendanceDate = date('Y-m-d', strtotime("-$dayOffset days"));
    
    foreach ($class1AStudents as $student) {
        $studentEmail = $student[0];
        if (isset($studentIds[$studentEmail])) {
            $studentId = $studentIds[$studentEmail];
            // Randomly assign present/absent (80% present, 20% absent)
            $status = (rand(1, 100) <= 80) ? 'present' : 'absent';
            
            $attendance[] = [
                $studentId,
                $classIds['Class 1A'],
                $attendanceDate,
                $status,
                $class1ATeacherId
            ];
        }
    }
}

foreach ($attendance as $a) {
    $pdo->prepare("INSERT IGNORE INTO attendance (student_id, class_id, date, status, marked_by) VALUES (?, ?, ?, ?, ?)")->execute($a);
}

// 7. Assignments (varied types, statuses, teachers, classes)
$assignments = [
    ['Algebra Test', 'Algebraic expressions', $subjectIds['MATH10A'], $classIds['Class 10A'], '2024-06-20', '2024-06-27', 100, $teacherIds['teacher1@school.com'], 'essays', 'completed'],
    ['English Essay', 'Essay on Indian Independence', $subjectIds['ENG10B'], $classIds['Class 10B'], '2024-06-22', '2024-06-29', 50, $teacherIds['teacher2@school.com'], 'reports', 'running'],
    ['Physics Lab', 'Experiment on Light', $subjectIds['PHY11S'], $classIds['Class 11 Science'], '2024-06-25', '2024-07-02', 75, $teacherIds['teacher5@school.com'], 'presentations', 'coming'],
    // New assignments with running/coming status and essays/reports types
    ['Mathematics Assignment', 'Solve quadratic equations and word problems', $subjectIds['MATH1A'], $classIds['Class 1A'], date('Y-m-d', strtotime('-2 days')), date('Y-m-d', strtotime('+5 days')), 80, $teacherIds['teacher1@school.com'], 'essays', 'running'],
    ['English Literature Report', 'Write a detailed report on Shakespeare plays', $subjectIds['ENG1A'], $classIds['Class 1A'], date('Y-m-d', strtotime('-1 day')), date('Y-m-d', strtotime('+7 days')), 60, $teacherIds['teacher2@school.com'], 'reports', 'running'],
    ['Science Research Essay', 'Research and write about renewable energy sources', $subjectIds['SCI2A'], $classIds['Class 2A'], date('Y-m-d'), date('Y-m-d', strtotime('+10 days')), 90, $teacherIds['teacher3@school.com'], 'essays', 'coming'],
    ['History Report', 'Write a comprehensive report on Indian Freedom Movement', $subjectIds['SST4A'], $classIds['Class 4A'], date('Y-m-d', strtotime('+2 days')), date('Y-m-d', strtotime('+12 days')), 70, $teacherIds['teacher5@school.com'], 'reports', 'coming'],
    ['Geography Essay', 'Essay on climate change and its global impact', $subjectIds['SST9A'], $classIds['Class 9A'], date('Y-m-d', strtotime('+3 days')), date('Y-m-d', strtotime('+15 days')), 85, $teacherIds['teacher10@school.com'], 'essays', 'coming'],
    ['Hindi Literature Report', 'Report on modern Hindi literature and authors', $subjectIds['HIN3A'], $classIds['Class 3A'], date('Y-m-d', strtotime('+1 day')), date('Y-m-d', strtotime('+8 days')), 55, $teacherIds['teacher4@school.com'], 'reports', 'coming'],
    // Class 1A specific assignments
    ['Basic Math Problems', 'Solve addition and subtraction word problems', $subjectIds['MATH1A'], $classIds['Class 1A'], date('Y-m-d', strtotime('-3 days')), date('Y-m-d', strtotime('+3 days')), 50, $teacherIds['teacher1@school.com'], 'essays', 'running'],
    ['My Family Essay', 'Write an essay about your family members', $subjectIds['ENG1A'], $classIds['Class 1A'], date('Y-m-d', strtotime('-1 day')), date('Y-m-d', strtotime('+6 days')), 40, $teacherIds['teacher2@school.com'], 'essays', 'running'],
    ['Animals Report', 'Write a report about your favorite animal', $subjectIds['SCI2A'], $classIds['Class 1A'], date('Y-m-d'), date('Y-m-d', strtotime('+8 days')), 45, $teacherIds['teacher3@school.com'], 'reports', 'coming'],
    ['Festivals Essay', 'Essay about Indian festivals and celebrations', $subjectIds['SST4A'], $classIds['Class 1A'], date('Y-m-d', strtotime('+1 day')), date('Y-m-d', strtotime('+9 days')), 35, $teacherIds['teacher5@school.com'], 'essays', 'coming'],
    ['Hindi Poems Report', 'Report on famous Hindi poems for children', $subjectIds['HIN3A'], $classIds['Class 1A'], date('Y-m-d', strtotime('+2 days')), date('Y-m-d', strtotime('+10 days')), 30, $teacherIds['teacher4@school.com'], 'reports', 'coming'],
    ['Numbers Assignment', 'Practice writing numbers 1-100 in words', $subjectIds['MATH1A'], $classIds['Class 1A'], date('Y-m-d', strtotime('+3 days')), date('Y-m-d', strtotime('+11 days')), 25, $teacherIds['teacher1@school.com'], 'essays', 'coming'],
];
foreach ($assignments as $a) {
    $pdo->prepare("INSERT IGNORE INTO assignments (title, description, subject_id, class_id, start_date, due_date, total_marks, teacher_id, type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")->execute($a);
}
// Fetch assignment IDs
$assignmentIds = [];
foreach ($assignments as $a) {
    $stmt = $pdo->prepare("SELECT id FROM assignments WHERE title = ?");
    $stmt->execute([$a[0]]);
    $assignmentIds[$a[0]] = $stmt->fetchColumn();
}

// 8. Student Assignments (submissions, graded/ungraded)
$studentAssignments = [
    [$assignmentIds['Algebra Test'], $studentIds['arjunreddy1@school.com'], '2024-06-26 10:00:00', 95, 'Well done', null, 'graded'],
    [$assignmentIds['English Essay'], $studentIds['zarakhan2@school.com'], '2024-06-28 12:00:00', 88, 'Good analysis', null, 'graded'],
    [$assignmentIds['Physics Lab'], $studentIds['meghasharma4@school.com'], null, null, null, null, 'submitted'],
];
foreach ($studentAssignments as $sa) {
    $pdo->prepare("INSERT IGNORE INTO student_assignments (assignment_id, student_id, submitted_date, marks_obtained, submitted_text, submitted_file, status) VALUES (?, ?, ?, ?, ?, ?, ?)")->execute($sa);
}

// 9. Exams (expanded to 22)
$exams = [];
$today = date('Y-m-d');
$today_plus_1 = date('Y-m-d', strtotime('+1 day'));
$today_minus_1 = date('Y-m-d', strtotime('-1 day'));
for ($i = 1; $i <= 22; $i++) {
    $classIndex = ($i - 1) % count($classes);
    $subjectIndex = ($i - 1) % count($subjects);
    $className = $classes[$classIndex][0];
    $subjectCode = $subjects[$subjectIndex][1];
    // Only 'midterm' and 'final' as exam types, alternate between them
    $examType = ($i % 2 == 0) ? 'final' : 'midterm';
    // Distribute statuses: 8 scheduled, 7 ongoing, 7 completed
    if ($i <= 8) {
        $status = 'scheduled';
        $date = $today_plus_1;
    } elseif ($i <= 15) {
        $status = 'ongoing';
        $date = $today;
    } else {
        $status = 'completed';
        // Alternate between today and today - 1 for completed
        $date = ($i % 2 == 0) ? $today : $today_minus_1;
    }
    $examName = $subjects[$subjectIndex][0] . ' ' . ucfirst($examType) . ' ' . $className;
    $start = sprintf('%02d:00:00', 8 + ($i % 5));
    $end = sprintf('%02d:00:00', 10 + ($i % 5));
    $total = 50 + ($i % 3) * 25;
    $exams[] = [
        $examName,
        $subjectIds[$subjectCode],
        $classIds[$className],
        $date,
        $start,
        $end,
        $total,
        $examType,
        $status,
    ];
}

// Add 10 exams specifically for Class 1A
$class1AExams = [
    ['Mathematics Midterm Class 1A', $subjectIds['MATH1A'], $classIds['Class 1A'], $today, '09:00:00', '10:00:00', 50, 'midterm', 'ongoing'],
    ['English Final Class 1A', $subjectIds['ENG1A'], $classIds['Class 1A'], $today, '10:00:00', '11:00:00', 60, 'final', 'ongoing'],
    ['Mathematics Final Class 1A', $subjectIds['MATH1A'], $classIds['Class 1A'], date('Y-m-d', strtotime('+2 days')), '09:00:00', '10:00:00', 50, 'final', 'scheduled'],
    ['English Midterm Class 1A', $subjectIds['ENG1A'], $classIds['Class 1A'], date('Y-m-d', strtotime('+3 days')), '10:00:00', '11:00:00', 60, 'midterm', 'scheduled'],
    ['Science Midterm Class 1A', $subjectIds['SCI2A'], $classIds['Class 1A'], date('Y-m-d', strtotime('+4 days')), '11:00:00', '12:00:00', 45, 'midterm', 'scheduled'],
    ['Hindi Final Class 1A', $subjectIds['HIN3A'], $classIds['Class 1A'], date('Y-m-d', strtotime('+5 days')), '09:00:00', '10:00:00', 40, 'final', 'scheduled'],
    ['Social Studies Midterm Class 1A', $subjectIds['SST4A'], $classIds['Class 1A'], date('Y-m-d', strtotime('+6 days')), '10:00:00', '11:00:00', 55, 'midterm', 'scheduled'],
    ['Mathematics Midterm Class 1A', $subjectIds['MATH1A'], $classIds['Class 1A'], date('Y-m-d', strtotime('+7 days')), '11:00:00', '12:00:00', 50, 'midterm', 'scheduled'],
    ['English Final Class 1A', $subjectIds['ENG1A'], $classIds['Class 1A'], date('Y-m-d', strtotime('+8 days')), '09:00:00', '10:00:00', 60, 'final', 'scheduled'],
    ['Science Final Class 1A', $subjectIds['SCI2A'], $classIds['Class 1A'], date('Y-m-d', strtotime('+9 days')), '10:00:00', '11:00:00', 45, 'final', 'scheduled'],
];

// Merge Class 1A exams with existing exams array
$exams = array_merge($exams, $class1AExams);

foreach ($exams as $e) {
    $pdo->prepare("INSERT IGNORE INTO exams (name, subject_id, class_id, date, start_time, end_time, total_marks, exam_type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")->execute($e);
}

// 10. Lecturers (weekly schedule, different days, times, teachers, classes)
$today = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$day_after_tomorrow = date('Y-m-d', strtotime('+2 days'));
$yesterday = date('Y-m-d', strtotime('-1 day'));

// Get current week dates (Monday to Friday, excluding today)
$currentWeekStart = date('Y-m-d', strtotime('monday this week'));
$currentWeekEnd = date('Y-m-d', strtotime('friday this week'));

$lecturers = [
    // Tomorrow's lecture - Scheduled
    [$subjectIds['MATH10A'], $teacherIds['teacher1@school.com'], $classIds['Class 10A'], $tomorrow, '08:00:00', '09:00:00', 'scheduled'],
    // Day after tomorrow's lecture - Scheduled
    [$subjectIds['ENG10B'], $teacherIds['teacher2@school.com'], $classIds['Class 10B'], $day_after_tomorrow, '09:00:00', '10:00:00', 'scheduled'],
    // Next week's lecture - Scheduled
    [$subjectIds['PHY11S'], $teacherIds['teacher5@school.com'], $classIds['Class 11 Science'], date('Y-m-d', strtotime('+1 week')), '10:00:00', '11:00:00', 'scheduled'],
    // Yesterday's lecture - Completed
    [$subjectIds['SCI2A'], $teacherIds['teacher3@school.com'], $classIds['Class 2A'], $yesterday, '11:00:00', '12:00:00', 'completed'],
];

// Add 5 completed lectures for different teachers and classes
$completedLectures = [
    [$subjectIds['MATH1A'], $teacherIds['teacher1@school.com'], $classIds['Class 1A'], date('Y-m-d', strtotime('-3 days')), '08:00:00', '09:00:00', 'completed'],
    [$subjectIds['ENG1A'], $teacherIds['teacher2@school.com'], $classIds['Class 1A'], date('Y-m-d', strtotime('-4 days')), '09:00:00', '10:00:00', 'completed'],
    [$subjectIds['SCI2A'], $teacherIds['teacher3@school.com'], $classIds['Class 2A'], date('Y-m-d', strtotime('-5 days')), '10:00:00', '11:00:00', 'completed'],
    [$subjectIds['HIN3A'], $teacherIds['teacher4@school.com'], $classIds['Class 3A'], date('Y-m-d', strtotime('-6 days')), '11:00:00', '12:00:00', 'completed'],
    [$subjectIds['SST4A'], $teacherIds['teacher5@school.com'], $classIds['Class 4A'], date('Y-m-d', strtotime('-7 days')), '12:00:00', '13:00:00', 'completed'],
];

// Merge completed lectures with existing lecturers array
$lecturers = array_merge($lecturers, $completedLectures);

// Add sample lectures for teacher1@school.com for current week (excluding today)
$teacher1SubjectId = $subjectIds['MATH1A']; // teacher1@school.com teaches MATH1A
$teacher1ClassId = $classIds['Class 1A']; // teacher1@school.com is class teacher of Class 1A

// Generate lectures for each day of the current week (Monday to Friday)
for ($i = 0; $i < 5; $i++) {
    $lectureDate = date('Y-m-d', strtotime("monday this week +$i days"));
    
    // Skip today's date
    if ($lectureDate === $today) {
        continue;
    }
    
    // Use future dates to ensure scheduled status
    if ($lectureDate <= $today) {
        // For past dates, use next week instead
        $lectureDate = date('Y-m-d', strtotime("monday next week +$i days"));
    }
    
    // Generate different time slots for variety
    $startHour = 8 + ($i % 3); // 8, 9, 10, 8, 9
    $startTime = sprintf('%02d:00:00', $startHour);
    $endTime = sprintf('%02d:00:00', $startHour + 1);
    
    $lecturers[] = [
        $teacher1SubjectId,
        $teacherIds['teacher1@school.com'],
        $teacher1ClassId,
        $lectureDate,
        $startTime,
        $endTime,
        'scheduled' // Always scheduled since we're using future dates
    ];
}

foreach ($lecturers as $l) {
    $pdo->prepare("INSERT IGNORE INTO lecturers (subject_id, teacher_id, class_id, date, start_time, end_time, status) VALUES (?, ?, ?, ?, ?, ?, ?)")->execute($l);
}

// 11. Teacher Classes (many-to-many)
$teacherClasses = [
    [$teacherIds['teacher1@school.com'], $classIds['Class 10A']],
    [$teacherIds['teacher1@school.com'], $classIds['Class 10B']],
    [$teacherIds['teacher2@school.com'], $classIds['Class 10B']],
    [$teacherIds['teacher3@school.com'], $classIds['Class 9A']],
    [$teacherIds['teacher4@school.com'], $classIds['Class 12 Commerce']],
    [$teacherIds['teacher5@school.com'], $classIds['Class 11 Science']],
];

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

// 12. Password Resets (for all user types)
// $passwordResets = [
//     ['principal@school.com', 'principal', bin2hex(random_bytes(32)), date('Y-m-d H:i:s', strtotime('+1 day')), false],
//     ['teacher1@school.com', 'teacher', bin2hex(random_bytes(32)), date('Y-m-d H:i:s', strtotime('+1 day')), false],
//     ['student1@school.com', 'student', bin2hex(random_bytes(32)), date('Y-m-d H:i:s', strtotime('+1 day')), false],
// ];
// foreach ($passwordResets as $pr) {
//     $pdo->prepare("INSERT IGNORE INTO password_resets (email, user_type, token, expires_at, used) VALUES (?, ?, ?, ?, ?)")->execute($pr);
// }

echo "Sample data inserted successfully!\n";
echo "\nDefault login credentials:\n";
echo "Principal: principal@school.com / priyasharma\n";
echo "Teachers: teacher1@school.com / amitsingh, teacher2@school.com / nehapatel, teacher3@school.com / vikramgupta, teacher4@school.com / sunitajain, teacher5@school.com / rajivmehra\n";
echo "Students: student1@school.com / arjunreddy, student2@school.com / zarakhan, student3@school.com / ishaanverma, student4@school.com / meghasharma, student5@school.com / rohanjoshi\n\n";
echo "Sample Data Features:\n";
echo "- Indian names: Arjun Reddy, Zara Khan, Ishaan Verma, Megha Sharma, Rohan Joshi\n";
echo "- Teachers: Amit Singh, Neha Patel, Vikram Gupta, Sunita Jain, Rajiv Mehra\n";
echo "- Principal: Priya Sharma\n";
echo "- Subjects: Mathematics, Science, English, Accountancy, Business Studies, Physics, Chemistry\n";
echo "- Classes: Class 9A, 10A, 10B, 11 Science, 12 Commerce\n";
echo "- Complete attendance, grades, exams, assignments, and schedule data\n";
echo "- Students use email for login, roll_number is unique\n\n"; 