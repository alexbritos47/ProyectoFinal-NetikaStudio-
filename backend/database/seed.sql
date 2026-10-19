-- =====================================================
-- seed.sql - Datos iniciales del sistema
-- Se ejecuta SOLO la primera vez que se crea la base,
-- justo después de schema.sql.
-- =====================================================
USE margato_db;

-- 1) Categorías de socio (definen cuánto paga cada uno por mes)
--    Cambiá los montos por los reales del club.
INSERT INTO CATEGORIA_SOCIO (nombre, monto_cuota) VALUES
  ('activo',    500.00),
  ('juvenil',   300.00),
  ('honorario', 100.00);

-- 2) Usuarios iniciales
--    Las contraseñas están guardadas cifradas (bcrypt), no en texto.
--    admin      -> contraseña: admin123
--    cobrador1  -> contraseña: cobrador123
INSERT INTO USUARIO (nombre_usuario, contrasena, nombre_completo, rol, email, estado) VALUES
  ('admin',     '$2y$10$FYxuPLDFLt1192ScNN.qYeTL1XFjPTKhPYrzA3kKEYgjlT02REnuG', 'Administrador', 'administrador', 'admin@maragato.com',     'activo'),
  ('cobrador1', '$2y$10$H8Nj3OIwwb92z1pMBtBtA.12cWUn05/CYqqEDF6PJLILCpeHtKyei', 'Cobrador Uno',  'cobrador',      'cobrador1@maragato.com', 'activo');

-- 3) Dos socios de prueba (para poder probar recibos y pagos enseguida)
INSERT INTO SOCIO
  (id_cobrador, numero_socio, nombre, apellido, tipo_documento, numero_documento,
   direccion, telefono, email, fecha_ingreso, estado, id_categoria)
VALUES
  ((SELECT id_usuario FROM USUARIO WHERE nombre_usuario = 'cobrador1'),
   'S-0001', 'Juan',  'Pérez',  'CI', '1.234.567-8', 'Calle 18 de Julio 123', '099111222', 'juan@correo.com',  CURDATE(), 'activo',
   (SELECT id_categoria FROM CATEGORIA_SOCIO WHERE nombre = 'activo')),
  ((SELECT id_usuario FROM USUARIO WHERE nombre_usuario = 'cobrador1'),
   'S-0002', 'María', 'Gómez',  'CI', '2.345.678-9', 'Av. Artigas 456',        '099333444', 'maria@correo.com', CURDATE(), 'activo',
   (SELECT id_categoria FROM CATEGORIA_SOCIO WHERE nombre = 'juvenil'));