document.addEventListener('DOMContentLoaded', () => {

  // ---------- Protección de sesión ----------
  if (sessionStorage.getItem('sesionActiva') !== 'true' || sessionStorage.getItem('rol') !== 'admin') {
    window.location.href = 'loginadm.html';
    return;
  }

  // ---------- API ----------
  // Con Docker (http://localhost:3000) la API está en el mismo origen.
  // Si abrís el HTML de otra forma (Live Server, etc.), llama directo al puerto 8080.
  const API_BASE = location.port === '3000' ? '' : 'http://localhost:8080';

  function mensajeDeError(data, status) {
    if (data && data.errores) return Object.values(data.errores).join(' ');
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

  // Id del admin logueado (para registrar quién cobró). Si no hay sesión en el servidor, queda null.
  async function idUsuarioActual() {
    try {
      const u = await api('/auth/me');
      return u && u.id_usuario ? Number(u.id_usuario) : null;
    } catch (e) {
      return null;
    }
  }

  // ---------- Datos ----------
  // Estados del backend (recibo): pendiente / pagado / anulado.
  // Estados del panel: pendiente (todavía no vence), impaga (pendiente y vencida), paga.
  // Los recibos anulados no se muestran.
  let facturas = [];
  let filtroActual = 'todas';

  function hoyISO() {
    const d = new Date();
    return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
  }

  function normalizar(r, nombres) {
    const vencida = r.estado === 'pendiente' && r.fecha_vencimiento < hoyISO();
    return {
      id: r.id_recibo,
      idSocio: r.id_socio,
      socio: nombres[r.id_socio] || ('Socio #' + r.id_socio),
      numero: r.numero_recibo,
      monto: Number(r.importe),
      fecha: r.fecha_vencimiento,
      estado: r.estado === 'pagado' ? 'paga' : (vencida ? 'impaga' : 'pendiente')
    };
  }

  async function cargarFacturas() {
    try {
      const [recibos, socios] = await Promise.all([api('/recibos'), api('/socios')]);
      const nombres = {};
      socios.forEach(s => { nombres[s.id_socio] = ((s.nombre || '') + ' ' + (s.apellido || '')).trim(); });
      facturas = recibos.filter(r => r.estado !== 'anulado').map(r => normalizar(r, nombres));
    } catch (err) {
      facturas = [];
      mostrarToast('No se pudieron cargar las facturas', err.message);
    }
    render();
  }

  // ---------- Referencias ----------
  const tabla = document.getElementById('tablaFacturas');
  const mensajeVacio = document.getElementById('mensajeVacioFacturas');
  const cantPendientes = document.getElementById('cantPendientes');
  const cantPagas = document.getElementById('cantPagas');
  const cantImpagas = document.getElementById('cantImpagas');
  const buscarFactura = document.getElementById('buscarFactura');
  const botonesFiltro = document.querySelectorAll('.filtros button[data-filtro]');
  const btnLogout = document.getElementById('btnLogout');
  const anioRecibos = document.getElementById('anioRecibos');
  const btnGenerarRecibos = document.getElementById('btnGenerarRecibos');

  anioRecibos.value = new Date().getFullYear();

  function formatoMonto(n) {
    return '$' + n.toLocaleString('es-UY');
  }

  function etiquetaEstado(estado) {
    return { pendiente: 'Pendiente', paga: 'Paga', impaga: 'Impaga' }[estado] || estado;
  }

  // Los datos vienen de la base: se escapan antes de insertarlos en el HTML.
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
    cantPendientes.textContent = facturas.filter(f => f.estado === 'pendiente').length;
    cantPagas.textContent = facturas.filter(f => f.estado === 'paga').length;
    cantImpagas.textContent = facturas.filter(f => f.estado === 'impaga').length;
  }

  function renderTabla() {
    const texto = buscarFactura.value.trim().toLowerCase();
    const filtradas = facturas.filter(f => {
      const pasaFiltro = filtroActual === 'todas' || f.estado === filtroActual;
      const pasaBusqueda = f.socio.toLowerCase().includes(texto);
      return pasaFiltro && pasaBusqueda;
    });

    tabla.innerHTML = '';
    mensajeVacio.style.display = filtradas.length ? 'none' : 'block';

    filtradas.forEach(f => {
      const fila = document.createElement('tr');
      fila.innerHTML = `
        <td data-label="Socio">${esc(f.socio)}</td>
        <td data-label="N° factura">${esc(f.numero)}</td>
        <td data-label="Monto" class="monto">${formatoMonto(f.monto)}</td>
        <td data-label="Vence">${esc(f.fecha)}</td>
        <td data-label="Estado"><span class="estado ${f.estado}">${etiquetaEstado(f.estado)}</span></td>
        <td data-label="Acción">
          <button class="btn-marcar-pagada" data-id="${f.id}" ${f.estado === 'paga' ? 'disabled' : ''}>
            ${f.estado === 'paga' ? 'Pagada' : 'Marcar pagada'}
          </button>
        </td>
      `;
      tabla.appendChild(fila);
    });
  }

  function render() {
    renderResumen();
    renderTabla();
  }

  // ---------- Filtros ----------
  botonesFiltro.forEach(boton => {
    boton.addEventListener('click', () => {
      botonesFiltro.forEach(b => b.classList.remove('activo'));
      boton.classList.add('activo');
      filtroActual = boton.dataset.filtro;
      render();
    });
  });

  buscarFactura.addEventListener('input', render);

  // ---------- Toast ----------
  const toastAdmin = document.getElementById('toastAdmin');
  const toastTitulo = document.getElementById('toastTitulo');
  const toastDetalle = document.getElementById('toastDetalle');
  let toastTimeout;

  function mostrarToast(titulo, detalle) {
    return window.mostrarToast(titulo, detalle);
  }

  // ---------- Generar recibos del año ----------
  // Crea los 12 recibos mensuales de cada socio ACTIVO. Los que ya existen no se duplican.
  btnGenerarRecibos.addEventListener('click', async () => {
    const anio = Number(anioRecibos.value);
    if (!Number.isInteger(anio) || anio < 2000 || anio > 2100) {
      mostrarToast('Año no válido', 'Escribí un año entre 2000 y 2100.');
      return;
    }
    const confirmacion = await confirmarAccion({
      title: '¿Generar recibos?',
      text: `Se van a generar los recibos de ${anio} para todos los socios activos. Los que ya existen no se duplican. ¿Continuar?`,
      confirmButtonText: 'Sí, generar'
    });
    if (!confirmacion.isConfirmed) return;

    btnGenerarRecibos.disabled = true;
    try {
      const r = await api('/recibos/generar-anuales', {
        method: 'POST',
        body: JSON.stringify({ anio })
      });
      await cargarFacturas();
      mostrarToast('Recibos generados', `Nuevos: ${r.generados} · Ya existían: ${r.omitidos}`);
    } catch (err) {
      mostrarToast('No se pudieron generar', err.message);
    } finally {
      btnGenerarRecibos.disabled = false;
    }
  });

  // ---------- Marcar factura como pagada ----------
  // Registra un pago real (tabla PAGO) que cancela ese recibo.
  tabla.addEventListener('click', async (e) => {
    const boton = e.target.closest('.btn-marcar-pagada');
    if (!boton || boton.disabled) return;
    const id = Number(boton.dataset.id);
    const factura = facturas.find(f => f.id === id);
    if (!factura) return;

    boton.disabled = true;
    try {
      await api('/pagos', {
        method: 'POST',
        body: JSON.stringify({
          id_socio: factura.idSocio,
          recibos: [factura.id],
          metodo_pago: 'efectivo',
          id_usuario: await idUsuarioActual()
        })
      });
      factura.estado = 'paga';
      render();
      mostrarToast('Pago registrado', `Se registró el pago de ${factura.socio} (${formatoMonto(factura.monto)}).`);

      // Deja un aviso guardado para que el panel de socios también lo muestre al volver.
      localStorage.setItem('ccm_ultimo_pago', JSON.stringify({ socio: factura.socio, monto: factura.monto }));
    } catch (err) {
      boton.disabled = false;
      mostrarToast('No se pudo registrar el pago', err.message);
    }
  });

  // ---------- Logout ----------
  btnLogout.addEventListener('click', () => {
    sessionStorage.clear();
    window.location.href = 'loginadm.html';
  });

  // ---------- Primer render ----------
  cargarFacturas();
});