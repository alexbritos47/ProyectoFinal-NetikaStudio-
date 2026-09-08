-- =====================================================
-- Club Ciclista Maragato - Schema
-- Alineado a docs/sistema_cobros_socios_club_ciclista.md
-- =====================================================

CREATE DATABASE IF NOT EXISTS margato_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_spanish_ci;

USE margato_db;

-- =====================================================
-- Tabla: CATEGORIA_SOCIO (define el monto de cuota)
-- =====================================================
CREATE TABLE CATEGORIA_SOCIO (
    id_categoria    INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(50)     NOT NULL,   -- activo/juvenil/honorario
    monto_cuota     DECIMAL(10,2)   NOT NULL
);

-- =====================================================
-- Tabla: USUARIO (administrador, socio, cobrador)
-- =====================================================
CREATE TABLE USUARIO (
    id_usuario      INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario  VARCHAR(50)     NOT NULL UNIQUE,
    contrasena      VARCHAR(255)    NOT NULL,   -- hash (password_hash)
    nombre_completo VARCHAR(150)    NOT NULL,
    rol             VARCHAR(20)     NOT NULL,   -- administrador/socio/cobrador
    email           VARCHAR(100)    UNIQUE,
    estado          VARCHAR(20)     NOT NULL DEFAULT 'activo'  -- activo/inactivo
);

-- =====================================================
-- Tabla: SOCIO
-- =====================================================
CREATE TABLE SOCIO (
    id_socio          INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario        INT             NULL,       -- login del socio (portal del socio)
    id_cobrador       INT             NULL,       -- cobrador asignado (cartera)
    numero_socio      VARCHAR(30)     NOT NULL UNIQUE,
    nombre            VARCHAR(100)    NOT NULL,
    apellido          VARCHAR(100)    NOT NULL,
    tipo_documento    VARCHAR(20)     NOT NULL,   -- CI/Pasaporte
    numero_documento  VARCHAR(30)     NOT NULL UNIQUE,
    direccion         VARCHAR(200),               -- necesaria para panel del cobrador
    telefono          VARCHAR(30),
    email             VARCHAR(100),
    fecha_ingreso     DATE            NOT NULL,
    estado            VARCHAR(20)     NOT NULL DEFAULT 'activo',  -- activo/inactivo/moroso
    id_categoria      INT             NOT NULL,
    CONSTRAINT fk_socio_categoria
        FOREIGN KEY (id_categoria) REFERENCES CATEGORIA_SOCIO(id_categoria)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_socio_usuario
        FOREIGN KEY (id_usuario) REFERENCES USUARIO(id_usuario)
        ON UPDATE CASCADE
        ON DELETE SET NULL,
    CONSTRAINT fk_socio_cobrador
        FOREIGN KEY (id_cobrador) REFERENCES USUARIO(id_usuario)
        ON UPDATE CASCADE
        ON DELETE SET NULL
);

-- =====================================================
-- Tabla: RECIBO (cuota mensual generada para un socio)
-- =====================================================
CREATE TABLE RECIBO (
    id_recibo            INT AUTO_INCREMENT PRIMARY KEY,
    numero_recibo        VARCHAR(30)     NOT NULL UNIQUE,
    id_socio             INT             NOT NULL,
    periodo              VARCHAR(7)      NOT NULL,   -- ej: 2026-09
    importe              DECIMAL(10,2)   NOT NULL,
    fecha_vencimiento    DATE            NOT NULL,
    estado               VARCHAR(20)     NOT NULL DEFAULT 'pendiente', -- pendiente/pagado/anulado
    fecha_pago           DATE            NULL,
    CONSTRAINT fk_recibo_socio
        FOREIGN KEY (id_socio) REFERENCES SOCIO(id_socio)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    UNIQUE KEY uk_recibo_socio_periodo (id_socio, periodo)
);

-- =====================================================
-- Tabla: PAGO (un pago puede cancelar 1 o varios recibos)
-- =====================================================
CREATE TABLE PAGO (
    id_pago         INT AUTO_INCREMENT PRIMARY KEY,
    id_socio        INT             NOT NULL,
    id_usuario      INT             NULL,        -- quien registró el cobro (admin o cobrador)
    monto_total     DECIMAL(10,2)   NOT NULL,
    metodo_pago     VARCHAR(20),                 -- efectivo/transferencia/domicilio
    fecha_pago      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    anulado         TINYINT(1)      NOT NULL DEFAULT 0,
    CONSTRAINT fk_pago_socio
        FOREIGN KEY (id_socio) REFERENCES SOCIO(id_socio)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_pago_usuario
        FOREIGN KEY (id_usuario) REFERENCES USUARIO(id_usuario)
        ON UPDATE CASCADE
        ON DELETE SET NULL
);

-- Detalle: qué recibos cancela cada pago (N a N)
CREATE TABLE PAGO_RECIBO (
    id_pago     INT NOT NULL,
    id_recibo   INT NOT NULL,
    PRIMARY KEY (id_pago, id_recibo),
    CONSTRAINT fk_pagorecibo_pago
        FOREIGN KEY (id_pago) REFERENCES PAGO(id_pago)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_pagorecibo_recibo
        FOREIGN KEY (id_recibo) REFERENCES RECIBO(id_recibo)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

-- =====================================================
-- Tabla: COBRANZA_VISITA (resultado de visitas a domicilio)
-- =====================================================
CREATE TABLE COBRANZA_VISITA (
    id_visita     INT AUTO_INCREMENT PRIMARY KEY,
    id_socio      INT             NOT NULL,
    id_cobrador   INT             NOT NULL,
    fecha_visita  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    resultado     VARCHAR(30)     NOT NULL, -- no_estaba/no_quiso_pagar/direccion_incorrecta/volver_a_visitar/cobro_realizado
    observacion   VARCHAR(255),
    CONSTRAINT fk_visita_socio
        FOREIGN KEY (id_socio) REFERENCES SOCIO(id_socio)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_visita_cobrador
        FOREIGN KEY (id_cobrador) REFERENCES USUARIO(id_usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

-- =====================================================
-- Tabla: NOTIFICACION (mensajes internos al socio)
-- =====================================================
CREATE TABLE NOTIFICACION (
    id_notificacion   INT AUTO_INCREMENT PRIMARY KEY,
    id_socio          INT             NOT NULL,
    titulo            VARCHAR(150)    NOT NULL,
    mensaje           TEXT            NOT NULL,
    fecha_envio       DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    leida             TINYINT(1)      NOT NULL DEFAULT 0,
    CONSTRAINT fk_notificacion_socio
        FOREIGN KEY (id_socio) REFERENCES SOCIO(id_socio)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

-- =====================================================
-- Tabla: CONSULTA (mensajes del socio hacia la administración)
-- =====================================================
CREATE TABLE CONSULTA (
    id_consulta   INT AUTO_INCREMENT PRIMARY KEY,
    id_socio      INT             NOT NULL,
    asunto        VARCHAR(150)    NOT NULL,
    mensaje       TEXT            NOT NULL,
    respuesta     TEXT            NULL,
    estado        VARCHAR(20)     NOT NULL DEFAULT 'pendiente', -- pendiente/respondida
    fecha         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_consulta_socio
        FOREIGN KEY (id_socio) REFERENCES SOCIO(id_socio)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

-- =====================================================
-- Tabla: HISTORIAL_ACTIVIDAD (trazabilidad de acciones)
-- =====================================================
CREATE TABLE HISTORIAL_ACTIVIDAD (
    id_historial  INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario    INT             NULL,
    accion        VARCHAR(100)    NOT NULL, -- ej: generar_recibos, registrar_pago, cambiar_estado_socio
    entidad       VARCHAR(50)     NOT NULL, -- ej: SOCIO, RECIBO, PAGO
    entidad_id    INT             NULL,
    detalle       VARCHAR(255),
    fecha         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_historial_usuario
        FOREIGN KEY (id_usuario) REFERENCES USUARIO(id_usuario)
        ON UPDATE CASCADE
        ON DELETE SET NULL
);

-- =====================================================
-- Índices adicionales recomendados
-- =====================================================
CREATE INDEX idx_socio_categoria ON SOCIO(id_categoria);
CREATE INDEX idx_socio_cobrador ON SOCIO(id_cobrador);
CREATE INDEX idx_recibo_socio ON RECIBO(id_socio);
CREATE INDEX idx_recibo_periodo ON RECIBO(periodo);
CREATE INDEX idx_pago_socio ON PAGO(id_socio);
CREATE INDEX idx_visita_socio ON COBRANZA_VISITA(id_socio);
CREATE INDEX idx_notificacion_socio ON NOTIFICACION(id_socio);
CREATE INDEX idx_consulta_socio ON CONSULTA(id_socio);
