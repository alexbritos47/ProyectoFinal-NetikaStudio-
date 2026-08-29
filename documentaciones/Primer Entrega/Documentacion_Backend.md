# Estructura del Backend

## Estructura de carpetas

```text
backend/
│
├── config/
│   ├── database.php
│   ├── config.php
│   └── cors.php
│
├── controllers/
│   ├── AuthController.php
│   ├── SocioController.php
│   ├── PagoController.php
│   ├── ReciboController.php
│   ├── NotificacionController.php
│   ├── CobradorController.php
│   └── AdminController.php
│
├── models/
│   ├── Usuario.php
│   ├── Socio.php
│   ├── Pago.php
│   ├── Recibo.php
│   ├── Notificacion.php
│   └── Rol.php
│
├── services/
│   ├── AuthService.php
│   ├── SocioService.php
│   ├── PagoService.php
│   ├── ReciboService.php
│   └── NotificacionService.php
│
├── middleware/
│   ├── AuthMiddleware.php
│   ├── AdminMiddleware.php
│   └── CobradorMiddleware.php
│
├── routes/
│   ├── auth.php
│   ├── socios.php
│   ├── pagos.php
│   ├── recibos.php
│   ├── notificaciones.php
│   └── index.php
│
├── uploads/
│   └── comprobantes/
│
├── utils/
│   ├── Response.php
│   ├── Validator.php
│   └── Helpers.php
│
├── .htaccess
└── index.php
```

---

# Explicación de cada carpeta

## config/

Contiene la configuración del sistema.

**Archivos principales:**

* `database.php`: realiza la conexión con la base de datos MySQL.
* `config.php`: guarda configuraciones generales del proyecto.
* `cors.php`: configura los permisos para que el frontend pueda acceder al backend desde otro origen.

---

## controllers/

Reciben las solicitudes enviadas por el frontend.

Los controladores procesan la petición, llaman a los servicios necesarios y devuelven una respuesta en formato JSON.

Ejemplos:

* AuthController
* SocioController
* PagoController
* ReciboController

---

## models/

Representan las tablas de la base de datos.

Su función es realizar operaciones como:

* Consultar registros.
* Insertar información.
* Modificar datos.
* Eliminar registros.

Cada modelo suele corresponder a una tabla de la base de datos.

---

## services/

Contienen la lógica de negocio.

Aquí se implementan las reglas del sistema, por ejemplo:

* Validar un inicio de sesión.
* Registrar un pago.
* Generar un recibo.
* Enviar notificaciones.
* Calcular cuotas pendientes.

Separar esta lógica facilita el mantenimiento del proyecto.

---

## middleware/

Funcionan como filtros antes de acceder a los controladores.

Se utilizan para verificar:

* Si el usuario inició sesión.
* Si tiene permisos de administrador.
* Si pertenece al rol de cobrador.

Si no cumple los requisitos, la solicitud es rechazada.

---

## routes/

Define las rutas (endpoints) de la API REST.

Ejemplos:

* `/login`
* `/socios`
* `/pagos`
* `/recibos`

Cada ruta indica qué controlador debe ejecutarse.

---

## uploads/

Almacena archivos enviados por los usuarios.

En este proyecto puede utilizarse para guardar:

* Comprobantes de pago.
* Imágenes de perfil.
* Otros documentos relacionados con los socios.

---

## utils/

Contiene funciones auxiliares reutilizables.

Por ejemplo:

* Generar respuestas JSON.
* Validar datos.
* Formatear fechas.
* Funciones de ayuda para todo el proyecto.

---

## index.php

Es el punto de entrada del backend.

Todas las solicitudes llegan primero a este archivo, que carga las rutas correspondientes y dirige la petición al controlador adecuado.

---

## .htaccess

Es un archivo de configuración del servidor Apache.

Permite:

* Redirigir todas las solicitudes hacia `index.php`.
* Crear URLs más limpias.
* Configurar reglas de seguridad.
* Controlar el acceso a carpetas y archivos.

---

# Flujo de una solicitud

```text
Frontend
    │
    ▼
Routes
    │
    ▼
Controllers
    │
    ▼
Services
    │
    ▼
Models
    │
    ▼
Base de datos (MySQL)
```

Este flujo representa la arquitectura del backend del sistema de **Gestión de Pago de Socios del Club Ciclista Maragato**, donde cada capa tiene una responsabilidad específica para mantener el código organizado, reutilizable y fácil de mantener.

Si querés, también puedo Prepara un README.md completo con la descripción del proyecto, tecnologías utilizadas, estructura de carpetas, instalación y documentación de la API.
