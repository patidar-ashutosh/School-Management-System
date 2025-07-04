// Teacher Dashboard JavaScript for School Management System

class TeacherDashboard {
  constructor() {
    this.apiBaseUrl = "http://localhost:8000/api";
    this.currentPage = "dashboard";
    this.init();
  }

  init() {
    console.log("Teacher dashboard initializing...");
    // Check authentication immediately
    this.checkAuthentication();
  }

  checkAuthentication() {
    // Check if user is logged in
    const user = this.getCurrentUser();
    console.log("Current user:", user);

    // If no user or wrong role, redirect to login
    if (!user || user.role !== "teacher") {
      console.log("Authentication failed, redirecting to login");
      this.redirectToLogin();
      return;
    }

    console.log("Authentication successful for teacher");

    // Initialize dashboard after successful authentication
    this.setupEventListeners();
    this.loadDashboardStats();
    this.loadUserInfo();
    console.log("Teacher dashboard initialized successfully");
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
  }

  async loadDashboardStats() {
    try {
      console.log("Loading teacher dashboard stats...");
      const user = this.getCurrentUser();
      if (user) {
        // Get teacher ID from user data or session
        const teacherId = user.id;

        // Load teacher-specific statistics from APIs
        const [studentsCount, subjectsCount, examsCount, avgMarks] =
          await Promise.all([
            this.fetchData("students.php", { action: "get_count" }),
            this.fetchData("subjects.php", { action: "get_count" }),
            this.fetchData("exams.php", { action: "get_count" }),
            this.fetchData("marks.php", {
              action: "get_class_average",
              class_id: 1,
            }), // Default to class 1 for demo
          ]);

        console.log("Teacher dashboard stats loaded:", {
          studentsCount,
          subjectsCount,
          examsCount,
          avgMarks,
        });

        // Update statistics cards
        document.getElementById("totalStudents").textContent =
          studentsCount.count || 0;
        document.getElementById("totalSubjects").textContent =
          subjectsCount.count || 0;
        document.getElementById("totalExams").textContent =
          examsCount.count || 0;
        document.getElementById("avgMarks").textContent = `${
          avgMarks.average || 0
        }%`;
      }
    } catch (error) {
      console.error("Error loading teacher dashboard stats:", error);
      // Fallback to default values
      document.getElementById("totalStudents").textContent = "0";
      document.getElementById("totalSubjects").textContent = "0";
      document.getElementById("totalExams").textContent = "0";
      document.getElementById("avgMarks").textContent = "0%";
    }
  }

  loadUserInfo() {
    const user = this.getCurrentUser();
    if (user) {
      const teacherNameElement = document.getElementById("teacherName");
      if (teacherNameElement) {
        teacherNameElement.textContent = user.name;
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
      dashboard: "Teacher Dashboard",
      students: "My Students",
      subjects: "My Subjects",
      exams: "Exams",
      marks: "Manage Marks",
    };
    return titles[page] || "Teacher Dashboard";
  }

  showDashboard() {
    this.hideAllContent();
    document.getElementById("dashboardContent").style.display = "block";
  }

  async loadStudentsPage() {
    const content = document.getElementById("studentsContent");
    content.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>My Students</h2>
                <div class="btn-group">
                    <button class="btn btn-outline-primary" onclick="teacherDashboard.exportStudents()">
                        <i class="fas fa-download me-2"></i>Export
                    </button>
                </div>
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
      console.log("Loading students data for teacher...");
      const response = await this.fetchData("students.php", {
        action: "get_all",
      });
      const students = response.data || [];

      console.log("Students data loaded:", students);

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
                        <button class="btn btn-sm btn-outline-info" onclick="teacherDashboard.viewStudentMarks(${
                          student.id
                        })">
                            <i class="fas fa-chart-line"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-primary" onclick="teacherDashboard.contactStudent(${
                          student.id
                        })">
                            <i class="fas fa-envelope"></i>
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

  async loadSubjectsPage() {
    const content = document.getElementById("subjectsContent");
    content.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>My Subjects</h2>
            </div>
            <div class="row" id="subjectsContainer">
                <div class="col-12 text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading subjects...</p>
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
      console.log("Loading subjects data for teacher...");
      const response = await this.fetchData("subjects.php", {
        action: "get_all",
      });
      const subjects = response.data || [];

      console.log("Subjects data loaded:", subjects);

      const container = document.getElementById("subjectsContainer");
      if (subjects.length === 0) {
        container.innerHTML = `
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>
                    No subjects found
                </div>
            </div>
        `;
        return;
      }

      container.innerHTML = subjects
        .map(
          (subject) => `
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-book fa-3x text-primary mb-3"></i>
                        <h5 class="card-title">${subject.name}</h5>
                        <p class="card-text">${
                          subject.description || "No description"
                        }</p>
                        <p class="text-muted">Code: ${subject.code}</p>
                        <button class="btn btn-primary" onclick="teacherDashboard.viewSubjectDetails(${
                          subject.id
                        })">
                            View Details
                        </button>
                    </div>
                </div>
            </div>
        `
        )
        .join("");
    } catch (error) {
      console.error("Error loading subjects:", error);
      document.getElementById("subjectsContainer").innerHTML = `
        <div class="col-12">
            <div class="alert alert-danger text-center">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Error loading subjects
            </div>
        </div>
      `;
    }
  }

  async loadExamsPage() {
    const content = document.getElementById("examsContent");
    content.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Exams</h2>
                <button class="btn btn-primary" onclick="teacherDashboard.createExam()">
                    <i class="fas fa-plus me-2"></i>Create Exam
                </button>
            </div>
            <div class="row" id="examsContainer">
                <div class="col-12 text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading exams...</p>
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
      console.log("Loading exams data for teacher...");
      const response = await this.fetchData("exams.php", {
        action: "get_all",
      });
      const exams = response.data || [];

      console.log("Exams data loaded:", exams);

      const container = document.getElementById("examsContainer");
      if (exams.length === 0) {
        container.innerHTML = `
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle me-2"></i>
                    No exams found
                </div>
            </div>
        `;
        return;
      }

      // Separate upcoming and recent exams
      const today = new Date();
      const upcomingExams = exams.filter(
        (exam) => new Date(exam.exam_date) >= today
      );
      const recentExams = exams.filter(
        (exam) => new Date(exam.exam_date) < today
      );

      container.innerHTML = `
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Upcoming Exams</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        ${
                          upcomingExams.length > 0
                            ? upcomingExams
                                .map(
                                  (exam) => `
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">${exam.exam_name}</h6>
                                    <small class="text-muted">${
                                      exam.class_name || "N/A"
                                    }</small>
                                </div>
                                <span class="badge bg-primary">${new Date(
                                  exam.exam_date
                                ).toLocaleDateString()}</span>
                            </div>
                        `
                                )
                                .join("")
                            : '<div class="text-center text-muted">No upcoming exams</div>'
                        }
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Exams</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        ${
                          recentExams.length > 0
                            ? recentExams
                                .map(
                                  (exam) => `
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">${exam.exam_name}</h6>
                                    <small class="text-muted">${
                                      exam.class_name || "N/A"
                                    }</small>
                                </div>
                                <span class="badge bg-success">${new Date(
                                  exam.exam_date
                                ).toLocaleDateString()}</span>
                            </div>
                        `
                                )
                                .join("")
                            : '<div class="text-center text-muted">No recent exams</div>'
                        }
                    </div>
                </div>
            </div>
        </div>
      `;
    } catch (error) {
      console.error("Error loading exams:", error);
      document.getElementById("examsContainer").innerHTML = `
        <div class="col-12">
            <div class="alert alert-danger text-center">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Error loading exams
            </div>
        </div>
      `;
    }
  }

  async loadMarksPage() {
    const content = document.getElementById("marksContent");
    content.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Manage Marks</h2>
                <div class="btn-group">
                    <button class="btn btn-primary" onclick="teacherDashboard.addMarks()">
                        <i class="fas fa-plus me-2"></i>Add Marks
                    </button>
                    <button class="btn btn-outline-secondary" onclick="teacherDashboard.exportMarks()">
                        <i class="fas fa-download me-2"></i>Export
                    </button>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="marksTable">
                            <thead>
                                <tr>
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

    // Load marks data
    await this.loadMarksData();
  }

  async loadMarksData() {
    try {
      console.log("Loading marks data for teacher...");
      const response = await this.fetchData("marks.php", {
        action: "get_all",
      });
      const marks = response.data || [];

      console.log("Marks data loaded:", marks);

      const tbody = document.getElementById("marksTableBody");
      if (marks.length === 0) {
        tbody.innerHTML =
          '<tr><td colspan="6" class="text-center">No marks found</td></tr>';
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
                        <td>${mark.student_name || "N/A"}</td>
                        <td>${mark.subject_name || "N/A"}</td>
                        <td>${mark.exam_name || "N/A"}</td>
                        <td>${mark.marks_obtained}/${mark.total_marks}</td>
                        <td><span class="badge bg-success">${percentage}%</span></td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" onclick="teacherDashboard.editMarks(${
                              mark.id
                            })">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger" onclick="teacherDashboard.deleteMarks(${
                              mark.id
                            })">
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
        '<tr><td colspan="6" class="text-center text-danger">Error loading marks</td></tr>';
    }
  }

  hideAllContent() {
    const contentDivs = [
      "dashboardContent",
      "studentsContent",
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

  // Teacher-specific methods
  exportStudents() {
    this.showAlert("Export functionality will be implemented", "info");
  }

  viewStudentMarks(studentId) {
    this.showAlert(
      `View marks for student ${studentId} functionality will be implemented`,
      "info"
    );
  }

  contactStudent(studentId) {
    this.showAlert(
      `Contact student ${studentId} functionality will be implemented`,
      "info"
    );
  }

  viewSubjectDetails(subjectId) {
    this.showAlert(
      `View subject ${subjectId} details functionality will be implemented`,
      "info"
    );
  }

  createExam() {
    const modal = `
      <div class="modal fade" id="createExamModal" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Create New Exam</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <form id="createExamForm">
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
              <button type="button" class="btn btn-primary" onclick="teacherDashboard.saveExam()">Create Exam</button>
            </div>
          </div>
        </div>
      </div>
    `;

    // Remove existing modal if any
    const existingModal = document.getElementById("createExamModal");
    if (existingModal) {
      existingModal.remove();
    }

    // Add modal to body
    document.body.insertAdjacentHTML("beforeend", modal);

    // Show modal
    const modalElement = document.getElementById("createExamModal");
    const bootstrapModal = new bootstrap.Modal(modalElement);
    bootstrapModal.show();
  }

  async saveExam() {
    const form = document.getElementById("createExamForm");
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
        this.showAlert("Exam created successfully!", "success");
        // Hide modal
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("createExamModal")
        );
        modal.hide();
        // Reload exams data
        await this.loadExamsData();
      } else {
        this.showAlert(response.message || "Failed to create exam", "danger");
      }
    } catch (error) {
      console.error("Error creating exam:", error);
      this.showAlert("Error creating exam. Please try again.", "danger");
    } finally {
      showLoading(false);
    }
  }

  addMarks() {
    const modal = `
      <div class="modal fade" id="addMarksModal" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Add Marks</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
              <form id="addMarksForm">
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
              <button type="button" class="btn btn-primary" onclick="teacherDashboard.saveMarks()">Save Marks</button>
            </div>
          </div>
        </div>
      </div>
    `;

    // Remove existing modal if any
    const existingModal = document.getElementById("addMarksModal");
    if (existingModal) {
      existingModal.remove();
    }

    // Add modal to body
    document.body.insertAdjacentHTML("beforeend", modal);

    // Show modal
    const modalElement = document.getElementById("addMarksModal");
    const bootstrapModal = new bootstrap.Modal(modalElement);
    bootstrapModal.show();
  }

  async saveMarks() {
    const form = document.getElementById("addMarksForm");
    const formData = new FormData(form);

    const marksData = {
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
      const response = await this.fetchData("marks.php", marksData);

      if (response.success) {
        this.showAlert("Marks added successfully!", "success");
        // Hide modal
        const modal = bootstrap.Modal.getInstance(
          document.getElementById("addMarksModal")
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

  exportMarks() {
    this.showAlert("Export marks functionality will be implemented", "info");
  }

  editMarks(markId) {
    this.showAlert(
      `Edit marks ${markId} functionality will be implemented`,
      "info"
    );
  }

  deleteMarks(markId) {
    if (confirm("Are you sure you want to delete this mark?")) {
      this.showAlert(
        `Delete marks ${markId} functionality will be implemented`,
        "info"
      );
    }
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
      } else if (data.action === "get_class_average") {
        return { success: true, average: 0 };
      }

      throw error;
    }
  }
}

// Initialize teacher dashboard when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  window.teacherDashboard = new TeacherDashboard();
});
