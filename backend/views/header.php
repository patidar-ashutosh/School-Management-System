<?php
require_once __DIR__ . '/../config/session.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../../frontend/index.html');
    exit;
}

$user = getCurrentUserData();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'School Management System'; ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/principal.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <?php if (isset($additionalCSS)): ?>
        <?php foreach ($additionalCSS as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="principal-dashboard">
    <header class="main-header">
        <div class="header-container">
            <div class="logo">
                <i class="fas fa-school"></i>
                <span>School Management System</span>
            </div>
            
            <nav class="main-nav">
                <ul>
                    <?php if (hasRole('principal')): ?>
                    <li><a href="principal-dashboard.html"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="manage-students.html"><i class="fas fa-users"></i> Students</a></li>
                    <li><a href="manage-teachers.html"><i class="fas fa-chalkboard-teacher"></i> Teachers</a></li>
                    <li><a href="manage-classes.html"><i class="fas fa-chalkboard"></i> Classes</a></li>
                    <li><a href="manage-subjects.html"><i class="fas fa-book"></i> Subjects</a></li>
                    <li><a href="manage-exams.html"><i class="fas fa-file-alt"></i> Exams</a></li>
                    <?php elseif (hasRole('teacher')): ?>
                    <li><a href="teacher-dashboard.html"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="my-classes.html"><i class="fas fa-chalkboard"></i> My Classes</a></li>
                    <li><a href="my-students.html"><i class="fas fa-users"></i> My Students</a></li>
                    <li><a href="attendance.html"><i class="fas fa-calendar-check"></i> Attendance</a></li>
                    <li><a href="grades.html"><i class="fas fa-chart-line"></i> Grades</a></li>
                    <li><a href="assignments.html"><i class="fas fa-tasks"></i> Assignments</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <div class="user-menu">
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($user['username']); ?></span>
                </div>
                <div class="dropdown-menu">
                    <a href="profile.html"><i class="fas fa-user"></i> Profile</a>
                    <a href="settings.html"><i class="fas fa-cog"></i> Settings</a>
                    <a href="#" onclick="logout()"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </header>
    
    <main class="main-content">
        <?php if (isset($pageHeader)): ?>
            <div class="page-header">
                <h1><?php echo $pageHeader; ?></h1>
                <?php if (isset($pageDescription)): ?>
                    <p><?php echo $pageDescription; ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?> 