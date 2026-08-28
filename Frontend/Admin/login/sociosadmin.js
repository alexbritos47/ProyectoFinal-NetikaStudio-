document.addEventListener('DOMContentLoaded', () => {

  // ---------- Protección de sesión ----------
  // Si no hay sesión de admin activa, vuelve al login.
  if (sessionStorage.getItem('sesionActiva') !== 'true' || sessionStorage.getItem('rol') !== 'admin') {
    window.location.href = 'loginadm.html';
    return;
  }

  // ---------- Datos de socios (localStorage como almacenamiento local mientras no hay backend) ----------
  const CLAVE_SOCIOS = 'ccm_socios';

  function socioInicialesDemo() {
    return [
      { id: 1, nombre: 'Ana Rodríguez', email: 'ana.rodriguez@correo.com', alDia: true },
      { id: 2, nombre: 'Bruno Méndez', email: 'bruno.mendez@correo.com', alDia: true },
      { id: 3, nombre: 'Carla Suárez', email: 'carla.suarez@correo.com', alDia: false },
      { id: 4, nombre: 'Diego Fernández', email: 'diego.fernandez@correo.com', alDia: false },
    ];
  }

  function cargarSocios() {
    const guardado = localStorage.getItem(CLAVE_SOCIOS);
    if (guardado) return JSON.parse(guardado);
    const iniciales = socioInicialesDemo();
    localStorage.setItem(CLAVE_SOCIOS, JSON.stringify(iniciales));
    return iniciales;
  }

  function guardarSocios(lista) {
    localStorage.setItem(CLAVE_SOCIOS, JSON.stringify(lista));
  }

  let socios = cargarSocios();

  // ---------- Referencias ----------
  const tablaSocios = document.getElementById('tablaSocios');
  const mensajeVacio = document.getElementById('mensajeVacio');
  const totalSociosEl = document.getElementById('totalSocios');
  const sociosAlDiaEl = document.getElementById('sociosAlDia');
  const sociosPendientesEl = document.getElementById('sociosPendientes');
  const buscarSocio = document.getElementById('buscarSocio');

  const modalFondo = document.getElementById('modalFondo');
  const btnAbrirModal = document.getElementById('btnAbrirModal');
  const btnCancelarModal = document.getElementById('btnCancelarModal');
  const btnConfirmarModal = document.getElementById('btnConfirmarModal');
  const nombreSocioInput = document.getElementById('nombreSocio');
  const emailSocioInput = document.getElementById('emailSocio');

  const btnLogout = document.getElementById('btnLogout');

  // ---------- Iniciales para el avatar ----------
  function iniciales(nombre) {
    return nombre.split(' ').filter(Boolean).slice(0, 2).map(p => p[0].toUpperCase()).join('');
  }

  // ---------- Render ----------
  function renderResumen() {
    totalSociosEl.textContent = socios.length;
    sociosAlDiaEl.textContent = socios.filter(s => s.alDia).length;
    sociosPendientesEl.textContent = socios.filter(s => !s.alDia).length;
  }

  function renderTabla(filtro = '') {
    const texto = filtro.trim().toLowerCase();
    const filtrados = socios.filter(s => s.nombre.toLowerCase().includes(texto));

    tablaSocios.innerHTML = '';
    mensajeVacio.style.display = filtrados.length ? 'none' : 'block';

    filtrados.forEach(socio => {
      const fila = document.createElement('tr');
      fila.innerHTML = `
        <td data-label="Socio">
          <div class="socio-nombre">
            <span class="socio-avatar">${iniciales(socio.nombre)}</span>
            ${socio.nombre}
          </div>
        </td>
        <td data-label="Email">${socio.email}</td>
        <td data-label="Estado">
          <span class="estado-admin ${socio.alDia ? 'al-dia' : 'pendiente'}">
            ${socio.alDia ? 'Al día' : 'Pendiente'}
          </span>
        </td>
        <td data-label="Acciones">
          <div class="acciones-fila">
            <button class="btn-fila marcar" data-id="${socio.id}">${socio.alDia ? 'Marcar pendiente' : 'Marcar pago'}</button>
            <button class="btn-fila eliminar" data-id="${socio.id}">Eliminar</button>
          </div>
        </td>
      `;
      tablaSocios.appendChild(fila);
    });
  }

  function render(filtro = '') {
    renderResumen();
    renderTabla(filtro);
  }

  // ---------- Buscador ----------
  buscarSocio.addEventListener('input', () => render(buscarSocio.value));

  // ---------- Modal: agregar socio ----------
  function abrirModal() {
    modalFondo.classList.add('abierto');
    nombreSocioInput.value = '';
    emailSocioInput.value = '';
    nombreSocioInput.focus();
  }
  function cerrarModal() { modalFondo.classList.remove('abierto'); }

  btnAbrirModal.addEventListener('click', abrirModal);
  btnCancelarModal.addEventListener('click', cerrarModal);
  modalFondo.addEventListener('click', (e) => { if (e.target === modalFondo) cerrarModal(); });

  btnConfirmarModal.addEventListener('click', () => {
    const nombre = nombreSocioInput.value.trim();
    const email = emailSocioInput.value.trim();
    if (!nombre || !email) {
      mostrarToast('Faltan datos', 'Completá nombre y email para agregar al socio.');
      return;
    }
    const nuevoId = socios.length ? Math.max(...socios.map(s => s.id)) + 1 : 1;
    socios.push({ id: nuevoId, nombre, email, alDia: false });
    guardarSocios(socios);
    render(buscarSocio.value);
    cerrarModal();
    mostrarToast('Socio agregado', `${nombre} se sumó al club.`);
  });

  // ---------- Acciones de fila: eliminar / marcar pago ----------
  tablaSocios.addEventListener('click', (e) => {
    const boton = e.target.closest('button');
    if (!boton) return;
    const id = Number(boton.dataset.id);
    const socio = socios.find(s => s.id === id);
    if (!socio) return;

    if (boton.classList.contains('eliminar')) {
      const confirmar = confirm(`¿Quitar a ${socio.nombre} del club?`);
      if (!confirmar) return;
      socios = socios.filter(s => s.id !== id);
      guardarSocios(socios);
      render(buscarSocio.value);
      mostrarToast('Socio eliminado', `${socio.nombre} fue quitado del club.`);
    }

    if (boton.classList.contains('marcar')) {
      socio.alDia = !socio.alDia;
      guardarSocios(socios);
      render(buscarSocio.value);
      if (socio.alDia) {
        mostrarToast('Pago registrado', `Se registró el pago de ${socio.nombre}.`);
      }
    }
  });

  // ---------- Toast ----------
  const toastAdmin = document.getElementById('toastAdmin');
  const toastTitulo = document.getElementById('toastTitulo');
  const toastDetalle = document.getElementById('toastDetalle');
  let toastTimeout;

  function mostrarToast(titulo, detalle) {
    toastTitulo.textContent = titulo;
    toastDetalle.textContent = detalle;
    toastAdmin.classList.add('mostrar');
    clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => toastAdmin.classList.remove('mostrar'), 4000);
  }

  // Si venimos de facturas-admin.html tras registrar un pago, mostramos el aviso acá también.
  const avisoPendiente = localStorage.getItem('ccm_ultimo_pago');
  if (avisoPendiente) {
    const datos = JSON.parse(avisoPendiente);
    mostrarToast('Pago registrado', `Se registró el pago de ${datos.socio}.`);
    localStorage.removeItem('ccm_ultimo_pago');
  }

  // ---------- Logout ----------
  btnLogout.addEventListener('click', () => {
    sessionStorage.clear();
    window.location.href = 'loginadm.html';
  });

  // ---------- Primer render ----------
  render();
});