// ===================== GLOBAL VARIABLES =====================
const container = document.querySelector(".container");

// Buttons
const registerBtns = document.querySelectorAll(".register-btn");
const loginBtns = document.querySelectorAll(".login-btn");
const forgetBtns = document.querySelectorAll(".forget-btn");

// Forms & Inputs
const formSignup = document.getElementById("signupForm");
const formLogin = document.getElementById("loginForm");
const signupError = document.getElementById("signupError");
const passwordError = document.getElementById("passwordError");
const loginError = document.getElementById("LoginError");

// Password fields
const registerPassword = document.getElementById("registerPassword");
const confirmPassword = document.getElementById("confirmPassword");
const loginPassword = document.getElementById("loginPassword");

// Toggle password buttons
const toggleRegisterPassword = document.getElementById(
  "toggleRegisterPassword"
);
const toggleConfirmPassword = document.getElementById("toggleConfirmPassword");
const toggleLoginPassword = document.getElementById("toggleLoginPassword");

// Arabic regex
const arabicRegex = /[\u0600-\u06FF]/;

// ===================== FORM SWITCH ANIMATION =====================
const forms = {
  login: document.getElementById("login"),
  signup: document.getElementById("signup"),
  forgot: document.getElementById("forgot"),
};

function switchForm(show) {
  const newForm = forms[show];

  // إخفاء كل الفورمات الأخرى فورًا
  Object.values(forms).forEach((f) => {
    if (f !== newForm) f.style.display = "none";
  });

  // إعداد الفورم الجديد للظهور
  newForm.style.opacity = 0;
  newForm.style.transform = "translateY(10px)";
  newForm.style.display = "block";

  let opacity = 0;
  let pos = 10;

  function animate() {
    opacity += 0.05; // سرعة التلاشي
    pos -= 0.5; // حركة للأعلى
    newForm.style.opacity = opacity;
    newForm.style.transform = `translateY(${pos}px)`;

    if (opacity < 1) requestAnimationFrame(animate);
  }

  animate();
}

function showLogin() {
  switchForm("login");
}
function showSignup() {
  switchForm("signup");
}
function showForgot() {
  switchForm("forgot");
}

// ===================== PARTICLES.JS INITIALIZATION =====================
window.onload = function () {
  if (window.particlesJS) {
    particlesJS("particles-js", {
      particles: {
        number: { value: 45, density: { enable: true, value_area: 800 } },
        color: { value: "#F0F4F8" },
        shape: {
          type: "circle",
          stroke: { width: 0, color: "#000" },
          polygon: { nb_sides: 5 },
        },
        opacity: { value: 0.8 },
        size: { value: 10, random: true },
        line_linked: {
          enable: true,
          distance: 220,
          color: "#F0F4F8",
          opacity: 0.4,
          width: 1.8,
        },
        move: { enable: true, speed: 2, direction: "none", out_mode: "out" },
      },
      interactivity: {
        detect_on: "canvas",
        events: {
          onhover: { enable: true, mode: "grab" },
          onclick: { enable: true, mode: "push" },
          resize: true,
        },
        modes: {
          grab: { distance: 200, line_linked: { opacity: 1 } },
          push: { particles_nb: 4 },
        },
      },
      retina_detect: true,
    });
  }
};

// ===================== PASSWORD TOGGLE =====================
function togglePasswordField(toggleBtn, passwordField) {
  if (!toggleBtn || !passwordField) return;

  toggleBtn.addEventListener("click", () => {
    const isPassword = passwordField.type === "password";
    passwordField.type = isPassword ? "text" : "password";

    if (isPassword) {
      toggleBtn.classList.replace("fa-eye", "fa-eye-slash");
    } else {
      toggleBtn.classList.replace("fa-eye-slash", "fa-eye");
    }
  });
}

// ربط الأزرار بالحقول
togglePasswordField(
  document.getElementById("toggleRegisterPassword"),
  document.getElementById("registerPassword")
);
togglePasswordField(
  document.getElementById("toggleConfirmPassword"),
  document.getElementById("confirmPassword")
);
togglePasswordField(
  document.getElementById("toggleLoginPassword"),
  document.getElementById("loginPassword")
);

// ===================== PRELOADER =====================
window.addEventListener("load", () => {
  const preloader = document.getElementById("preloader");
  if (!preloader) return;
  setTimeout(() => {
    preloader.style.opacity = "0";
    preloader.style.transition = "opacity 0.5s ease";
    setTimeout(() => (preloader.style.display = "none"), 500);
  }, 500);
});

// ===================== SIGNUP FORM =====================
if (formSignup) {
  formSignup.addEventListener("submit", async (e) => {
    e.preventDefault();

    const username = document.getElementById("username").value.trim();
    const email = document.getElementById("email").value.trim();
    const passwordVal = registerPassword.value.trim();
    const confirmPasswordVal = confirmPassword.value.trim();

    if (
      arabicRegex.test(username) ||
      arabicRegex.test(email) ||
      arabicRegex.test(passwordVal) ||
      arabicRegex.test(confirmPasswordVal)
    ) {
      Swal.fire({
        icon: "warning",
        title: "⚠️ Invalid Input",
        text: "Username, Email, and Password must not contain Arabic characters!",
        confirmButtonColor: "#3085d6",
      });
      return;
    }

    // التحقق من مطابقة الباسورد
    if (passwordVal !== confirmPasswordVal) {
      Swal.fire({
        icon: "error",
        title: "Passwords do not match",
        text: "Please re-type your password carefully.",
        confirmButtonColor: "#ef4444",
      });
      confirmPassword.focus();
      return;
    }

    const formData = new FormData(formSignup);

    Swal.fire({
      title: "Creating your account...",
      text: "Please check your email for verification link after signup.",
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading(),
    });

    try {
      const response = await fetch("signup.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (result.status === "success") {
        // الحساب جديد وتم إرسال البريد
        Swal.fire({
          icon: "success",
          title: "Email Sent ✅",
          text: result.message,
          confirmButtonText: "OK",
          confirmButtonColor: "#22c55e",
        }).then(() => formSignup.reset());
      } else if (result.status === "pending_verification") {
        // المستخدم موجود وغير مفعل → نعرض علامة صح خضراء
        Swal.fire({
          icon: "success",
          title: "Verification Email Sent ✅",
          text: result.message,
          confirmButtonText: "OK",
          confirmButtonColor: "#22c55e",
        }).then(() => formSignup.reset());
      } else if (result.status === "error") {
        let errorMsg = "";
        switch (result.type) {
          case "username":
            errorMsg = "Username already taken! Please choose another.";
            break;
          case "email":
            errorMsg = "Email already registered! Please use another.";
            break;
          case "weak_password":
            errorMsg = "Password must include letters, not numbers only.";
            break;
          case "short_password":
            errorMsg = "Please enter a password with at least 8 characters.";
            break;
          case "both":
            errorMsg = "Account already exists! Please log in.";
            break;
          case "mail_failed":
            errorMsg = "Could not send verification email. Contact support.";
            break;
          default:
            errorMsg = "Unexpected error. Please try again later.";
        }

        Swal.fire({
          icon: "error",
          title: "Signup Failed",
          text: errorMsg,
          confirmButtonColor: "#ef4444",
        });
      }
    } catch (err) {
      Swal.close();
      Swal.fire({
        icon: "error",
        title: "Network Error",
        text: "Please check your internet connection and try again.",
        confirmButtonColor: "#ef4444",
      });
    }
  });
}
// ===================== LOGIN FORM =====================
if (formLogin) {
  const LoginError = document.getElementById("LoginError");

  formLogin.addEventListener("submit", async (e) => {
    e.preventDefault();

    const username = document.getElementById("loginUsername").value.trim();
    const passwordVal = loginPassword.value.trim();

    // منع الحروف العربية
    if (arabicRegex.test(username) || arabicRegex.test(passwordVal)) {
      Swal.fire({
        icon: "warning",
        title: "Invalid Input ⚠️",
        text: "Username and Password must not contain Arabic characters!",
        confirmButtonColor: "#3085d6",
      });
      return;
    }

    const formData = new FormData(formLogin);

    // إعادة ضبط الكلاسات وإخفاء الرسالة
    LoginError.className = "";
    LoginError.style.display = "none";

    try {
      // عرض SweetAlert تحميل
      Swal.fire({
        title: "Processing...",
        html: "Please wait while we check your account",
        allowOutsideClick: false,
        didOpen: () => {
          Swal.showLoading();
        },
      });

      const response = await fetch("login.php", {
        method: "POST",
        body: formData,
      });

      if (!response.ok) throw new Error("Network response was not ok");

      const result = await response.json();
      Swal.close(); // إغلاق الـ loading قبل عرض النتيجة

      switch (result.status) {
        case "notfound":
          Swal.fire({
            icon: "info",
            title: "Account Not Found",
            text: "No account found! Please create an account first.",
            confirmButtonColor: "#6366f1",
          });
          break;

        case "invalid":
          Swal.fire({
            icon: "error",
            title: "Incorrect Credentials",
            text: "Incorrect username/email or password! Please try again.",
            confirmButtonColor: "#ef4444",
          });
          break;

        case "unverified":
          Swal.fire({
            icon: "warning",
            title: "Email Not Verified",
            text:
              result.message ||
              "A confirmation email has been resent to your email.",
            confirmButtonColor: "#22c55e",
          });
          break;

        case "success":
          Swal.fire({
            icon: "success",
            title: `Welcome back, ${result.username}!`,
            text: "Redirecting to your dashboard...",
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
          });

          setTimeout(() => {
            window.location.href = "dashboard.php";
          }, 2500);
          break;

        default:
          Swal.fire({
            icon: "error",
            title: "Unexpected Error",
            text: "Something went wrong! Please try again later.",
            confirmButtonColor: "#ef4444",
          });
      }
    } catch (err) {
      console.error(err);
      Swal.fire({
        icon: "error",
        title: "Network Error",
        text: "Please check your internet connection and try again.",
        confirmButtonColor: "#ef4444",
      });
    }
  });
}
// ===================== Forgot Your Password Form =====================
document.getElementById("forgotForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  const form = e.target;
  const formData = new FormData(form);

  // 🔄 عرض رسالة تحميل أثناء الإرسال
  Swal.fire({
    title: "Please wait...",
    text: "We’re sending a secure reset link to your email.",
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading(),
  });

  try {
    const response = await fetch(form.action, {
      method: "POST",
      body: formData,
    });

    const result = await response.json();

    // ⚠️ حالة الخطأ
    if (result.status === "error") {
      Swal.fire({
        icon: "error",
        title: "Request Failed",
        html: `<p style="font-size:15px;color:#555;">${result.message}</p>`,
        confirmButtonColor: "#d33",
        confirmButtonText: "Try Again",
      });
    }

    // 🔐 الحساب غير مفعّل
    else if (result.status === "unverified") {
      Swal.fire({
        icon: "warning",
        title: "Account Not Verified",
        html: `
          <p style="font-size:15px;color:#444;">
            Your account is not verified yet.<br>
            We’ve sent a new confirmation email — please check your inbox or spam folder.
          </p>
        `,
        confirmButtonColor: "#f59e0b",
        confirmButtonText: "Got it",
      });
    }

    // ✅ تم إرسال رابط إعادة التعيين
    else if (result.status === "success") {
      Swal.fire({
        icon: "success",
        title: "Email Sent!",
        html: `
          <p style="font-size:15px;color:#444;">
            A password reset link has been sent to your email.<br>
            Please check your inbox and follow the instructions.
          </p>
        `,
        confirmButtonColor: "#1cc88a",
        confirmButtonText: "Okay",
      });
      form.reset();
    }

    // ⚙️ حالة غير متوقعة
    else {
      Swal.fire({
        icon: "info",
        title: "Unexpected Response",
        text: "We received an unexpected response. Please try again.",
        confirmButtonColor: "#3085d6",
      });
    }
  } catch (err) {
    // ❌ فشل في الاتصال بالسيرفر
    Swal.fire({
      icon: "error",
      title: "Connection Error",
      html: `
        <p style="font-size:15px;color:#555;">
          Something went wrong while connecting to the server.<br>
          Please check your internet connection and try again.
        </p>
      `,
      confirmButtonColor: "#c62828",
    });
  }
});

// ===================== Remember Me =====================
document.addEventListener("DOMContentLoaded", () => {
    const loginForm = document.getElementById("loginForm");
    const rememberMeCheckbox = document.getElementById("rememberMe");
    const loginUsernameInput = document.getElementById("loginUsername");
    const loginPasswordInput = document.getElementById("loginPassword");

    const savedUsername = localStorage.getItem("rememberedUsername");
    if (savedUsername) {
        loginUsernameInput.value = savedUsername;
        rememberMeCheckbox.checked = true;
    }

    loginForm.addEventListener("submit", (e) => {
        e.preventDefault();

        const username = loginUsernameInput.value.trim();
        const password = loginPasswordInput.value;

        if (rememberMeCheckbox.checked) {
            localStorage.setItem("rememberedUsername", username);
        } else {
            localStorage.removeItem("rememberedUsername");
        }

        console.log("Login submitted:", username);
    });
});

// ===================== google =====================
const googleBtns = document.querySelectorAll(".google-btn");

googleBtns.forEach((btn) => {
  btn.addEventListener("click", (e) => {
    e.preventDefault();

    Swal.fire({
      icon: "info",
      title: "Coming Soon",
      text: "You can easily login or create an account to enjoy our services!",
      confirmButtonText: "OK",
      confirmButtonColor: "#306ABF",
    });
  });
});