document.addEventListener('DOMContentLoaded', () => {

  // ---------- Protección de sesión ----------
  if (sessionStorage.getItem('sesionActiva') !== 'true' || sessionStorage.getItem('rol') !== 'admin') {
    window.location.href = 'loginadm.html';
    return;
  }

  // ---------- Datos de facturas (localStorage mientras no hay backend) ----------
  const CLAVE_FACTURAS = 'ccm_facturas';

  function facturasInicialesDemo() {
    return [
      { id: 1, socio: 'Ana Rodríguez', numero: '0001-0004', monto: 1500, fecha: '2026-08-01', estado: 'paga' },
      { id: 2, socio: 'Bruno Méndez', numero: '0001-0005', monto: 1500, fecha: '2026-08-05', estado: 'pendiente' },
      { id: 3, socio: 'Carla Suárez', numero: '0001-0006', monto: 1500, fecha: '2026-07-20', estado: 'impaga' },
      { id: 4, socio: 'Diego Fernández', numero: '0001-0007', monto: 1500, fecha: '2026-08-10', estado: 'pendiente' },
    ];
  }

  function cargarFacturas() {
    const guardado = localStorage.getItem(CLAVE_FACTURAS);
    if (guardado) return JSON.parse(guardado);
    const iniciales = facturasInicialesDemo();
    localStorage.setItem(CLAVE_FACTURAS, JSON.stringify(iniciales));
    return iniciales;
  }

  function guardarFacturas(lista) {
    localStorage.setItem(CLAVE_FACTURAS, JSON.stringify(lista));
  }

  let facturas = cargarFacturas();
  let filtroActual = 'todas';

  // ---------- Referencias ----------
  const tabla = document.getElementById('tablaFacturas');
  const mensajeVacio = document.getElementById('mensajeVacioFacturas');
  const cantPendientes = document.getElementById('cantPendientes');
  const cantPagas = document.getElementById('cantPagas');
  const cantImpagas = document.getElementById('cantImpagas');
  const buscarFactura = document.getElementById('buscarFactura');
  const botonesFiltro = document.querySelectorAll('.filtros button[data-filtro]');
  const btnLogout = document.getElementById('btnLogout');

  function formatoMonto(n) {
    return '$' + n.toLocaleString('es-UY');
  }

  function etiquetaEstado(estado) {
    return { pendiente: 'Pendiente', paga: 'Paga', impaga: 'Impaga' }[estado] || estado;
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
        <td data-label="Socio">${f.socio}</td>
        <td data-label="N° factura">${f.numero}</td>
        <td data-label="Monto" class="monto">${formatoMonto(f.monto)}</td>
        <td data-label="Fecha">${f.fecha}</td>
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
    toastTitulo.textContent = titulo;
    toastDetalle.textContent = detalle;
    toastAdmin.classList.add('mostrar');
    clearTimeout(toastTimeout);
    toastTimeout = setTimeout(() => toastAdmin.classList.remove('mostrar'), 4000);
  }

  // ---------- Marcar factura como pagada ----------
  tabla.addEventListener('click', (e) => {
    const boton = e.target.closest('.btn-marcar-pagada');
    if (!boton || boton.disabled) return;
    const id = Number(boton.dataset.id);
    const factura = facturas.find(f => f.id === id);
    if (!factura) return;

    factura.estado = 'paga';
    guardarFacturas(facturas);
    render();
    mostrarToast('Pago registrado', `Se registró el pago de ${factura.socio} (${formatoMonto(factura.monto)}).`);

    // Deja un aviso guardado para que el panel de socios también lo muestre al volver.
    localStorage.setItem('ccm_ultimo_pago', JSON.stringify({ socio: factura.socio, monto: factura.monto }));
  });

  // ---------- Logout ----------
  btnLogout.addEventListener('click', () => {
    sessionStorage.clear();
    window.location.href = 'loginadm.html';
  });

  // ---------- Primer render ----------
  render();
});