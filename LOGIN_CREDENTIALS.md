# School Management System - Login Credentials

## Test Users

The system is now configured with database authentication for testing purposes.

### Principal Login

- **URL**: http://localhost:8000/frontend/admin/principal/index.html
- **Email**: `principal@school.com`
- **Password**: `priyasharma`
- **Role**: Principal

### Teacher Login

- **URL**: http://localhost:8000/frontend/admin/teacher/index.html
- **Email**: `teacher1@school.com`
- **Password**: `amitsingh`
- **Role**: Teacher

### Student Login

- **URL**: http://localhost:8000/frontend/user/index.html
- **Email**: `student1@school.com`
- **Password**: `arjunreddy`
- **Role**: Student

## Additional Test Credentials

### Teachers

- **Email**: `teacher2@school.com` / **Password**: `nehapatel`
- **Email**: `teacher3@school.com` / **Password**: `vikramgupta`

### Students

- **Email**: `student2@school.com` / **Password**: `zarakhan`
- **Email**: `student3@school.com` / **Password**: `ishaanverma`

## How to Access

1. Start the PHP development server:

   ```bash
   php -S localhost:8000
   ```

2. Open your browser and go to:

   - **Main Page**: http://localhost:8000/frontend/index.html
   - **Principal Login**: http://localhost:8000/frontend/admin/principal/index.html
   - **Teacher Login**: http://localhost:8000/frontend/admin/teacher/index.html
   - **Student Login**: http://localhost:8000/frontend/user/index.html

3. Use the credentials above to log in and test the system.

## New Admin Structure

The admin section has been reorganized into separate sections:

### Principal Section (`/admin/principal/`)

- **Login**: `/admin/principal/index.html`
- **Dashboard**: `/admin/principal/pages/principal-dashboard.html`
- **Features**: Manage students, teachers, classes, subjects, exams

### Teacher Section (`/admin/teacher/`)

- **Login**: `/admin/teacher/index.html`
- **Dashboard**: `/admin/teacher/pages/teacher-dashboard.html`
- **Features**: View classes, take attendance, manage assignments

## Database Structure

The system now uses separate tables for different user types:

### Principals Table

- Stores login credentials for principals
- Email-based authentication
- Role: 'principal'

### Teachers Table

- Stores login credentials for teachers
- Email-based authentication
- Role: 'teacher'

### Students Table

- Students have login accounts with passwords
- Email-based authentication
- Roll number is auto-increment and unique
- Role: 'student'

## Features Available

- **Principal Dashboard**: Manage students, teachers, classes, subjects, and exams
- **Teacher Dashboard**: View classes, take attendance, manage assignments
- **Student Dashboard**: View grades, attendance, schedule, and assignments

## Notes

- Database authentication is now implemented
- All data is stored in MySQL database
- Run `php setup_database.php` to initialize the database
- The system uses proper session management and security
- All users (principal, teachers, students) use email for login
- Passwords are hashed for security
- Default passwords are: firstname+lastname (lowercase)
