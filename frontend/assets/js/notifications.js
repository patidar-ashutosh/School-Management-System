/**
 * Notification System Utility
 * Provides beautiful, centered notifications with gradient backgrounds
 */

class NotificationSystem {
  constructor() {
    this.container = null;
    this.init();
  }

  init() {
    // Create notification container if it doesn't exist
    if (!document.getElementById("notification-container")) {
      this.container = document.createElement("div");
      this.container.id = "notification-container";
      this.container.className = "notification-container";
      document.body.appendChild(this.container);
    } else {
      this.container = document.getElementById("notification-container");
    }
  }

  show(message, type = "info", duration = 5000) {
    const notification = document.createElement("div");
    notification.className = `notification notification-${type}`;

    // Get appropriate icon based on type
    const icon = this.getIcon(type);

    notification.innerHTML = `
            <div class="notification-content">
                <i class="notification-icon ${icon}"></i>
                <span class="notification-message">${message}</span>
                <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

    this.container.appendChild(notification);

    // Auto-remove after duration
    if (duration > 0) {
      setTimeout(() => {
        this.remove(notification);
      }, duration);
    }

    return notification;
  }

  success(message, duration = 5000) {
    return this.show(message, "success", duration);
  }

  error(message, duration = 7000) {
    return this.show(message, "error", duration);
  }

  warning(message, duration = 6000) {
    return this.show(message, "warning", duration);
  }

  info(message, duration = 5000) {
    return this.show(message, "info", duration);
  }

  loading(message = "Loading...") {
    const notification = document.createElement("div");
    notification.className = "notification notification-info";

    notification.innerHTML = `
            <div class="notification-content">
                <div class="notification-loading"></div>
                <span class="notification-message">${message}</span>
            </div>
        `;

    this.container.appendChild(notification);
    return notification;
  }

  remove(notification) {
    if (notification && notification.parentNode) {
      notification.classList.add("fade-out");
      setTimeout(() => {
        if (notification.parentNode) {
          notification.remove();
        }
      }, 300);
    }
  }

  clear() {
    if (this.container) {
      this.container.innerHTML = "";
    }
  }

  getIcon(type) {
    const icons = {
      success: "fas fa-check-circle",
      error: "fas fa-exclamation-circle",
      warning: "fas fa-exclamation-triangle",
      info: "fas fa-info-circle",
    };
    return icons[type] || icons.info;
  }
}

// Global notification instance
const notifications = new NotificationSystem();

// Global functions for backward compatibility
function showNotification(message, type = "info", duration = 5000) {
  return notifications.show(message, type, duration);
}

function showSuccess(message, duration = 5000) {
  return notifications.success(message, duration);
}

function showError(message, duration = 7000) {
  return notifications.error(message, duration);
}

function showWarning(message, duration = 6000) {
  return notifications.warning(message, duration);
}

function showInfo(message, duration = 5000) {
  return notifications.info(message, duration);
}

function showLoading(message = "Loading...") {
  return notifications.loading(message);
}

// Form validation helper
function showValidationError(message) {
  return notifications.error(message, 7000);
}

function showValidationSuccess(message) {
  return notifications.success(message, 5000);
}

// API response helper
function handleApiResponse(
  response,
  successMessage = "Operation completed successfully"
) {
  if (response.success) {
    notifications.success(successMessage);
    return true;
  } else {
    notifications.error(response.message || "An error occurred");
    return false;
  }
}

// Network error helper
function showNetworkError() {
  return notifications.error(
    "Network error. Please check your connection and try again.",
    7000
  );
}
