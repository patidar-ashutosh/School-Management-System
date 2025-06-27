// Admin Dashboard JavaScript for School Management System

class AdminDashboard {
  constructor() {
    this.apiBaseUrl = "http://localhost:8000/api";
    this.currentPage = "dashboard";
    this.init();
  }

  init() {
    console.log("Admin dashboard initializing...");
    // Check authentication immediately
    this.checkAuthentication();
  }

  checkAuthentication() {
    // Check if user is logged in
    const user = this.getCurrentUser();
    console.log("Current user:", user);

    // If no user or wrong role, redirect to login
    if (!user || user.role !== "admin") {
      console.log("Authentication failed, redirecting to login");
      this.redirectToLogin();
      return;
    }

    console.log("Authentication successful for admin");

    // Initialize dashboard after successful authentication
    this.setupEventListeners();
    this.loadDashboardStats();
    this.loadUserInfo();
    console.log("Admin dashboard initialized successfully");
  }

  getCurrentUser() {
    const user = localStorage.getItem("user");
    return user ? JSON.parse(user) : null;
  }

  redirectToLogin() {
    const currentPath = window.location.pathname;

    let loginUrl = "";
    if (currentPath.includes("/pages/")) {
      // We're in pages directory, go back to root
      loginUrl = "../index.html";
    } else {
      // We're at root or other directory
      loginUrl = "./index.html";
    }

    console.log("Redirecting to login:", loginUrl);
    window.location.href = loginUrl;
  }

  setupEventListeners() {
    // Navigation links
    document.querySelectorAll(".nav-link[data-page]").forEach((link) => {
      link.addEventListener("click", (e) => {
        e.preventDefault();
        const page = e.target.closest(".nav-link").dataset.page;
        this.loadPage(page);
      });
    });

    // Mobile menu toggle
    const sidebarToggle = document.querySelector(".sidebar-toggle");
    if (sidebarToggle) {
      sidebarToggle.addEventListener("click", () => {
        document.querySelector(".sidebar").classList.toggle("show");
      });
    }
  }

  async loadDashboardStats() {
    try {
      console.log("Loading dashboard stats...");
      // Load statistics from different APIs
      const [studentsCount, teachersCount, classesCount, examsCount] =
        await Promise.all([
          this.fetchData("students.php", { action: "get_count" }),
          this.fetchData("teachers.php", { action: "get_count" }),
          this.fetchData("classes.php", { action: "get_count" }),
          this.fetchData("exams.php", { action: "get_count" }),
        ]);

      console.log("Dashboard stats loaded:", {
        studentsCount,
        teachersCount,
        classesCount,
        examsCount,
      });

      // Update statistics cards
      document.getElementById("totalStudents").textContent =
        studentsCount.count || 0;
      document.getElementById("totalTeachers").textContent =
        teachersCount.count || 0;
      document.getElementById("totalClasses").textContent =
        classesCount.count || 0;
      document.getElementById("totalExams").textContent = examsCount.count || 0;
    } catch (error) {
      console.error("Error loading dashboard stats:", error);
      // Set default values on error
      document.getElementById("totalStudents").textContent = "0";
      document.getElementById("totalTeachers").textContent = "0";
      document.getElementById("totalClasses").textContent = "0";
      document.getElementById("totalExams").textContent = "0";
    }
  }

  loadUserInfo() {
    const user = this.getCurrentUser();
    if (user) {
      const userNameElement = document.getElementById("userName");
      if (userNameElement) {
        userNameElement.textContent = user.name;
      }
    }
  }

  async loadPage(page) {
    this.currentPage = page;
    this.updateNavigation(page);
    showLoading(true);

    try {
      console.log("Loading page:", page);

      switch (page) {
        case "dashboard":
          this.showDashboard();
          break;
        case "students":
          await this.loadStudentsPage();
          break;
        case "teachers":
          await this.loadTeachersPage();
          break;
        case "classes":
          await this.loadClassesPage();
          break;
        case "subjects":
          await this.loadSubjectsPage();
          break;
        case "exams":
          await this.loadExamsPage();
          break;
        case "marks":
          await this.loadMarksPage();
          break;
        default:
          this.showDashboard();
      }

      console.log("Page loaded successfully:", page);
    } catch (error) {
      console.error("Error loading page:", error);
      this.showAlert("Error loading page content. Please try again.", "danger");

      // Show dashboard as fallback
      this.showDashboard();
    } finally {
      // Always hide loading animation
      console.log("Hiding loading animation");
      showLoading(false);
    }
  }

  updateNavigation(activePage) {
    // Remove active class from all nav links
    document.querySelectorAll(".nav-link").forEach((link) => {
      link.classList.remove("active");
    });

    // Add active class to current page
    const activeLink = document.querySelector(`[data-page="${activePage}"]`);
    if (activeLink) {
      activeLink.classList.add("active");
    }

    // Update page title
    const pageTitle = document.querySelector(".h2");
    if (pageTitle) {
      pageTitle.textContent = this.getPageTitle(activePage);
    }
  }

  getPageTitle(page) {
    const titles = {
      dashboard: "Dashboard",
      students: "Student Management",
      teachers: "Teacher Management",
      classes: "Class Management",
      subjects: "Subject Management",
      exams: "Exam Management",
      marks: "Marks Management",
    };
    return titles[page] || "Dashboard";
  }

  showDashboard() {
    this.hideAllContent();
    document.getElementById("dashboardContent").style.display = "block";
  }

  async loadStudentsPage() {
    const content = document.getElementById("studentsContent");
    content.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Student Management</h2>
                <button class="btn btn-primary" onclick="adminDashboard.showAddStudentModal()">
                    <i class="fas fa-plus me-2"></i>Add New Student
                </button>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="studentsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Roll Number</th>
                                    <th>Class</th>
                                    <th>Section</th>
                                    <th>Email</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="studentsTableBody">
                                <tr>
                                    <td colspan="7" class="text-center">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;

    this.hideAllContent();
    content.style.display = "block";

    // Load students data
    await this.loadStudentsData();
  }

  async loadStudentsData() {
    try {
      const response = await this.fetchData("students.php", {
        action: "get_all",
      });
      const students = response.data || [];

      const tbody = document.getElementById("studentsTableBody");
      if (students.length === 0) {
        tbody.innerHTML =
          '<tr><td colspan="7" class="text-center">No students found</td></tr>';
        return;
      }

      tbody.innerHTML = students
        .map(
          (student) => `
                <tr>
                    <td>${student.id}</td>
                    <td>${student.name}</td>
                    <td>${student.roll_number}</td>
                    <td>${student.class_name || "N/A"}</td>
                    <td>${student.section || "N/A"}</td>
                    <td>${student.email || "N/A"}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="adminDashboard.editStudent(${
                          student.id
                        })">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="adminDashboard.deleteStudent(${
                          student.id
                        })">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `
        )
        .join("");
    } catch (error) {
      console.error("Error loading students:", error);
      document.getElementById("studentsTableBody").innerHTML =
        '<tr><td colspan="7" class="text-center text-danger">Error loading students</td></tr>';
    }
  }

  async loadTeachersPage() {
    const content = document.getElementById("teachersContent");
    content.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Teacher Management</h2>
                <button class="btn btn-primary" onclick="adminDashboard.showAddTeacherModal()">
                    <i class="fas fa-plus me-2"></i>Add New Teacher
                </button>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="teachersTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Subject</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="teachersTableBody">
                                <tr>
                                    <td colspan="6" class="text-center">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;

    this.hideAllContent();
    content.style.display = "block";

    // Load teachers data
    await this.loadTeachersData();
  }

  async loadTeachersData() {
    try {
      const response = await this.fetchData("teachers.php", {
        action: "get_all",
      });
      const teachers = response.data || [];

      const tbody = document.getElementById("teachersTableBody");
      if (teachers.length === 0) {
        tbody.innerHTML =
          '<tr><td colspan="6" class="text-center">No teachers found</td></tr>';
        return;
      }

      tbody.innerHTML = teachers
        .map(
          (teacher) => `
                <tr>
                    <td>${teacher.id}</td>
                    <td>${teacher.name}</td>
                    <td>${teacher.email}</td>
                    <td>${teacher.phone || "N/A"}</td>
                    <td>${teacher.subject_name || "N/A"}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="adminDashboard.editTeacher(${
                          teacher.id
                        })">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="adminDashboard.deleteTeacher(${
                          teacher.id
                        })">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `
        )
        .join("");
    } catch (error) {
      console.error("Error loading teachers:", error);
      document.getElementById("teachersTableBody").innerHTML =
        '<tr><td colspan="6" class="text-center text-danger">Error loading teachers</td></tr>';
    }
  }

  async loadClassesPage() {
    const content = document.getElementById("classesContent");
    content.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Class Management</h2>
                <button class="btn btn-primary" onclick="adminDashboard.showAddClassModal()">
                    <i class="fas fa-plus me-2"></i>Add New Class
                </button>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="classesTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Class Name</th>
                                    <th>Student Count</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="classesTableBody">
                                <tr>
                                    <td colspan="4" class="text-center">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;

    this.hideAllContent();
    content.style.display = "block";

    // Load classes data
    await this.loadClassesData();
  }

  async loadClassesData() {
    try {
      const response = await this.fetchData("classes.php", {
        action: "get_with_student_count",
      });
      const classes = response.data || [];

      const tbody = document.getElementById("classesTableBody");
      if (classes.length === 0) {
        tbody.innerHTML =
          '<tr><td colspan="4" class="text-center">No classes found</td></tr>';
        return;
      }

      tbody.innerHTML = classes
        .map(
          (cls) => `
                <tr>
                    <td>${cls.id}</td>
                    <td>${cls.class_name}</td>
                    <td>${cls.student_count || 0}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="adminDashboard.editClass(${
                          cls.id
                        })">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="adminDashboard.deleteClass(${
                          cls.id
                        })">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `
        )
        .join("");
    } catch (error) {
      console.error("Error loading classes:", error);
      document.getElementById("classesTableBody").innerHTML =
        '<tr><td colspan="4" class="text-center text-danger">Error loading classes</td></tr>';
    }
  }

  async loadSubjectsPage() {
    const content = document.getElementById("subjectsContent");
    content.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Subject Management</h2>
                <button class="btn btn-primary" onclick="adminDashboard.showAddSubjectModal()">
                    <i class="fas fa-plus me-2"></i>Add New Subject
                </button>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="subjectsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Subject Name</th>
                                    <th>Class</th>
                                    <th>Teacher</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="subjectsTableBody">
                                <tr>
                                    <td colspan="5" class="text-center">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;

    this.hideAllContent();
    content.style.display = "block";

    // Load subjects data
    await this.loadSubjectsData();
  }

  async loadSubjectsData() {
    try {
      const response = await this.fetchData("subjects.php", {
        action: "get_all",
      });
      const subjects = response.data || [];

      const tbody = document.getElementById("subjectsTableBody");
      if (subjects.length === 0) {
        tbody.innerHTML =
          '<tr><td colspan="5" class="text-center">No subjects found</td></tr>';
        return;
      }

      tbody.innerHTML = subjects
        .map(
          (subject) => `
                <tr>
                    <td>${subject.id}</td>
                    <td>${subject.subject_name}</td>
                    <td>${subject.class_name || "N/A"}</td>
                    <td>${subject.teacher_name || "N/A"}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="adminDashboard.editSubject(${
                          subject.id
                        })">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="adminDashboard.deleteSubject(${
                          subject.id
                        })">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `
        )
        .join("");
    } catch (error) {
      console.error("Error loading subjects:", error);
      document.getElementById("subjectsTableBody").innerHTML =
        '<tr><td colspan="5" class="text-center text-danger">Error loading subjects</td></tr>';
    }
  }

  async loadExamsPage() {
    const content = document.getElementById("examsContent");
    content.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Exam Management</h2>
                <button class="btn btn-primary" onclick="adminDashboard.showAddExamModal()">
                    <i class="fas fa-plus me-2"></i>Schedule New Exam
                </button>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="examsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Exam Name</th>
                                    <th>Date</th>
                                    <th>Class</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="examsTableBody">
                                <tr>
                                    <td colspan="5" class="text-center">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;

    this.hideAllContent();
    content.style.display = "block";

    // Load exams data
    await this.loadExamsData();
  }

  async loadExamsData() {
    try {
      const response = await this.fetchData("exams.php", { action: "get_all" });
      const exams = response.data || [];

      const tbody = document.getElementById("examsTableBody");
      if (exams.length === 0) {
        tbody.innerHTML =
          '<tr><td colspan="5" class="text-center">No exams found</td></tr>';
        return;
      }

      tbody.innerHTML = exams
        .map(
          (exam) => `
                <tr>
                    <td>${exam.id}</td>
                    <td>${exam.exam_name}</td>
                    <td>${new Date(exam.exam_date).toLocaleDateString()}</td>
                    <td>${exam.class_name || "N/A"}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="adminDashboard.editExam(${
                          exam.id
                        })">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="adminDashboard.deleteExam(${
                          exam.id
                        })">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `
        )
        .join("");
    } catch (error) {
      console.error("Error loading exams:", error);
      document.getElementById("examsTableBody").innerHTML =
        '<tr><td colspan="5" class="text-center text-danger">Error loading exams</td></tr>';
    }
  }

  async loadMarksPage() {
    const content = document.getElementById("marksContent");
    content.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Marks Management</h2>
                <button class="btn btn-primary" onclick="adminDashboard.showAddMarkModal()">
                    <i class="fas fa-plus me-2"></i>Add Marks
                </button>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="marksTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Student</th>
                                    <th>Subject</th>
                                    <th>Exam</th>
                                    <th>Marks</th>
                                    <th>Percentage</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="marksTableBody">
                                <tr>
                                    <td colspan="7" class="text-center">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;

    this.hideAllContent();
    content.style.display = "block";

    // Load marks data
    await this.loadMarksData();
  }

  async loadMarksData() {
    try {
      const response = await this.fetchData("marks.php", { action: "get_all" });
      const marks = response.data || [];

      const tbody = document.getElementById("marksTableBody");
      if (marks.length === 0) {
        tbody.innerHTML =
          '<tr><td colspan="7" class="text-center">No marks found</td></tr>';
        return;
      }

      tbody.innerHTML = marks
        .map((mark) => {
          const percentage = (
            (mark.marks_obtained / mark.total_marks) *
            100
          ).toFixed(2);
          return `
                    <tr>
                        <td>${mark.id}</td>
                        <td>${mark.student_name}</td>
                        <td>${mark.subject_name}</td>
                        <td>${mark.exam_name}</td>
                        <td>${mark.marks_obtained}/${mark.total_marks}</td>
                        <td>${percentage}%</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="adminDashboard.editMark(${mark.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="adminDashboard.deleteMark(${mark.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
        })
        .join("");
    } catch (error) {
      console.error("Error loading marks:", error);
      document.getElementById("marksTableBody").innerHTML =
        '<tr><td colspan="7" class="text-center text-danger">Error loading marks</td></tr>';
    }
  }

  hideAllContent() {
    const contentDivs = [
      "dashboardContent",
      "studentsContent",
      "teachersContent",
      "classesContent",
      "subjectsContent",
      "examsContent",
      "marksContent",
    ];

    contentDivs.forEach((id) => {
      const element = document.getElementById(id);
      if (element) {
        element.style.display = "none";
      }
    });
  }

  async fetchData(endpoint, data) {
    console.log(`Making API call to ${endpoint}:`, data);

    try {
      // Add timeout to prevent hanging requests
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout

      const response = await fetch(`${this.apiBaseUrl}/${endpoint}`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
        signal: controller.signal,
      });

      clearTimeout(timeoutId);

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const result = await response.json();
      console.log(`API response from ${endpoint}:`, result);
      return result;
    } catch (error) {
      console.error(`API call failed for ${endpoint}:`, error);

      // Return empty data structure when API fails
      if (data.action === "get_all") {
        return { success: true, data: [] };
      } else if (data.action === "get_count") {
        return { success: true, count: 0 };
      } else if (data.action === "get_with_student_count") {
        return { success: true, data: [] };
      }

      throw error;
    }
  }

  showAlert(message, type = "info") {
    // Create alert element
    const alertDiv = document.createElement("div");
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText =
      "top: 20px; right: 20px; z-index: 9999; min-width: 300px;";
    alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

    // Add to body
    document.body.appendChild(alertDiv);

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
      if (alertDiv.parentNode) {
        alertDiv.remove();
      }
    }, 5000);
  }

  // CRUD Operations - Actual Forms
  showAddStudentModal() {
    const modal = `
      <div class="modal fade" id="addStudentModal" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Add New Student</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <form id="addStudentForm">
                <div class="mb-3">
                  <label class="form-label">Full Name</label>
                  <input type="text" class="form-control" name="name" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Roll Number</label>
                  <input type="text" class="form-control" name="roll_number" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Email</label>
                  <input type="email" class="form-control" name="email" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Class</label>
                  <select class="form-select" name="class_id" required>
                    <option value="">Select Class</option>
                    <option value="1">Class 1</option>
                    <option value="2">Class 2</option>
                    <option value="3">Class 3</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Section</label>
                  <select class="form-select" name="section" required>
                    <option value="">Select Section</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Phone</label>
                  <input type="tel" class="form-control" name="phone">
                </div>
                <div class="mb-3">
                  <label class="form-label">Address</label>
                  <textarea class="form-control" name="address" rows="3"></textarea>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary" onclick="adminDashboard.saveStudent()">Save Student</button>
            </div>
          </div>
        </div>
      </div>
    `;

    // Remove existing modal if any
    const existingModal = document.getElementById("addStudentModal");
    if (existingModal) {
      existingModal.remove();
    }

    // Add modal to body
    document.body.insertAdjacentHTML("beforeend", modal);

    // Show modal
    const modalElement = document.getElementById("addStudentModal");
    const bootstrapModal = new bootstrap.Modal(modalElement);
    bootstrapModal.show();
  }

  async saveStudent() {
    const form = document.getElementById("addStudentForm");
    const formData = new FormData(form);

    const studentData = {
      action: "add",
      name: formData.get("name"),
      roll_number: formData.get("roll_number"),
      email: formData.get("email"),
      class_id: formData.get("class_id"),
      section: formData.get("section"),
      phone: formData.get("phone"),
      address: formData.get("address"),
    };

    try {
      showLoading(true);
      const response = await this.fetchData("students.php", studentData);

      if (response.success) {
        this.showAlert("Student added successfully!", "success");
        // Hide modal
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("addStudentModal")
        );
        modal.hide();
        // Reload students data
        await this.loadStudentsData();
      } else {
        this.showAlert(response.message || "Failed to add student", "danger");
      }
    } catch (error) {
      console.error("Error saving student:", error);
      this.showAlert("Error saving student. Please try again.", "danger");
    } finally {
      showLoading(false);
    }
  }

  showAddTeacherModal() {
    const modal = `
      <div class="modal fade" id="addTeacherModal" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Add New Teacher</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <form id="addTeacherForm">
                <div class="mb-3">
                  <label class="form-label">Full Name</label>
                  <input type="text" class="form-control" name="name" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Email</label>
                  <input type="email" class="form-control" name="email" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Phone</label>
                  <input type="tel" class="form-control" name="phone" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Subject</label>
                  <input type="text" class="form-control" name="subject" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Qualification</label>
                  <input type="text" class="form-control" name="qualification">
                </div>
                <div class="mb-3">
                  <label class="form-label">Address</label>
                  <textarea class="form-control" name="address" rows="3"></textarea>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary" onclick="adminDashboard.saveTeacher()">Save Teacher</button>
            </div>
          </div>
        </div>
      </div>
    `;

    // Remove existing modal if any
    const existingModal = document.getElementById("addTeacherModal");
    if (existingModal) {
      existingModal.remove();
    }

    // Add modal to body
    document.body.insertAdjacentHTML("beforeend", modal);

    // Show modal
    const modalElement = document.getElementById("addTeacherModal");
    const bootstrapModal = new bootstrap.Modal(modalElement);
    bootstrapModal.show();
  }

  async saveTeacher() {
    const form = document.getElementById("addTeacherForm");
    const formData = new FormData(form);

    const teacherData = {
      action: "add",
      name: formData.get("name"),
      email: formData.get("email"),
      phone: formData.get("phone"),
      subject: formData.get("subject"),
      qualification: formData.get("qualification"),
      address: formData.get("address"),
    };

    try {
      showLoading(true);
      const response = await this.fetchData("teachers.php", teacherData);

      if (response.success) {
        this.showAlert("Teacher added successfully!", "success");
        // Hide modal
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("addTeacherModal")
        );
        modal.hide();
        // Reload teachers data
        await this.loadTeachersData();
      } else {
        this.showAlert(response.message || "Failed to add teacher", "danger");
      }
    } catch (error) {
      console.error("Error saving teacher:", error);
      this.showAlert("Error saving teacher. Please try again.", "danger");
    } finally {
      showLoading(false);
    }
  }

  showAddClassModal() {
    const modal = `
      <div class="modal fade" id="addClassModal" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Add New Class</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <form id="addClassForm">
                <div class="mb-3">
                  <label class="form-label">Class Name</label>
                  <input type="text" class="form-control" name="class_name" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Capacity</label>
                  <input type="number" class="form-control" name="capacity" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Room Number</label>
                  <input type="text" class="form-control" name="room_number">
                </div>
                <div class="mb-3">
                  <label class="form-label">Description</label>
                  <textarea class="form-control" name="description" rows="3"></textarea>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary" onclick="adminDashboard.saveClass()">Save Class</button>
            </div>
          </div>
        </div>
      </div>
    `;

    // Remove existing modal if any
    const existingModal = document.getElementById("addClassModal");
    if (existingModal) {
      existingModal.remove();
    }

    // Add modal to body
    document.body.insertAdjacentHTML("beforeend", modal);

    // Show modal
    const modalElement = document.getElementById("addClassModal");
    const bootstrapModal = new bootstrap.Modal(modalElement);
    bootstrapModal.show();
  }

  async saveClass() {
    const form = document.getElementById("addClassForm");
    const formData = new FormData(form);

    const classData = {
      action: "add",
      class_name: formData.get("class_name"),
      capacity: formData.get("capacity"),
      room_number: formData.get("room_number"),
      description: formData.get("description"),
    };

    try {
      showLoading(true);
      const response = await this.fetchData("classes.php", classData);

      if (response.success) {
        this.showAlert("Class added successfully!", "success");
        // Hide modal
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("addClassModal")
        );
        modal.hide();
        // Reload classes data
        await this.loadClassesData();
      } else {
        this.showAlert(response.message || "Failed to add class", "danger");
      }
    } catch (error) {
      console.error("Error saving class:", error);
      this.showAlert("Error saving class. Please try again.", "danger");
    } finally {
      showLoading(false);
    }
  }

  showAddSubjectModal() {
    const modal = `
      <div class="modal fade" id="addSubjectModal" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Add New Subject</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <form id="addSubjectForm">
                <div class="mb-3">
                  <label class="form-label">Subject Name</label>
                  <input type="text" class="form-control" name="subject_name" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Class</label>
                  <select class="form-select" name="class_id" required>
                    <option value="">Select Class</option>
                    <option value="1">Class 1</option>
                    <option value="2">Class 2</option>
                    <option value="3">Class 3</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Teacher</label>
                  <select class="form-select" name="teacher_id" required>
                    <option value="">Select Teacher</option>
                    <option value="1">John Smith</option>
                    <option value="2">Jane Johnson</option>
                    <option value="3">Mike Wilson</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Description</label>
                  <textarea class="form-control" name="description" rows="3"></textarea>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary" onclick="adminDashboard.saveSubject()">Save Subject</button>
            </div>
          </div>
        </div>
      </div>
    `;

    // Remove existing modal if any
    const existingModal = document.getElementById("addSubjectModal");
    if (existingModal) {
      existingModal.remove();
    }

    // Add modal to body
    document.body.insertAdjacentHTML("beforeend", modal);

    // Show modal
    const modalElement = document.getElementById("addSubjectModal");
    const bootstrapModal = new bootstrap.Modal(modalElement);
    bootstrapModal.show();
  }

  async saveSubject() {
    const form = document.getElementById("addSubjectForm");
    const formData = new FormData(form);

    const subjectData = {
      action: "add",
      subject_name: formData.get("subject_name"),
      class_id: formData.get("class_id"),
      teacher_id: formData.get("teacher_id"),
      description: formData.get("description"),
    };

    try {
      showLoading(true);
      const response = await this.fetchData("subjects.php", subjectData);

      if (response.success) {
        this.showAlert("Subject added successfully!", "success");
        // Hide modal
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("addSubjectModal")
        );
        modal.hide();
        // Reload subjects data
        await this.loadSubjectsData();
      } else {
        this.showAlert(response.message || "Failed to add subject", "danger");
      }
    } catch (error) {
      console.error("Error saving subject:", error);
      this.showAlert("Error saving subject. Please try again.", "danger");
    } finally {
      showLoading(false);
    }
  }

  showAddExamModal() {
    const modal = `
      <div class="modal fade" id="addExamModal" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Schedule New Exam</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <form id="addExamForm">
                <div class="mb-3">
                  <label class="form-label">Exam Name</label>
                  <input type="text" class="form-control" name="exam_name" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Class</label>
                  <select class="form-select" name="class_id" required>
                    <option value="">Select Class</option>
                    <option value="1">Class 1</option>
                    <option value="2">Class 2</option>
                    <option value="3">Class 3</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Exam Date</label>
                  <input type="date" class="form-control" name="exam_date" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Start Time</label>
                  <input type="time" class="form-control" name="start_time" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Duration (minutes)</label>
                  <input type="number" class="form-control" name="duration" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Total Marks</label>
                  <input type="number" class="form-control" name="total_marks" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Description</label>
                  <textarea class="form-control" name="description" rows="3"></textarea>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary" onclick="adminDashboard.saveExam()">Schedule Exam</button>
            </div>
          </div>
        </div>
      </div>
    `;

    // Remove existing modal if any
    const existingModal = document.getElementById("addExamModal");
    if (existingModal) {
      existingModal.remove();
    }

    // Add modal to body
    document.body.insertAdjacentHTML("beforeend", modal);

    // Show modal
    const modalElement = document.getElementById("addExamModal");
    const bootstrapModal = new bootstrap.Modal(modalElement);
    bootstrapModal.show();
  }

  async saveExam() {
    const form = document.getElementById("addExamForm");
    const formData = new FormData(form);

    const examData = {
      action: "add",
      exam_name: formData.get("exam_name"),
      class_id: formData.get("class_id"),
      exam_date: formData.get("exam_date"),
      start_time: formData.get("start_time"),
      duration: formData.get("duration"),
      total_marks: formData.get("total_marks"),
      description: formData.get("description"),
    };

    try {
      showLoading(true);
      const response = await this.fetchData("exams.php", examData);

      if (response.success) {
        this.showAlert("Exam scheduled successfully!", "success");
        // Hide modal
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("addExamModal")
        );
        modal.hide();
        // Reload exams data
        await this.loadExamsData();
      } else {
        this.showAlert(response.message || "Failed to schedule exam", "danger");
      }
    } catch (error) {
      console.error("Error saving exam:", error);
      this.showAlert("Error saving exam. Please try again.", "danger");
    } finally {
      showLoading(false);
    }
  }

  showAddMarkModal() {
    const modal = `
      <div class="modal fade" id="addMarkModal" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Add Marks</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <form id="addMarkForm">
                <div class="mb-3">
                  <label class="form-label">Student</label>
                  <select class="form-select" name="student_id" required>
                    <option value="">Select Student</option>
                    <option value="1">Alice Brown</option>
                    <option value="2">Bob Wilson</option>
                    <option value="3">Carol Davis</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Subject</label>
                  <select class="form-select" name="subject_id" required>
                    <option value="">Select Subject</option>
                    <option value="1">Mathematics</option>
                    <option value="2">English</option>
                    <option value="3">Science</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Exam</label>
                  <select class="form-select" name="exam_id" required>
                    <option value="">Select Exam</option>
                    <option value="1">Mid Term</option>
                    <option value="2">Final Exam</option>
                    <option value="3">Unit Test</option>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="form-label">Marks Obtained</label>
                  <input type="number" class="form-control" name="marks_obtained" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Total Marks</label>
                  <input type="number" class="form-control" name="total_marks" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Remarks</label>
                  <textarea class="form-control" name="remarks" rows="3"></textarea>
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              <button type="button" class="btn btn-primary" onclick="adminDashboard.saveMark()">Save Marks</button>
            </div>
          </div>
        </div>
      </div>
    `;

    // Remove existing modal if any
    const existingModal = document.getElementById("addMarkModal");
    if (existingModal) {
      existingModal.remove();
    }

    // Add modal to body
    document.body.insertAdjacentHTML("beforeend", modal);

    // Show modal
    const modalElement = document.getElementById("addMarkModal");
    const bootstrapModal = new bootstrap.Modal(modalElement);
    bootstrapModal.show();
  }

  async saveMark() {
    const form = document.getElementById("addMarkForm");
    const formData = new FormData(form);

    const markData = {
      action: "add",
      student_id: formData.get("student_id"),
      subject_id: formData.get("subject_id"),
      exam_id: formData.get("exam_id"),
      marks_obtained: formData.get("marks_obtained"),
      total_marks: formData.get("total_marks"),
      remarks: formData.get("remarks"),
    };

    try {
      showLoading(true);
      const response = await this.fetchData("marks.php", markData);

      if (response.success) {
        this.showAlert("Marks added successfully!", "success");
        // Hide modal
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("addMarkModal")
        );
        modal.hide();
        // Reload marks data
        await this.loadMarksData();
      } else {
        this.showAlert(response.message || "Failed to add marks", "danger");
      }
    } catch (error) {
      console.error("Error saving marks:", error);
      this.showAlert("Error saving marks. Please try again.", "danger");
    } finally {
      showLoading(false);
    }
  }

  // Edit and Delete Functions
  editStudent(id) {
    this.showAlert(`Edit Student ${id} - Form will be implemented`, "info");
  }

  deleteStudent(id) {
    if (confirm("Are you sure you want to delete this student?")) {
      this.showAlert(`Delete Student ${id} - Will be implemented`, "info");
    }
  }

  editTeacher(id) {
    this.showAlert(`Edit Teacher ${id} - Form will be implemented`, "info");
  }

  deleteTeacher(id) {
    if (confirm("Are you sure you want to delete this teacher?")) {
      this.showAlert(`Delete Teacher ${id} - Will be implemented`, "info");
    }
  }

  editClass(id) {
    this.showAlert(`Edit Class ${id} - Form will be implemented`, "info");
  }

  deleteClass(id) {
    if (confirm("Are you sure you want to delete this class?")) {
      this.showAlert(`Delete Class ${id} - Will be implemented`, "info");
    }
  }

  editSubject(id) {
    this.showAlert(`Edit Subject ${id} - Form will be implemented`, "info");
  }

  deleteSubject(id) {
    if (confirm("Are you sure you want to delete this subject?")) {
      this.showAlert(`Delete Subject ${id} - Will be implemented`, "info");
    }
  }

  editExam(id) {
    this.showAlert(`Edit Exam ${id} - Form will be implemented`, "info");
  }

  deleteExam(id) {
    if (confirm("Are you sure you want to delete this exam?")) {
      this.showAlert(`Delete Exam ${id} - Will be implemented`, "info");
    }
  }

  editMark(id) {
    this.showAlert(`Edit Mark ${id} - Form will be implemented`, "info");
  }

  deleteMark(id) {
    if (confirm("Are you sure you want to delete this mark?")) {
      this.showAlert(`Delete Mark ${id} - Will be implemented`, "info");
    }
  }
}

// Initialize admin dashboard when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  window.adminDashboard = new AdminDashboard();
});

// Global function for navigation
function loadPage(page) {
  if (window.adminDashboard) {
    window.adminDashboard.loadPage(page);
  }
}
