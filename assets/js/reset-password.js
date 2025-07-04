// Reset Password JavaScript

class ResetPassword {
  constructor() {
    this.baseUrl = window.location.origin;
    this.apiUrl = this.baseUrl + "/backend/controllers/forgot-password.php";
    this.token = this.getUrlParameter("token");
    this.email = this.getUrlParameter("email");
    this.userType = this.getUrlParameter("userType");
    this.init();
  }

  init() {
    this.validateToken();
    this.setupEventListeners();
    this.setupPasswordValidation();
  }

  getUrlParameter(name) {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get(name);
  }

  validateToken() {
    if (!this.token || !this.email || !this.userType) {
      this.showAlert(
        "Invalid reset link. Please request a new password reset.",
        "danger"
      );
      setTimeout(() => {
        window.location.href = "/frontend/forgot-password.html";
      }, 3000);
    }
  }

  setupEventListeners() {
    const form = document.getElementById("resetPasswordForm");
    if (form) {
      form.addEventListener("submit", (e) => this.handleSubmit(e));
    }

    // Password input listeners
    const newPasswordInput = document.getElementById("newPassword");
    const confirmPasswordInput = document.getElementById("confirmPassword");

    if (newPasswordInput) {
      newPasswordInput.addEventListener("input", () =>
        this.validatePasswords()
      );
    }

    if (confirmPasswordInput) {
      confirmPasswordInput.addEventListener("input", () =>
        this.validatePasswords()
      );
    }
  }

  setupPasswordValidation() {
    const newPasswordInput = document.getElementById("newPassword");
    if (newPasswordInput) {
      newPasswordInput.addEventListener("input", () =>
        this.checkPasswordRequirements()
      );
    }
  }

  checkPasswordRequirements() {
    const password = document.getElementById("newPassword").value;

    // Check length
    const lengthCheck = document.getElementById("length-check");
    if (lengthCheck) {
      if (password.length >= 8) {
        lengthCheck.classList.add("valid");
        lengthCheck.classList.remove("invalid");
      } else {
        lengthCheck.classList.add("invalid");
        lengthCheck.classList.remove("valid");
      }
    }

    // Check uppercase
    const uppercaseCheck = document.getElementById("uppercase-check");
    if (uppercaseCheck) {
      if (/[A-Z]/.test(password)) {
        uppercaseCheck.classList.add("valid");
        uppercaseCheck.classList.remove("invalid");
      } else {
        uppercaseCheck.classList.add("invalid");
        uppercaseCheck.classList.remove("valid");
      }
    }

    // Check lowercase
    const lowercaseCheck = document.getElementById("lowercase-check");
    if (lowercaseCheck) {
      if (/[a-z]/.test(password)) {
        lowercaseCheck.classList.add("valid");
        lowercaseCheck.classList.remove("invalid");
      } else {
        lowercaseCheck.classList.add("invalid");
        lowercaseCheck.classList.remove("valid");
      }
    }

    // Check number
    const numberCheck = document.getElementById("number-check");
    if (numberCheck) {
      if (/\d/.test(password)) {
        numberCheck.classList.add("valid");
        numberCheck.classList.remove("invalid");
      } else {
        numberCheck.classList.add("invalid");
        numberCheck.classList.remove("valid");
      }
    }
  }

  validatePasswords() {
    const newPassword = document.getElementById("newPassword").value;
    const confirmPassword = document.getElementById("confirmPassword").value;
    const submitBtn = document.querySelector("#resetPasswordForm .btn-primary");

    // Check password requirements
    const hasLength = newPassword.length >= 8;
    const hasUppercase = /[A-Z]/.test(newPassword);
    const hasLowercase = /[a-z]/.test(newPassword);
    const hasNumber = /\d/.test(newPassword);
    const passwordsMatch =
      newPassword === confirmPassword && newPassword.length > 0;

    const isValid =
      hasLength && hasUppercase && hasLowercase && hasNumber && passwordsMatch;
    submitBtn.disabled = !isValid;

    // Show/hide confirm password error
    if (confirmPassword && !passwordsMatch) {
      this.showConfirmPasswordError("Passwords do not match");
    } else {
      this.hideConfirmPasswordError();
    }

    return isValid;
  }

  showConfirmPasswordError(message) {
    this.hideConfirmPasswordError();
    const errorDiv = document.createElement("div");
    errorDiv.className = "confirm-password-error";
    errorDiv.style.color = "#dc3545";
    errorDiv.style.fontSize = "12px";
    errorDiv.style.marginTop = "5px";
    errorDiv.textContent = message;

    const confirmPasswordInput = document.getElementById("confirmPassword");
    confirmPasswordInput.parentNode.appendChild(errorDiv);
  }

  hideConfirmPasswordError() {
    const existingError = document.querySelector(".confirm-password-error");
    if (existingError) {
      existingError.remove();
    }
  }

  async handleSubmit(e) {
    e.preventDefault();

    if (!this.validatePasswords()) {
      this.showAlert(
        "Please ensure all password requirements are met and passwords match.",
        "danger"
      );
      return;
    }

    const formData = new FormData(e.target);
    const newPassword = formData.get("newPassword");
    const confirmPassword = formData.get("confirmPassword");

    if (newPassword !== confirmPassword) {
      this.showAlert("Passwords do not match", "danger");
      return;
    }

    this.showLoading(true);

    try {
      const response = await fetch(this.apiUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          action: "reset_password",
          token: this.token,
          email: this.email,
          userType: this.userType,
          newPassword: newPassword,
        }),
      });

      const data = await response.json();
      console.log("Reset password response:", data);

      if (data.success) {
        this.showAlert(data.message, "success");
        // Redirect to appropriate login page
        setTimeout(() => {
          this.redirectToLogin();
        }, 2000);
      } else {
        this.showAlert(data.message || "Failed to reset password", "danger");
      }
    } catch (error) {
      console.error("Reset password error:", error);
      this.showAlert("Network error. Please try again.", "danger");
    } finally {
      this.showLoading(false);
    }
  }

  redirectToLogin() {
    switch (this.userType) {
      case "principal":
        window.location.href = "/frontend/admin/principal/index.html";
        break;
      case "teacher":
        window.location.href = "/frontend/admin/teacher/index.html";
        break;
      case "student":
        window.location.href = "/frontend/user/index.html";
        break;
      default:
        window.location.href = "/frontend/index.html";
    }
  }

  showLoading(show) {
    const submitBtn = document.querySelector("#resetPasswordForm .btn-primary");
    if (show) {
      submitBtn.classList.add("loading");
      submitBtn.disabled = true;
    } else {
      submitBtn.classList.remove("loading");
      submitBtn.disabled = false;
    }
  }

  showAlert(message, type = "info") {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll(".alert");
    existingAlerts.forEach((alert) => alert.remove());

    // Create new alert
    const alertDiv = document.createElement("div");
    alertDiv.className = `alert alert-${type}`;

    let icon = "fas fa-info-circle";
    if (type === "success") icon = "fas fa-check-circle";
    if (type === "danger") icon = "fas fa-exclamation-circle";

    alertDiv.innerHTML = `
            <i class="${icon}"></i>
            ${message}
        `;

    // Insert alert before the form
    const form = document.getElementById("resetPasswordForm");
    if (form) {
      form.parentNode.insertBefore(alertDiv, form);
    } else {
      document.body.insertBefore(alertDiv, document.body.firstChild);
    }

    // Auto-dismiss after 5 seconds
    setTimeout(() => {
      if (alertDiv.parentNode) {
        alertDiv.remove();
      }
    }, 5000);
  }
}

// Global function for password toggle
function togglePassword(inputId) {
  const input = document.getElementById(inputId);
  const toggleBtn = input.parentNode.querySelector(".password-toggle i");

  if (input.type === "password") {
    input.type = "text";
    toggleBtn.classList.remove("fa-eye");
    toggleBtn.classList.add("fa-eye-slash");
  } else {
    input.type = "password";
    toggleBtn.classList.remove("fa-eye-slash");
    toggleBtn.classList.add("fa-eye");
  }
}

// Initialize ResetPassword class
const resetPassword = new ResetPassword();

// Global functions for external use
window.showAlert = (message, type) => resetPassword.showAlert(message, type);
window.showLoading = (show) => resetPassword.showLoading(show);
window.togglePassword = togglePassword;
