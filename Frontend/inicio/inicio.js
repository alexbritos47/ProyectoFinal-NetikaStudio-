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

  carousels.forEach((carousel) => {
    const track = carousel.querySelector(".carousel-track");
    const slides = Array.from(carousel.querySelectorAll(".carousel-slide"));
    const prevBtn = carousel.querySelector(".carousel-prev");
    const nextBtn = carousel.querySelector(".carousel-next");
    const dotsContainer = carousel.querySelector(".carousel-dots");

    let currentIndex = 0;

    slides.forEach((_, i) => {
      const dot = document.createElement("button");
      dot.className = "carousel-dot" + (i === 0 ? " active" : "");
      dot.setAttribute("aria-label", "Ir a la imagen " + (i + 1));
      dot.addEventListener("click", () => goToSlide(i));
      dotsContainer.appendChild(dot);
    });

    const dots = Array.from(dotsContainer.querySelectorAll(".carousel-dot"));

    function updateCarousel() {
      track.style.transform = `translateX(-${currentIndex * 100}%)`;
      dots.forEach((dot, i) => dot.classList.toggle("active", i === currentIndex));
    }

    function goToSlide(index) {
      currentIndex = (index + slides.length) % slides.length;
      updateCarousel();
    }

    prevBtn.addEventListener("click", () => goToSlide(currentIndex - 1));
    nextBtn.addEventListener("click", () => goToSlide(currentIndex + 1));

    // Autoplay
    let autoplay = setInterval(() => goToSlide(currentIndex + 1), 5000);

    carousel.addEventListener("mouseenter", () => clearInterval(autoplay));
    carousel.addEventListener("mouseleave", () => {
      autoplay = setInterval(() => goToSlide(currentIndex + 1), 5000);
    });
  });
}

document.addEventListener("DOMContentLoaded", initCarousels);