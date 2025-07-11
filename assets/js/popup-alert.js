// Add Bootstrap alert HTML to body if not present
if (!document.getElementById("customAlert")) {
  const alertDiv = document.createElement("div");
  alertDiv.id = "customAlert";
  alertDiv.className = "alert d-none custom-popup-alert";
  alertDiv.style =
    "z-index: 9999; min-width: 300px; position: fixed; left: 50%; top: 24px; transform: translateX(-50%); margin: 0; padding: 12px 20px; box-sizing: border-box;";
  alertDiv.innerHTML = '<span id="alertMsg"></span>';
  document.body.appendChild(alertDiv);
}

window.showAlert = function (message, type, redirectUrl = null) {
  const alertBox = document.getElementById("customAlert");
  const alertMsg = document.getElementById("alertMsg");
  alertMsg.textContent = message;

  // Remove all alert classes
  alertBox.className = "alert custom-popup-alert";
  alertBox.classList.add(
    type === "success"
      ? "alert-success"
      : type === "info"
      ? "alert-info"
      : "alert-danger"
  );
  alertBox.classList.remove("d-none");

  if (type === "success" || type === "info") {
    setTimeout(() => {
      alertBox.classList.add("d-none");
      if (redirectUrl) {
        window.location.href = redirectUrl;
      }
    }, 3000);
  }
  // For error, do not auto-close or redirect
};
