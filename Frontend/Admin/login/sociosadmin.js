document.addEventListener('DOMContentLoaded', () => {

  // ---------- Protección de sesión ----------
  // Si no hay sesión de admin activa, vuelve al login.
  if (sessionStorage.getItem('sesionActiva') !== 'true' || sessionStorage.getItem('rol') !== 'admin') {
    window.location.href = 'loginadm.html';
    return;
  }

  // ---------- API ----------
  // Con Docker (http://localhost:3000) la API está en el mismo origen.
  // Si abrís el HTML de otra forma (Live Server, etc.), llama directo al puerto 8080.
  const API_BASE = location.port === '3000' ? '' : 'http://localhost:8080';

  // Traduce las respuestas de error del backend a un mensaje legible.
  function mensajeDeError(data, status) {
    if (data && data.errores) return Object.values(data.errores).join(' ');
    const detalle = (data && data.detalle) || '';
    if (detalle.includes('Duplicate entry')) {
      return 'Ya existe un socio con ese número de documento o número de socio.';
    }
    if (detalle.includes('foreign key constraint')) {
      return 'No se puede eliminar: el socio tiene recibos, pagos o cobranzas registrados.';
    }
    if (data && data.mensaje) return data.mensaje;
    return 'Error inesperado del servidor (' + status + ').';
  }

  async function api(ruta, opciones = {}) {
    const res = await fetch(API_BASE + '/api' + ruta, {
      headers: { 'Content-Type': 'application/json' },
      ...opciones
    });
    let data = null;
    try { data = await res.json(); } catch (e) { /* respuesta sin JSON */ }
    if (!res.ok) throw new Error(mensajeDeError(data, res.status));
    return data;
  }

  // ---------- Datos ----------
  // "Al día" / "Pendiente" se calcula con los recibos reales del socio:
  // un socio está Pendiente si tiene algún recibo en estado "pendiente" cuya fecha de vencimiento ya pasó.
  let socios = [];
  let categorias = [];

  function hoyISO() {
    const d = new Date();
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
  }

  function normalizarSocio(s, recibos) {
    const hoy = hoyISO();
    const vencidos = recibos.filter(r =>
      r.id_socio === s.id_socio && r.estado === 'pendiente' && r.fecha_vencimiento < hoy
    );
    return {
      id: s.id_socio,
      nombre: ((s.nombre || '') + ' ' + (s.apellido || '')).trim(),
      email: s.email || '',
      estado: s.estado,
      alDia: vencidos.length === 0,
      vencidos: vencidos.map(r => ({ id: r.id_recibo, importe: Number(r.importe) }))
    };
  }

  // Id del admin logueado (para registrar quién cobró). Si no hay sesión en el servidor, queda null.
  async function idUsuarioActual() {
    try {
      const u = await api('/auth/me');
      return u && u.id_usuario ? Number(u.id_usuario) : null;
    } catch (e) {
      return null;
    }
  }

  async function cargarSocios() {
    try {
      const [listaSocios, recibos] = await Promise.all([api('/socios'), api('/recibos')]);
      socios = listaSocios.map(s => normalizarSocio(s, recibos));
    } catch (err) {
      socios = [];
      mostrarToast('No se pudieron cargar los socios', err.message);
    }
    render(buscarSocio.value);
  }

  async function cargarCategorias() {
    try {
      categorias = await api('/categorias');
    } catch (err) {
      categorias = [];
    }
    categoriaSocioInput.innerHTML = '';
    if (!categorias.length) {
      categoriaSocioInput.innerHTML = '<option value="">No hay categorías cargadas</option>';
      return;
    }
    categorias.forEach(c => {
      const op = document.createElement('option');
      op.value = c.id_categoria;
      op.textContent = c.nombre + ' ($' + c.monto_cuota + ')';
      categoriaSocioInput.appendChild(op);
    });
  }

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
  const apellidoSocioInput = document.getElementById('apellidoSocio');
  const tipoDocSocioInput = document.getElementById('tipoDocSocio');
  const nroDocSocioInput = document.getElementById('nroDocSocio');
  const emailSocioInput = document.getElementById('emailSocio');
  const categoriaSocioInput = document.getElementById('categoriaSocio');
  const crearAccesoInput = document.getElementById('crearAcceso');
  const bloqueAcceso = document.getElementById('bloqueAcceso');
  const usuarioSocioInput = document.getElementById('usuarioSocio');
  const contrasenaSocioInput = document.getElementById('contrasenaSocio');

  const btnLogout = document.getElementById('btnLogout');

  // ---------- Utilidades ----------
  function iniciales(nombre) {
    return nombre.split(' ').filter(Boolean).slice(0, 2).map(p => p[0].toUpperCase()).join('');
  }

  // Los datos ahora vienen de la base: se escapan antes de insertarlos en el HTML.
  function esc(texto) {
    return String(texto)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
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
            <span class="socio-avatar">${esc(iniciales(socio.nombre))}</span>
            ${esc(socio.nombre)}
          </div>
        </td>
        <td data-label="Email">${esc(socio.email)}</td>
        <td data-label="Estado">
          <span class="estado-admin ${socio.alDia ? 'al-dia' : 'pendiente'}">
            ${socio.alDia ? 'Al día' : 'Pendiente'}
          </span>
        </td>
        <td data-label="Acciones">
          <div class="acciones-fila">
            <button class="btn-fila marcar" data-id="${socio.id}" ${socio.alDia ? 'disabled' : ''}>${socio.alDia ? 'Al día' : 'Marcar pago' + (socio.vencidos.length > 1 ? ' (' + socio.vencidos.length + ')' : '')}</button>
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
  async function abrirModal() {
    modalFondo.classList.add('abierto');
    nombreSocioInput.value = '';
    apellidoSocioInput.value = '';
    tipoDocSocioInput.value = 'CI';
    nroDocSocioInput.value = '';
    emailSocioInput.value = '';
    crearAccesoInput.checked = false;
    bloqueAcceso.classList.remove('visible');
    usuarioSocioInput.value = '';
    contrasenaSocioInput.value = '';
    nombreSocioInput.focus();
    if (!categorias.length) await cargarCategorias();
  }
  function cerrarModal() { modalFondo.classList.remove('abierto'); }

  // Muestra u oculta los campos de usuario y contraseña.
  crearAccesoInput.addEventListener('change', () => {
    bloqueAcceso.classList.toggle('visible', crearAccesoInput.checked);
    if (crearAccesoInput.checked) usuarioSocioInput.focus();
  });

  btnAbrirModal.addEventListener('click', abrirModal);
  btnCancelarModal.addEventListener('click', cerrarModal);
  modalFondo.addEventListener('click', (e) => { if (e.target === modalFondo) cerrarModal(); });

  btnConfirmarModal.addEventListener('click', async () => {
    const nombre = nombreSocioInput.value.trim();
    const apellido = apellidoSocioInput.value.trim();
    const numeroDocumento = nroDocSocioInput.value.trim();
    const email = emailSocioInput.value.trim();
    const idCategoria = categoriaSocioInput.value;

    if (!nombre || !apellido || !numeroDocumento) {
      mostrarToast('Faltan datos', 'Completá nombre, apellido y documento.');
      return;
    }
    if (!idCategoria) {
      mostrarToast('Falta la categoría', 'Primero tiene que haber al menos una categoría de socio cargada.');
      return;
    }

    // Acceso al portal (opcional): si se tilda, hacen falta usuario y contraseña.
    const crearAcceso = crearAccesoInput.checked;
    const usuario = usuarioSocioInput.value.trim();
    const contrasena = contrasenaSocioInput.value;
    if (crearAcceso) {
      if (!usuario) {
        mostrarToast('Falta el usuario', 'Escribí el usuario con el que el socio va a entrar.');
        return;
      }
      if (contrasena.length < 6) {
        mostrarToast('Contraseña muy corta', 'La contraseña debe tener al menos 6 caracteres.');
        return;
      }
    }

    const datos = {
      nombre,
      apellido,
      tipo_documento: tipoDocSocioInput.value,
      numero_documento: numeroDocumento,
      id_categoria: Number(idCategoria)
      // estado: no se envía; el backend lo crea como "activo" y así recibe recibos al generarlos.
    };
    if (email) datos.email = email;
    if (crearAcceso) {
      datos.nombre_usuario = usuario;
      datos.contrasena = contrasena;
    }

    btnConfirmarModal.disabled = true;
    try {
      const respuesta = await api('/socios', { method: 'POST', body: JSON.stringify(datos) });
      cerrarModal();
      await cargarSocios();
      const conAcceso = crearAcceso && respuesta && respuesta.id_usuario
        ? ` Entra al portal con el usuario "${usuario}".`
        : '';
      mostrarToast('Socio agregado', `${nombre} ${apellido} se sumó al club.${conAcceso}`);
    } catch (err) {
      mostrarToast('No se pudo agregar', err.message);
    } finally {
      btnConfirmarModal.disabled = false;
    }
  });

  // ---------- Acciones de fila: eliminar / marcar pago ----------
  tablaSocios.addEventListener('click', async (e) => {
    const boton = e.target.closest('button');
    if (!boton) return;
    const id = Number(boton.dataset.id);
    const socio = socios.find(s => s.id === id);
    if (!socio) return;

    if (boton.classList.contains('eliminar')) {
      const confirmar = confirm(`¿Quitar a ${socio.nombre} del club?`);
      if (!confirmar) return;
      try {
        await api('/socios/' + id, { method: 'DELETE' });
        socios = socios.filter(s => s.id !== id);
        render(buscarSocio.value);
        mostrarToast('Socio eliminado', `${socio.nombre} fue quitado del club.`);
      } catch (err) {
        mostrarToast('No se pudo eliminar', err.message);
      }
    }

    if (boton.classList.contains('marcar')) {
      if (!socio.vencidos.length) return;
      const total = socio.vencidos.reduce((suma, r) => suma + r.importe, 0);
      const cuotas = socio.vencidos.length;
      const confirmar = confirm(
        `¿Registrar el pago de ${cuotas} ${cuotas === 1 ? 'cuota vencida' : 'cuotas vencidas'} de ${socio.nombre} ` +
        `por $${total.toLocaleString('es-UY')}?`
      );
      if (!confirmar) return;

      boton.disabled = true;
      try {
        await api('/pagos', {
          method: 'POST',
          body: JSON.stringify({
            id_socio: socio.id,
            recibos: socio.vencidos.map(r => r.id),
            metodo_pago: 'efectivo',
            id_usuario: await idUsuarioActual()
          })
        });
        await cargarSocios();
        mostrarToast('Pago registrado', `Se registró el pago de ${socio.nombre}.`);
      } catch (err) {
        boton.disabled = false;
        mostrarToast('No se pudo registrar el pago', err.message);
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
    try {
      const datos = JSON.parse(avisoPendiente);
      mostrarToast('Pago registrado', `Se registró el pago de ${datos.socio}.`);
    } catch (e) { /* aviso dañado: se ignora */ }
    localStorage.removeItem('ccm_ultimo_pago');
  }

  // ---------- Logout ----------
  btnLogout.addEventListener('click', () => {
    sessionStorage.clear();
    window.location.href = 'loginadm.html';
  });

  // ---------- Primer render ----------
  cargarCategorias();
  cargarSocios();
});