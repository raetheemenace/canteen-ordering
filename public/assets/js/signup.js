document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("form");
  const emailInput = document.getElementById("email");
  const studentNumberInput = document.getElementById("student_number");
  const passwordInput = document.getElementById("password");
  const confirmPasswordInput = document.getElementById("confirm_password");

  // If form or inputs are missing, stop script (avoid null errors)
  if (!form || !emailInput || !studentNumberInput || !passwordInput || !confirmPasswordInput) {
    console.error("Registration form elements not found. Check HTML IDs.");
    return;
  }

  // Live feedback under student number field
  const liveDisplay = document.createElement("p");
  liveDisplay.style.color = "yellow";
  liveDisplay.style.fontSize = "14px";
  studentNumberInput.insertAdjacentElement("afterend", liveDisplay);

  studentNumberInput.addEventListener("input", () => {
    liveDisplay.textContent = `Typing Student No: ${studentNumberInput.value}`;
  });

  // Handle form submission
  form.addEventListener("submit", (event) => {
    const studentNumber = studentNumberInput.value.trim();
    const email = emailInput.value.trim();
    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;

    if (!/^[0-9]{7}$/.test(studentNumber)) {
      event.preventDefault();
      showPopup("Student number must be exactly 7 digits.", true);
      return;
    }
    if (!/\S+@\S+\.\S+/.test(email)) {
      event.preventDefault();
      showPopup("Invalid email address.", true);
      return;
    }
    if (password.length < 6) {
      event.preventDefault();
      showPopup("Password must be at least 6 characters.", true);
      return;
    }
    if (password !== confirmPassword) {
      event.preventDefault();
      showPopup("Passwords do not match.", true);
      return;
    }
  });

  // Popup helper
  function showPopup(message, isError = false) {
    const popup = document.createElement("div");
    popup.className = "popup" + (isError ? " error" : "");
    popup.textContent = message;
    document.body.appendChild(popup);

    // fade in/out (CSS handles opacity transition)
    setTimeout(() => (popup.style.opacity = "1"), 50);
    setTimeout(() => {
      popup.style.opacity = "0";
      setTimeout(() => popup.remove(), 300);
    }, 2000);
  }
});
