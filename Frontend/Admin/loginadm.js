window.addEventListener('load', () => {
  const loader = document.getElementById('loader');
  const main = document.getElementById('main');

  setTimeout(() => {
    loader.style.display = 'none';
    main.style.display = 'block';
  }, 600);
});

document.addEventListener('DOMContentLoaded', () => {
  const userInput = document.getElementById('user');
  const passInput = document.getElementById('password');
  const btnIngresar = document.getElementById('btnIngresar');
  const loginBox = document.querySelector('.login');

  // Credenciales de prueba mientras no está el backend PHP conectado.
  // Cuando tengas login_admin.php andando, borrá este bloque y dejá solo el fetch.
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
    window.location.href = '../inicio/inicio.html';
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
      const res = await fetch('login_admin.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ usuario, password })
      });

      if (!res.ok) throw new Error('sin backend');

      const data = await res.json();

      if (data.success && data.rol === 'admin') {
        entrarComoAdmin(usuario);
      } else if (data.success && data.rol !== 'admin') {
        mostrarError('Este usuario no tiene permisos de administrador.');
      } else {
        mostrarError(data.message || 'Usuario o contraseña incorrectos.');
      }
    } catch (err) {
      // Todavía no hay backend: valida contra las credenciales de prueba.
      if (usuario === ADMIN_PRUEBA.usuario && password === ADMIN_PRUEBA.password) {
        entrarComoAdmin(usuario);
      } else {
        mostrarError('Usuario o contraseña incorrectos.');
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