// Datos de ejemplo (mock). Más adelante esto va a venir de la base de datos vía el backend.
const facturas = [
  { concepto: "Cuota mensual — Julio",        vencimiento: "2026-07-10", monto: 1200, estado: "impaga"    },
  { concepto: "Cuota mensual — Junio",        vencimiento: "2026-06-10", monto: 1200, estado: "paga"      },
  { concepto: "Cuota mensual — Agosto",       vencimiento: "2026-08-10", monto: 1200, estado: "pendiente" },
  { concepto: "Inscripción carrera regional", vencimiento: "2026-07-25", monto: 800,  estado: "pendiente" },
  { concepto: "Cuota mensual — Mayo",         vencimiento: "2026-05-10", monto: 1200, estado: "paga"      },
  { concepto: "Remera oficial del club",      vencimiento: "2026-07-05", monto: 950,  estado: "impaga"    },
];

const formatoMonto = (n) => "$U " + n.toLocaleString("es-UY");
const formatoFecha = (f) => new Date(f + "T00:00:00").toLocaleDateString("es-UY");

const nombreEstado = {
  pendiente: "Pendiente",
  paga: "Paga",
  impaga: "No paga"
};

const cuerpoTabla = document.getElementById("cuerpoTabla");
const mensajeVacio = document.getElementById("mensajeVacio");
const botones = document.querySelectorAll(".filtros button");

function actualizarResumen(){
  document.getElementById("totalPendiente").textContent =
    facturas.filter(f => f.estado === "pendiente").length;
  document.getElementById("totalPaga").textContent =
    facturas.filter(f => f.estado === "paga").length;
  document.getElementById("totalImpaga").textContent =
    facturas.filter(f => f.estado === "impaga").length;
}

function renderizar(filtro){
  const datos = filtro === "todas" ? facturas : facturas.filter(f => f.estado === filtro);
  cuerpoTabla.innerHTML = "";

  mensajeVacio.style.display = datos.length === 0 ? "block" : "none";

  datos.forEach(f => {
    const fila = document.createElement("tr");
    fila.innerHTML = `
      <td data-label="Concepto">${f.concepto}</td>
      <td data-label="Vencimiento">${formatoFecha(f.vencimiento)}</td>
      <td data-label="Monto" class="monto">${formatoMonto(f.monto)}</td>
      <td data-label="Estado"><span class="estado ${f.estado}">${nombreEstado[f.estado]}</span></td>
      <td data-label="">${f.estado !== "paga" ? '<a class="accion" href="#">Pagar</a>' : ''}</td>
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

actualizarResumen();
renderizar("todas");