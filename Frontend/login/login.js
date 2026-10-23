
// ===== LOADER =====
setTimeout(() => {
  const loader = document.getElementById("loader");

  if (!loader) return;

  loader.style.opacity = "0";

  setTimeout(() => {
    loader.style.display = "none";
  }, 1000);
}, 3000);


// ===== UTILIDADES =====
const CLAVE_USUARIO_RECORDADO = "usuarioRecordado";

function cajaUsuario(user) {
  return `
    <div style="background:#1f2937;border-radius:8px;padding:12px;text-align:left;line-height:1.8">
      <div><b>Usuario:</b> ${escaparHtml(user)}</div>
    </div>
  `;
}


// ===== MANEJO DE ERRORES =====
window.addEventListener("error", () => {
  alerta(
    "error",
    "Error inesperado",
    "Algo salió mal. Recargá la página e intentá de nuevo."
  );
});

window.addEventListener("unhandledrejection", () => {
  alerta(
    "error",
    "Error inesperado",
    "Algo salió mal. Recargá la página e intentá de nuevo."
  );
});


// ===== RECORDAR USUARIO =====
function cargarUsuarioRecordado() {
  try {
    localStorage.removeItem("datosRecordados");

    const guardado = localStorage.getItem(CLAVE_USUARIO_RECORDADO);
    const inputUser = document.getElementById("user");

    if (guardado && inputUser) {
      inputUser.value = guardado;
    }
  } catch (e) {
    // El almacenamiento es opcional.
  }
}


async function preguntarRecordar(user) {
  const r = await Swal.fire({
    icon: "question",
    title: "¿Recordar tu usuario?",
    html:
      cajaUsuario(user) +
      "<p style='margin-top:10px;font-size:13px;opacity:.8'>La contraseña no se guarda.</p>",
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
    // No afecta al inicio de sesión.
  }
}


document.addEventListener("DOMContentLoaded", cargarUsuarioRecordado);


// ===== ¿CÓMO OBTENGO MI USUARIO? =====
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
    const checkTerminos = document.getElementById("checkTerminos");

    // Verificar términos antes de realizar cualquier petición.
    if (!checkTerminos || !checkTerminos.checked) {
      await Swal.fire({
        icon: "warning",
        title: "¡Atención!",
        text: "Debés aceptar los términos y condiciones para poder ingresar.",
        confirmButtonText: "Entendido",
        confirmButtonColor: "#2563eb",
        allowOutsideClick: false,
        ...window.SWAL_BASE
      });

      return;
    }

    // Validar los campos.
    if (!user || !pass) {
      return alerta(
        "warning",
        "Faltan datos",
        "Completá tu usuario y contraseña."
      );
    }

    // Enviar los datos a la API.
    let respuesta;

    try {
      respuesta = await apiPortal("/auth/login", {
        method: "POST",
        body: JSON.stringify({
          nombre_usuario: user,
          contrasena: pass
        })
      });
    } catch (e) {
      if (e.status === 400 || e.status === 401) {
        return alerta(
          "error",
          "No se pudo iniciar sesión",
          e.message
        );
      }

      return alerta(
        "error",
        "Sin conexión",
        "No se pudo conectar con el servidor. Probá de nuevo en un momento."
      );
    }

    const usuario = respuesta.usuario;

    // Permitir el ingreso únicamente a socios.
    if (!usuario || usuario.rol !== "socio") {
      try {
        await apiPortal("/auth/logout", {
          method: "POST"
        });
      } catch (e) {
        // Se muestra la alerta igualmente.
      }

      return alerta(
        "info",
        "Acceso del personal",
        "Este usuario es del personal del club. Ingresá desde el panel de administración."
      );
    }

    // Verificar que la cuenta esté vinculada a un socio.
    if (!usuario.id_socio) {
      try {
        await apiPortal("/auth/logout", {
          method: "POST"
        });
      } catch (e) {
        // Se muestra la alerta igualmente.
      }

      return alerta(
        "warning",
        "Cuenta sin socio asociado",
        "Tu usuario todavía no está vinculado a un socio. Consultá en la administración del club."
      );
    }

    // Guardar el usuario activo.
    sessionStorage.setItem(
      "usuarioActivo",
      usuario.nombre_usuario
    );

    // Preguntar si desea recordar el nombre de usuario.
    await preguntarRecordar(user);

    // Ingresar al portal del socio.
    window.location.href = "../inicio/inicio.html";

  } catch (e) {
    console.error(e);

    alerta(
      "error",
      "Error al iniciar sesión",
      "Ocurrió un problema inesperado. Intentá de nuevo."
    );
  }
}


// ===== CERRAR SESIÓN =====
function logout() {
  cerrarSesionPortal();
}


// ===== TÉRMINOS Y CONDICIONES =====
const modalTerminos = document.getElementById("modalTerminos");
const btnVerTerminos = document.getElementById("btnVerTerminos");
const btnCerrarModal = document.getElementById("btnCerrarModal");
const checkTerminos = document.getElementById("checkTerminos");
const btnIngresar = document.getElementById("btnIngresar");


// Abrir el modal.
if (btnVerTerminos && modalTerminos) {
  btnVerTerminos.addEventListener("click", () => {
    modalTerminos.style.display = "flex";
  });
}


// Cerrar el modal.
if (btnCerrarModal && modalTerminos) {
  btnCerrarModal.addEventListener("click", () => {
    modalTerminos.style.display = "none";
  });
}


// Mantener el botón disponible para poder mostrar la alerta.
if (btnIngresar) {
  btnIngresar.disabled = false;

  btnIngresar.addEventListener("click", (event) => {
    event.preventDefault();
    login();
  });
}
