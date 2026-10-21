// ===== LOADER =====
setTimeout(() => {
  const loader = document.getElementById("loader");
  if (!loader) return;
  loader.style.opacity = "0";
  setTimeout(() => { loader.style.display = "none"; }, 1000);
}, 3000);


// ===== UTILIDADES =====
const CLAVE_USUARIO_RECORDADO = "usuarioRecordado";

// Recuadro con el usuario (nunca se muestra la contraseña)
function cajaUsuario(user) {
  return `
    <div style="background:#1f2937;border-radius:8px;padding:12px;text-align:left;line-height:1.8">
      <div><b>Usuario:</b> ${escaparHtml(user)}</div>
    </div>
  `;
}

// Alerta global por si algo falla sin que lo esperemos
window.addEventListener("error", () => {
  alerta("error", "Error inesperado", "Algo salió mal. Recargá la página e intentá de nuevo.");
});
window.addEventListener("unhandledrejection", () => {
  alerta("error", "Error inesperado", "Algo salió mal. Recargá la página e intentá de nuevo.");
});


// ===== RECORDAR USUARIO =====
// Solo se recuerda el NOMBRE DE USUARIO. La contraseña nunca se guarda.
function cargarUsuarioRecordado() {
  try {
    // Versiones anteriores guardaban usuario Y contraseña en el navegador.
    // Se borra ese dato viejo para que no quede una contraseña guardada.
    localStorage.removeItem("datosRecordados");

    const guardado = localStorage.getItem(CLAVE_USUARIO_RECORDADO);
    const inputUser = document.getElementById("user");

    if (guardado && inputUser) inputUser.value = guardado;
  } catch (e) {
    // Si el navegador bloquea el almacenamiento, simplemente no se recuerda.
  }
}

async function preguntarRecordar(user) {
  const r = await Swal.fire({
    icon: "question",
    title: "¿Recordar tu usuario?",
    html: cajaUsuario(user) + "<p style='margin-top:10px;font-size:13px;opacity:.8'>La contraseña no se guarda.</p>",
    showCancelButton: true,
    confirmButtonText: "Sí, recordar",
    cancelButtonText: "No",
    cancelButtonColor: "#475569",
    allowOutsideClick: false,
    ...window.SWAL_BASE
  });

  try {
    if (r.isConfirmed) {
      localStorage.setItem(CLAVE_USUARIO_RECORDADO, user);
    } else {
      localStorage.removeItem(CLAVE_USUARIO_RECORDADO);
    }
  } catch (e) {
    // No es grave: solo no se recuerda el usuario.
  }
}

document.addEventListener("DOMContentLoaded", cargarUsuarioRecordado);


// ===== ¿CÓMO OBTENGO MI USUARIO? =====
// Los socios no se registran solos: los da de alta la administración del club.
function infoAlta() {
  return alerta(
    "info",
    "¿Todavía no tenés usuario?",
    "El alta de socios la realiza la administración del club. Si ya sos socio, pedí tu usuario y contraseña en la administración."
  );
}


// ===== LOGIN =====
async function login() {
  try {
    const user = document.getElementById("user").value.trim();
    const pass = document.getElementById("pass").value;

    if (!user || !pass) {
      return alerta("warning", "Faltan datos", "Completá todos los campos");
    }

    let respuesta;

    try {
      respuesta = await apiPortal("/auth/login", {
        method: "POST",
        body: JSON.stringify({ nombre_usuario: user, contrasena: pass })
      });
    } catch (e) {
      // 400 y 401: el servidor respondió (datos incorrectos o usuario inactivo).
      if (e.status === 400 || e.status === 401) {
        return alerta("error", "No se pudo iniciar sesión", e.message);
      }
      // Sin status: no hubo conexión con el servidor.
      return alerta("error", "Sin conexión", "No se pudo conectar con el servidor. Probá de nuevo en un momento.");
    }

    const usuario = respuesta.usuario;

    // Este portal es solo para socios. El personal entra por su propio panel.
    if (!usuario || usuario.rol !== "socio") {
      try { await apiPortal("/auth/logout", { method: "POST" }); } catch (e) { /* sin problema */ }
      return alerta("info", "Acceso del personal", "Este usuario es del personal del club. Ingresá desde el panel de administración.");
    }

    // Es un usuario de socio, pero no está vinculado a ninguna ficha de socio
    // (por ejemplo, si el socio fue dado de baja): no tendría datos para ver.
    if (!usuario.id_socio) {
      try { await apiPortal("/auth/logout", { method: "POST" }); } catch (e) { /* sin problema */ }
      return alerta("warning", "Cuenta sin socio asociado", "Tu usuario todavía no está vinculado a un socio. Consultá en la administración del club.");
    }

    sessionStorage.setItem("usuarioActivo", usuario.nombre_usuario);

    await preguntarRecordar(user);

    window.location.href = "../inicio/inicio.html";
  } catch (e) {
    console.error(e);
    alerta("error", "Error al iniciar sesión", "Ocurrió un problema inesperado. Intentá de nuevo.");
  }
}


// ===== LOGOUT =====
function logout() {
  cerrarSesionPortal();
}


// ===== TÉRMINOS Y CONDICIONES =====
const modalTerminos = document.getElementById("modalTerminos");
const btnVerTerminos = document.getElementById("btnVerTerminos");
const btnCerrarModal = document.getElementById("btnCerrarModal");
const checkTerminos = document.getElementById("checkTerminos");
const btnIngresar = document.getElementById("btnIngresar");

if (btnVerTerminos && modalTerminos) {
  btnVerTerminos.addEventListener("click", () => { modalTerminos.style.display = "flex"; });
}
if (btnCerrarModal && modalTerminos) {
  btnCerrarModal.addEventListener("click", () => { modalTerminos.style.display = "none"; });
}
if (checkTerminos && btnIngresar) {
  checkTerminos.addEventListener("change", () => { btnIngresar.disabled = !checkTerminos.checked; });
}