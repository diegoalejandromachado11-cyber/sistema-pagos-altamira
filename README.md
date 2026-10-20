# Sistema de Gestión de Pagos — Urbanización Altamira

Sistema web para registrar los pagos, gastos y rubros de cobro de la Urbanización Altamira, con conversión automática de dólares a bolívares usando la tasa oficial del BCV (obtenida por *web scraping*).

Hecho con **PHP 8, MySQL y Bootstrap 5**, siguiendo el patrón **Modelo-Vista-Controlador (MVC)**.

**Funciona sin internet:** Bootstrap está guardado dentro del proyecto (`assets/`). Internet solo se usa para consultar la tasa del BCV; si no hay conexión, el sistema sigue trabajando con la última tasa guardada.

## Cómo instalarlo (XAMPP)

1. Copiar la carpeta del proyecto dentro de `C:\xampp\htdocs\` con el nombre `altamira`.
2. Encender **Apache** y **MySQL** en el panel de XAMPP.
3. Abrir `http://localhost/phpmyadmin`, ir a **Importar** y subir `database.sql`.
4. Abrir `http://localhost/altamira/`.

Usuario inicial: **admin@altamira.com** / clave **admin123**

Si tu MySQL tiene otra clave, cámbiala en `config/conexion.php`.

`database.sql` es el único archivo de base de datos. Si la base `altamira` ya existe, la borra y la crea de nuevo vacía, así que no lo vuelvas a importar cuando ya tengas datos reales.

## Funciones

| Función | Dónde | Cómo funciona |
|---|---|---|
| Buscar pagos por cédula | Pagos | Escribe la cédula con o sin puntos y guion (`31525776` o `V-31.525.776`), o el nombre. También filtra por rubro y mes. Muestra el total encontrado. |
| Deudas | Deudas / Mi deuda | Revisa cada mes desde **enero del año pasado** (fecha de corte) hasta hoy y lista lo que falta por pagar. El botón **Pagar** abre el formulario ya lleno. |
| Pagos sin duplicados | Pagos | Un vecino no puede pagar dos veces el mismo rubro del mismo mes. Un rubro de pago único solo se paga una vez. Lo controla el código y también la base de datos (`UNIQUE`). |
| Tasa BCV automática | Todas las páginas | A las **9:30 am y 3:30 pm** (hora de Venezuela) el sistema intenta leer la tasa del BCV. Hace un solo intento por horario y lo registra en Auditoría. |
| Buscar rubros y gastos | Rubros, Gastos | Rubros por nombre o descripción. Gastos por descripción o rubro y por rango de fechas, con el total. |
| Recaudación del mes | Recaudación e Inicio | Cuánto pagó cada vecino en el mes, el total recaudado y cuántos vecinos no han pagado. |
| Buscar en auditoría | Auditoría | Por usuario, cédula o detalle, por tipo de acción y por fechas. |
| Factura automática | Pagos | Al registrar un pago se abre su factura lista para **Imprimir / Guardar PDF**. Cada pago tiene su botón **Factura**. |

### Tasa a la hora exacta (opcional)

El sistema actualiza la tasa la primera vez que alguien lo usa después de las 9:30 am o de las 3:30 pm. Para que lo haga a la hora exacta aunque nadie lo esté usando, crea dos tareas en Windows. Abre **Símbolo del sistema como administrador** y escribe:

```
schtasks /create /tn "Tasa BCV 9-30" /sc daily /st 09:30 /tr "C:\xampp\php\php.exe C:\xampp\htdocs\altamira\tareas\actualizar_tasa.php"
schtasks /create /tn "Tasa BCV 15-30" /sc daily /st 15:30 /tr "C:\xampp\php\php.exe C:\xampp\htdocs\altamira\tareas\actualizar_tasa.php"
```

## Estructura MVC

```
altamira/
├── index.php              ← Controlador frontal: recibe todas las peticiones
├── config/conexion.php    ← Conexión a MySQL (PDO)
├── models/                ← MODELO: consultas a la base de datos
│   ├── Usuario.php  Rol.php  Rubro.php  Pago.php  Gasto.php  Auditoria.php
│   ├── TasaBcv.php        ← tasa, scraping y actualización 9:30 / 3:30
│   └── Deuda.php          ← calcula deudas (no tiene tabla propia)
├── controllers/           ← CONTROLADOR: recibe la petición, usa los modelos y elige la vista
│   ├── Controller.php     ← base (sesión, roles, mensajes, cargar vistas)
│   ├── AuthController.php  InicioController.php  PagosController.php  DeudasController.php
│   ├── RecaudacionController.php  RubrosController.php  GastosController.php
│   └── UsuariosController.php  TasaController.php  AuditoriaController.php
├── views/                 ← VISTA: solo HTML con Bootstrap, sin consultas
│   ├── layout/ (header.php, footer.php)
│   ├── pagos/ (index.php, factura.php)  deudas/  recaudacion/  inicio/  auth/
│   └── rubros/  gastos/  usuarios/  tasa/  auditoria/
├── assets/                ← Bootstrap 5 local (funciona sin internet)
├── tareas/actualizar_tasa.php ← opcional, para el Programador de tareas de Windows
├── uploads/               ← Comprobantes de gastos
└── database.sql           ← Crea la base de datos completa
```

### Cómo viaja una petición

La URL indica el controlador (`c`) y la acción (`a`):

```
index.php?c=pagos&a=guardar
```

1. `index.php` crea `PagosController` y llama a su método `guardar()`.
2. El controlador revisa la sesión y le pregunta al **modelo** `Pago::yaPagado(...)` si ese mes ya está pagado. Si lo está, muestra el aviso y no guarda.
3. Si no, busca la tasa con `TasaBcv::vigente(...)`, guarda con `Pago::crear(...)` y registra `Auditoria::registrar(...)`.
4. Redirige a `index.php?c=pagos&a=factura&id=...`, que muestra la **vista** `views/pagos/factura.php`.

| Módulo | Controlador | Modelos que usa | Vista |
|---|---|---|---|
| Iniciar / cerrar sesión | AuthController | Usuario, Auditoria | auth/login.php |
| Panel de inicio | InicioController | TasaBcv, Pago, Gasto, Usuario, Deuda | inicio/index.php |
| Pagos, búsqueda y factura | PagosController | Pago, Rubro, Usuario, TasaBcv, Auditoria | pagos/index.php, pagos/factura.php |
| Deudas | DeudasController | Deuda, Rubro, Pago, Usuario, TasaBcv | deudas/index.php |
| Recaudación del mes | RecaudacionController | Usuario, Pago, Deuda | recaudacion/index.php |
| Rubros | RubrosController | Rubro, Auditoria | rubros/index.php |
| Gastos | GastosController | Gasto, Rubro, Auditoria | gastos/index.php |
| Usuarios | UsuariosController | Usuario, Rol, Auditoria | usuarios/index.php |
| Tasa BCV | TasaController | TasaBcv, Auditoria | tasa/index.php |
| Auditoría | AuditoriaController | Auditoria | auditoria/index.php |

## Cómo se calculan las deudas

El modelo `Deuda` no tiene tabla: compara lo que cada vecino debía pagar con lo que pagó.

- **Fecha de corte:** enero del año pasado (en 2026 es `2025-01`). Así el cálculo no crece sin límite.
- **Rubro mensual:** cada mes desde `max(corte, cobrar_desde)` hasta el mes actual sin pago es una deuda.
- **Rubro único:** se debe una vez si nunca se pagó.
- El monto usa el precio actual del rubro en dólares y la tasa BCV vigente para mostrarlo en bolívares.

## Cómo funciona el scraping del BCV

`TasaBcv::obtenerDesdeBCV()` en `models/TasaBcv.php`:

1. Descarga el HTML de `https://www.bcv.org.ve/` con `file_get_contents` (espera máximo unos segundos).
2. Lo carga en un `DOMDocument` y con `DOMXPath` busca el `<strong>` dentro del `<div id="dolar">`.
3. Convierte el texto `"39,18130000"` a número (`39.1813`).
4. Si no hay internet devuelve `null`: se registra `SCRAPING FALLIDO` en Auditoría y se sigue con la última tasa.

## Roles

- **Administrador**: acceso a todo.
- **Vecino**: registra y ve solo sus propios pagos, sus facturas y su deuda.
