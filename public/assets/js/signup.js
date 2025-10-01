document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("#register-form");
  const emailInput = document.getElementById("email");
  const studentNumberInput = document.getElementById("student_number");
  const passwordInput = document.getElementById("password");
  const confirmPasswordInput = document.getElementById("confirm_password");

  // If any field is missing, stop
  if (!form || !emailInput || !studentNumberInput || !passwordInput || !confirmPasswordInput) {
    console.error("Registration form elements not found. Check HTML IDs.");
    return;
  }

  // Live display under student number (like login page)
  const liveDisplay = document.createElement("p");
  liveDisplay.style.color = "yellow";
  liveDisplay.style.fontSize = "14px";
  studentNumberInput.insertAdjacentElement("afterend", liveDisplay);

  studentNumberInput.addEventListener("input", () => {
    liveDisplay.textContent = `Typing Student No: ${studentNumberInput.value}`;
  });

  // Handle form submission
  form.addEventListener("submit", (event) => {
    event.preventDefault();

    const email = emailInput.value.trim();
    const studentNumber = studentNumberInput.value.trim();
    const password = passwordInput.value.trim();
    const confirmPassword = confirmPasswordInput.value.trim();

    // Validation
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      showPopup("Please enter a valid email address.", true);
      return;
    }
    if (!/^[0-9]{7}$/.test(studentNumber)) {
      showPopup("Student number must be exactly 7 digits.", true);
      return;
    }
    if (password.length < 6) {
      showPopup("Password must be at least 6 characters.", true);
      return;
    }
    if (password !== confirmPassword) {
      showPopup("Passwords do not match.", true);
      return;
    }

    // Send registration data to backend
    fetch("/canteen-ordering/public/auth/register.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({
        email: email,
        student_number: studentNumber,
        password: password,
        confirm_password: confirmPassword
      })
    })
      .then((response) => response.text())
      .then((data) => {
        console.log("Server Response:", data);

        if (data.startsWith("success|")) {
          const [, redirectUrl] = data.split("|");
          showPopup("Registration successful! Redirecting to login...");
          setTimeout(() => {
            window.location.href = redirectUrl;
          }, 2000);
        } else {
          showPopup(data, true);
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        showPopup("Something went wrong. Try again later.", true);
      });
  });

  // Popup helper
  function showPopup(message, isError = false) {
    const popup = document.createElement("div");
    popup.className = "popup" + (isError ? " error" : "");
    popup.textContent = message;
    document.body.appendChild(popup);

    setTimeout(() => (popup.style.opacity = "1"), 50);
    setTimeout(() => {
      popup.style.opacity = "0";
      setTimeout(() => popup.remove(), 300);
    }, 2000);
  }
});
