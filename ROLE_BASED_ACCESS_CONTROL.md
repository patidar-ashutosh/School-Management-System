# Role-Based Access Control (RBAC) System

## Overview

The School Management System implements a comprehensive Role-Based Access Control (RBAC) system that ensures users can only access appropriate sections and functionality based on their assigned roles.

## User Roles

### 1. Principal

- **Access Level**: Full system access
- **Can Access**:
  - Principal dashboard and management pages
  - Teacher dashboard and management pages
  - Student dashboard and management pages
  - All CRUD operations for students, teachers, classes, subjects, and exams
  - System-wide analytics and reports

### 2. Teacher

- **Access Level**: Limited administrative access
- **Can Access**:
  - Teacher dashboard and management pages
  - Student dashboard (view-only access)
  - Their assigned classes and students
  - Grade and attendance management for their students
  - Assignment management

### 3. Student

- **Access Level**: Personal data access
- **Can Access**:
  - Student dashboard and personal pages
  - Teacher dashboard (view-only access)
  - Their own grades, attendance, and assignments
  - Personal profile management

## Access Control Matrix

| Feature              | Principal    | Teacher    | Student |
| -------------------- | ------------ | ---------- | ------- |
| **Dashboard Access** |
| Principal Dashboard  | ✅ Full      | ❌ None    | ❌ None |
| Teacher Dashboard    | ✅ View      | ✅ Full    | ✅ View |
| Student Dashboard    | ✅ View      | ✅ View    | ✅ Full |
| **Data Management**  |
| Student Management   | ✅ Full CRUD | ❌ None    | ❌ None |
| Teacher Management   | ✅ Full CRUD | ❌ None    | ❌ None |
| Class Management     | ✅ Full CRUD | ✅ Limited | ❌ None |
| Subject Management   | ✅ Full CRUD | ✅ Limited | ❌ None |
| Exam Management      | ✅ Full CRUD | ✅ Limited | ❌ None |
| **Personal Data**    |
| Own Profile          | ✅ Full      | ✅ Full    | ✅ Full |
| Own Grades           | ✅ View      | ✅ View    | ✅ Full |
| Own Attendance       | ✅ View      | ✅ View    | ✅ Full |
| Own Assignments      | ✅ View      | ✅ View    | ✅ Full |
