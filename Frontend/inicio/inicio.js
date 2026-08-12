const usuario = sessionStorage.getItem("usuarioActivo");
const navSocio = document.getElementById("nav-socio");

if (usuario) {
  navSocio.className = "dropdown";
  navSocio.innerHTML = `
    <a href="#">✔ Soy socio</a>
    <ul class="dropdown-menu">
      <li><a href="../misfacturas/facturas.html">Mis facturas</a></li>
      <li><a href="#" onclick="logout()">Cerrar sesión</a></li>
    </ul>
  `;
} else {
  navSocio.innerHTML = `<a href="../login/login.html">Hacerse socio</a>`;
}

function logout() {
  sessionStorage.removeItem("usuarioActivo");
  window.location.href = "../login/login.html";
}

// ===== Carruseles de beneficios =====

function initCarousels() {
  const carousels = document.querySelectorAll("[data-carousel]");
  const prefersReducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)"
  ).matches;

  carousels.forEach((carousel) => {
    const track = carousel.querySelector(".carousel-track");
    const slides = Array.from(carousel.querySelectorAll(".carousel-slide"));
    const prevBtn = carousel.querySelector(".carousel-prev");
    const nextBtn = carousel.querySelector(".carousel-next");
    const dotsContainer = carousel.querySelector(".carousel-dots");

    let currentIndex = 0;
    let autoplay = null;
    let isPaused = false;

    // Botón de pausa/play manual (WCAG 2.2.2 - Pause, Stop, Hide)
    const pauseBtn = document.createElement("button");
    pauseBtn.className = "carousel-pause";
    pauseBtn.type = "button";
    updatePauseBtnLabel();
    carousel.insertBefore(pauseBtn, track.nextSibling);

    pauseBtn.addEventListener("click", () => {
      isPaused = !isPaused;
      if (isPaused) {
        stopAutoplay();
      } else {
        startAutoplay();
      }
      updatePauseBtnLabel();
    });

    function updatePauseBtnLabel() {
      pauseBtn.setAttribute(
        "aria-label",
        isPaused ? "Reanudar carrusel automático" : "Pausar carrusel automático"
      );
      pauseBtn.textContent = isPaused ? "▶" : "⏸";
    }

    slides.forEach((_, i) => {
      const dot = document.createElement("button");
      dot.className = "carousel-dot" + (i === 0 ? " active" : "");
      dot.type = "button";
      dot.setAttribute("aria-label", "Ir a la imagen " + (i + 1));
      if (i === 0) dot.setAttribute("aria-current", "true");
      dot.addEventListener("click", () => goToSlide(i));
      dotsContainer.appendChild(dot);
    });

    const dots = Array.from(dotsContainer.querySelectorAll(".carousel-dot"));

    function updateCarousel() {
      track.style.transform = `translateX(-${currentIndex * 100}%)`;
      dots.forEach((dot, i) => {
        const active = i === currentIndex;
        dot.classList.toggle("active", active);
        if (active) {
          dot.setAttribute("aria-current", "true");
        } else {
          dot.removeAttribute("aria-current");
        }
      });
    }

    function goToSlide(index) {
      currentIndex = (index + slides.length) % slides.length;
      updateCarousel();
    }

    prevBtn.addEventListener("click", () => goToSlide(currentIndex - 1));
    nextBtn.addEventListener("click", () => goToSlide(currentIndex + 1));

    function startAutoplay() {
      if (isPaused || prefersReducedMotion) return;
      stopAutoplay();
      autoplay = setInterval(() => goToSlide(currentIndex + 1), 5000);
    }

    function stopAutoplay() {
      clearInterval(autoplay);
      autoplay = null;
    }

    // Pausa con mouse
    carousel.addEventListener("mouseenter", stopAutoplay);
    carousel.addEventListener("mouseleave", startAutoplay);

    // Pausa con foco de teclado (antes faltaba: WCAG 2.2.2)
    carousel.addEventListener("focusin", stopAutoplay);
    carousel.addEventListener("focusout", (e) => {
      // Solo reanudar si el foco salió del carrusel por completo
      if (!carousel.contains(e.relatedTarget)) {
        startAutoplay();
      }
    });

    startAutoplay();
  });
}

document.addEventListener("DOMContentLoaded", initCarousels);