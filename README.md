# Indice Application

This project is a PHP based management system for purchase orders, maintenance reports and now customer service requests.

## Structure
- `minipanel_mantenimiento.php` and related files: manage maintenance orders.
- `minipanel.php` and purchases related files: handle purchase orders.
- `usuarios.php` & `editar_usuario.php`: user management.
- `kpis_mantenimiento.php` and similar: KPI dashboards.
- Newly added `minipanel_servicio_cliente.php` module replicates the maintenance workflow for **Tareas**.
- `minipanel_transfers.php` module allows registering airport-hotel transfers.

## Database
The application expects a MySQL database. Connection credentials are read from environment variables. Copy `.env.example` to `.env` and set `DB_HOST`, `DB_USER`, `DB_PASSWORD` and `DB_NAME`.
Tables such as `ordenes_compra`, `ordenes_mantenimiento`, `ordenes_servicio_cliente` and `ordenes_transfers` store the different orders.

## Usage
1. Create a `.env` file with your database credentials.
2. Access `index.php` to login.
3. Depending on user role, the main menu (`menu_principal.php`) provides access to modules like purchases, maintenance, customer service and KPIs.

## Modules
- **Mantenimiento**: existing module for maintenance requests.
- **Tareas**: duplicated from maintenance, adjusted for customer service requests. Only roles *servicio al cliente*, *gerente* and *admin* can use it.
- **Transfers**: module to manage private transfers between airport and hotels with filters, exports and KPIs.

## Puestos Múltiples
`editar_usuario.php` permite ingresar más de un puesto separado por comas (por ejemplo: `"Servicio al Cliente, Mantenimiento"`).
En `menu_principal.php` se evalúan esos puestos para mostrar los módulos correspondientes.

## KPIs
Each module has export options to CSV/PDF and printable views. Charts are generated with Chart.js.

