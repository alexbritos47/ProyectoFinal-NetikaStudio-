// ===== LOADER =====
setTimeout(() => {
  const loader = document.getElementById("loader");
  loader.style.opacity = "0";
  setTimeout(() => {
    loader.style.display = "none";
  }, 1000);
}, 3000);


// ===== REGISTRO =====
function register() {
  const user = document.getElementById("user").value.trim();
  const pass = document.getElementById("pass").value.trim();

  if (!user || !pass) {
    alert("Completa todos los campos");
    return;
  }

  localStorage.setItem(user, pass);
  alert("Usuario registrado correctamente");

  document.getElementById("user").value = "";
  document.getElementById("pass").value = "";
}


// ===== LOGIN =====
function login() {
  const user = document.getElementById("user").value.trim();
  const pass = document.getElementById("pass").value.trim();

  if (!user || !pass) {
    alert("Completa todos los campos");
    return;
  }

  const passGuardada = localStorage.getItem(user);

  if (passGuardada === pass) {
    sessionStorage.setItem("usuarioActivo", user);
    window.location.href = "../inicio/inicio.html";
  } else {
    alert("Usuario o contraseña incorrectos");
  }
}


// ===== LOGOUT =====
function logout() {
  sessionStorage.removeItem("usuarioActivo");
  window.location.href = "../inicio/inicio.html";
}