// ==========================================
// Navbar compartida del portal del socio
// ==========================================
// Dibuja el MISMO nav (nav.main-nav) en inicio, perfil y facturas.
// Requiere que la página cargue antes js/portal-api.js.
// Uso: <nav class="main-nav" data-nav-portal></nav>

(function () {
  const nav = document.querySelector("nav.main-nav[data-nav-portal]");
  if (!nav) return;

  nav.innerHTML = `
    <div class="nav-logo">
      <a href="../inicio/inicio.html" aria-label="Ir al inicio">
        <img src="../login/img/logoucm.png" alt="Logo Club Ciclista Maragato">
      </a>
    </div>

    <button class="nav-toggle" aria-label="Abrir menú" aria-expanded="false" aria-controls="menu-principal">
      <span></span><span></span><span></span>
    </button>

    <ul class="nav-personal" id="menu-principal">
      <li class="nav-sobre">
        <a href="../inicio/inicio.html#sobre-nosotros" aria-label="Sobre nosotros">
          <i class="ph ph-info"></i>
          <span class="nav-texto">Sobre nosotros</span>
        </a>
      </li>
      <li id="nav-socio"></li>
    </ul>
  `;

  // --- Botón hamburguesa ---
  const toggle = nav.querySelector(".nav-toggle");
  toggle.addEventListener("click", () => {
    const abierto = nav.classList.toggle("nav-open");
    toggle.setAttribute("aria-expanded", abierto);
  });

  // --- Menú del socio ---
  const navSocio = document.getElementById("nav-socio");

  // En perfil y facturas: en vez del desplegable, botón "Volver al inicio"
  if (nav.dataset.navPortal === "volver") {
    navSocio.innerHTML = `<a href="../inicio/inicio.html" class="btn-nav-volver">&larr; Volver al inicio</a>`;
    return;
  }

  function pintarNavSocio(conSesion) {
    if (conSesion) {
      navSocio.className = "dropdown";
      navSocio.innerHTML = `
        <a href="#">✔ Soy socio</a>
        <ul class="dropdown-menu">
          <li><a href="../perfil/perfil.html">Mi perfil</a></li>
          <li><a href="../misfacturas/facturas.html">Mis facturas</a></li>
          <li><a href="#" id="nav-logout">Cerrar sesión</a></li>
        </ul>
      `;
      document.getElementById("nav-logout").addEventListener("click", (e) => {
        e.preventDefault();
        cerrarSesionPortal();
      });
    } else {
      navSocio.className = "";
      navSocio.innerHTML = `<a href="../login/login.html">Hacerse socio</a>`;
    }
  }

  pintarNavSocio(!!sessionStorage.getItem("usuarioActivo"));

  sesionSocio().then((usuario) => {
    if (usuario) {
      sessionStorage.setItem("usuarioActivo", usuario.nombre_usuario);
    } else {
      sessionStorage.removeItem("usuarioActivo");
    }
    pintarNavSocio(!!usuario);
  });
})();