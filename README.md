# ProyectoFinal-NetikaStudio

# Sistema de Gestión de Cuotas - Club Ciclista Maragato

## Descripción

Proyecto educativo desarrollado por **Netika Studio** para crear un sistema digital que facilite la gestión de socios y cuotas del Club Ciclista Maragato.

El sistema busca mejorar la organización de la información y facilitar el control de socios y pagos mediante una plataforma sencilla, organizada y fácil de utilizar.

## Objetivos

- Mejorar la gestión de socios.
- Facilitar el control de cuotas y pagos.
- Organizar mejor la información.
- Crear una solución sencilla y fácil de utilizar.

## Estado del proyecto

**Proyecto finalizado.**

Se completó el desarrollo del sistema, incluyendo la implementación del frontend, el backend y la integración con la base de datos.

También se realizaron las pruebas correspondientes, las correcciones y los ajustes necesarios para completar el proyecto y dejar una versión final funcional.

## Tecnologías utilizadas

- **HTML** — Estructura de las páginas web.
- **CSS** — Diseño y estilos de la interfaz.
- **JavaScript** — Interactividad del sistema.
- **PHP** — Desarrollo del backend y procesamiento de datos.
- **MySQL** — Gestión y almacenamiento de la base de datos.
- **Docker** — Configuración y ejecución de los servicios mediante contenedores.
- **XAMPP** — Entorno de desarrollo local para ejecutar PHP y administrar MySQL.

## Ejecutar con Docker

Requisitos: Docker Desktop con Docker Compose habilitado.

Desde la carpeta raíz del proyecto, ejecutar:

```bash
docker compose up --build
```

Cuando los servicios estén listos, acceder a:

- Aplicación web: http://localhost:3000
- API: http://localhost:8080/api
- MySQL desde el equipo: puerto `3307`

Para detener los servicios, ejecutar:

```bash
docker compose down
```

Los datos de MySQL se conservan en el volumen `db_data`.

Para eliminar también esos datos y volver a inicializar la base de datos:

```bash
docker compose down -v
```

El esquema y los datos iniciales se cargan automáticamente solo al crear el volumen por primera vez.

## Resultado final

El proyecto permitió desarrollar una solución informática orientada a las necesidades del Club Ciclista Maragato, aplicando conocimientos de programación, bases de datos, desarrollo web, infraestructura con Docker y ciberseguridad.

Se da por finalizada la etapa de desarrollo y se presenta el sistema como resultado del trabajo realizado durante el año 2026.

## Integrantes

- **Alex Britos** — Coordinador del proyecto.
- **Nahuel Alba** — Subcoordinador.
- **Jonathan López**
- **Santiago Vilar**
- **Bruno Méndez**

## Proyecto educativo

Proyecto realizado con fines educativos como trabajo final de **Netika Studio — 2026**.

**Estado: FINALIZADO.**
