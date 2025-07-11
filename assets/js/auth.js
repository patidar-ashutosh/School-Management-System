// Authentication JavaScript for School Management System

class Auth {
  constructor() {
    this.baseUrl = window.location.origin;
    this.apiUrl = this.baseUrl + "/backend/controllers/auth.php";
    this.currentUser = null;
    // Restore user from localStorage if present
    const userStr = localStorage.getItem("user");
    if (userStr) {
      this.currentUser = JSON.parse(userStr);
    }
    this.init();
  }

  init() {
    // Check auth status on all pages, but handle login pages differently
    this.checkAuthStatus();
    this.setupEventListeners();
  }

  isLoginPage() {
    const currentPath = window.location.pathname;
    return (
      currentPath.includes("/index.html") ||
      currentPath.endsWith("/") ||
      currentPath.includes("/login") ||
      currentPath.includes("/frontend/index.html")
    );
  }

  setupEventListeners() {
    // Login form submission
    const loginForm = document.getElementById("loginForm");
    if (loginForm) {
      loginForm.addEventListener("submit", (e) => this.handleLogin(e));
    }

    // Password toggle
    const togglePassword = document.getElementById("togglePassword");
    if (togglePassword) {
      togglePassword.addEventListener("click", () =>
        this.togglePasswordVisibility()
      );
    }

    // Setup logout functionality
    document.addEventListener("click", (e) => {
      if (
        e.target.matches("[data-logout]") ||
        e.target.closest("[data-logout]")
      ) {
        e.preventDefault();
        this.logout();
      }
    });
  }

  async handleLogin(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const email = formData.get("email");
    const password = formData.get("password");
    const role = formData.get("role");

    if (!email || !password || !role) {
      this.showAlert("Please fill in all fields", "danger");
      return;
    }

    console.log("Attempting login with:", { email, role });
    showLoading(true);

    try {
      let loginData;

      if (role === "student") {
        // For students, use email and role
        loginData = {
          email: email,
          password: password,
          role: role,
        };
      } else {
        // For admin/teacher, use email and role
        loginData = {
          email: email,
          password: password,
          role: role,
        };
      }

      const response = await fetch(this.apiUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(loginData),
        credentials: "include",
      });

      const data = await response.json();
      console.log("Login response:", data);

      if (data.success) {
        this.currentUser = data.user;
        localStorage.setItem("user", JSON.stringify(data.user)); // Persist user
        this.showAlert("Login successful! Redirecting...", "success");

        // Redirect based on role
        setTimeout(() => {
          console.log("Redirecting to dashboard for role:", data.user.role);
          this.redirectAfterLogin(data.user.role);
        }, 1000);
      } else {
        this.showAlert(data.message || "Login failed", "danger");
      }
    } catch (error) {
      console.error("Login error:", error);
      this.showAlert("Network error. Please try again.", "danger");
    } finally {
      showLoading(false);
    }
  }

  async checkAuthStatus() {
    try {
      const response = await fetch(this.apiUrl, {
        method: "GET",
        credentials: "include",
      });

      if (response.ok) {
        const data = await response.json();
        if (data.loggedIn) {
          this.currentUser = data.user;
          this.updateUI();

          // If we're on a login page and user is already logged in, show a message
          if (this.isLoginPage()) {
            this.showLoggedInMessage();
          } else {
            // Check if user has access to current page (only for protected pages)
            this.validatePageAccess();
          }
        } else {
          // User is not logged in
          if (!this.isLoginPage()) {
            // Only redirect to login if we're not already on a login page
            this.redirectToLogin();
          }
        }
      } else {
        // Response not ok
        if (!this.isLoginPage()) {
          // Only redirect to login if we're not already on a login page
          this.redirectToLogin();
        }
      }
    } catch (error) {
      console.error("Auth check failed:", error);
      if (!this.isLoginPage()) {
        // Only redirect to login if we're not already on a login page
        this.redirectToLogin();
      }
    }
  }

  showLoggedInMessage() {
    // Show a message that user is already logged in
    const existingMessage = document.querySelector(".logged-in-message");
    if (existingMessage) {
      existingMessage.remove();
    }

    // Get display name with proper fallbacks
    let displayName = "User";
    if (this.currentUser.first_name && this.currentUser.last_name) {
      displayName =
        this.currentUser.first_name + " " + this.currentUser.last_name;
    } else if (this.currentUser.username) {
      displayName = this.currentUser.username;
    } else {
      const roleNames = {
        principal: "Principal",
        teacher: "Teacher",
        student: "Student",
      };
      displayName = roleNames[this.currentUser.role] || "User";
    }

    // Create a full-screen overlay for the message
    const overlay = document.createElement("div");
    overlay.className = "logged-in-overlay";
    overlay.style = `
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      width: 100vw; height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 99999;
      background: rgba(255,255,255,0.3);
      backdrop-filter: blur(6px);
    `;

    const messageDiv = document.createElement("div");
    messageDiv.className =
      "logged-in-message alert alert-info alert-dismissible fade show";
    messageDiv.style = `
      max-width: 420px;
      width: 90vw;
      text-align: center;
      box-shadow: 0 8px 32px rgba(102, 126, 234, 0.1), 0 1.5px 6px rgba(0,0,0,0.04);
      border-radius: 16px;
      background: white;
      padding: 2.5rem 1.5rem;
      margin: 0;
      position: relative;
      font-size: 1.1rem;
      color: black;
    `;
    messageDiv.innerHTML = `
      <i class="fas fa-info-circle"></i> 
      You are already logged in as <strong style="font-weight: bold; color: black;">${displayName}</strong> (${
      this.currentUser.role
    }).
      <span class="redirect-countdown">Redirecting to dashboard in <span id="countdown">5</span> seconds...</span>
      <a href="${this.getDashboardUrlForRole(
        this.currentUser.role
      )}" id="dashboard-link">Go to Dashboard Now</a>
      <button type="button" class="btn-close" data-bs-dismiss="alert" style="position:absolute;top:12px;right:12px;"></button>
    `;
    overlay.appendChild(messageDiv);
    document.body.appendChild(overlay);

    document.getElementById("dashboard-link").style =
      "font-weight: bold; color: black; padding: 4px 8px; border-radius: 4px; cursor: pointer;";

    // Start countdown and redirect
    let countdown = 5;
    const countdownElement = document.getElementById("countdown");
    countdownElement.style = "font-weight: bold; color: black;";
    const countdownInterval = setInterval(() => {
      countdown--;
      if (countdownElement) {
        countdownElement.textContent = countdown;
      }

      if (countdown <= 0) {
        clearInterval(countdownInterval);
        // Redirect to dashboard
        window.location.href = this.getDashboardUrlForRole(
          this.currentUser.role
        );
      }
    }, 1000);

    // Auto-dismiss after 10 seconds (as backup, but redirect should happen first)
    setTimeout(() => {
      if (messageDiv.parentNode) {
        messageDiv.remove();
      }
    }, 10000);
  }

  getDashboardUrlForRole(role) {
    switch (role) {
      case "principal":
        return (
          this.baseUrl +
          "/frontend/admin/principal/pages/principal-dashboard.html"
        );
      case "teacher":
        return (
          this.baseUrl + "/frontend/admin/teacher/pages/teacher-dashboard.html"
        );
      case "student":
        return this.baseUrl + "/frontend/user/pages/student-dashboard.html";
      default:
        return this.baseUrl + "/frontend/index.html";
    }
  }

  validatePageAccess() {
    if (!this.currentUser) return;

    const currentPath = window.location.pathname;
    const userRole = this.currentUser.role;

    // Define allowed paths for each role
    const allowedPaths = {
      principal: [
        "/frontend/admin/principal/",
        "/frontend/admin/teacher/",
        "/frontend/user/",
      ],
      teacher: ["/frontend/admin/teacher/", "/frontend/user/"],
      student: ["/frontend/user/", "/frontend/admin/teacher/"],
    };

    const userAllowedPaths = allowedPaths[userRole] || [];
    const hasAccess = userAllowedPaths.some((path) =>
      currentPath.includes(path)
    );

    if (!hasAccess) {
      console.log(
        `User role ${userRole} doesn't have access to ${currentPath}`
      );
      this.showAlert(
        `You don't have permission to access this page. Redirecting to your dashboard.`,
        "warning"
      );
      setTimeout(() => {
        this.redirectToUserDashboard();
      }, 2000);
    }
  }

  redirectToUserDashboard() {
    if (!this.currentUser) return;

    switch (this.currentUser.role) {
      case "principal":
        window.location.href =
          this.baseUrl +
          "/frontend/admin/principal/pages/principal-dashboard.html";
        break;
      case "teacher":
        window.location.href =
          this.baseUrl + "/frontend/admin/teacher/pages/teacher-dashboard.html";
        break;
      case "student":
        window.location.href =
          this.baseUrl + "/frontend/user/pages/student-dashboard.html";
        break;
      default:
        window.location.href = this.baseUrl + "/frontend/index.html";
    }
  }

  async logout() {
    try {
      console.log("Logging out user...");

      // Call backend logout
      const response = await fetch(this.apiUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ action: "logout" }),
        credentials: "include",
      });

      const data = await response.json();
      console.log("Logout response:", data);

      if (data.success) {
        console.log("Logout successful, redirecting to login");
        this.currentUser = null;
        localStorage.removeItem("user"); // Clear user
        this.redirectToLogin();
      } else {
        console.log("Logout failed, but clearing local data");
        this.currentUser = null;
        localStorage.removeItem("user"); // Clear user
        this.redirectToLogin();
      }
    } catch (error) {
      console.error("Logout error:", error);
      // Force redirect even if logout fails
      this.currentUser = null;
      localStorage.removeItem("user"); // Clear user
      this.redirectToLogin();
    }
  }

  redirectAfterLogin(role) {
    const currentPath = window.location.pathname;
    let targetUrl = this.baseUrl;

    switch (role) {
      case "principal":
        targetUrl += "/frontend/admin/principal/pages/principal-dashboard.html";
        break;
      case "teacher":
        targetUrl += "/frontend/admin/teacher/pages/teacher-dashboard.html";
        break;
      case "student":
        targetUrl += "/frontend/user/pages/student-dashboard.html";
        break;
      default:
        targetUrl += "/frontend/index.html";
    }

    console.log("Redirecting to:", targetUrl);
    window.location.href = targetUrl;
  }

  redirectToLogin() {
    const currentPath = window.location.pathname;

    if (currentPath.includes("/admin/principal/")) {
      window.location.href =
        this.baseUrl + "/frontend/admin/principal/index.html";
    } else if (currentPath.includes("/admin/teacher/")) {
      window.location.href =
        this.baseUrl + "/frontend/admin/teacher/index.html";
    } else if (currentPath.includes("/user/")) {
      window.location.href = this.baseUrl + "/frontend/user/index.html";
    } else {
      window.location.href = this.baseUrl + "/frontend/index.html";
    }
  }

  updateUI() {
    // Update user name display
    const userNameElements = document.querySelectorAll("[data-username]");
    userNameElements.forEach((element) => {
      if (this.currentUser) {
        // For all roles, use first_name + last_name if available, otherwise fallback to username or role
        if (this.currentUser.first_name && this.currentUser.last_name) {
          element.textContent =
            this.currentUser.first_name + " " + this.currentUser.last_name;
        } else if (this.currentUser.username) {
          element.textContent = this.currentUser.username;
        } else {
          // Fallback to role-based display
          const roleNames = {
            principal: "Principal",
            teacher: "Teacher",
            student: "Student",
          };
          element.textContent = roleNames[this.currentUser.role] || "User";
        }
      }
    });

    // Update role-specific UI elements
    if (this.currentUser) {
      document.body.setAttribute("data-role", this.currentUser.role);

      // Show/hide role-specific elements
      const roleElements = document.querySelectorAll("[data-role]");
      roleElements.forEach((element) => {
        const requiredRole = element.getAttribute("data-role");
        if (requiredRole === this.currentUser.role) {
          element.style.display = "";
        } else {
          element.style.display = "none";
        }
      });
    }
  }

  togglePasswordVisibility() {
    const passwordInput = document.getElementById("password");
    const toggleButton = document.getElementById("togglePassword");
    const icon = toggleButton.querySelector("i");

    if (passwordInput.type === "password") {
      passwordInput.type = "text";
      icon.classList.remove("fa-eye");
      icon.classList.add("fa-eye-slash");
    } else {
      passwordInput.type = "password";
      icon.classList.remove("fa-eye-slash");
      icon.classList.add("fa-eye");
    }
  }

  showAlert(message, type = "info") {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll(".alert-dismissible");
    existingAlerts.forEach((alert) => alert.remove());

    // Create new alert
    const alertDiv = document.createElement("div");
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

    // Insert alert after the form
    const loginForm = document.getElementById("loginForm");
    if (loginForm) {
      loginForm.parentNode.insertBefore(alertDiv, loginForm.nextSibling);
    } else {
      // Insert at the top of the body if no form
      document.body.insertBefore(alertDiv, document.body.firstChild);
    }

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
      if (alertDiv.parentNode) {
        alertDiv.remove();
      }
    }, 5000);
  }

  // Utility method to get current user
  getCurrentUser() {
    return this.currentUser;
  }

  // Utility method to check if user is authenticated
  isAuthenticated() {
    return this.currentUser !== null;
  }

  // Utility method to get user role
  getUserRole() {
    return this.currentUser ? this.currentUser.role : null;
  }

  // Method to check if user should be on current page
  checkPageAccess() {
    const user = this.getCurrentUser();
    const currentUrl = window.location.href;

    console.log("Checking page access:", { user, currentUrl });

    if (!user) {
      console.log("No user found, redirecting to login");
      this.redirectToLogin();
      return false;
    }

    const expectedPage = this.getExpectedPageForRole(user.role);
    if (currentUrl !== expectedPage) {
      console.log("User on wrong page, redirecting to expected page");
      window.location.href = expectedPage;
      return false;
    }

    return true;
  }

  getExpectedPageForRole(role) {
    const baseUrl = this.baseUrl;
    switch (role) {
      case "principal":
        return (
          baseUrl + "/frontend/admin/principal/pages/principal-dashboard.html"
        );
      case "teacher":
        return baseUrl + "/frontend/admin/teacher/pages/teacher-dashboard.html";
      case "student":
        return baseUrl + "/frontend/user/pages/student-dashboard.html";
      default:
        return baseUrl + "/frontend/index.html";
    }
  }
}

// Initialize Auth class
const auth = new Auth();

// Global logout function
function logout() {
  auth.logout();
}

// Global loading functions
function showLoading(element) {
  if (element) {
    element.disabled = true;
    element.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
  }
}

function hideLoading(element) {
  if (element) {
    element.disabled = false;
    element.innerHTML = element.getAttribute("data-original-text") || "Submit";
  }
}

// Global alert function
function showAlert(message, type = "info") {
  // Remove existing alerts
  const existingAlerts = document.querySelectorAll(".alert-dismissible");
  existingAlerts.forEach((alert) => alert.remove());

  // Create new alert
  const alertDiv = document.createElement("div");
  alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
  alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

  // Insert alert at the top of the body
  document.body.insertBefore(alertDiv, document.body.firstChild);

  // Auto-dismiss after 5 seconds
  setTimeout(() => {
    if (alertDiv.parentNode) {
      alertDiv.remove();
    }
  }, 5000);
}

// Utility functions
function validateForm(formData) {
  for (let [key, value] of formData.entries()) {
    if (!value || value.trim() === "") {
      return false;
    }
  }
  return true;
}

function formatDate(dateString) {
  if (!dateString) return "";
  const date = new Date(dateString);
  return date.toLocaleDateString();
}

function formatTime(timeString) {
  if (!timeString) return "";
  return timeString.substring(0, 5); // Return HH:MM format
}

function toggleDropdown() {
  const dropdown = document.querySelector(".dropdown-menu");
  if (dropdown) {
    dropdown.classList.toggle("show");
  }
}

// Initialize page
document.addEventListener("DOMContentLoaded", function () {
  // Check authentication on protected pages
  const currentPath = window.location.pathname;
  if (currentPath.includes("dashboard") || currentPath.includes("manage")) {
    auth.checkAuthStatus();
  }

  // Add event listeners for dropdown
  const userInfo = document.querySelector(".user-info");
  if (userInfo) {
    userInfo.addEventListener("click", toggleDropdown);
  }
});

// Export for use in other scripts
window.AuthManager = Auth;
window.authManager = auth;
window.logout = logout;
