# Arquitectura

Swimming Up se organiza como una aplicacion web por servicios:

- `nginx`: entrada unica HTTP y proxy inverso.
- `frontend`: interfaz React.
- `backend`: API Laravel.
- `postgres`: datos sensibles de autenticacion.
- `mariadb`: datos principales de aplicacion y WordPress.
- `wordpress`: blog del club.
- `phpmyadmin`: administracion visual de MariaDB en desarrollo.

## Flujo de peticiones

- `/` se envia al frontend React.
- `/api/` se envia al backend Laravel.
- `/blog/` se envia a WordPress.
- `/phpmyadmin/` se envia a phpMyAdmin cuando el perfil `dev` este activo.

## Decision pendiente

Laravel tendra que configurar dos conexiones de base de datos:

- Conexion principal a MariaDB para datos funcionales.
- Conexion secundaria a PostgreSQL para usuarios y credenciales.

Esta separacion mejora el aislamiento de datos sensibles, pero aumenta la complejidad de migraciones, relaciones y backups. Conviene documentar cada modelo indicando en que base vive.
