// Student Dashboard JavaScript for School Management System

class StudentDashboard {
  constructor() {
    this.apiBaseUrl = "http://localhost:8000/api";
    this.currentPage = "dashboard";
    this.init();
  }

  init() {
    console.log("Student dashboard initializing...");
    // Check authentication immediately
    this.checkAuthentication();
  }

  checkAuthentication() {
    // Check if user is logged in
    const user = this.getCurrentUser();
    console.log("Current user:", user);

    // If no user or wrong role, redirect to login
    if (!user || user.role !== "student") {
      console.log("Authentication failed, redirecting to login");
      this.redirectToLogin();
      return;
    }

    console.log("Authentication successful for student");

    // Initialize dashboard after successful authentication
    this.setupEventListeners();
    this.loadDashboardStats();
    this.loadUserInfo();
    console.log("Student dashboard initialized successfully");
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
      console.log("Loading student dashboard stats...");
      const user = this.getCurrentUser();
      if (user) {
        // Load student-specific statistics from APIs
        const [subjectsCount, examsCount, avgMarks] = await Promise.all([
          this.fetchData("subjects.php", { action: "get_count" }),
          this.fetchData("exams.php", { action: "get_count" }),
          this.fetchData("marks.php", {
            action: "get_student_average",
            student_id: user.id,
          }),
        ]);

        console.log("Student dashboard stats loaded:", {
          subjectsCount,
          examsCount,
          avgMarks,
        });

        // Update statistics cards
        document.getElementById("totalSubjects").textContent =
          subjectsCount.count || 0;
        document.getElementById("totalExams").textContent =
          examsCount.count || 0;
        document.getElementById("avgMarks").textContent = `${
          avgMarks.average || 0
        }%`;
        document.getElementById("attendance").textContent = "95%"; // This would need a separate attendance API
      }
    } catch (error) {
      console.error("Error loading student dashboard stats:", error);
      // Fallback to default values
      document.getElementById("totalSubjects").textContent = "0";
      document.getElementById("totalExams").textContent = "0";
      document.getElementById("avgMarks").textContent = "0%";
      document.getElementById("attendance").textContent = "0%";
    }
  }

  loadUserInfo() {
    const user = this.getCurrentUser();
    if (user) {
      const studentNameElement = document.getElementById("studentName");
      if (studentNameElement) {
        studentNameElement.textContent = user.name;
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
        case "profile":
          await this.loadProfilePage();
          break;
        case "subjects":
          await this.loadSubjectsPage();
          break;
        case "exams":
          await this.loadExamsPage();
          break;
        case "schedule":
          await this.loadSchedulePage();
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
      dashboard: "Student Dashboard",
      profile: "My Profile",
      subjects: "My Subjects",
      exams: "My Exams",
      schedule: "Class Schedule",
    };
    return titles[page] || "Student Dashboard";
  }

  showDashboard() {
    this.hideAllContent();
    document.getElementById("dashboardContent").style.display = "block";
  }

  async loadProfilePage() {
    const content = document.getElementById("profileContent");
    content.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>My Profile</h2>
                <button class="btn btn-primary" onclick="studentDashboard.editProfile()">
                    <i class="fas fa-edit me-2"></i>Edit Profile
                </button>
            </div>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-user-circle fa-5x text-primary"></i>
                            </div>
                            <h5 class="card-title">Alice Brown</h5>
                            <p class="text-muted">Student</p>
                            <p class="mb-0"><strong>Roll Number:</strong> STU001</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-8 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Personal Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><strong>Full Name:</strong></label>
                                    <p>Alice Brown</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><strong>Email:</strong></label>
                                    <p>alice.brown@school.com</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><strong>Class:</strong></label>
                                    <p>Class 1</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><strong>Section:</strong></label>
                                    <p>A</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><strong>Date of Birth:</strong></label>
                                    <p>March 15, 2015</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label"><strong>Phone:</strong></label>
                                    <p>+1 234 567 8900</p>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label"><strong>Address:</strong></label>
                                    <p>123 School Street, Education City, EC 12345</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

    this.hideAllContent();
    content.style.display = "block";
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
      console.log("Loading subjects data for student...");
      const response = await this.fetchData("subjects.php", {
        action: "get_all",
      });
      const subjects = response.data || [];

      console.log("Student subjects data loaded:", subjects);

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
                        <div class="progress mb-3">
                            <div class="progress-bar bg-success" style="width: 85%">85%</div>
                        </div>
                        <button class="btn btn-primary" onclick="studentDashboard.viewSubjectDetails(${
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
      console.error("Error loading student subjects:", error);
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
                <h2>My Exams</h2>
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
      console.log("Loading exams data for student...");
      const response = await this.fetchData("exams.php", {
        action: "get_all",
      });
      const exams = response.data || [];

      console.log("Student exams data loaded:", exams);

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
      console.error("Error loading student exams:", error);
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

  async loadSchedulePage() {
    const content = document.getElementById("scheduleContent");
    content.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Class Schedule</h2>
                <button class="btn btn-outline-primary" onclick="studentDashboard.downloadSchedule()">
                    <i class="fas fa-download me-2"></i>Download Schedule
                </button>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Monday</th>
                                    <th>Tuesday</th>
                                    <th>Wednesday</th>
                                    <th>Thursday</th>
                                    <th>Friday</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><strong>8:00 - 9:00</strong></td>
                                    <td class="table-primary">Mathematics<br><small>Mr. Smith</small></td>
                                    <td class="table-success">English<br><small>Ms. Johnson</small></td>
                                    <td class="table-warning">Science<br><small>Mr. Wilson</small></td>
                                    <td class="table-primary">Mathematics<br><small>Mr. Smith</small></td>
                                    <td class="table-success">English<br><small>Ms. Johnson</small></td>
                                </tr>
                                <tr>
                                    <td><strong>9:00 - 10:00</strong></td>
                                    <td class="table-success">English<br><small>Ms. Johnson</small></td>
                                    <td class="table-warning">Science<br><small>Mr. Wilson</small></td>
                                    <td class="table-primary">Mathematics<br><small>Mr. Smith</small></td>
                                    <td class="table-success">English<br><small>Ms. Johnson</small></td>
                                    <td class="table-warning">Science<br><small>Mr. Wilson</small></td>
                                </tr>
                                <tr>
                                    <td><strong>10:00 - 10:15</strong></td>
                                    <td colspan="5" class="text-center table-secondary"><strong>Break</strong></td>
                                </tr>
                                <tr>
                                    <td><strong>10:15 - 11:15</strong></td>
                                    <td class="table-warning">Science<br><small>Mr. Wilson</small></td>
                                    <td class="table-primary">Mathematics<br><small>Mr. Smith</small></td>
                                    <td class="table-success">English<br><small>Ms. Johnson</small></td>
                                    <td class="table-warning">Science<br><small>Mr. Wilson</small></td>
                                    <td class="table-primary">Mathematics<br><small>Mr. Smith</small></td>
                                </tr>
                                <tr>
                                    <td><strong>11:15 - 12:15</strong></td>
                                    <td class="table-info">Physical Education<br><small>Mr. Coach</small></td>
                                    <td class="table-info">Art<br><small>Ms. Artist</small></td>
                                    <td class="table-info">Music<br><small>Mr. Musician</small></td>
                                    <td class="table-info">Physical Education<br><small>Mr. Coach</small></td>
                                    <td class="table-info">Art<br><small>Ms. Artist</small></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;

    this.hideAllContent();
    content.style.display = "block";
  }

  hideAllContent() {
    const contentDivs = [
      "dashboardContent",
      "profileContent",
      "subjectsContent",
      "examsContent",
      "scheduleContent",
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

  // Student-specific methods
  editProfile() {
    this.showAlert("Edit profile functionality will be implemented", "info");
  }

  viewSubjectDetails(subjectId) {
    this.showAlert(
      `View subject ${subjectId} details functionality will be implemented`,
      "info"
    );
  }

  downloadSchedule() {
    this.showAlert(
      "Download schedule functionality will be implemented",
      "info"
    );
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
      } else if (data.action === "get_by_student") {
        return { success: true, data: [] };
      } else if (data.action === "get_student_average") {
        return { success: true, average: 0 };
      }

      throw error;
    }
  }
}

// Initialize student dashboard when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  window.studentDashboard = new StudentDashboard();
});
