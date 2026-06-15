# Swimming Up

Swimming Up es una aplicacion web para la gestion y consulta de informacion de un club de natacion. El proyecto combina una aplicacion publica en React, un panel de administracion en Laravel y un blog en WordPress, todo servido detras de Nginx como proxy inverso.

La aplicacion esta pensada para funcionar en contenedores Docker. En desarrollo se usan servidores internos de Vite y Laravel, mientras que en produccion se compilan imagenes especificas para servir React como contenido estatico y Laravel sobre Apache/PHP.

## Funcionamiento General

La entrada principal del sistema es Nginx. Segun la ruta solicitada, Nginx envia la peticion al contenedor correspondiente:

- `/`: aplicacion React publica.
- `/login`: login de la aplicacion React.
- `/app`: area privada de usuario en React.
- `/admin`: panel de administracion Blade de Laravel.
- `/blog`: WordPress con el tema personalizado del proyecto.
- `/phpmyadmin`: phpMyAdmin en desarrollo o perfil de depuracion.

React consume la API de Laravel para autenticar usuarios de la app, cargar datos del calendario, eventos, perfiles y nadadores asociados. Laravel tambien ofrece un area de administracion independiente para gestionar nadadores, eventos, calendario, tiempos y usuarios. WordPress queda separado como blog/noticias, pero se sirve bajo el mismo dominio mediante el proxy.

## Tecnologias

- **Frontend:** React 19, Vite, CSS.
- **Backend:** Laravel 13, PHP 8.4, Blade.
- **Blog:** WordPress 6.5 con tema personalizado.
- **Proxy:** Nginx.
- **Bases de datos:** MariaDB 11.4 y PostgreSQL 16.
- **Administracion DB:** phpMyAdmin en desarrollo/perfil debug.
- **Contenedores:** Docker Compose.

## Bases de Datos

El proyecto usa dos motores:

- **MariaDB:** datos principales de Laravel y base de datos de WordPress.
- **PostgreSQL:** datos de autenticacion y usuarios de la aplicacion.

Laravel tiene configuradas dos conexiones:

- `mariadb`: conexion principal.
- `auth_pgsql`: conexion de usuarios/auth.

## Variables de Entorno

Para desarrollo:

```bash
cp .env.example .env
```

Para produccion:

```bash
cp .env.production.example .env.production
```

Variables importantes:

- `APP_KEY`: clave de cifrado de Laravel. Debe ser valida y no puede quedarse vacia ni con texto placeholder.
- `APP_URL`: URL publica del panel Laravel, por ejemplo `http://swimmingup.hopto.org/admin`.
- `ASSET_URL`: URL base para assets del admin.
- `VITE_API_BASE_URL`: base de la API consumida por React, normalmente `/api`.
- `MARIADB_*`: credenciales de MariaDB.
- `POSTGRES_*`: credenciales de PostgreSQL.
- `WORDPRESS_*`: credenciales de WordPress.

Generar `APP_KEY`:

```bash
printf 'base64:%s\n' "$(openssl rand -base64 32)"
```

Copiar el resultado en `.env.production`:

```env
APP_KEY=base64:...
```

## Desarrollo Local

1. Crear el archivo `.env`:

```bash
cp .env.example .env
```

2. Ajustar credenciales si es necesario.

3. Levantar los servicios:

```bash
docker compose up -d --build
```

4. Accesos habituales:

```text
Frontend:      http://swimmingup.hopto.org/
Admin Laravel: http://swimmingup.hopto.org/admin/
WordPress:     http://swimmingup.hopto.org/blog/
phpMyAdmin:    http://swimmingup.hopto.org/phpmyadmin/
```

## Despliegue en Produccion

El despliegue de produccion usa `compose.prod.yaml`. La idea es que solo Nginx exponga HTTP al exterior y el resto de servicios queden en la red interna de Docker.

1. Instalar Docker Engine y Docker Compose en la maquina Ubuntu/EC2.

2. Clonar el repositorio.

3. Crear el archivo de entorno:

```bash
cp .env.production.example .env.production
```

4. Editar `.env.production`:

- Cambiar todas las contrasenas `change_me_*`.
- Generar y configurar `APP_KEY`.
- Revisar dominio y URLs.

5. Construir y levantar la app:

```bash
docker compose -f compose.prod.yaml --env-file .env.production up -d --build
```

6. Ejecutar migraciones:

```bash
docker compose -f compose.prod.yaml --env-file .env.production exec backend php artisan migrate --force
```

7. Limpiar cache de Laravel cuando se cambien variables o configuracion:

```bash
docker compose -f compose.prod.yaml --env-file .env.production exec backend php artisan optimize:clear
```

## Comandos Utiles

Ver contenedores:

```bash
docker compose -f compose.prod.yaml --env-file .env.production ps
```

Ver logs del backend:

```bash
docker compose -f compose.prod.yaml --env-file .env.production logs --tail=200 backend
```

Ver logs de Nginx:

```bash
docker compose -f compose.prod.yaml --env-file .env.production logs --tail=200 nginx
```

Recrear backend y Nginx despues de cambiar variables:

```bash
docker compose -f compose.prod.yaml --env-file .env.production up -d --force-recreate backend nginx
```

Reconstruir todo y eliminar contenedores huerfanos:

```bash
docker compose -f compose.prod.yaml --env-file .env.production up -d --build --remove-orphans
```

