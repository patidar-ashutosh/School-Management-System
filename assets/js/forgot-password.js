// Forgot Password JavaScript

class ForgotPassword {
  constructor() {
    this.baseUrl = window.location.origin;
    this.apiUrl = this.baseUrl + "/backend/controllers/forgot-password.php";
    this.init();
  }

  init() {
    this.setupEventListeners();
  }

  setupEventListeners() {
    const form = document.getElementById("forgotPasswordForm");
    if (form) {
      form.addEventListener("submit", (e) => this.handleSubmit(e));
    }

    // Real-time validation
    const emailInput = document.getElementById("email");
    const userTypeSelect = document.getElementById("userType");

    if (emailInput) {
      emailInput.addEventListener("input", () => this.validateEmail());
    }

    if (userTypeSelect) {
      userTypeSelect.addEventListener("change", () => this.validateForm());
    }
  }

  validateEmail() {
    const email = document.getElementById("email").value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const isValid = emailRegex.test(email);

    const emailInput = document.getElementById("email");
    if (email && !isValid) {
      emailInput.style.borderColor = "#dc3545";
      this.showFieldError("Please enter a valid email address");
    } else {
      emailInput.style.borderColor = "#e1e5e9";
      this.hideFieldError();
    }

    return isValid;
  }

  validateForm() {
    const email = document.getElementById("email").value;
    const userType = document.getElementById("userType").value;
    const submitBtn = document.querySelector(
      "#forgotPasswordForm .btn-primary"
    );

    const isValid = email && this.validateEmail() && userType;
    submitBtn.disabled = !isValid;

    return isValid;
  }

  showFieldError(message) {
    this.hideFieldError();
    const errorDiv = document.createElement("div");
    errorDiv.className = "field-error";
    errorDiv.style.color = "#dc3545";
    errorDiv.style.fontSize = "12px";
    errorDiv.style.marginTop = "5px";
    errorDiv.textContent = message;

    const emailInput = document.getElementById("email");
    emailInput.parentNode.appendChild(errorDiv);
  }

  hideFieldError() {
    const existingError = document.querySelector(".field-error");
    if (existingError) {
      existingError.remove();
    }
  }

  async handleSubmit(e) {
    e.preventDefault();

    if (!this.validateForm()) {
      this.showAlert("Please fill in all fields correctly", "danger");
      return;
    }

    const formData = new FormData(e.target);
    const email = formData.get("email");
    const userType = formData.get("userType");

    this.showLoading(true);

    try {
      const response = await fetch(this.apiUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          action: "forgot_password",
          email: email,
          userType: userType,
        }),
      });

      const data = await response.json();
      console.log("Forgot password response:", data);

      if (data.success) {
        this.showAlert(data.message, "success");
        // Redirect to reset password page with token
        setTimeout(() => {
          window.location.href = `/frontend/reset-password.html?token=${
            data.token
          }&email=${encodeURIComponent(email)}&userType=${userType}`;
        }, 2000);
      } else {
        this.showAlert(data.message || "Failed to process request", "danger");
      }
    } catch (error) {
      console.error("Forgot password error:", error);
      this.showAlert("Network error. Please try again.", "danger");
    } finally {
      this.showLoading(false);
    }
  }

  showLoading(show) {
    const submitBtn = document.querySelector(
      "#forgotPasswordForm .btn-primary"
    );
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
    const form = document.getElementById("forgotPasswordForm");
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

// Initialize ForgotPassword class
const forgotPassword = new ForgotPassword();

// Global functions for external use
window.showAlert = (message, type) => forgotPassword.showAlert(message, type);
window.showLoading = (show) => forgotPassword.showLoading(show);
