# Final-Project-DAW
Final project for web application development

## Swimming Up

Aplicacion web para la gestion integral de nadadores de un club de natacion.

La arquitectura prevista usa Docker Compose con servicios separados para Nginx, React, Laravel, PostgreSQL, MariaDB, WordPress y phpMyAdmin en desarrollo.

El frontend ya esta creado con React y Vite en `frontend/`.

Consulta [gia.md](gia.md) para la guia completa de desarrollo y estructura.

## Rutas locales

- React: `http://swimmingup/`
- Laravel Admin (Blade): `http://swimmingup/admin/`
- API Laravel (JSON): `http://swimmingup/api/`
- WordPress: `http://swimmingup/blog/`
- phpMyAdmin: `http://swimmingup/phpmyadmin/`

## Comandos utiles de refresco

```bash
docker compose exec backend php artisan optimize:clear
docker compose exec backend php artisan route:list
docker compose exec nginx nginx -t
docker compose restart nginx backend
```
