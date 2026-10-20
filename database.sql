-- =====================================================
-- Base de datos: Sistema de Gestión de Pagos
-- Urbanización Altamira
-- Importar este archivo en phpMyAdmin
-- =====================================================

CREATE DATABASE IF NOT EXISTS altamira CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE altamira;

-- Roles del sistema (Administrador, Vecino)
CREATE TABLE rol (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(50) NOT NULL,
    descripcion VARCHAR(255),
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Usuarios: cada usuario TIENE un rol (N:1)
CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    cedula VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    contrasena_hash VARCHAR(255) NOT NULL,
    id_rol INT NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_rol) REFERENCES rol(id_rol)
);

-- Rubros: conceptos de cobro (condominio, vigilancia, etc.)
CREATE TABLE rubro (
    id_rubro INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    monto_base DECIMAL(12,2) NOT NULL,     -- monto del mes en dólares
    mensual TINYINT(1) NOT NULL DEFAULT 1, -- 1 = se cobra cada mes, 0 = se cobra una sola vez
    cobrar_desde CHAR(7) NOT NULL,         -- mes desde el que se cobra (AAAA-MM)
    descripcion VARCHAR(255),
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Tasa del dólar publicada por el BCV (una por día)
CREATE TABLE tasa_bcv (
    id_tasa INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL UNIQUE,
    tasa_usd DECIMAL(14,4) NOT NULL,
    fecha_registro DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Pagos: un usuario REALIZA pagos, un rubro GENERA pagos,
-- y la tasa APLICA A los pagos (opcional)
CREATE TABLE pago (
    id_pago INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_rubro INT NOT NULL,
    id_tasa INT NULL,
    monto DECIMAL(14,2) NOT NULL,          -- monto en bolívares
    monto_usd DECIMAL(14,2),               -- monto convertido a dólares
    fecha_pago DATE NOT NULL,
    periodo VARCHAR(7) NOT NULL,           -- formato AAAA-MM
    metodo_pago VARCHAR(50) NOT NULL,
    referencia VARCHAR(50),
    -- Un vecino no puede pagar dos veces el mismo rubro del mismo mes
    CONSTRAINT pago_unico UNIQUE (id_usuario, id_rubro, periodo),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario),
    FOREIGN KEY (id_rubro) REFERENCES rubro(id_rubro),
    FOREIGN KEY (id_tasa) REFERENCES tasa_bcv(id_tasa)
);

-- Gastos: un rubro GENERA gastos (ej. pago a vigilantes)
CREATE TABLE gasto (
    id_gasto INT AUTO_INCREMENT PRIMARY KEY,
    id_rubro INT NOT NULL,
    monto DECIMAL(14,2) NOT NULL,
    fecha DATE NOT NULL,
    descripcion VARCHAR(255),
    comprobante_url VARCHAR(255),
    FOREIGN KEY (id_rubro) REFERENCES rubro(id_rubro)
);

-- Auditoría: registra cada acción que hace un usuario
CREATE TABLE auditoria (
    id_auditoria INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT,
    accion VARCHAR(50) NOT NULL,
    tabla_afectada VARCHAR(50),
    registro_id INT,
    detalle VARCHAR(255),
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip VARCHAR(45),
    FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
);

-- ===================== DATOS INICIALES =====================
INSERT INTO rol (nombre_rol, descripcion) VALUES
('Administrador', 'Gestiona todo el sistema'),
('Vecino', 'Registra y consulta sus propios pagos');

-- Usuario administrador -> correo: admin@altamira.com  clave: admin123
INSERT INTO usuario (nombre, cedula, email, contrasena_hash, id_rol) VALUES
('Administrador', 'V-00000000', 'admin@altamira.com',
 '$2y$10$zBJDOpsXUO5WgCiXRWAEoe1aD.GXzjICcpB55z1GHdgGXTSPHLD.O', 1);

-- Los rubros iniciales se cobran desde enero del año pasado (fecha de corte de deudas)
INSERT INTO rubro (nombre, monto_base, mensual, cobrar_desde, descripcion) VALUES
('Condominio', 20.00, 1, DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 YEAR), '%Y-01'), 'Cuota mensual de condominio (USD)'),
('Vigilancia', 10.00, 1, DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 YEAR), '%Y-01'), 'Servicio de vigilancia (USD)'),
('Mantenimiento', 5.00, 1, DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 YEAR), '%Y-01'), 'Mantenimiento de áreas comunes (USD)');
