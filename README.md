# ProyectoFinal-NetikaStudio

# Sistema de Gestión de Cuotas - Club Ciclista Maragato

## Descripción

Proyecto educativo desarrollado por **Netika Studio** para crear un sistema digital que facilite la gestión de socios y cuotas del Club Ciclista Maragato.

El sistema busca mejorar la organización de la información y facilitar el control de socios y pagos.

## Objetivos

- Mejorar la gestión de socios.
- Facilitar el control de cuotas y pagos.
- Organizar mejor la información.
- Crear una solución sencilla y fácil de utilizar.

## Estado del proyecto

El proyecto se encuentra actualmente en **desarrollo**.

Se realizó el relevamiento y análisis de las necesidades del club y actualmente se está trabajando en la implementación del sistema, incluyendo el desarrollo del **backend** y la integración con la base de datos.

## Tecnologías utilizadas

Por el momento, el proyecto utiliza:

- **HTML** — Estructura de las páginas web.
- **CSS** — Diseño y estilos de la interfaz.
- **JavaScript** — Interactividad del sistema.
- **PHP** — Desarrollo del backend y procesamiento de datos.
- **MySQL** — Gestión y almacenamiento de la base de datos.
- **Docker** — Configuración y ejecución de los servicios del proyecto mediante contenedores.
- **XAMPP** — Entorno de desarrollo local para ejecutar PHP y administrar MySQL.

## Ejecutar con Docker

Requisitos: Docker Desktop con Docker Compose habilitado.

Desde la carpeta raíz del proyecto, ejecuta:

```bash
docker compose up --build
```

Cuando los servicios estén listos, abre:

- Aplicación web: http://localhost:3000
- API: http://localhost:8080/api
- MySQL desde el equipo: puerto `3307`

Para detener los servicios, pulsa `Ctrl+C` y ejecuta `docker compose down`. Los datos de MySQL se conservan en el volumen `db_data`. Para eliminar también esos datos y volver a inicializar la base, ejecuta `docker compose down -v`.

El esquema y los datos iniciales se cargan automáticamente solo al crear el volumen por primera vez.

## Próximas etapas

- Continuar implementando el backend.
- Integrar y completar las funcionalidades de la base de datos.
- Continuar con el desarrollo del sistema.
- Realizar pruebas.
- Corregir errores.
- Finalizar el proyecto.
- Presentar el proyecto.

## Integrantes

- **Alex Britos** — Coordinador
- **Nahuel Alba** — Subcoordinador
- **Jonathan López**
- **Santiago Vilar**
- **Bruno Méndez**

## Proyecto educativo

Proyecto realizado con fines educativos como trabajo final de **Netika Studio — 2026**.
