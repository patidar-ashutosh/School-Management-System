// Authentication JavaScript for School Management System

class Auth {
  constructor() {
    this.apiUrl = "http://localhost:8000/api/auth.php";
    this.init();
  }

  init() {
    this.setupEventListeners();
    this.checkSession();
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
      const response = await fetch(this.apiUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          action: "login",
          email: email,
          password: password,
          role: role,
        }),
      });

      const data = await response.json();
      console.log("Login response:", data);

      if (data.success) {
        this.showAlert("Login successful! Redirecting...", "success");

        // Store user data in localStorage
        localStorage.setItem("user", JSON.stringify(data.user));
        console.log("User data stored:", data.user);

        // Redirect based on role
        setTimeout(() => {
          console.log("Redirecting to dashboard for role:", data.user.role);
          this.redirectToDashboard(data.user.role);
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

  async checkSession() {
    // Don't check session on dashboard pages to prevent infinite redirect
    const currentPath = window.location.pathname;
    if (currentPath.includes("/pages/")) {
      console.log("On dashboard page, skipping session check");
      return;
    }

    try {
      const response = await fetch(this.apiUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          action: "check_session",
        }),
      });

      const data = await response.json();
      console.log("Session check response:", data);

      if (data.success && data.authenticated) {
        // User is already logged in, redirect to dashboard
        console.log("User already logged in, redirecting to dashboard");
        this.redirectToDashboard(data.user.role);
      } else {
        // Check if there's user data in localStorage
        const user = this.getCurrentUser();
        if (user) {
          console.log("User found in localStorage, redirecting to dashboard");
          this.redirectToDashboard(user.role);
        }
      }
    } catch (error) {
      console.error("Session check error:", error);
      // Fallback to localStorage check
      const user = this.getCurrentUser();
      if (user) {
        console.log(
          "Fallback: User found in localStorage, redirecting to dashboard"
        );
        this.redirectToDashboard(user.role);
      }
    }
  }

  async logout() {
    try {
      console.log("Logging out user...");

      // Clear localStorage first
      localStorage.removeItem("user");
      console.log("Cleared localStorage");

      // Call backend logout
      const response = await fetch(this.apiUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          action: "logout",
        }),
      });

      const data = await response.json();
      console.log("Logout response:", data);

      if (data.success) {
        console.log("Logout successful, redirecting to login");
        // Redirect to login page
        this.redirectToLogin();
      } else {
        console.log("Logout failed, but clearing local data");
        // Even if backend logout fails, redirect to login
        this.redirectToLogin();
      }
    } catch (error) {
      console.error("Logout error:", error);
      // Even if there's an error, clear local data and redirect
      localStorage.removeItem("user");
      this.redirectToLogin();
    }
  }

  redirectToDashboard(role) {
    // Simple string-based approach for Live Server
    const currentUrl = window.location.href;
    const currentPath = window.location.pathname;

    console.log("Current URL:", currentUrl);
    console.log("Current path:", currentPath);

    let targetUrl = "";

    // Check if we're in pages directory or at root
    if (currentPath.includes("/pages/")) {
      // We're in pages directory, go back to root first
      targetUrl = "../pages/";
    } else {
      // We're at root or other directory
      targetUrl = "./pages/";
    }

    switch (role) {
      case "admin":
        targetUrl += "admin-dashboard.html";
        break;
      case "teacher":
        targetUrl += "teacher-dashboard.html";
        break;
      case "student":
        targetUrl += "student-dashboard.html";
        break;
      default:
        targetUrl = "./index.html";
    }

    console.log("Redirecting to:", targetUrl);
    window.location.href = targetUrl;
  }

  getBaseUrl(currentUrl) {
    // Extract base URL from current URL
    const url = new URL(currentUrl);
    const pathParts = url.pathname.split("/");

    // Remove empty parts and get the base path
    const cleanParts = pathParts.filter((part) => part !== "");

    if (cleanParts.length === 0) {
      // We're at root, return relative path
      return "./";
    } else if (cleanParts.includes("pages")) {
      // We're in pages directory, go back to root
      return "../";
    } else {
      // We're in some other directory, stay at current level
      return "./";
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
    const user = localStorage.getItem("user");
    return user ? JSON.parse(user) : null;
  }

  // Utility method to check if user is authenticated
  isAuthenticated() {
    return this.getCurrentUser() !== null;
  }

  // Utility method to get user role
  getUserRole() {
    const user = this.getCurrentUser();
    return user ? user.role : null;
  }

  // Method to check if user should be on current page
  checkPageAccess() {
    const user = this.getCurrentUser();
    const currentUrl = window.location.href;

    console.log("Checking page access:", { user, currentUrl });

    // If no user is logged in, redirect to login
    if (!user) {
      console.log("No user logged in, redirecting to login");
      this.redirectToLogin();
      return false;
    }

    // Check if user is on the correct dashboard for their role
    const expectedPage = this.getExpectedPageForRole(user.role);
    const currentPage = window.location.pathname;

    console.log("Current page:", currentPage);
    console.log("Expected page:", expectedPage);

    if (currentPage !== expectedPage) {
      console.log(
        `User role ${user.role} should be on ${expectedPage}, redirecting`
      );
      this.redirectToDashboard(user.role);
      return false;
    }

    console.log("User has access to current page");
    return true;
  }

  // Helper method to redirect to login
  redirectToLogin() {
    const currentPath = window.location.pathname;

    console.log("Redirecting to login from:", window.location.href);

    let loginUrl = "";
    if (currentPath.includes("/pages/")) {
      // We're in pages directory, go back to root
      loginUrl = "../index.html";
    } else {
      // We're at root or other directory
      loginUrl = "./index.html";
    }

    console.log("Login URL:", loginUrl);
    window.location.href = loginUrl;
  }

  // Get expected page for user role
  getExpectedPageForRole(role) {
    switch (role) {
      case "admin":
        return "/pages/admin-dashboard.html";
      case "teacher":
        return "/pages/teacher-dashboard.html";
      case "student":
        return "/pages/student-dashboard.html";
      default:
        return "/index.html";
    }
  }
}

// Initialize authentication when DOM is loaded
document.addEventListener("DOMContentLoaded", () => {
  window.auth = new Auth();
});

// Global logout function
function logout() {
  if (window.auth) {
    window.auth.logout();
  }
}
