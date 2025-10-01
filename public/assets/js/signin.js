document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("form");
  const studentNumberInput = document.getElementById("student-number");
  const passwordInput = document.getElementById("password");

  // If form or inputs are missing, stop script (avoid null errors)
  if (!form || !studentNumberInput || !passwordInput) {
    console.error("Login form elements not found. Check HTML IDs.");
    return;
  }

  // Create live feedback display under student number field
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

    const studentNumber = studentNumberInput.value.trim();
    const password = passwordInput.value.trim();

    if (!/^[0-9]{7}$/.test(studentNumber)) {
      showPopup("Student number must be exactly 7 digits.", true);
      return;
    }
    if (password.length < 6) {
      showPopup("Password must be at least 6 characters.", true);
      return;
    }

    // Send login request to backend PHP
    fetch("/canteen-ordering/public/auth/login.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({
        student_number: studentNumber,
        password: password
      })
    })
      .then((response) => response.text())
      .then((data) => {
        console.log("Server Response:", data);

        if (data.startsWith("success|")) {
          const [, redirectUrl] = data.split("|");
          showPopup("Sign in successful! Redirecting...");
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
  
console.log("Form:", form);
console.log("Student input:", studentNumberInput);
console.log("Password input:", passwordInput);

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
