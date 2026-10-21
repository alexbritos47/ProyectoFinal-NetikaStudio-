/* Alertas compartidas del portal Club Ciclista Maragato. */
(function () {
  const BASE = {
    background: "#111827",
    color: "#fff",
    confirmButtonColor: "#29aae1"
  };

  window.SWAL_BASE = BASE;

  window.alerta = function (icon, title, text) {
    if (!window.Swal) {
      console.error("SweetAlert2 no está cargado:", title, text);
      return Promise.resolve();
    }
    return window.Swal.fire({ icon, title, text, ...BASE });
  };

  window.confirmarAccion = function ({
    title = "¿Estás seguro?",
    text = "Esta acción no se puede deshacer.",
    confirmButtonText = "Sí, continuar"
  } = {}) {
    return window.Swal.fire({
      icon: "warning",
      title,
      text,
      showCancelButton: true,
      confirmButtonText,
      cancelButtonText: "Cancelar",
      cancelButtonColor: "#475569",
      allowOutsideClick: false,
      ...BASE
    });
  };

  // Mantiene el nombre usado por las pantallas de administración,
  // pero presenta los mensajes con el mismo estilo del login.
  window.mostrarToast = function (titulo, detalle = "") {
    const t = String(titulo || "").toLowerCase();
    const icon = /no se pudo|no se pudieron|error|inválid|invalido|falta/.test(t)
      ? (/falta|inválid|invalido/.test(t) ? "warning" : "error")
      : "success";
    return window.alerta(icon, titulo, detalle);
  };
})();
