<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="shortcut icon" href="badge.png" type="image/x-icon">
    <style>
        /* ================== Particle Layer ================== */
.particles {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -1;
    opacity: 0.3;
}
        
        /* ================== Background Effects ================== */

.bg-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -2;
    overflow: hidden;
}

.bg-gradient {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg,
            rgba(26, 32, 44, 0.9) 0%,
            rgba(15, 17, 26, 0.95) 50%,
            rgba(26, 32, 44, 0.9) 100%);
}

.bg-pattern {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0.15;
    background-image:
        radial-gradient(circle at 25% 25%,
            #7FBFFF 0%,
            transparent 20%),
        radial-gradient(circle at 75% 75%,
            #4A90E2 0%,
            transparent 20%);
    background-size: 300px 300px;
    animation: bgMove 30s linear infinite;
}

.bg-shapes {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
}

.bg-shape {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    opacity: 0.15;
}

.bg-shape-1 {
    width: 500px;
    height: 500px;
    background: #4A90E2;
    top: -100px;
    left: -100px;
    animation: float 15s ease-in-out infinite;
}

.bg-shape-2 {
    width: 400px;
    height: 400px;
    background: #7FBFFF;
    bottom: -150px;
    right: -100px;
    animation: float 18s ease-in-out infinite reverse;
}

.bg-shape-3 {
    width: 300px;
    height: 300px;
    background: #306ABF;
    top: 40%;
    right: 20%;
    animation: float 12s ease-in-out infinite 2s;
}

@keyframes bgMove {
    0% {
        background-position: 0 0;
    }

    100% {
        background-position: 300px 300px;
    }
}

@keyframes float {

    0%,
    100% {
        transform: translate(0, 0);
    }

    25% {
        transform: translate(50px, 50px);
    }

    50% {
        transform: translate(0, 100px);
    }

    75% {
        transform: translate(-50px, 50px);
    }
}
    </style>
</head>

<body>
    
    <!-- Particles background -->
    <div id="particles-js" class="particles"></div>

    <!-- Background Wrapper: contains all background layers -->
    <div class="bg-wrapper">
        <div class="bg-gradient"></div>
        <div class="bg-pattern"></div>
        <div class="bg-shapes">
            <div class="bg-shape bg-shape-1"></div>
            <div class="bg-shape bg-shape-2"></div>
            <div class="bg-shape bg-shape-3"></div>
        </div>
    </div>

    <script>
        (async () => {
            const urlParams = new URLSearchParams(window.location.search);
            const token = urlParams.get('token');

            if (!token) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Token',
                    text: 'No verification token provided.',
                    confirmButtonColor: '#ef4444',     
                });
                return;
            }

            try {
                const formData = new FormData();
                formData.append('token', token);

                Swal.fire({
                    title: 'Verifying your account...',
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });

                const response = await fetch('verify-ajax.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Verified! ✅',
                        html: result.message + '<br><br><button id="loginBtn" class="swal2-confirm swal2-styled">Login</button>',
                        showConfirmButton: false
                    });

                    document.getElementById('loginBtn').addEventListener('click', () => {
                        window.location.href = 'login.html';
                    });

                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: result.message,
                        confirmButtonColor: '#ef4444'
                    });
                }
            } catch (err) {
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Please check your connection and try again.',
                    confirmButtonColor: '#ef4444'
                });
                console.error(err);
            }
        })();
        
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
    </script>

    <!-- AOS Library for Scroll Animations -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <!-- Particles.js Library for Background Particle Effects -->
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>

    <!-- Include SweetAlert2 library for modern alert popups -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>