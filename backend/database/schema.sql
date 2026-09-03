-- =====================================================
-- Club Ciclista Margato - Schema (Persona 3)
-- Tablas: CATEGORIA_SOCIO, SOCIO, CUOTA_PAGO, USUARIO
-- =====================================================

CREATE DATABASE IF NOT EXISTS margato_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_spanish_ci;

USE margato_db;

-- =====================================================
-- Tabla: CATEGORIA_SOCIO
-- =====================================================
CREATE TABLE CATEGORIA_SOCIO (
    id_categoria    INT AUTO_INCREMENT PRIMARY KEY,
    nombre          VARCHAR(50)     NOT NULL,   -- activo/juvenil/honorario
    monto_cuota     DECIMAL(10,2)   NOT NULL
);

-- =====================================================
-- Tabla: USUARIO
-- =====================================================
CREATE TABLE USUARIO (
    id_usuario      INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario  VARCHAR(50)     NOT NULL UNIQUE,
    contrasena      VARCHAR(255)    NOT NULL,   -- hash
    nombre_completo VARCHAR(150)    NOT NULL,
    rol             VARCHAR(20)     NOT NULL,   -- administrador/cobrador/consulta
    email           VARCHAR(100),
    estado          VARCHAR(20)     NOT NULL DEFAULT 'activo'  -- activo/inactivo
);

-- =====================================================
-- Tabla: SOCIO
-- =====================================================
CREATE TABLE SOCIO (
    id_socio          INT AUTO_INCREMENT PRIMARY KEY,
    numero_socio      VARCHAR(30)     NOT NULL UNIQUE,
    nombre            VARCHAR(100)    NOT NULL,
    apellido          VARCHAR(100)    NOT NULL,
    tipo_documento    VARCHAR(20)     NOT NULL,  -- DNI/CI/Pasaporte
    numero_documento  VARCHAR(30)     NOT NULL,
    telefono          VARCHAR(30),
    email             VARCHAR(100),
    fecha_ingreso     DATE            NOT NULL,
    estado            VARCHAR(20)     NOT NULL DEFAULT 'activo',  -- activo/inactivo/baja
    id_categoria      INT             NOT NULL,
    CONSTRAINT fk_socio_categoria
        FOREIGN KEY (id_categoria) REFERENCES CATEGORIA_SOCIO(id_categoria)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

-- =====================================================
-- Tabla: CUOTA_PAGO
-- =====================================================
CREATE TABLE CUOTA_PAGO (
    id_pago             INT AUTO_INCREMENT PRIMARY KEY,
    id_socio            INT             NOT NULL,
    periodo             VARCHAR(7)      NOT NULL,   -- ej: 2026-09
    monto               DECIMAL(10,2)   NOT NULL,
    fecha_vencimiento   DATE            NOT NULL,
    fecha_pago          DATE            NULL,
    estado_pago         VARCHAR(20)     NOT NULL DEFAULT 'pendiente',  -- pagado/pendiente/vencido
    metodo_pago         VARCHAR(20),    -- efectivo/transferencia/tarjeta
    numero_comprobante  VARCHAR(50),
    id_usuario          INT             NULL,      -- quien registró el cobro
    CONSTRAINT fk_cuota_socio
        FOREIGN KEY (id_socio) REFERENCES SOCIO(id_socio)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_cuota_usuario
        FOREIGN KEY (id_usuario) REFERENCES USUARIO(id_usuario)
        ON UPDATE CASCADE
        ON DELETE SET NULL
);

-- =====================================================
-- Índices adicionales recomendados
-- =====================================================
CREATE INDEX idx_socio_categoria ON SOCIO(id_categoria);
CREATE INDEX idx_cuota_socio ON CUOTA_PAGO(id_socio);
CREATE INDEX idx_cuota_usuario ON CUOTA_PAGO(id_usuario);
CREATE INDEX idx_cuota_periodo ON CUOTA_PAGO(periodo);