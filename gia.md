# Guia de desarrollo - Swimming Up

## 1. Objetivo del proyecto

Swimming Up sera una aplicacion web completa para la gestion deportiva y administrativa de un club de natacion. El sistema debe permitir que padres, nadadores, entrenadores y administradores consulten y gestionen informacion como horarios, asistencia, grupos, entrenadores, informes de rendimiento, comparativas de tiempos, logros, metas progresivas y contenido informativo del club.

El proyecto se desarrollara con una arquitectura basada en contenedores Docker para facilitar el despliegue en entornos locales, maquinas virtuales de pruebas y servidores reales como una instancia EC2 de AWS.

## 2. Principios de arquitectura

- Todos los servicios se levantaran desde un unico `compose.yaml`.
- Cada servicio con logica propia tendra su `Dockerfile` correspondiente.
- Nginx actuara como punto de entrada y proxy inverso.
- El frontend estara desarrollado con React.
- El backend principal estara desarrollado con Laravel.
- PostgreSQL se usara para usuarios, credenciales y datos sensibles de autenticacion.
- MariaDB se usara para datos principales de la aplicacion y para WordPress.
- WordPress se usara como blog complementario del club.
- phpMyAdmin se incluira para gestionar MariaDB durante desarrollo.
- Las credenciales y configuraciones se gestionaran mediante variables de entorno.
- Los datos persistentes se guardaran en volumenes Docker.

## 3. Estructura recomendada del proyecto

```text
Final-Project-DAW/
+-- compose.yaml
+-- .env.example
+-- README.md
+-- gia.md
+-- docker/
|   +-- nginx/
|   |   +-- Dockerfile
|   |   +-- conf.d/
|   |       +-- default.conf
|   +-- frontend/
|   |   +-- Dockerfile
|   +-- backend/
|   |   +-- Dockerfile
|   +-- wordpress/
|       +-- Dockerfile
+-- frontend/
|   +-- package.json
|   +-- vite.config.js
|   +-- src/
+-- backend/
|   +-- composer.json
|   +-- artisan
|   +-- app/
|   +-- config/
|   +-- database/
|   +-- routes/
+-- wordpress/
|   +-- wp-content/
|       +-- themes/
|       +-- plugins/
|       +-- uploads/
+-- database/
|   +-- mariadb/
|   |   +-- init/
|   +-- postgres/
|       +-- init/
+-- scripts/
|   +-- dev/
|   +-- deploy/
+-- docs/
    +-- arquitectura.md
    +-- api.md
    +-- despliegue.md
```

## 4. Contenedores previstos

### 4.1 Nginx

Responsabilidades:

- Recibir todas las peticiones HTTP/HTTPS.
- Redirigir trafico hacia React, Laravel o WordPress segun la ruta.
- Evitar la exposicion directa de servicios internos.
- Preparar la futura configuracion SSL para produccion.

Rutas recomendadas:

- `/` hacia el frontend React.
- `/api` hacia Laravel.
- `/blog` hacia WordPress.
- `/phpmyadmin` hacia phpMyAdmin solo en desarrollo.
- `http://swimmingup:8081` hacia phpMyAdmin en desarrollo si se activa el perfil `dev`.

### 4.2 Frontend React

Responsabilidades:

- Interfaz principal de Swimming Up.
- Paneles para padres, nadadores, entrenadores y administradores.
- Consumo de la API Laravel.
- Gestion de sesion desde el cliente sin exponer credenciales sensibles.

Recomendacion:

- Usar Vite como base de React por simplicidad, velocidad y buen soporte Docker.
- Separar servicios de API, componentes, rutas y vistas desde el inicio.

### 4.3 Backend Laravel

Responsabilidades:

- API principal.
- Logica de negocio.
- Gestion de permisos y roles.
- Conexion con PostgreSQL para autenticacion.
- Conexion con MariaDB para datos funcionales.
- Generacion de informes, historicos y comparativas.

Recomendacion:

- Definir desde el principio roles como `admin`, `entrenador`, `nadador` y `tutor`.
- Mantener una capa clara de controladores, servicios y modelos.
- Documentar endpoints en `docs/api.md`.

### 4.4 PostgreSQL

Uso previsto:

- Usuarios.
- Credenciales.
- Sesiones o tokens si aplica.
- Datos especialmente sensibles.

Recomendacion:

- No exponer el puerto en produccion.
- Acceso solo desde Laravel dentro de la red Docker.
- Considerar pgAdmin en desarrollo si se quiere una interfaz grafica equivalente a phpMyAdmin para PostgreSQL.

### 4.5 MariaDB

Uso previsto:

- Base de datos principal de Laravel.
- Base de datos de WordPress.

Bases recomendadas:

- `swimmingup_app`
- `swimmingup_wordpress`

Recomendacion:

- Separar usuarios de base de datos para Laravel y WordPress.
- Usar scripts de inicializacion en `database/mariadb/init/`.

### 4.6 WordPress

Responsabilidades:

- Blog del club.
- Noticias.
- Propuestas e iniciativas.
- Contenido complementario no critico para la aplicacion principal.

Recomendacion:

- Montar solo `wp-content` como volumen del proyecto.
- Evitar modificar el core de WordPress.
- Gestionar plugins y tema de forma controlada.

### 4.7 phpMyAdmin

Responsabilidades:

- Gestion visual de MariaDB en desarrollo.
- Revision de tablas, usuarios, migraciones y datos.

Recomendacion:

- Activarlo por defecto solo en desarrollo.
- No exponerlo publicamente en produccion.
- Si se necesita en servidor real, protegerlo con VPN, basic auth o reglas de firewall.

## 5. Redes y volumenes Docker

Redes recomendadas:

- `public`: red donde estara Nginx.
- `internal`: red privada para frontend, backend, bases de datos, WordPress y herramientas.

Volumenes recomendados:

- `postgres_data`
- `mariadb_data`
- `wordpress_data`
- `backend_storage`

Los servicios de base de datos deben persistir informacion en volumenes. Los contenedores de aplicacion deben poder reconstruirse sin perder datos.

## 6. Variables de entorno

Crear un `.env.example` con valores de ejemplo y mantener el `.env` real fuera de Git.

Variables minimas:

```env
APP_ENV=local
APP_DOMAIN=swimmingup
APP_URL=http://swimmingup

POSTGRES_DB=swimmingup_auth
POSTGRES_USER=swimmingup_auth_user
POSTGRES_PASSWORD=change_me

MARIADB_DATABASE=swimmingup_app
MARIADB_USER=swimmingup_app_user
MARIADB_PASSWORD=change_me
MARIADB_ROOT_PASSWORD=change_me

WORDPRESS_DB_NAME=swimmingup_wordpress
WORDPRESS_DB_USER=swimmingup_wp_user
WORDPRESS_DB_PASSWORD=change_me

PHPMYADMIN_PORT=8081
```

Recomendacion:

- No subir nunca `.env`.
- Usar secretos externos o variables del proveedor en produccion.
- Cambiar todas las contrasenas antes de desplegar.

## 7. Orden de desarrollo recomendado

1. Definir la estructura base de carpetas.
2. Crear `compose.yaml` con todos los servicios.
3. Crear los Dockerfile de Nginx, frontend, backend y WordPress.
4. Configurar Nginx como proxy inverso.
5. Crear `.env.example`.
6. Crear el esqueleto de React.
7. Crear el proyecto Laravel. Estado: completado en `backend/`.
8. Configurar las conexiones de Laravel con PostgreSQL y MariaDB. Estado: base preparada con MariaDB como conexion principal y `auth_pgsql` como conexion secundaria.
9. Crear las migraciones iniciales.
10. Configurar WordPress con su base de datos separada.
11. Anadir phpMyAdmin para desarrollo.
12. Documentar comandos de uso en README.
13. Definir modelos, roles y permisos.
14. Implementar autenticacion.
15. Construir la API.
16. Construir las pantallas principales del frontend.
17. Integrar el blog en `/blog`.
18. Preparar configuracion de produccion.
19. Preparar copias de seguridad.
20. Ejecutar pruebas solo cuando el director del proyecto lo autorice.

## 8. Modelo funcional inicial

Entidades principales recomendadas:

- Usuarios.
- Roles.
- Nadadores.
- Tutores o padres.
- Entrenadores.
- Grupos de entrenamiento.
- Temporadas.
- Horarios.
- Asistencias.
- Competiciones.
- Pruebas.
- Tiempos.
- Informes de rendimiento.
- Logros.
- Metas o desafios.
- Publicaciones del blog.

Consejo:

- Aunque PostgreSQL gestione usuarios y credenciales, Laravel debe mantener una relacion clara entre usuario autenticado y perfil funcional del sistema.
- Conviene decidir pronto si WordPress sera independiente o si compartira algun tipo de inicio de sesion con la aplicacion principal. Para reducir complejidad inicial, recomiendo mantenerlo independiente.

## 9. Seguridad

Medidas recomendadas desde el principio:

- No exponer bases de datos fuera de Docker.
- No exponer phpMyAdmin en produccion.
- Usar HTTPS en servidor real.
- Separar credenciales por servicio.
- Aplicar CORS de forma restrictiva.
- Validar todas las entradas en Laravel.
- Usar migraciones y seeders controlados.
- Crear backups periodicos de MariaDB y PostgreSQL.
- Configurar permisos por rol desde el inicio.

## 10. Despliegue en maquina virtual o EC2

Pasos generales:

1. Instalar Docker y Docker Compose.
2. Clonar el repositorio.
3. Crear `.env` a partir de `.env.example`.
4. Ajustar dominios, puertos y contrasenas.
5. Levantar servicios con `docker compose up -d`.
6. Ejecutar migraciones de Laravel cuando proceda.
7. Configurar HTTPS con certificados.
8. Configurar firewall para exponer solo HTTP/HTTPS y SSH.
9. Configurar backups.
10. Documentar procedimiento de actualizacion.

Recomendacion para AWS EC2:

- Usar security groups para limitar puertos.
- Mantener bases de datos sin exposicion publica.
- Considerar RDS en una fase futura si el proyecto crece.
- Usar Elastic IP o dominio propio para estabilidad.

## 11. Comandos previstos

Estos comandos son orientativos. No se ejecutaran pruebas ni builds sin permiso explicito.

```bash
docker compose up -d
docker compose down
docker compose logs -f
docker compose exec backend php artisan migrate
docker compose exec backend php artisan db:seed
docker compose exec frontend npm run dev
```

## 12. Consejos tecnicos para mejorar la estructura

- Anadir `docs/` desde el inicio para decisiones tecnicas, API y despliegue.
- Crear `.env.example` cuanto antes para evitar configuraciones implicitas.
- Usar nombres de servicios claros en Docker: `nginx`, `frontend`, `backend`, `postgres`, `mariadb`, `wordpress`, `phpmyadmin`.
- Separar desarrollo y produccion mediante perfiles de Compose o archivos complementarios si el proyecto crece.
- Mantener phpMyAdmin como herramienta de desarrollo, no como dependencia de produccion.
- Considerar pgAdmin si se quiere gestionar PostgreSQL visualmente.
- Evitar que WordPress comparta la misma base exacta que Laravel; mejor dos bases dentro de MariaDB.
- Mantener la API Laravel como fuente principal de verdad para la aplicacion.
- Evitar acoplar React directamente a WordPress salvo para consumir contenido publico.
- Preparar una politica de backups antes de introducir datos reales.

## 13. Proximos pasos inmediatos

Cuando el director del proyecto lo autorice, el siguiente paso sera crear la estructura fisica de carpetas y los archivos base:

- `compose.yaml`
- Dockerfile de Nginx.
- Dockerfile de React.
- Dockerfile de Laravel.
- Dockerfile de WordPress.
- Configuracion inicial de Nginx.
- `.env.example`.
- Carpetas `frontend/`, `backend/`, `wordpress/`, `database/`, `docker/` y `docs/`.

Estado actual:

- Frontend React/Vite creado en `frontend/`.
- Backend Laravel creado en `backend/`.
- Ruta de salud inicial disponible en `GET /api/health`.

No se ejecutaran pruebas, builds, instalaciones ni levantamiento de contenedores sin permiso explicito.
