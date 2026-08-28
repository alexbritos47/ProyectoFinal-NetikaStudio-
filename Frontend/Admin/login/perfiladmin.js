document.addEventListener('DOMContentLoaded', () => {

  // ---------- Protección de sesión ----------
  if (sessionStorage.getItem('sesionActiva') !== 'true' || sessionStorage.getItem('rol') !== 'admin') {
    window.location.href = 'loginadm.html';
    return;
  }

  const usuario = sessionStorage.getItem('usuario') || 'admin';

  const avatarIniciales = document.getElementById('avatarIniciales');
  const nombreAdmin = document.getElementById('nombreAdmin');
  const datoUsuario = document.getElementById('datoUsuario');
  const datoEmail = document.getElementById('datoEmail');
  const btnVerPassword = document.getElementById('btnVerPassword');
  const datoPassword = document.getElementById('datoPassword');
  const btnGuardarPerfil = document.getElementById('btnGuardarPerfil');
  const btnLogout = document.getElementById('btnLogout');

  // ---------- Datos del admin (localStorage mientras no hay backend) ----------
  const CLAVE_PERFIL = 'ccm_perfil_admin_' + usuario;

  function cargarPerfil() {
    const guardado = localStorage.getItem(CLAVE_PERFIL);
    if (guardado) return JSON.parse(guardado);
    return { nombre: usuario.charAt(0).toUpperCase() + usuario.slice(1), email: `${usuario}@clubciclistamaragato.com` };
  }

  function guardarPerfil(datos) {
    localStorage.setItem(CLAVE_PERFIL, JSON.stringify(datos));
  }

  let perfil = cargarPerfil();

  function render() {
    nombreAdmin.textContent = perfil.nombre;
    datoUsuario.textContent = usuario;
    datoEmail.textContent = perfil.email;
    avatarIniciales.textContent = perfil.nombre.charAt(0).toUpperCase();
  }
  render();

  // ---------- Editar datos con doble clic (simple, sin backend) ----------
  nombreAdmin.addEventListener('dblclick', () => {
    const nuevoNombre = prompt('Nombre a mostrar:', perfil.nombre);
    if (nuevoNombre && nuevoNombre.trim()) {
      perfil.nombre = nuevoNombre.trim();
      render();
    }
  });

  datoEmail.addEventListener('dblclick', () => {
    const nuevoEmail = prompt('Correo electrónico:', perfil.email);
    if (nuevoEmail && nuevoEmail.trim()) {
      perfil.email = nuevoEmail.trim();
      render();
    }
  });

  btnVerPassword.addEventListener('click', () => {
    const nueva = prompt('Ingresá la nueva contraseña:');
    if (nueva && nueva.trim()) {
      datoPassword.textContent = '••••••••';
      alert('Contraseña actualizada (pendiente de conectar con el backend).');
    }
  });

  btnGuardarPerfil.addEventListener('click', () => {
    guardarPerfil(perfil);
    alert('Perfil guardado.');
  });

  // ---------- Logout ----------
  btnLogout.addEventListener('click', () => {
    sessionStorage.clear();
    window.location.href = 'loginadm.html';
  });
});