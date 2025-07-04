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

## Implementation Details

### Frontend Implementation

#### 1. Authentication Class (`assets/js/auth.js`)

The `Auth` class handles:

- User authentication and session management
- Role-based page access validation
- Dynamic navigation based on user role
- Automatic redirects for unauthorized access

**Key Methods:**

- `validatePageAccess()`: Checks if user has permission to access current page
- `getRoleNavigationLinks()`: Returns appropriate navigation links based on role
- `redirectToUserDashboard()`: Redirects user to their appropriate dashboard

#### 2. Role-Based Navigation

Each dashboard includes role-specific navigation links in the user dropdown menu:

```javascript
// Principal can access teacher and student sections
case 'principal':
  links.push(
    { text: 'Teacher Dashboard', url: '/frontend/admin/teacher/pages/teacher-dashboard.html', icon: 'fas fa-chalkboard-teacher' },
    { text: 'Student Dashboard', url: '/frontend/user/pages/student-dashboard.html', icon: 'fas fa-user-graduate' }
  );
  break;
```

#### 3. Page Access Validation

The system validates page access on every protected page:

```javascript
validatePageAccess() {
  const allowedPaths = {
    'principal': [
      '/frontend/admin/principal/',
      '/frontend/admin/teacher/',
      '/frontend/user/'
    ],
    'teacher': [
      '/frontend/admin/teacher/',
      '/frontend/user/'
    ],
    'student': [
      '/frontend/user/',
      '/frontend/admin/teacher/'
    ]
  };
}
```

### Backend Implementation

#### 1. Session Management (`backend/config/session.php`)

- Stores user role and authentication status
- Validates session on each request
- Provides role-based data access

#### 2. Authentication Controller (`backend/controllers/auth.php`)

- Handles login for all user types
- Sets appropriate session data based on role
- Manages logout and session cleanup

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

## Security Features

### 1. Automatic Redirects

- Users attempting to access unauthorized pages are automatically redirected
- Warning messages inform users about access restrictions
- Redirects go to the user's appropriate dashboard

### 2. Session Validation

- Every page load validates user session
- Expired sessions automatically redirect to login
- Role information is verified on each request

### 3. Frontend Protection

- Role-based UI elements are hidden/shown dynamically
- Navigation links are filtered based on user role
- Form submissions include role validation

## Testing the RBAC System

### Test Page

Visit `/frontend/test-role-access.html` to test the role-based access control system.

This page demonstrates:

- Current user information display
- Role-specific content visibility
- Navigation testing between different dashboards
- Access control validation

### Testing Scenarios

1. **Login as Principal**:

   - Should access all dashboards
   - Should see all management options
   - Should be able to navigate between roles

2. **Login as Teacher**:

   - Should access teacher dashboard
   - Should access student dashboard (view-only)
   - Should NOT access principal dashboard
   - Should see limited management options

3. **Login as Student**:
   - Should access student dashboard
   - Should access teacher dashboard (view-only)
   - Should NOT access principal dashboard
   - Should see only personal data

## Configuration

### Adding New Roles

1. **Update Database Schema**:

   ```sql
   ALTER TABLE admins ADD COLUMN role VARCHAR(50) DEFAULT 'teacher';
   ```

2. **Update Auth Class**:

   ```javascript
   // Add new role to allowedPaths
   const allowedPaths = {
     newrole: ["/frontend/newrole/", "/frontend/shared/"],
   };
   ```

3. **Update Navigation Links**:
   ```javascript
   case 'newrole':
     links.push(
       { text: 'New Role Dashboard', url: '/frontend/newrole/dashboard.html', icon: 'fas fa-icon' }
     );
     break;
   ```

### Modifying Access Permissions

1. **Update Access Matrix**: Modify the `allowedPaths` object in `auth.js`
2. **Update UI Elements**: Add `data-role="rolename"` attributes to HTML elements
3. **Update Backend**: Modify session validation and data access controls

## Best Practices

1. **Always Validate on Both Frontend and Backend**

   - Frontend provides UX improvements
   - Backend ensures security

2. **Use Descriptive Role Names**

   - Clear, meaningful role identifiers
   - Consistent naming conventions

3. **Implement Principle of Least Privilege**

   - Users get minimum required access
   - Access is granted incrementally

4. **Regular Security Audits**

   - Review access permissions regularly
   - Test role transitions and edge cases

5. **Log Access Attempts**
   - Monitor unauthorized access attempts
   - Track role-based actions for audit trails

## Troubleshooting

### Common Issues

1. **Infinite Redirects**:

   - Check `isLoginPage()` method in auth.js
   - Verify login page paths are correctly identified

2. **Access Denied Errors**:

   - Verify user role in session
   - Check `allowedPaths` configuration
   - Ensure page paths match exactly

3. **Navigation Links Not Showing**:
   - Check `getRoleNavigationLinks()` method
   - Verify user role is properly set
   - Check CSS for dropdown visibility

### Debug Mode

Enable debug logging by adding to browser console:

```javascript
localStorage.setItem("debug", "true");
```

This will show detailed authentication and access control logs.

## Future Enhancements

1. **Permission Granularity**: Implement fine-grained permissions within roles
2. **Role Hierarchy**: Support role inheritance and delegation
3. **Temporary Access**: Time-limited access grants
4. **Audit Logging**: Comprehensive access and action logging
5. **Multi-Factor Authentication**: Enhanced security for sensitive operations
