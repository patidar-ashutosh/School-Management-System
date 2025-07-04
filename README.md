# School Management System

A comprehensive web-based school management system built with PHP, MySQL, HTML, CSS, and JavaScript. The system provides separate interfaces for principals, teachers, and students with role-based access control.

## Features

### Principal Features

- **Dashboard**: Overview of school statistics
- **Student Management**: Add, edit, delete, and view student records
- **Teacher Management**: Manage teacher profiles and assignments
- **Class Management**: Create and manage classes
- **Subject Management**: Organize subjects and curriculum
- **Exam Management**: Schedule and manage examinations
- **Reports**: Generate various reports and analytics

### Teacher Features

- **Dashboard**: Personal teaching overview
- **Class Management**: View assigned classes
- **Attendance**: Take and manage student attendance
- **Grade Management**: Record and manage student grades
- **Assignment Management**: Create and grade assignments

### Student Features

- **Dashboard**: Personal academic overview
- **Grades**: View academic performance
- **Attendance**: Check attendance records
- **Schedule**: View class schedules
- **Assignments**: Access and submit assignments

## Project Structure

```
School Management System/
├── frontend/
│   ├── index.html                 # Main landing page
│   ├── admin/
│   │   ├── index.html             # Admin portal landing
│   │   ├── principal/
│   │   │   ├── index.html         # Principal login
│   │   │   └── pages/
│   │   │       ├── principal-dashboard.html
│   │   │       ├── students.html
│   │   │       ├── teachers.html
│   │   │       ├── classes.html
│   │   │       ├── subjects.html
│   │   │       └── exams.html
│   │   └── teacher/
│   │       ├── index.html         # Teacher login
│   │       └── pages/
│   │           └── teacher-dashboard.html
│   └── user/
│       ├── index.html             # Student access page
│       └── pages/
│           └── student-dashboard.html
├── backend/
│   ├── config/
│   │   ├── db.php                 # Database configuration
│   │   └── session.php            # Session management
│   ├── controllers/
│   │   └── auth.php               # Authentication controller
│   ├── models/
│   │   ├── Admin.php              # Admin model (replaces User.php)
│   │   ├── Student.php            # Student model
│   │   ├── Teacher.php            # Teacher model
│   │   ├── Class.php              # Class model
│   │   ├── Subject.php            # Subject model
│   │   └── Exam.php               # Exam model
│   ├── views/
│   └── assets/
│       ├── css/
│       │   ├── style.css          # Common styles
│       │   ├── principal.css      # Principal-specific styles
│       │   └── user.css           # User-specific styles
│       └── js/
│           └── auth.js            # Authentication functions
├── assets/
│   ├── css/
│   │   ├── admin.css              # Admin portal styles
│   │   ├── principal.css          # Principal styles
│   │   └── user.css               # User styles
│   └── js/
│       └── auth.js                # Authentication functions
├── database/
│   └── db.md                      # Database schema documentation
├── setup_database.php             # Database setup script
├── .htaccess                      # URL rewriting rules
└── README.md                      # Project documentation
```

## Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx) or PHP built-in server

### Setup Instructions

1. **Clone or download the project**

   ```bash
   git clone <repository-url>
   cd "School Management System"
   ```

2. **Configure Database**

   - Create a MySQL database named `school_management`
   - Update database credentials in `backend/config/db.php`

   ```php
   define('DB_HOST', '127.0.0.1');
   define('DB_NAME', 'school_management');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

3. **Initialize Database**

   ```bash
   php setup_database.php
   ```

4. **Start Development Server**

   ```bash
   php -S localhost:8000
   ```

5. **Access the System**
   - Open your browser and go to `http://localhost:8000/frontend/index.html`

## Default Login Credentials

### Principal

- **Email**: `principal@school.com`
- **Password**: `priyasharma`
- **URL**: `http://localhost:8000/frontend/admin/principal/index.html`

### Teacher

- **Email**: `teacher1@school.com`
- **Password**: `amitsingh`
- **URL**: `http://localhost:8000/frontend/admin/teacher/index.html`

### Student Access

Students can login using their email and password:

- **Email**: `student1@school.com`
- **Password**: `arjunreddy`
- **URL**: `http://localhost:8000/frontend/user/index.html`

## Additional Test Credentials

### Teachers

- **Email**: `teacher2@school.com` / **Password**: `nehapatel`
- **Email**: `teacher3@school.com` / **Password**: `vikramgupta`

### Students

- **Email**: `student2@school.com` / **Password**: `zarakhan`
- **Email**: `student3@school.com` / **Password**: `ishaanverma`

## Database Schema

The system uses the following main tables:

- **principals**: Stores principal login credentials and profiles
- **teachers**: Teacher profiles and login credentials
- **students**: Student records with login credentials (email-based)
- **classes**: Class information with teacher assignments
- **subjects**: Subject details with teacher assignments
- **exams**: Examination schedules
- **assignments**: Assignment details with teacher assignments
- **attendance**: Student attendance records
- **student_assignments**: Student assignment submissions
- **schedule**: Class schedules
- **password_resets**: Password reset tokens

## Key Features

### Security

- Password hashing using PHP's `password_hash()`
- Session-based authentication
- Role-based access control
- SQL injection prevention with prepared statements

### User Interface

- Responsive design for all devices
- Modern and intuitive interface
- Role-specific dashboards
- Real-time data updates

### Data Management

- CRUD operations for all entities
- Search and filter functionality
- Pagination for large datasets
- Export capabilities

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support and questions, please open an issue in the repository or contact the development team.

SITE : https://byet.host/
