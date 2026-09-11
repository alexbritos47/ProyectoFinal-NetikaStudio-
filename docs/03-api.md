# 03 · API del Backend — Sistema de Cobros a Socios (Club Ciclista Maragato)

Este documento describe el estado **real y funcional** del backend luego de la
corrección de errores críticos y de completar los módulos faltantes respecto
a `sistema_cobros_socios_club_ciclista.md`.

## 1. Qué se corrigió

| Problema encontrado | Corrección aplicada |
|---|---|
| `index.php` requería `database/Database.php` (mayúscula) pero el archivo era `database.php` → error fatal en Linux | Archivo renombrado a `Database.php`, requires actualizados |
| `SocioController` llamaba a `$validator->validar()`, método inexistente | `SocioValidator` reescrito con `validarCrear()` / `validarActualizar()` de instancia |
| `AuthController.php`, `UsuarioController.php`, `AuthService.php`, `UsuarioService.php` vacíos (0 bytes) | Implementados completos: login con sesión, CRUD de usuarios |
| Modelo `Socio.php` con campos que no existían en la tabla real (`documento`, `correo`) | Modelo alineado 1:1 con las columnas de `SOCIO` |
| `routes.php` sólo enrutaba `/api/socios` | Enrutador reescrito, cubre los 9 grupos de endpoints de la especificación |
| `CategoriaRepository` / `CuotaPagoRepository` (con espacios en el nombre de archivo) sin service/controller/ruta | Renombrados, conectados con su Service y Controller correspondientes |
| Cada repositorio abría su propia conexión PDO | Una única conexión PDO se crea en `index.php` y se inyecta a todos los repositorios |
| No existían tablas para notificaciones, consultas, historial ni visitas de cobranza | `schema.sql` extendido con `NOTIFICACION`, `CONSULTA`, `HISTORIAL_ACTIVIDAD`, `COBRANZA_VISITA`, `PAGO`, `PAGO_RECIBO` |
| Un pago no podía cancelar varios recibos de forma atómica | `PagoRepository::registrarPago()` usa una transacción PDO (`beginTransaction`/`commit`/`rollBack`) |
| Errores de base de datos devolvían HTML crudo de PHP | `index.php` envuelve todo en `try/catch` y siempre responde JSON |

Se verificó con `php -l` que los ~35 archivos PHP no tienen errores de sintaxis,
y se corrió una prueba funcional de punta a punta (login, alta de socio,
generación de 12 recibos anuales, detección de morosidad, pago que cancela
varios recibos a la vez, rechazo de pagos duplicados) con resultado exitoso.

## 2. Modelo de datos implementado

```
CATEGORIA_SOCIO ──< SOCIO ──< RECIBO
                       │  \
                       │   \── < PAGO ──< PAGO_RECIBO >── RECIBO
                       │
                       ├──< NOTIFICACION
                       ├──< CONSULTA
                       └──< COBRANZA_VISITA >── USUARIO (cobrador)

USUARIO ──< HISTORIAL_ACTIVIDAD
USUARIO ──(1:1 opcional)── SOCIO   (login del portal del socio)
USUARIO ──(1:N cartera)──  SOCIO   (asignación de cobrador)
```

Puntos de diseño relevantes:

- **RECIBO** y **PAGO** están separados: el recibo es la cuota generada al
  inicio del año; el pago es el evento de cobro y puede asociarse a **uno o
  varios** recibos mediante la tabla puente `PAGO_RECIBO` (regla 3.4 de la
  especificación: "los pagos son completos, no parciales" y "pueden cancelar
  uno o varios recibos").
- **Morosidad** (regla 3.5): un socio es moroso si tiene **más de un** recibo
  en estado `pendiente`. Implementado en `ReciboRepository::obtenerMorosos()`.
- **Asignación de cartera**: `SOCIO.id_cobrador` referencia a `USUARIO` con
  `rol = 'cobrador'`.

## 3. Endpoints implementados

Todas las respuestas son JSON. Prefijo base: `/api`.

### 3.1 Autenticación
| Método | Ruta | Descripción |
|---|---|---|
| POST | `/api/auth/login` | Body: `{nombre_usuario, contrasena}`. Abre sesión PHP. |
| POST | `/api/auth/logout` | Cierra la sesión activa. |
| GET | `/api/auth/me` | Devuelve el usuario logueado (401 si no hay sesión). |

### 3.2 Usuarios
| Método | Ruta |
|---|---|
| GET | `/api/usuarios` |
| POST | `/api/usuarios` |
| GET | `/api/usuarios/{id}` |
| PUT | `/api/usuarios/{id}` |
| DELETE | `/api/usuarios/{id}` |

### 3.3 Socios
| Método | Ruta |
|---|---|
| GET | `/api/socios` |
| POST | `/api/socios` |
| GET | `/api/socios/{id}` |
| PUT | `/api/socios/{id}` |
| DELETE | `/api/socios/{id}` |
| PATCH | `/api/socios/{id}/estado` — body `{estado: activo|inactivo|moroso}` |

### 3.4 Categorías de socio (necesarias para el monto de cuota)
| Método | Ruta |
|---|---|
| GET | `/api/categorias` |
| POST | `/api/categorias` |
| GET / PUT / DELETE | `/api/categorias/{id}` |

### 3.5 Recibos
| Método | Ruta | Descripción |
|---|---|---|
| POST | `/api/recibos/generar-anuales` | Body opcional `{anio}`. Genera 12 recibos por cada socio activo (idempotente). |
| GET | `/api/recibos` | Lista todos. |
| GET | `/api/recibos/{id}` | |
| GET | `/api/socios/{id}/recibos` | Recibos de un socio. |
| PATCH | `/api/recibos/{id}/anular` | |

> Nota: el endpoint `PATCH /recibos/{id}/cancelar` de la especificación
> original se resuelve registrando un **pago** (ver 3.6), ya que cancelar un
> recibo es siempre consecuencia de un pago (permite auditar quién y cuándo
> lo cobró).

### 3.6 Pagos
| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/pagos` | |
| GET | `/api/pagos/{id}` | Incluye el listado de recibos cancelados. |
| POST | `/api/pagos` | Body: `{id_socio, recibos: [ids], id_usuario?, metodo_pago?}`. Cancela 1..N recibos en una transacción. |
| DELETE | `/api/pagos/{id}` | Anula el pago (no revierte automáticamente el estado de los recibos; a definir según criterio administrativo). |

### 3.7 Cobranza (panel del cobrador)
| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/cobranzas/pendientes?id_cobrador=&fecha=` | Socios con recibos pendientes, con nombre/dirección/teléfono/cantidad de recibos. |
| POST | `/api/cobranzas/registrar` | Equivale a `POST /api/pagos` (registra el cobro a domicilio). |
| POST | `/api/cobranzas/resultado-visita` | Body: `{id_socio, id_cobrador, resultado, observacion?}`. `resultado` ∈ `no_estaba, no_quiso_pagar, direccion_incorrecta, volver_a_visitar, cobro_realizado`. |

### 3.8 Reportes
| Método | Ruta |
|---|---|
| GET | `/api/reportes/ingresos?desde=&hasta=` |
| GET | `/api/reportes/morosos` |
| GET | `/api/reportes/cobranza-porcentaje?fecha=AAAA-MM-DD` |

### 3.9 Notificaciones
| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/notificaciones?id_socio=` | |
| POST | `/api/notificaciones` | Body: `{id_socio, titulo, mensaje}` |
| PATCH | `/api/notificaciones/{id}/leer` | Se marca como leída automáticamente al abrirla desde el portal. |

### 3.10 Consultas del socio
| Método | Ruta |
|---|---|
| GET | `/api/consultas` |
| GET | `/api/consultas/{id}` |
| POST | `/api/consultas` — body `{id_socio, asunto, mensaje}` |

## 4. Cómo levantar el backend

```bash
cd backend
php -S localhost:8000            # servidor embebido de PHP para desarrollo
```

Configurar la base de datos por variables de entorno (opcional; si no se
definen, usa los valores por defecto de `Database.php` para desarrollo
local):

```bash
export DB_HOST=localhost
export DB_NAME=margato_db
export DB_USER=root
export DB_PASSWORD=""
```

Crear el esquema:

```bash
mysql -u root -p < backend/database/schema.sql
```

## 5. Pendientes / mejoras sugeridas (no bloqueantes)

- `historial_actividad` está en el schema pero todavía no se escribe desde
  los controllers; agregar el registro de auditoría en las acciones
  administrativas (cambio de estado, anulación de pagos/recibos).
- Autenticación basada en sesión PHP es suficiente para el alcance del
  proyecto; si el cobrador va a usar la app fuera de la red del club,
  conviene migrar a tokens (JWT) para evitar problemas de cookies entre
  orígenes.
- Falta paginación en los listados (`GET /socios`, `GET /recibos`, etc.) —
  no es crítico para el volumen de datos de un club, pero es una mejora
  natural a mencionar en la defensa.
