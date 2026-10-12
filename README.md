# Sistema de Gestión de Pagos — Urbanización Altamira

Hecho con PHP, MySQL y Bootstrap 5.

## Cómo instalarlo (XAMPP)

1. Copiar la carpeta `altamira` dentro de `C:\xampp\htdocs\`.
2. Encender **Apache** y **MySQL** en el panel de XAMPP.
3. Abrir `http://localhost/phpmyadmin`, ir a **Importar** y subir `database.sql`.
4. Abrir `http://localhost/altamira/login.php`.

Usuario inicial: **admin@altamira.com** / clave **admin123**

Si tu MySQL tiene otra clave, cámbiala en `conexion.php`.

## Archivos

| Archivo | Qué hace |
|---|---|
| `database.sql` | Crea las 7 tablas del modelo entidad-relación y los datos iniciales |
| `conexion.php` | Conecta a la base de datos e inicia la sesión |
| `funciones.php` | Funciones comunes: sesión, roles, auditoría y **scraping del BCV** |
| `header.php` / `footer.php` | Menú y estructura con Bootstrap que comparten todas las páginas |
| `login.php` / `logout.php` | Entrar y salir del sistema |
| `index.php` | Panel de inicio con totales y últimos pagos |
| `pagos.php` | Registrar y ver pagos (convierte Bs a USD con la tasa BCV) |
| `gastos.php` | Registrar gastos con comprobante (solo admin) |
| `rubros.php` | Conceptos de cobro (solo admin) |
| `usuarios.php` | Crear vecinos y administradores (solo admin) |
| `tasa.php` | Obtener la tasa del BCV por scraping o registrarla a mano |
| `auditoria.php` | Ver el historial de acciones |

## Cómo funciona el scraping del BCV

La función `obtenerTasaBCV()` en `funciones.php`:

1. Descarga el HTML de `https://www.bcv.org.ve/` con `file_get_contents`.
2. Lo carga en un `DOMDocument` y con `DOMXPath` busca el `<strong>` dentro del `<div id="dolar">`.
3. Convierte el texto `"39,18130000"` a número (`39.1813`).
4. `tasa.php` guarda el valor en la tabla `tasa_bcv` con la fecha de hoy.

Se desactiva la verificación SSL porque el certificado del sitio del BCV suele fallar. Si la página no responde, la tasa se puede registrar manualmente.

## Roles

- **Administrador**: acceso a todo.
- **Vecino**: solo registra y ve sus propios pagos.
