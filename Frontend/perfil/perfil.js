/* ==========================================
   CARGAR PERFIL
========================================== */

document.addEventListener(
    "DOMContentLoaded",
    function () {

        // Buscar los datos guardados
        const usuarioGuardado =
            localStorage.getItem("usuario");


        // ==========================================
        // COMPROBAR SESIÓN
        // ==========================================

       

        // ==========================================
        // LEER DATOS
        // ==========================================

        let usuario;


        try {

            usuario =
                JSON.parse(usuarioGuardado);

        } catch (error) {

            console.error(
                "Error al leer los datos:",
                error
            );

            alert(
                "Los datos del usuario no son válidos."
            );

            return;
        }


        // ==========================================
        // MOSTRAR INFORMACIÓN
        // ==========================================

        document.getElementById(
            "nombre"
        ).textContent =
            usuario.nombre || "---";


        document.getElementById(
            "apellido"
        ).textContent =
            usuario.apellido || "---";


        document.getElementById(
            "cedula"
        ).textContent =
            usuario.cedula || "---";


        document.getElementById(
            "email"
        ).textContent =
            usuario.email || "---";


        document.getElementById(
            "usuario"
        ).textContent =
            usuario.usuario || "---";


        // ==========================================
        // AVATAR
        // ==========================================

        const primeraLetraNombre =
            usuario.nombre
                ? usuario.nombre
                    .charAt(0)
                    .toUpperCase()
                : "U";


        const primeraLetraApellido =
            usuario.apellido
                ? usuario.apellido
                    .charAt(0)
                    .toUpperCase()
                : "S";


        document.getElementById(
            "avatar"
        ).textContent =
            primeraLetraNombre +
            primeraLetraApellido;


        // ==========================================
        // MOSTRAR / OCULTAR CONTRASEÑA
        // ==========================================

        const passwordElemento =
            document.getElementById(
                "password"
            );


        const botonPassword =
            document.getElementById(
                "mostrarPassword"
            );


        const passwordReal =
            usuario.password || "";


        let passwordVisible = false;


        botonPassword.addEventListener(
            "click",
            function () {

                if (passwordVisible) {

                    passwordElemento.textContent =
                        "••••••••";

                    botonPassword.textContent =
                        "Mostrar";

                    passwordVisible = false;

                } else {

                    passwordElemento.textContent =
                        passwordReal ||
                        "No disponible";

                    botonPassword.textContent =
                        "Ocultar";

                    passwordVisible = true;
                }

            }
        );


        // ==========================================
        // CERRAR SESIÓN
        // ==========================================

        document
            .getElementById("cerrarSesion")
            .addEventListener(
                "click",
                function () {

                    localStorage.removeItem(
                        "usuario"
                    );

                    window.location.href =
                        "login.html";

                }
            );


        // ==========================================
        // EDITAR PERFIL
        // ==========================================

        document
            .getElementById("editarPerfil")
            .addEventListener(
                "click",
                function () {

                    alert(
                        "La edición del perfil se implementará próximamente."
                    );

                }
            );

    }
);