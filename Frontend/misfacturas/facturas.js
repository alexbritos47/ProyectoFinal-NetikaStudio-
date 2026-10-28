// Facturas (recibos) del socio, leídas del backend.
//
// Estados que ve el socio:
//   paga      -> el recibo está pagado
//   pendiente -> sin pagar, todavía no venció
//   impaga    -> sin pagar y ya venció
// Los recibos anulados no se muestran.

const MESES = [
  "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
  "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
];

const nombreEstado = {
  pendiente: "Pendiente",
  paga: "Paga",
  impaga: "No paga"
};

const formatoMonto = (n) => "$U " + Number(n).toLocaleString("es-UY");
const formatoFecha = (f) => new Date(f + "T00:00:00").toLocaleDateString("es-UY");

const cuerpoTabla = document.getElementById("cuerpoTabla");
const mensajeVacio = document.getElementById("mensajeVacio");
const botones = document.querySelectorAll(".filtros button");

let facturas = [];
let filtroActual = "todas";

// Fecha de hoy en formato AAAA-MM-DD, con la hora local del navegador.
function hoyISO() {
  const d = new Date();
  const mes = String(d.getMonth() + 1).padStart(2, "0");
  const dia = String(d.getDate()).padStart(2, "0");
  return `${d.getFullYear()}-${mes}-${dia}`;
}

// "2026-07" -> "Cuota mensual — Julio 2026"
function conceptoDe(periodo) {
  const [anio, mes] = String(periodo).split("-");
  const nombreMes = MESES[Number(mes) - 1] || periodo;
  return `Cuota mensual — ${nombreMes} ${anio}`;
}

// Convierte un recibo del backend en una factura para mostrar.
function aFactura(recibo, hoy) {
  let estado;

  if (recibo.estado === "pagado") {
    estado = "paga";
  } else if (recibo.fecha_vencimiento < hoy) {
    estado = "impaga";
  } else {
    estado = "pendiente";
  }

  return {
    concepto: conceptoDe(recibo.periodo),
    vencimiento: recibo.fecha_vencimiento,
    monto: Number(recibo.importe),
    estado
  };
}

function actualizarResumen() {
  document.getElementById("totalPendiente").textContent =
    facturas.filter(f => f.estado === "pendiente").length;
  document.getElementById("totalPaga").textContent =
    facturas.filter(f => f.estado === "paga").length;
  document.getElementById("totalImpaga").textContent =
    facturas.filter(f => f.estado === "impaga").length;
}

function renderizar(filtro) {
  filtroActual = filtro;

  const datos = filtro === "todas" ? facturas : facturas.filter(f => f.estado === filtro);
  cuerpoTabla.innerHTML = "";

  mensajeVacio.textContent = "No hay facturas para mostrar en este filtro.";
  mensajeVacio.style.display = datos.length === 0 ? "block" : "none";

  datos.forEach(f => {
    const fila = document.createElement("tr");
    fila.innerHTML = `
      <td data-label="Concepto">${escaparHtml(f.concepto)}</td>
      <td data-label="Vencimiento">${formatoFecha(f.vencimiento)}</td>
      <td data-label="Monto" class="monto">${formatoMonto(f.monto)}</td>
      <td data-label="Estado"><span class="estado ${f.estado}">${nombreEstado[f.estado]}</span></td>
    `;
    cuerpoTabla.appendChild(fila);
  });
}

botones.forEach(boton => {
  boton.addEventListener("click", () => {
    botones.forEach(b => b.classList.remove("activo"));
    boton.classList.add("activo");
    renderizar(boton.dataset.filtro);
  });
});

async function cargarFacturas() {
  const sesion = await exigirSesionSocio();
  if (!sesion) return;

  try {
    // El servidor devuelve solo los recibos de este socio.
    const recibos = await apiPortal("/recibos?id_socio=" + sesion.id_socio);
    const hoy = hoyISO();

    facturas = recibos
      .filter(r => r.estado !== "anulado")
      .map(r => aFactura(r, hoy));

    actualizarResumen();
    renderizar(filtroActual);

  } catch (e) {
    console.error(e);
    cuerpoTabla.innerHTML = "";
    mensajeVacio.textContent = "No se pudieron cargar tus facturas. Probá de nuevo en un momento.";
    mensajeVacio.style.display = "block";
  }
}

cargarFacturas();