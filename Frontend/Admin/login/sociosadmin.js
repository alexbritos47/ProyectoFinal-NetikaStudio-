document.addEventListener('DOMContentLoaded', () => {

  // ---------- Protección de sesión ----------
  if (sessionStorage.getItem('sesionActiva') !== 'true' || sessionStorage.getItem('rol') !== 'admin') {
    window.location.href = 'loginadm.html';
    return;
  }

  // ---------- API ----------
  const API_BASE = location.port === '3000' ? '' : 'http://localhost:8080';

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
    let res;
    try {
      res = await fetch(API_BASE + '/api' + ruta, {
        headers: { 'Content-Type': 'application/json' },
        ...opciones
      });
    } catch (e) {
      // El navegador no llegó al servidor (apagado, bloqueado o dirección equivocada)
      throw new Error(
        'No se pudo conectar con ' + (API_BASE || location.origin) + '/api' + ruta +
        ' — estás entrando desde ' + location.origin +
        '. Tiene que ser http://localhost:3000 y Docker tiene que estar levantado.'
      );
    }
    let data = null;
    try { data = await res.json(); } catch (e) { /* respuesta sin JSON */ }
    if (!res.ok) throw new Error(mensajeDeError(data, res.status));
    return data;
  }

  // ---------- Datos ----------
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
      vencidos: vencidos.map(r => ({ id: r.id_recibo, importe: Number(r.importe) })),
      raw: s
    };
  }

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
  const telefonoSocioInput = document.getElementById('telefonoSocio');
  const categoriaSocioInput = document.getElementById('categoriaSocio');
  const crearAccesoInput = document.getElementById('crearAcceso');
  const bloqueAcceso = document.getElementById('bloqueAcceso');
  const usuarioSocioInput = document.getElementById('usuarioSocio');
  const contrasenaSocioInput = document.getElementById('contrasenaSocio');
  const btnOjoNueva = document.getElementById('btnOjoNueva');

  // Ficha del socio
  const modalPerfilFondo = document.getElementById('modalPerfilFondo');
  const perfilAvatar = document.getElementById('perfilAvatar');
  const perfilNombre = document.getElementById('perfilNombre');
  const perfilEstadoCuota = document.getElementById('perfilEstadoCuota');
  const perfilDatos = document.getElementById('perfilDatos');
  const btnCerrarPerfil = document.getElementById('btnCerrarPerfil');

  const btnLogout = document.getElementById('btnLogout');

  // ---------- Utilidades ----------
  function iniciales(nombre) {
    return nombre.split(' ').filter(Boolean).slice(0, 2).map(p => p[0].toUpperCase()).join('');
  }

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
  // Convierte lo escrito en minúsculas para facilitar la búsqueda.
  const texto = filtro.trim().toLowerCase();

  // Busca por nombre, apellido, documento o número de socio.
  const filtrados = socios.filter(s => {
    // Obtiene los datos originales que vienen de la API.
    const datos = s.raw || {};

    // Obtiene el documento del socio.
    const documento = String(datos.numero_documento || '').toLowerCase();

    // Obtiene el tipo de documento, por ejemplo CI o Pasaporte.
    const tipoDocumento = String(datos.tipo_documento || '').toLowerCase();

    // Obtiene el número de socio.
    const numeroSocio = String(datos.numero_socio || '').toLowerCase();

    // Comprueba si coincide cualquiera de los campos.
    return (
      s.nombre.toLowerCase().includes(texto) ||
      documento.includes(texto) ||
      tipoDocumento.includes(texto) ||
      numeroSocio.includes(texto)
    );
  });

    tablaSocios.innerHTML = '';
    mensajeVacio.style.display = filtrados.length ? 'none' : 'block';

    filtrados.forEach(socio => {
      const fila = document.createElement('tr');
      fila.innerHTML = `
        <td data-label="Socio">
          <div class="socio-nombre">
            <span class="socio-avatar" data-id="${socio.id}" role="button" tabindex="0" title="Ver datos del socio">${esc(iniciales(socio.nombre))}</span>
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

  // ---------- Ficha del socio ----------
  function valor(v) {
    return (v === undefined || v === null || v === '') ? '—' : v;
  }

  function fechaLinda(f) {
    if (!f) return '—';
    const d = new Date(f);
    return isNaN(d) ? String(f) : d.toLocaleDateString('es-UY');
  }

  function campoPerfil(etiqueta, texto, ancho) {
    return `<div class="perfil-dato${ancho ? ' ancho' : ''}"><span>${esc(etiqueta)}</span><strong>${esc(texto)}</strong></div>`;
  }

  function campoReset(id) {
    return `<div class="perfil-dato ancho">
      <span>Contraseña</span>
      <div class="perfil-clave">
        <strong>Encriptada (no se puede ver)</strong>
        <button type="button" class="btn-fila" id="btnResetClave" data-id="${id}">Restablecer</button>
      </div>
    </div>`;
  }

  function generarClave() {
    const car = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
    return Array.from({ length: 8 }, () => car[Math.floor(Math.random() * car.length)]).join('');
  }

  // Restablecer contraseña (genera una nueva y se la muestra al admin)
  perfilDatos.addEventListener('click', async (e) => {
    const btn = e.target.closest('#btnResetClave');
    if (!btn) return;
    const nueva = generarClave();
    const ok = await confirmarAccion({
      title: '¿Restablecer contraseña?',
      text: 'Se va a generar una contraseña nueva y la anterior deja de funcionar.',
      confirmButtonText: 'Sí, restablecer'
    });
    if (!ok.isConfirmed) return;
    try {
      await api('/socios/' + btn.dataset.id + '/contrasena', {
        method: 'PUT',
        body: JSON.stringify({ contrasena: nueva })
      });
      Swal.fire({
        title: 'Contraseña nueva',
        html: 'Pasásela al socio:<br><b style="font-size:22px">' + nueva + '</b>',
        icon: 'success'
      });
    } catch (err) {
      mostrarToast('No se pudo restablecer', err.message);
    }
  });

  async function abrirPerfil(id) {
    const socio = socios.find(s => s.id === id);
    if (!socio) return;

    let d = socio.raw || {};
    try {
      const detalle = await api('/socios/' + id);
      if (detalle && typeof detalle === 'object') d = { ...d, ...detalle };
    } catch (e) { /* se usa lo que ya tenemos */ }

    if (!categorias.length) await cargarCategorias();
    const cat = categorias.find(c => String(c.id_categoria) === String(d.id_categoria));
    const categoriaTxt = cat
      ? cat.nombre + ' ($' + cat.monto_cuota + ')'
      : valor(d.categoria || d.nombre_categoria);

    perfilAvatar.textContent = iniciales(socio.nombre);
    perfilNombre.textContent = socio.nombre;
    perfilEstadoCuota.className = 'estado-admin ' + (socio.alDia ? 'al-dia' : 'pendiente');
    perfilEstadoCuota.textContent = socio.alDia ? 'Cuota al día' : 'Cuota pendiente';

    perfilDatos.innerHTML =
      campoPerfil('Nombre', valor(d.nombre)) +
      campoPerfil('Apellido', valor(d.apellido)) +
      campoPerfil('Tipo de documento', valor(d.tipo_documento)) +
      campoPerfil('Número de documento', valor(d.numero_documento)) +
      campoPerfil('Email', valor(d.email), true) +
      campoPerfil('Teléfono / celular', valor(d.telefono), true) +
      campoPerfil('Categoría', categoriaTxt, true) +
      campoPerfil('N° de socio', valor(d.numero_socio || d.id_socio)) +
      campoPerfil('Estado', valor(d.estado)) +
      campoPerfil('Usuario', valor(d.nombre_usuario || d.usuario)) +
      campoReset(socio.id) +
      campoPerfil('Fecha de alta', fechaLinda(d.fecha_alta || d.fecha_ingreso || d.created_at)) +
      campoPerfil('Cuotas vencidas', String(socio.vencidos.length));

    modalPerfilFondo.classList.add('abierto');
  }

  function cerrarPerfil() { modalPerfilFondo.classList.remove('abierto'); }

  btnCerrarPerfil.addEventListener('click', cerrarPerfil);
  modalPerfilFondo.addEventListener('click', (e) => { if (e.target === modalPerfilFondo) cerrarPerfil(); });
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') cerrarPerfil(); });

  // ---------- Modal: agregar socio ----------
  async function abrirModal() {
    modalFondo.classList.add('abierto');
    nombreSocioInput.value = '';
    apellidoSocioInput.value = '';
    tipoDocSocioInput.value = 'CI';
    nroDocSocioInput.value = '';
    emailSocioInput.value = '';
    telefonoSocioInput.value = '';
    crearAccesoInput.checked = false;
    bloqueAcceso.classList.remove('visible');
    usuarioSocioInput.value = '';
    contrasenaSocioInput.value = '';
    contrasenaSocioInput.type = 'password';
    btnOjoNueva.textContent = '👁';
    nombreSocioInput.focus();
    if (!categorias.length) await cargarCategorias();
  }
  function cerrarModal() { modalFondo.classList.remove('abierto'); }

  crearAccesoInput.addEventListener('change', () => {
    bloqueAcceso.classList.toggle('visible', crearAccesoInput.checked);
    if (crearAccesoInput.checked) usuarioSocioInput.focus();
  });

  // Ojito del modal: mostrar / ocultar la contraseña que se está escribiendo
  btnOjoNueva.addEventListener('click', () => {
    const mostrar = contrasenaSocioInput.type === 'password';
    contrasenaSocioInput.type = mostrar ? 'text' : 'password';
    btnOjoNueva.textContent = mostrar ? '🙈' : '👁';
  });

  btnAbrirModal.addEventListener('click', abrirModal);
  btnCancelarModal.addEventListener('click', cerrarModal);
  modalFondo.addEventListener('click', (e) => { if (e.target === modalFondo) cerrarModal(); });

  btnConfirmarModal.addEventListener('click', async () => {
    const nombre = nombreSocioInput.value.trim();
    const apellido = apellidoSocioInput.value.trim();
    const numeroDocumento = nroDocSocioInput.value.trim();
    const email = emailSocioInput.value.trim();
    const telefono = telefonoSocioInput.value.trim();
    const idCategoria = categoriaSocioInput.value;

    if (!nombre || !apellido || !numeroDocumento) {
      mostrarToast('Faltan datos', 'Completá nombre, apellido y documento.');
      return;
    }
    if (!idCategoria) {
      mostrarToast('Falta la categoría', 'Primero tiene que haber al menos una categoría de socio cargada.');
      return;
    }

    // Teléfono (opcional): solo números, espacios, +, - y paréntesis; entre 7 y 15 dígitos
    if (telefono) {
      const soloCaracteresValidos = /^[\d\s+\-()]+$/.test(telefono);
      const cantidadDigitos = telefono.replace(/\D/g, '').length;
      if (!soloCaracteresValidos || cantidadDigitos < 7 || cantidadDigitos > 15) {
        mostrarToast('Teléfono inválido', 'Ingresá un teléfono o celular válido. Ej: 099 123 456.');
        return;
      }
    }

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
    };
    if (email) datos.email = email;
    if (telefono) datos.telefono = telefono;
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
        ? ` Usuario: "${usuario}" · Contraseña: "${contrasena}". Anotala, no se vuelve a mostrar.`
        : '';
      mostrarToast('Socio agregado', `${nombre} ${apellido} se sumó al club.${conAcceso}`);
    } catch (err) {
      mostrarToast('No se pudo agregar', err.message);
    } finally {
      btnConfirmarModal.disabled = false;
    }
  });

  // ---------- Acciones de fila: ficha / eliminar / marcar pago ----------
  tablaSocios.addEventListener('keydown', (e) => {
    if ((e.key === 'Enter' || e.key === ' ') && e.target.classList.contains('socio-avatar')) {
      e.preventDefault();
      abrirPerfil(Number(e.target.dataset.id));
    }
  });

  tablaSocios.addEventListener('click', async (e) => {
    const avatar = e.target.closest('.socio-avatar');
    if (avatar) {
      abrirPerfil(Number(avatar.dataset.id));
      return;
    }

    const boton = e.target.closest('button');
    if (!boton) return;
    const id = Number(boton.dataset.id);
    const socio = socios.find(s => s.id === id);
    if (!socio) return;

    if (boton.classList.contains('eliminar')) {
      const confirmacion = await confirmarAccion({
        title: '¿Eliminar socio?',
        text: `¿Quitar a ${socio.nombre} del club?`,
        confirmButtonText: 'Sí, eliminar'
      });
      if (!confirmacion.isConfirmed) return;
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
      const confirmacion = await confirmarAccion({
        title: '¿Registrar el pago?',
        text: `¿Registrar el pago de ${cuotas} ${cuotas === 1 ? 'cuota vencida' : 'cuotas vencidas'} de ${socio.nombre} por $${total.toLocaleString('es-UY')}?`,
        confirmButtonText: 'Sí, registrar pago'
      });
      if (!confirmacion.isConfirmed) return;

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
    return window.mostrarToast(titulo, detalle);
  }

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