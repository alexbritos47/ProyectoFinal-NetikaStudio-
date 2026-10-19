// ==========================================
// Funciones compartidas del portal del socio
// ==========================================
// Todas las pantallas del socio (inicio, login, perfil y facturas)
// usan estas funciones para hablar con el backend.
//
// La sesión la guarda el SERVIDOR (cookie). En el navegador solo se
// guarda "usuarioActivo" como ayuda para dibujar el menú rápido.

const API_PORTAL = "/api";

// Llama a la API y devuelve el JSON.
// Si el servidor responde con error, lanza un Error con:
//   e.message -> mensaje para mostrar
//   e.status  -> código HTTP (si es undefined, no hubo conexión)
async function apiPortal(ruta, opciones = {}) {
  const respuesta = await fetch(API_PORTAL + ruta, {
    headers: { "Content-Type": "application/json" },
    credentials: "same-origin",
    ...opciones
  });

  let datos = null;
  try { datos = await respuesta.json(); } catch (e) { /* sin cuerpo */ }

  if (!respuesta.ok) {
    let mensaje = "No se pudo completar la operación.";

    if (datos && datos.mensaje) {
      mensaje = datos.mensaje;
    } else if (datos && datos.errores) {
      mensaje = Object.values(datos.errores).join(" ");
    }

    const error = new Error(mensaje);
    error.status = respuesta.status;
    throw error;
  }

  return datos;
}

// Devuelve el usuario con sesión iniciada SI es socio y tiene una ficha
// de socio vinculada (id_socio); si no, null.
async function sesionSocio() {
  try {
    const usuario = await apiPortal("/auth/me");
    return usuario && usuario.rol === "socio" && usuario.id_socio ? usuario : null;
  } catch (e) {
    return null;
  }
}

// Para las páginas que exigen sesión: si no hay, vuelve al login.
async function exigirSesionSocio() {
  const usuario = await sesionSocio();

  if (!usuario) {
    sessionStorage.removeItem("usuarioActivo");
    window.location.href = "../login/login.html";
    return null;
  }

  return usuario;
}

// Cierra la sesión en el servidor y vuelve al inicio.
async function cerrarSesionPortal() {
  try { await apiPortal("/auth/logout", { method: "POST" }); } catch (e) { /* igual se sale */ }
  sessionStorage.removeItem("usuarioActivo");
  window.location.href = "../inicio/inicio.html";
}

// Evita que caracteres especiales rompan el HTML.
function escaparHtml(texto) {
  return String(texto ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;");
}