const usuario = sessionStorage.getItem("usuarioActivo");
const navSocio = document.getElementById("nav-socio");

if (usuario) {
  navSocio.className = "dropdown";
  navSocio.innerHTML = `
    <a href="#">✔ Soy socio</a>
    <ul class="dropdown-menu">
      <li><a href="../Usuario/Frontend/login/misfacturas/facturas.html">Mis facturas</a></li>
      <li><a href="../Usuario/Frontend/pagos.html">Mis pagos</a></li>
      <li><a href="#" onclick="logout()">Cerrar sesión</a></li>
    </ul>
  `;
} else {
  navSocio.innerHTML = `<a href="../Usuario/Frontend/login/login.html">Hacerse socio</a>`;
}

function logout() {
  sessionStorage.removeItem("usuarioActivo");
  window.location.href = "../Usuario/Frontend/login/login.html";
}