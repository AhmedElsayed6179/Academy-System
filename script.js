// Hamburger Menu
document.addEventListener('DOMContentLoaded', () => {
    const hamburger = document.querySelector(".hamburger");      
    const navLinksContainer = document.querySelector(".nav-links"); 
    const navLinks = document.querySelectorAll(".nav-links a");  

    const navHeight = navLinksContainer.offsetHeight;

    hamburger.addEventListener("click", () => {
        hamburger.classList.toggle("active");       
        navLinksContainer.classList.toggle("active"); 
    });

    navLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault(); 
            
            const targetId = link.getAttribute('href').substring(1);
            const targetSection = document.getElementById(targetId);
            
            if (targetSection) {
                const sectionPosition = targetSection.offsetTop - navHeight - 20;
                window.scrollTo({
                    top: sectionPosition,
                    behavior: 'smooth'
                });
            }

            navLinksContainer.classList.remove("active");
            hamburger.classList.remove("active");
        });
    });
});

// Stats Counter
const counters = document.querySelectorAll(".stat-number");
const speed = 100;
const animateCounters = () => {
  counters.forEach((counter) => {
    const target = +counter.getAttribute("data-target");
    const update = () => {
      const value = +counter.innerText;
      const inc = target / speed;
      if (value < target) {
        counter.innerText = Math.ceil(value + inc);
        setTimeout(update, 20);
      } else {
        counter.innerText = target;
      }
    };
    update();
  });
};
let statsSection = document.querySelector(".stats");
let statsVisible = false;
window.addEventListener("scroll", () => {
  const sectionTop = statsSection.offsetTop - window.innerHeight + 200;
  if (window.scrollY > sectionTop && !statsVisible) {
    animateCounters();
    statsVisible = true;
  }
});

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

// ===================== ContactForm =====================
document.getElementById("contactForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const formData = new FormData(this);

  Swal.fire({
    title: "Sending...",
    text: "Please wait while your message is being sent.",
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    },
  });

  fetch("send_message.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.status === "success") {
        Swal.fire({
          icon: "success",
          title: "Message Sent!",
          text: "Your message has been sent successfully.",
          confirmButtonColor: "#007bff",
        });
        document.getElementById("contactForm").reset();
      } else {
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "Failed to send message. Please try again later.",
          confirmButtonColor: "#007bff",
        });
      }
    })
    .catch(() => {
      Swal.fire({
        icon: "error",
        title: "Server Error",
        text: "Something went wrong. Try again later.",
        confirmButtonColor: "#007bff",
      });
    });
});

// ===================== free plan =====================
document.querySelector(".login").addEventListener("click", function() {
    Swal.fire({
        icon: 'info',
        title: 'Login or Sign Up',
        text: 'You can easily login or create an account to enjoy our services!',
        confirmButtonText: 'Login',
        showCloseButton: true,
        allowOutsideClick: false,
        allowEscapeKey: false
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = "login.html";
        }
    });
});

// ===================== login Button =====================
document.addEventListener("DOMContentLoaded", function() {
  const buttons = document.querySelectorAll(".loginButton");

  buttons.forEach(button => {
    button.addEventListener("click", function(e) {
      e.preventDefault();

      Swal.fire({
        icon: 'success',
        title: 'Welcome!',
        html: 'You are now on the <b>Free Plan</b>. Enjoy it!',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false
      });

      setTimeout(() => {
        window.location.href = button.href;
      }, 2000);
    });
  });
});
