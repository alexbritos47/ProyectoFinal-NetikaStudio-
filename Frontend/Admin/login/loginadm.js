window.addEventListener('load', () => {
  const loader = document.getElementById('loader');
  const main = document.getElementById('main');

  setTimeout(() => {
    loader.style.display = 'none';
    main.style.display = 'block';
  }, 600);
});

document.addEventListener('DOMContentLoaded', () => {
  // Con Docker (http://localhost:3000) la API está en el mismo origen.
  // Si abrís el HTML de otra forma (Live Server, etc.), llama directo al puerto 8080.
  const API_BASE = location.port === '3000' ? '' : 'http://localhost:8080';

  const userInput = document.getElementById('user');
  const passInput = document.getElementById('password');
  const btnIngresar = document.getElementById('btnIngresar');
  const loginBox = document.querySelector('.login');

  // Credenciales de respaldo, solo se usan si el backend no responde.
  const ADMIN_PRUEBA = { usuario: 'admin', password: 'admin123' };

  // Mensaje de error (se agrega solo, no hace falta tocar el HTML)
  const errorMsg = document.createElement('p');
  errorMsg.className = 'login-error';
  loginBox.insertBefore(errorMsg, btnIngresar);

  function mostrarError(texto) {
    errorMsg.textContent = texto;
  }

  function entrarComoAdmin(usuario) {
    sessionStorage.setItem('sesionActiva', 'true');
    sessionStorage.setItem('usuario', usuario);
    sessionStorage.setItem('rol', 'admin');
    // El panel de socios queda en esta misma carpeta (Admin/login/).
    window.location.href = 'sociosadmin.html';
  }

  async function login() {
    const usuario = userInput.value.trim();
    const password = passInput.value;

    if (!usuario || !password) {
      mostrarError('Completá usuario y contraseña.');
      return;
    }

    mostrarError('');
    btnIngresar.disabled = true;
    btnIngresar.value = 'Ingresando...';

    try {
      const res = await fetch(API_BASE + '/api/auth/login', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ nombre_usuario: usuario, contrasena: password })
      });

      let data = {};
      try { data = await res.json(); } catch (e) { /* respuesta sin JSON */ }

      if (res.ok && data.usuario && data.usuario.rol === 'administrador') {
        entrarComoAdmin(data.usuario.nombre_usuario || usuario);
      } else if (res.ok && data.usuario) {
        mostrarError('Este usuario no tiene permisos de administrador.');
      } else {
        mostrarError(data.mensaje || 'Usuario o contraseña incorrectos.');
      }
    } catch (err) {
      // No se pudo conectar con el backend: valida contra las credenciales de prueba.
      if (usuario === ADMIN_PRUEBA.usuario && password === ADMIN_PRUEBA.password) {
        entrarComoAdmin(usuario);
      } else {
        mostrarError('No se pudo conectar con el servidor.');
      }
    } finally {
      btnIngresar.disabled = false;
      btnIngresar.value = 'Ingresar';
    }
  }

  btnIngresar.addEventListener('click', login);
  passInput.addEventListener('keydown', (e) => {
    if (e.key === 'Enter') login();
  });
});