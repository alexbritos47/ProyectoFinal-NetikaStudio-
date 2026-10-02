// ===== LOADER =====
setTimeout(() => {
  const loader = document.getElementById("loader");
  if (!loader) return;
  loader.style.opacity = "0";
  setTimeout(() => { loader.style.display = "none"; }, 1000);
}, 3000);


// ===== UTILIDADES =====
const PREFIJO = "usuario_";
const CLAVE_RECORDAR = "datosRecordados";
const SWAL_BASE = { background: "#111827", color: "#fff", confirmButtonColor: "#29aae1" };

function alerta(icon, title, text) {
  return Swal.fire({ icon, title, text, ...SWAL_BASE });
}

// Muestra varios errores juntos en una lista
function alertaErrores(titulo, errores) {
  const lista = errores.map(e => `<li style="margin:4px 0">${e}</li>`).join("");
  return Swal.fire({
    icon: "error",
    title: titulo,
    html: `<ul style="text-align:left;padding-left:20px">${lista}</ul>`,
    ...SWAL_BASE
  });
}

// Evita que caracteres especiales rompan el HTML de las alertas
function escaparHtml(texto) {
  return String(texto)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;");
}

// Recuadro con usuario y contraseña (se usa en varias alertas)
function cajaDatos(user, pass) {
  return `
    <div style="background:#1f2937;border-radius:8px;padding:12px;text-align:left;line-height:1.8">
      <div><b>Usuario:</b> ${escaparHtml(user)}</div>
      <div><b>Contraseña:</b> ${escaparHtml(pass)}</div>
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

function cryptoDisponible() {
  return !!(window.crypto && window.crypto.subtle);
}

async function hashPass(pass, salt) {
  const data = new TextEncoder().encode(salt + pass);
  const buf = await crypto.subtle.digest("SHA-256", data);
  return Array.from(new Uint8Array(buf)).map(b => b.toString(16).padStart(2, "0")).join("");
}

function generarSalt() {
  const arr = crypto.getRandomValues(new Uint8Array(16));
  return Array.from(arr).map(b => b.toString(16).padStart(2, "0")).join("");
}

function obtenerUsuarios() {
  const lista = [];
  for (let i = 0; i < localStorage.length; i++) {
    const k = localStorage.key(i);
    if (k && k.startsWith(PREFIJO)) {
      try { lista.push(JSON.parse(localStorage.getItem(k))); } catch {}
    }
  }
  return lista;
}

function storageDisponible() {
  try {
    localStorage.setItem("__test", "1");
    localStorage.removeItem("__test");
    return true;
  } catch {
    return false;
  }
}


// ===== RECORDAR DATOS =====
function cargarDatosRecordados() {
  try {
    const guardado = localStorage.getItem(CLAVE_RECORDAR);
    if (!guardado) return;
    const { user, pass } = JSON.parse(guardado);
    const inputUser = document.getElementById("user");
    const inputPass = document.getElementById("pass");
    if (inputUser && user) inputUser.value = user;
    if (inputPass && pass) inputPass.value = pass;
  } catch {
    localStorage.removeItem(CLAVE_RECORDAR);
  }
}

async function preguntarRecordar(user, pass) {
  const r = await Swal.fire({
    icon: "question",
    title: "¿Recordar tus datos?",
    html: cajaDatos(user, pass),
    showCancelButton: true,
    confirmButtonText: "Sí, recordar",
    cancelButtonText: "No",
    cancelButtonColor: "#475569",
    allowOutsideClick: false,
    ...SWAL_BASE
  });

  try {
    if (r.isConfirmed) {
      localStorage.setItem(CLAVE_RECORDAR, JSON.stringify({ user, pass }));
    } else {
      localStorage.removeItem(CLAVE_RECORDAR);
    }
  } catch {
    await alerta("error", "No se pudo guardar", "El almacenamiento está lleno o bloqueado.");
  }
}


// ===== RESTRICCIONES EN LAS CASILLAS =====
function soloNumeros(input, maxLen) {
  input.setAttribute("inputmode", "numeric");
  input.setAttribute("maxlength", maxLen);
  input.addEventListener("input", () => {
    input.value = input.value.replace(/\D/g, "").slice(0, maxLen);
  });
}

function soloLetras(input) {
  input.setAttribute("maxlength", 30);
  input.addEventListener("input", () => {
    input.value = input.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñÜü\s'-]/g, "");
  });
}

document.addEventListener("DOMContentLoaded", () => {
  const ced = document.getElementById("cedula");
  const nom = document.getElementById("nombre");
  const ape = document.getElementById("apellido");
  if (ced) soloNumeros(ced, 8);
  if (nom) soloLetras(nom);
  if (ape) soloLetras(ape);

  cargarDatosRecordados();

  if (!storageDisponible()) {
    alerta("warning", "Almacenamiento bloqueado", "Tu navegador no deja guardar datos (¿modo privado?). No vas a poder registrarte ni iniciar sesión.");
  }
  if (!cryptoDisponible()) {
    alerta("warning", "Navegador no compatible", "Abrí la página con https:// o desde un navegador actualizado para poder iniciar sesión.");
  }
});


// ===== CAMBIO DE VISTAS =====
function mostrarRegistro() {
  document.getElementById("loginBox").style.display = "none";
  document.getElementById("registerBox").style.display = "flex";
}

function mostrarLogin() {
  document.getElementById("registerBox").style.display = "none";
  document.getElementById("loginBox").style.display = "block";
}


// ===== REGISTRO =====
async function register() {
  try {
    const nombre = document.getElementById("nombre").value.trim();
    const apellido = document.getElementById("apellido").value.trim();
    const cedula = document.getElementById("cedula").value.trim();
    const email = document.getElementById("email").value.trim().toLowerCase();
    const user = document.getElementById("regUser").value.trim();
    const pass = document.getElementById("regPass").value;

    if (!nombre || !apellido || !cedula || !email || !user || !pass) {
      return alerta("warning", "Faltan datos", "Completa todos los campos");
    }

    if (!storageDisponible()) {
      return alerta("error", "Sin almacenamiento", "Tu navegador no permite guardar datos. Salí del modo privado e intentá de nuevo.");
    }
    if (!cryptoDisponible()) {
      return alerta("error", "Navegador no compatible", "Abrí la página con https:// o usá un navegador actualizado.");
    }

    // Validaciones: se juntan todos los errores
    const errores = [];

    if (nombre.length < 2) errores.push("El <b>nombre</b> debe tener al menos 2 letras.");
    if (apellido.length < 2) errores.push("El <b>apellido</b> debe tener al menos 2 letras.");

    if (!/^\d{8}$/.test(cedula)) {
      errores.push("La <b>cédula</b> debe tener exactamente 8 números (sin puntos ni guión).");
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email)) {
      errores.push("El <b>email</b> no es válido.");
    }

    if (pass.length < 6) {
      errores.push("La <b>contraseña</b> debe tener al menos 6 caracteres.");
    }

    if (errores.length > 0) {
      return alertaErrores("Revisá estos datos", errores);
    }

    // Duplicados
    if (localStorage.getItem(PREFIJO + user.toLowerCase())) {
      return alerta("error", "Usuario existente", "Ese usuario ya existe, elegí otro.");
    }
    const usuarios = obtenerUsuarios();
    if (usuarios.some(u => u.cedula === cedula)) {
      return alerta("error", "Cédula registrada", "Ya existe una cuenta con esa cédula.");
    }
    if (usuarios.some(u => u.email === email)) {
      return alerta("error", "Email registrado", "Ya existe una cuenta con ese email.");
    }

    // Guardar
    const salt = generarSalt();
    const passHash = await hashPass(pass, salt);
    const datosUsuario = { user, nombre, apellido, cedula, email, salt, passHash };

    try {
      localStorage.setItem(PREFIJO + user.toLowerCase(), JSON.stringify(datosUsuario));
    } catch {
      return alerta("error", "No se pudo guardar", "El almacenamiento está lleno o bloqueado. Liberá espacio e intentá de nuevo.");
    }

    await Swal.fire({
      icon: "success",
      title: "¡Registro exitoso!",
      html: cajaDatos(user, pass),
      ...SWAL_BASE
    });

    ["nombre", "apellido", "cedula", "email", "regUser", "regPass"].forEach(id => {
      document.getElementById(id).value = "";
    });

    mostrarLogin();
  } catch (e) {
    console.error(e);
    alerta("error", "Error al registrar", "Ocurrió un problema inesperado. Intentá de nuevo.");
  }
}


// ===== LOGIN =====
async function login() {
  try {
    const user = document.getElementById("user").value.trim();
    const pass = document.getElementById("pass").value;

    if (!user || !pass) {
      return alerta("warning", "Faltan datos", "Completa todos los campos");
    }

    if (!cryptoDisponible()) {
      return alerta("error", "Navegador no compatible", "Abrí la página con https:// o usá un navegador actualizado.");
    }

    const guardado = localStorage.getItem(PREFIJO + user.toLowerCase());

    if (!guardado) {
      return alerta("error", "Usuario no encontrado", "Ese usuario no existe. ¿Te registraste?");
    }

    let datos;
    try {
      datos = JSON.parse(guardado);
    } catch {
      return alerta("error", "Cuenta dañada", "Los datos de esta cuenta están corruptos. Registrate de nuevo.");
    }

    const hashIngresado = await hashPass(pass, datos.salt);

    if (hashIngresado !== datos.passHash) {
      return alerta("error", "Contraseña incorrecta", "La contraseña no coincide. Probá de nuevo.");
    }

    sessionStorage.setItem("usuarioActivo", datos.user);

    // Muestra el usuario tal como se escribió al loguearse
    await preguntarRecordar(user, pass);

    window.location.href = "../inicio/inicio.html";
  } catch (e) {
    console.error(e);
    alerta("error", "Error al iniciar sesión", "Ocurrió un problema inesperado. Intentá de nuevo.");
  }
}


// ===== LOGOUT =====
function logout() {
  sessionStorage.removeItem("usuarioActivo");
  window.location.href = "../inicio/inicio.html";
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