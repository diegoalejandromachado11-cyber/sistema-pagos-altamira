# Sistema de Gestión de Pagos — Urbanización Altamira

Sistema web para registrar los pagos, gastos y rubros de cobro de la Urbanización Altamira, con conversión automática de dólares a bolívares usando la tasa oficial del BCV (obtenida por *web scraping*).

Hecho con **PHP 8, MySQL y Bootstrap 5**, siguiendo el patrón **Modelo-Vista-Controlador (MVC)**.

## Cómo instalarlo (XAMPP)

1. Copiar la carpeta del proyecto dentro de `C:\xampp\htdocs\` con el nombre `altamira`.
2. Encender **Apache** y **MySQL** en el panel de XAMPP.
3. Abrir `http://localhost/phpmyadmin`, ir a **Importar** y subir `database.sql`.
4. Abrir `http://localhost/altamira/`.

Usuario inicial: **admin@altamira.com** / clave **admin123**

Si tu MySQL tiene otra clave, cámbiala en `config/conexion.php`.

## Estructura MVC

```
altamira/
├── index.php              ← Controlador frontal: recibe todas las peticiones
├── config/
│   └── conexion.php       ← Conexión a MySQL (PDO)
├── models/                ← MODELO: consultas a la base de datos (una clase por tabla)
│   ├── Usuario.php  Rol.php  Rubro.php  Pago.php
│   ├── Gasto.php    TasaBcv.php (incluye el scraping)  Auditoria.php
├── controllers/           ← CONTROLADOR: recibe la petición, usa los modelos y elige la vista
│   ├── Controller.php     ← Controlador base (sesión, roles, mensajes, cargar vistas)
│   ├── AuthController.php  InicioController.php  PagosController.php
│   ├── RubrosController.php  GastosController.php  UsuariosController.php
│   ├── TasaController.php  AuditoriaController.php
├── views/                 ← VISTA: solo HTML con Bootstrap, sin consultas
│   ├── layout/ (header.php, footer.php)
│   ├── auth/  inicio/  pagos/  rubros/  gastos/  usuarios/  tasa/  auditoria/
├── uploads/               ← Comprobantes de gastos
└── database.sql           ← Las 7 tablas del modelo entidad-relación
```

### Cómo viaja una petición

La URL indica el controlador (`c`) y la acción (`a`):

```
index.php?c=rubros&a=actualizar
```

1. `index.php` crea `RubrosController` y llama a su método `actualizar()`.
2. El controlador revisa que el usuario sea administrador y llama al **modelo**: `Rubro::actualizar(...)` y `Auditoria::registrar(...)`.
3. Deja un mensaje ("Condominio ahora cuesta $11") y redirige a `index.php?c=rubros`.
4. La acción `index()` pide los datos al modelo y los entrega a la **vista** `views/rubros/index.php`, que solo los muestra.

| Módulo | Controlador | Modelos que usa | Vista |
|---|---|---|---|
| Iniciar / cerrar sesión | AuthController | Usuario, Auditoria | auth/login.php |
| Panel de inicio | InicioController | TasaBcv, Pago, Gasto, Usuario | inicio/index.php |
| Pagos | PagosController | Pago, Rubro, Usuario, TasaBcv, Auditoria | pagos/index.php |
| Rubros (cambiar el monto del mes) | RubrosController | Rubro, Auditoria | rubros/index.php |
| Gastos con comprobante | GastosController | Gasto, Rubro, Auditoria | gastos/index.php |
| Usuarios | UsuariosController | Usuario, Rol, Auditoria | usuarios/index.php |
| Tasa BCV (scraping) | TasaController | TasaBcv, Auditoria | tasa/index.php |
| Auditoría | AuditoriaController | Auditoria | auditoria/index.php |

## Cómo funciona el scraping del BCV

El método `TasaBcv::obtenerDesdeBCV()` en `models/TasaBcv.php`:

1. Descarga el HTML de `https://www.bcv.org.ve/` con `file_get_contents`.
2. Lo carga en un `DOMDocument` y con `DOMXPath` busca el `<strong>` dentro del `<div id="dolar">`.
3. Convierte el texto `"39,18130000"` a número (`39.1813`).
4. `TasaController` lo guarda en la tabla `tasa_bcv` con la fecha de hoy.

Si la página del BCV no responde, la tasa se puede registrar manualmente.

## Roles

- **Administrador**: acceso a todo, incluido cambiar el monto mensual de cada rubro.
- **Vecino**: solo registra y ve sus propios pagos.
