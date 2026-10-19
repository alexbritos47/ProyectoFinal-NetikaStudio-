/* ==========================================
   PERFIL DEL SOCIO
   Los datos vienen del backend (no del navegador).
   El socio puede editar solo sus datos de contacto:
   email, teléfono y dirección. Lo demás (nombre, documento,
   categoría...) lo modifica la administración del club.
========================================== */

document.addEventListener("DOMContentLoaded", async function () {

    // Si no hay sesión de socio, vuelve al login.
    const usuario = await exigirSesionSocio();
    if (!usuario) return;

    // Escribe un dato en pantalla. Si esa casilla no existe en el HTML,
    // se ignora (así una página desactualizada no rompe todo lo demás).
    const poner = (id, valor) => {
        const elemento = document.getElementById(id);
        if (elemento) elemento.textContent = valor || "---";
    };

    // Datos que el socio puede cambiar.
    const CAMPOS_EDITABLES = [
        { id: "email",     tipo: "email", max: 100 },
        { id: "telefono",  tipo: "tel",   max: 30  },
        { id: "direccion", tipo: "text",  max: 200 }
    ];

    let socio = null;       // datos actuales del socio
    let editando = false;

    const botonEditar = document.getElementById("editarPerfil");
    const botonSalir = document.getElementById("cerrarSesion");
    let botonCancelar = null;

    // ==========================================
    // MOSTRAR LOS DATOS EN PANTALLA
    // ==========================================

    function mostrarContacto() {
        poner("email", socio.email || usuario.email);
        poner("telefono", socio.telefono);
        poner("direccion", socio.direccion);
    }

    // ==========================================
    // EDICIÓN
    // ==========================================

    function entrarEdicion() {
        editando = true;

        CAMPOS_EDITABLES.forEach(campo => {
            const lugar = document.getElementById(campo.id);
            if (!lugar) return;

            const entrada = document.createElement("input");
            entrada.type = campo.tipo;
            entrada.id = "editar_" + campo.id;
            entrada.maxLength = campo.max;
            entrada.value = socio[campo.id] || "";
            entrada.setAttribute("aria-label", campo.id);
            entrada.style.cssText =
                "width:100%;border:none;background:transparent;outline:none;" +
                "font:inherit;color:inherit;padding:12px 0;";

            lugar.textContent = "";
            lugar.style.width = "100%";
            lugar.appendChild(entrada);
        });

        botonEditar.textContent = "Guardar cambios";

        botonCancelar = document.createElement("button");
        botonCancelar.id = "cancelarEdicion";
        botonCancelar.className = "btn-principal";
        botonCancelar.textContent = "Cancelar";
        botonCancelar.style.cssText =
            "background:transparent;color:var(--c-500);" +
            "border:1px solid var(--c-300);box-shadow:none;margin-right:12px;";
        botonCancelar.addEventListener("click", salirEdicion);
        botonEditar.parentNode.insertBefore(botonCancelar, botonEditar);

        const primera = document.getElementById("editar_email");
        if (primera) primera.focus();
    }

    function salirEdicion() {
        editando = false;

        CAMPOS_EDITABLES.forEach(campo => {
            const lugar = document.getElementById(campo.id);
            if (lugar) lugar.style.width = "";
        });

        mostrarContacto();

        botonEditar.textContent = "Editar información";
        botonEditar.disabled = false;

        if (botonCancelar) {
            botonCancelar.remove();
            botonCancelar = null;
        }
    }

    // Valida lo escrito. Devuelve un texto de error, o "" si está todo bien.
    function validar(valores) {
        if (valores.email && !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(valores.email)) {
            return "El email no es válido.";
        }
        if (valores.telefono && !/^[0-9+()\s-]{6,30}$/.test(valores.telefono)) {
            return "El teléfono solo puede tener números, espacios y los signos + - ( ). Mínimo 6 caracteres.";
        }
        return "";
    }

    async function guardar() {
        // Se lee lo que escribió el socio.
        const valores = {};
        CAMPOS_EDITABLES.forEach(campo => {
            const entrada = document.getElementById("editar_" + campo.id);
            if (entrada) valores[campo.id] = entrada.value.trim();
        });

        const error = validar(valores);
        if (error) {
            alert(error);
            return;
        }

        // Solo se envía lo que cambió.
        const cambios = {};
        CAMPOS_EDITABLES.forEach(campo => {
            if (!(campo.id in valores)) return;
            if (valores[campo.id] !== (socio[campo.id] || "")) {
                cambios[campo.id] = valores[campo.id];
            }
        });

        if (Object.keys(cambios).length === 0) {
            salirEdicion();
            return;
        }

        botonEditar.disabled = true;
        botonEditar.textContent = "Guardando...";

        try {
            await apiPortal("/socios/" + usuario.id_socio, {
                method: "PUT",
                body: JSON.stringify(cambios)
            });

            Object.assign(socio, cambios);
            salirEdicion();
            alert("Tus datos fueron actualizados.");

        } catch (e) {
            console.error("Error al guardar:", e);
            botonEditar.disabled = false;
            botonEditar.textContent = "Guardar cambios";
            alert("No se pudieron guardar los cambios: " + e.message);
        }
    }

    // ==========================================
    // BOTONES (funcionan aunque falle la carga)
    // ==========================================

    if (botonSalir) botonSalir.addEventListener("click", cerrarSesionPortal);

    if (botonEditar) {
        botonEditar.addEventListener("click", function () {
            if (!socio) return;               // todavía no cargaron los datos
            if (editando) {
                guardar();
            } else {
                entrarEdicion();
            }
        });
    }

    // ==========================================
    // DATOS DEL SOCIO
    // ==========================================

    try {
        socio = await apiPortal("/socios/" + usuario.id_socio);
    } catch (error) {
        console.error("Error al leer el perfil:", error);
        alert("No se pudieron cargar tus datos: " + error.message);
        return;
    }

    // La categoría es un dato extra: si falla, el resto se muestra igual.
    let categoria = null;
    try {
        const categorias = await apiPortal("/categorias");
        categoria = categorias.find(c => Number(c.id_categoria) === Number(socio.id_categoria));
    } catch (error) {
        console.error("No se pudo leer la categoría:", error);
    }

    poner("nombre", socio.nombre);
    poner("apellido", socio.apellido);
    poner("cedula", socio.numero_documento
        ? `${socio.tipo_documento || ""} ${socio.numero_documento}`.trim()
        : "");
    mostrarContacto();
    poner("usuario", usuario.nombre_usuario);
    poner("numeroSocio", socio.numero_socio);
    poner("categoria", categoria
        ? `${categoria.nombre} — $U ${Number(categoria.monto_cuota).toLocaleString("es-UY")} por mes`
        : "");
    poner("fechaIngreso", socio.fecha_ingreso
        ? new Date(socio.fecha_ingreso + "T00:00:00").toLocaleDateString("es-UY")
        : "");

    // ==========================================
    // AVATAR (iniciales)
    // ==========================================

    const inicialNombre = socio.nombre ? socio.nombre.charAt(0).toUpperCase() : "U";
    const inicialApellido = socio.apellido ? socio.apellido.charAt(0).toUpperCase() : "S";

    poner("avatar", inicialNombre + inicialApellido);
});