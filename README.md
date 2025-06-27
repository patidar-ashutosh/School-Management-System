# School Management System

A complete web-based School Management System built with HTML, CSS, JavaScript for the frontend and PHP with MVC pattern for the backend.

## 🚀 Tech Stack

- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Backend**: PHP 8.0+, MySQL
- **Architecture**: MVC (Model-View-Controller) Pattern
- **Authentication**: PHP Sessions

## 📁 Project Structure

```
School Management System/
├── frontend/                 # Frontend files
│   ├── css/                 # Stylesheets
│   ├── js/                  # JavaScript files
│   ├── pages/               # HTML pages
│   └── index.html           # Main entry point
├── backend/                 # Backend files (MVC)
│   ├── config/              # Configuration files
│   │   └── db.php           # Database connection
│   ├── controllers/         # Controller classes
│   ├── models/              # Model classes
│   ├── views/               # View templates
│   ├── routes/              # Routing logic
│   └── api/                 # API endpoints
└── README.md               # This file
```

## 🛠️ Setup Instructions

### Prerequisites

- PHP 8.0 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx) or PHP built-in server
- Modern web browser

### 1. Database Setup

1. Create a new MySQL database:

```sql
CREATE DATABASE school_management;
USE school_management;
```

2. Run the following SQL queries to create tables:

```sql
-- Users table for authentication
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'student') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Classes table
CREATE TABLE classes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_name VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Teachers table
CREATE TABLE teachers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    subject_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Students table
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    roll_number VARCHAR(20) UNIQUE NOT NULL,
    class_id INT,
    section VARCHAR(10),
    email VARCHAR(100),
    dob DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id)
);

-- Subjects table
CREATE TABLE subjects (
    id INT PRIMARY KEY AUTO_INCREMENT,
    subject_name VARCHAR(100) NOT NULL,
    class_id INT,
    teacher_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(id)
);

-- Exams table
CREATE TABLE exams (
    id INT PRIMARY KEY AUTO_INCREMENT,
    exam_name VARCHAR(100) NOT NULL,
    exam_date DATE NOT NULL,
    class_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id)
);

-- Marks table
CREATE TABLE marks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT,
    subject_id INT,
    exam_id INT,
    marks_obtained DECIMAL(5,2),
    total_marks DECIMAL(5,2) DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (exam_id) REFERENCES exams(id)
);
```

3. Insert sample data:

```sql
-- Insert sample users
INSERT INTO users (name, email, password, role) VALUES
('Admin User', 'admin@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('John Teacher', 'teacher@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('Alice Student', 'student@school.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student');

-- Insert sample classes
INSERT INTO classes (class_name) VALUES
('Class 1'), ('Class 2'), ('Class 3'), ('Class 4'), ('Class 5');

-- Insert sample teachers
INSERT INTO teachers (name, email, phone) VALUES
('John Smith', 'john.smith@school.com', '1234567890'),
('Mary Johnson', 'mary.johnson@school.com', '0987654321'),
('David Wilson', 'david.wilson@school.com', '1122334455');

-- Insert sample students
INSERT INTO students (name, roll_number, class_id, section, email, dob) VALUES
('Alice Brown', 'STU001', 1, 'A', 'alice.brown@school.com', '2015-03-15'),
('Bob Davis', 'STU002', 1, 'A', 'bob.davis@school.com', '2015-07-22'),
('Carol Evans', 'STU003', 2, 'B', 'carol.evans@school.com', '2014-11-08');

-- Insert sample subjects
INSERT INTO subjects (subject_name, class_id, teacher_id) VALUES
('Mathematics', 1, 1),
('English', 1, 2),
('Science', 2, 3);

-- Insert sample exams
INSERT INTO exams (exam_name, exam_date, class_id) VALUES
('Mid Term Exam', '2024-03-15', 1),
('Final Exam', '2024-06-20', 1);
```

### 2. Backend Setup

1. Navigate to the backend directory:

```bash
cd backend
```

2. Update database configuration in `config/db.php`:

```php
// Update these values according to your database setup
define('DB_HOST', 'localhost');
define('DB_NAME', 'school_management');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

3. Start PHP development server:

```bash
php -S localhost:8000
```

### 3. Frontend Setup

1. Open the `frontend/index.html` file in your web browser
2. Or serve it using a local server:

```bash
cd frontend
python -m http.server 3000
# or
npx serve .
```

## 🔐 Authentication

### Login Credentials

- **Admin**: admin@school.com / password
- **Teacher**: teacher@school.com / password
- **Student**: student@school.com / password

### How Login Works

1. User enters credentials on the login page
2. Frontend sends AJAX request to `backend/api/auth.php`
3. Backend validates credentials against the `users` table
4. If valid, PHP session is created with user role and ID
5. User is redirected to role-specific dashboard

## 🔗 Frontend-Backend Connection

The frontend communicates with the backend through AJAX requests to PHP API endpoints:

- **Authentication**: `backend/api/auth.php`
- **Students**: `backend/api/students.php`
- **Teachers**: `backend/api/teachers.php`
- **Classes**: `backend/api/classes.php`
- **Subjects**: `backend/api/subjects.php`
- **Exams**: `backend/api/exams.php`
- **Marks**: `backend/api/marks.php`

### Example AJAX Request:

```javascript
fetch("backend/api/students.php", {
  method: "POST",
  headers: {
    "Content-Type": "application/json",
  },
  body: JSON.stringify({
    action: "get_all",
    // other parameters
  }),
})
  .then((response) => response.json())
  .then((data) => {
    // Handle response
  });
```

## 📋 Features

### Admin Features

- Dashboard with overview statistics
- Manage students (CRUD operations)
- Manage teachers (CRUD operations)
- Manage classes and sections
- Manage subjects
- View exam results

### Teacher Features

- Dashboard with assigned classes
- View student lists
- Manage exam marks
- View subject assignments

### Student Features

- Dashboard with personal information
- View exam results
- View class schedule

## 🚀 Running the Application

1. **Start Backend Server**:

```bash
cd backend
php -S localhost:8000
```

2. **Open Frontend**:

- Navigate to `frontend/index.html` in your browser
- Or serve frontend on a different port

3. **Access the Application**:

- Frontend: `http://localhost:3000` (or file:// protocol)
- Backend API: `http://localhost:8000/api/`

## 🔧 Adding New Modules

To add new modules to the system:

1. **Create Model**: Add new model class in `backend/models/`
2. **Create Controller**: Add new controller in `backend/controllers/`
3. **Create API Endpoint**: Add new PHP file in `backend/api/`
4. **Create Frontend Page**: Add new HTML page in `frontend/pages/`
5. **Update Navigation**: Modify navigation in dashboard pages
6. **Add Database Table**: Create corresponding MySQL table

### Example Module Structure:

```
backend/
├── models/NewModule.php
├── controllers/NewModuleController.php
└── api/new_module.php

frontend/
└── pages/new-module.html
```

## 🐛 Troubleshooting

### Common Issues:

1. **Database Connection Error**: Check database credentials in `config/db.php`
2. **CORS Issues**: Ensure backend server is running and accessible
3. **Session Issues**: Check PHP session configuration
4. **File Permissions**: Ensure web server has read access to project files

### Debug Mode:

Enable debug mode by setting `DEBUG = true` in `backend/config/db.php` to see detailed error messages.

## 📝 License

This project is open source and available under the MIT License.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

---

**Note**: This is a basic implementation. For production use, consider adding:

- Input validation and sanitization
- CSRF protection
- Password hashing with salt
- Rate limiting
- Error logging
- HTTPS enforcement
