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

    // Deslizar con el dedo (celular)
    let startX = 0, startY = 0, tracking = false;
    carousel.addEventListener("touchstart", (e) => {
      startX = e.touches[0].clientX;
      startY = e.touches[0].clientY;
      tracking = true;
    }, { passive: true });
    carousel.addEventListener("touchend", (e) => {
      if (!tracking) return;
      tracking = false;
      const dx = e.changedTouches[0].clientX - startX;
      const dy = e.changedTouches[0].clientY - startY;
      if (Math.abs(dx) > 40 && Math.abs(dx) > Math.abs(dy)) {
        goToSlide(currentIndex + (dx < 0 ? 1 : -1));
      }
    }, { passive: true });

    startAutoplay();
  });
}

document.addEventListener("DOMContentLoaded", initCarousels);

// ===== Nav auto-hide + botón subir =====
(function () {
  const nav = document.querySelector('nav.main-nav');
  const btnSubir = document.getElementById('btnSubir');
  let lastScrollY = window.scrollY;
  const umbral = 80; // px antes de empezar a ocultar

  window.addEventListener('scroll', () => {
    const currentScrollY = window.scrollY;

    // No ocultar el nav si el menú móvil está abierto
    if (nav.classList.contains('nav-open')) {
      lastScrollY = currentScrollY;
      return;
    }

    // Ocultar nav al bajar, mostrar al subir
    if (currentScrollY > lastScrollY && currentScrollY > umbral) {
      nav.classList.add('nav-hidden');
    } else {
      nav.classList.remove('nav-hidden');
    }

    // Mostrar botón "subir" solo si bajaste lo suficiente
    if (currentScrollY > umbral) {
      btnSubir.classList.add('visible');
    } else {
      btnSubir.classList.remove('visible');
    }

    lastScrollY = currentScrollY;
  });

  btnSubir.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
})();

// ===== Cerrar el menú hamburguesa al tocar un enlace con ancla (#beneficios, #contactos...) =====
document.addEventListener('click', (e) => {
  const link = e.target.closest('nav.main-nav a[href^="#"]:not([href="#"])');
  if (!link) return;
  if (link.parentElement && link.parentElement.classList.contains('dropdown')) return;
  const nav = document.querySelector('nav.main-nav');
  if (!nav) return;
  nav.classList.remove('nav-open');
  const toggle = nav.querySelector('.nav-toggle');
  if (toggle) toggle.setAttribute('aria-expanded', 'false');
});