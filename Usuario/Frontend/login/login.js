// ===== LOADER =====
setTimeout(() => {
  const loader = document.getElementById("loader");
  loader.style.opacity = "0";
  setTimeout(() => {
    loader.style.display = "none";
  }, 1000);
}, 3000);


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
function register() {
  const nombre = document.getElementById("nombre").value.trim();
  const apellido = document.getElementById("apellido").value.trim();
  const email = document.getElementById("email").value.trim();
  const user = document.getElementById("regUser").value.trim();
  const pass = document.getElementById("regPass").value.trim();

  if (!nombre || !apellido || !email || !user || !pass) {
    Swal.fire({
      icon: "warning",
      title: "Faltan datos",
      text: "Completa todos los campos",
      background: "#111827",
      color: "#fff"
    });
    return;
  }

  if (localStorage.getItem(user)) {
    Swal.fire({
      icon: "error",
      title: "Usuario existente",
      text: "Ese usuario ya existe",
      background: "#111827",
      color: "#fff"
    });
    return;
  }

  const datosUsuario = {
    nombre,
    apellido,
    email,
    pass
  };

  localStorage.setItem(user, JSON.stringify(datosUsuario));

  Swal.fire({
    icon: "success",
    title: "¡Registro exitoso!",
    html: `Guardá tus datos:<br><br><b>Usuario:</b> ${user}<br><b>Contraseña:</b> ${pass}`,
    background: "#111827",
    color: "#fff"
  });

  document.getElementById("nombre").value = "";
  document.getElementById("apellido").value = "";
  document.getElementById("email").value = "";
  document.getElementById("regUser").value = "";
  document.getElementById("regPass").value = "";

  mostrarLogin();
}


// ===== LOGIN =====
function login() {
  const user = document.getElementById("user").value.trim();
  const pass = document.getElementById("pass").value.trim();

  if (!user || !pass) {
    Swal.fire({
      icon: "warning",
      title: "Faltan datos",
      text: "Completa todos los campos",
      background: "#111827",
      color: "#fff"
    });
    return;
  }

  const datosGuardados = localStorage.getItem(user);

  if (datosGuardados) {
    const datosUsuario = JSON.parse(datosGuardados);
    if (datosUsuario.pass === pass) {
      sessionStorage.setItem("usuarioActivo", user);
      window.location.href = "../inicio/inicio.html";
      return;
    }
  }

  Swal.fire({
    icon: "error",
    title: "Error",
    text: "Usuario o contraseña incorrectos",
    background: "#111827",
    color: "#fff"
  });
}


// ===== LOGOUT =====
function logout() {
  sessionStorage.removeItem("usuarioActivo");
  window.location.href = "../inicio/inicio.html";
}