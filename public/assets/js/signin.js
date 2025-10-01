// public/assets/js/signin.js
document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("form");
  if (!form) return;

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const studentNumber = document.getElementById("student_number").value.trim();
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();

    if (!/^\d{7}$/.test(studentNumber)) {
      showPopup("Student number must be exactly 7 digits.", true);
      return;
    }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      showPopup("Please enter a valid email.", true);
      return;
    }
    if (password.length < 6) {
      showPopup("Password must be at least 6 characters.", true);
      return;
    }

    try {
      const res = await fetch(`${BASE || ''}/auth/login.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
        body: new URLSearchParams({ student_number: studentNumber, email, password })
      });
      const data = await res.json();
      if (data.success) {
        showPopup("Sign in successful. Redirecting...");
        setTimeout(() => { window.location.href = data.redirect || (BASE || '') + '/student/dashboard.php'; }, 600);
      } else {
        const msg = data.errors ? data.errors.join('\n') : data.error || 'Login failed';
        showPopup(msg, true);
      }
    } catch (err) {
      console.error(err);
      showPopup('Network error. Try again later.', true);
    }
  });

  function showPopup(message, isError = false) {
    const popup = document.createElement('div');
    popup.className = 'popup' + (isError ? ' error' : '');
    popup.textContent = message;
    document.body.appendChild(popup);
    setTimeout(() => popup.style.opacity = 1, 20);
    setTimeout(() => { popup.style.opacity = 0; setTimeout(() => popup.remove(), 300); }, 2000);
  }
});
