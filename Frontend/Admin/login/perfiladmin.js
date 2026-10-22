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
  const inputEmail = document.getElementById('inputEmail');
  const btnVerPassword = document.getElementById('btnVerPassword');
  const inputPassword = document.getElementById('inputPassword');
  const bloquePassword = document.getElementById('bloquePassword');
  const datoPasswordActual = document.getElementById('datoPasswordActual');
  const datoPasswordNueva = document.getElementById('datoPasswordNueva');
  const inputPasswordActual = document.getElementById('inputPasswordActual');
  const inputPasswordNueva = document.getElementById('inputPasswordNueva');

  // Misma lógica de conexión que el login del admin.
  const API_BASE = location.port === '3000' ? '' : 'http://localhost:8080';
  const ADMIN_PRUEBA = { usuario: 'admin', password: 'admin123' }; // solo si no hay backend
  const btnGuardarPerfil = document.getElementById('btnGuardarPerfil');
  const btnLogout = document.getElementById('btnLogout');

  // ---------- Aviso dentro de la página (reemplaza a alert) ----------
  const toastAdmin = document.getElementById('toastAdmin');
  const toastIcono = document.getElementById('toastIcono');
  const toastTitulo = document.getElementById('toastTitulo');
  const toastDetalle = document.getElementById('toastDetalle');
  let toastTimeout;

  // tipo: 'ok' (verde/azul) o 'error' (rojo)
  function mostrarToast(titulo, detalle, tipo = 'ok') {
    toastTitulo.textContent = titulo;
    toastDetalle.textContent = detalle || '';
    toastIcono.textContent = tipo === 'error' ? '!' : '✓';
    toastAdmin.classList.toggle('error', tipo === 'error');
    toastAdmin.classList.add('mostrar');
    clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => toastAdmin.classList.remove('mostrar'), 4500);
  }

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
    inputEmail.value = perfil.email;
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

  // ---------- Correo: se edita directamente en el input ----------
  const EMAIL_VALIDO = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  inputEmail.addEventListener('input', () => datoEmail.classList.remove('invalido'));

  // ---------- Contraseña: pide la actual y la nueva ----------
  const PASSWORD_OCULTA = '••••••••';
  const MIN_PASSWORD = 6;
  let cambiandoPassword = false;

  function activarCambioPassword() {
    cambiandoPassword = true;
    bloquePassword.hidden = false;
    inputPasswordActual.value = '';
    inputPasswordNueva.value = '';
    btnVerPassword.textContent = 'Cancelar';
    inputPasswordActual.focus();
  }

  function cancelarCambioPassword() {
    cambiandoPassword = false;
    bloquePassword.hidden = true;
    inputPasswordActual.value = '';
    inputPasswordNueva.value = '';
    datoPasswordActual.classList.remove('invalido');
    datoPasswordNueva.classList.remove('invalido');
    inputPassword.value = PASSWORD_OCULTA;
    btnVerPassword.textContent = 'Cambiar';
  }

  btnVerPassword.addEventListener('click', () => {
    if (cambiandoPassword) cancelarCambioPassword();
    else activarCambioPassword();
  });

  inputPasswordActual.addEventListener('input', () => datoPasswordActual.classList.remove('invalido'));
  inputPasswordNueva.addEventListener('input', () => datoPasswordNueva.classList.remove('invalido'));

  // Comprueba la contraseña actual con el login del backend.
  // Devuelve: 'ok' (correcta), 'mal' (incorrecta) o 'sin-servidor' (backend no disponible).
  async function verificarPasswordActual(password) {
    try {
      const res = await fetch(API_BASE + '/api/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nombre_usuario: usuario, contrasena: password })
      });
      return res.ok ? 'ok' : 'mal';
    } catch (e) {
      return 'sin-servidor';
    }
  }

  // Guarda la nueva contraseña en el backend (usuario con sesión iniciada).
  async function guardarPasswordNueva(password) {
    const resMe = await fetch(API_BASE + '/api/auth/me');
    if (!resMe.ok) throw new Error('No se pudo identificar al usuario.');
    const me = await resMe.json();

    const res = await fetch(API_BASE + '/api/usuarios/' + me.id_usuario, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ contrasena: password })
    });
    if (!res.ok) {
      let data = {};
      try { data = await res.json(); } catch (e) { /* sin JSON */ }
      const detalle = data.errores ? Object.values(data.errores).join(' ') : data.mensaje;
      throw new Error(detalle || 'No se pudo guardar la contraseña.');
    }
  }

  btnGuardarPerfil.addEventListener('click', async () => {
    // Correo
    const email = inputEmail.value.trim();
    if (!EMAIL_VALIDO.test(email)) {
      datoEmail.classList.add('invalido');
      mostrarToast('Correo no válido', 'Escribí un correo electrónico válido.', 'error');
      inputEmail.focus();
      return;
    }

    // Contraseña (solo si se está cambiando)
    let detalle = 'Tus datos se guardaron correctamente.';
    let tipoAviso = 'ok';

    if (cambiandoPassword) {
      const actual = inputPasswordActual.value;
      const nueva = inputPasswordNueva.value;

      if (!actual) {
        datoPasswordActual.classList.add('invalido');
        mostrarToast('Falta la contraseña actual', 'Ingresá tu contraseña actual.', 'error');
        inputPasswordActual.focus();
        return;
      }
      if (nueva.length < MIN_PASSWORD) {
        datoPasswordNueva.classList.add('invalido');
        mostrarToast('Contraseña muy corta', 'La nueva contraseña debe tener al menos ' + MIN_PASSWORD + ' caracteres.', 'error');
        inputPasswordNueva.focus();
        return;
      }
      if (nueva === actual) {
        datoPasswordNueva.classList.add('invalido');
        mostrarToast('Contraseña repetida', 'La nueva contraseña tiene que ser distinta a la actual.', 'error');
        inputPasswordNueva.focus();
        return;
      }

      btnGuardarPerfil.disabled = true;
      try {
        const estado = await verificarPasswordActual(actual);

        if (estado === 'mal') {
          datoPasswordActual.classList.add('invalido');
          mostrarToast('Contraseña incorrecta', 'La contraseña actual no es correcta.', 'error');
          inputPasswordActual.focus();
          return;
        }

        if (estado === 'sin-servidor') {
          // Modo prueba: sin backend solo se puede comprobar la contraseña de prueba.
          if (!(usuario === ADMIN_PRUEBA.usuario && actual === ADMIN_PRUEBA.password)) {
            datoPasswordActual.classList.add('invalido');
            mostrarToast('Contraseña incorrecta', 'La contraseña actual no es correcta.', 'error');
            inputPasswordActual.focus();
            return;
          }
          detalle = 'Sin conexión con el servidor: la contraseña nueva no se pudo guardar.';
          tipoAviso = 'error';
        } else {
          try {
            await guardarPasswordNueva(nueva);
            detalle = 'Tus datos y tu contraseña se actualizaron.';
          } catch (err) {
            mostrarToast('No se pudo guardar', err.message, 'error');
            return;
          }
        }
        cancelarCambioPassword();
      } finally {
        btnGuardarPerfil.disabled = false;
      }
    }

    perfil.email = email;
    guardarPerfil(perfil);
    render();
    mostrarToast('Perfil guardado', detalle, tipoAviso);
  });

  // ---------- Logout ----------
  btnLogout.addEventListener('click', () => {
    sessionStorage.clear();
    window.location.href = 'loginadm.html';
  });
});