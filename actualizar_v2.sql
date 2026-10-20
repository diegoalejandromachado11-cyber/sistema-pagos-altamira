-- =====================================================
-- actualizar_v2.sql
-- SOLO para quien ya tenía instalada la versión anterior.
-- (Si es una instalación nueva, importe database.sql y listo.)
-- Importar en phpMyAdmin con la base "altamira" seleccionada.
-- =====================================================
USE altamira;

-- 1. Tipo de cobro de cada rubro y mes desde el que se cobra
ALTER TABLE rubro
    ADD mensual TINYINT(1) NOT NULL DEFAULT 1 AFTER monto_base,
    ADD cobrar_desde CHAR(7) NOT NULL DEFAULT '2025-01' AFTER mensual;

-- Los rubros que ya existían se cobran desde enero del año pasado
UPDATE rubro SET cobrar_desde = DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 1 YEAR), '%Y-01');

-- 2. Bloquear pagos duplicados: mismo vecino, mismo rubro, mismo mes.
--    Si este paso da error es porque ya hay pagos repetidos: elimine
--    el duplicado desde el módulo Pagos y vuelva a ejecutar solo esta línea.
ALTER TABLE pago ADD CONSTRAINT pago_unico UNIQUE (id_usuario, id_rubro, periodo);
