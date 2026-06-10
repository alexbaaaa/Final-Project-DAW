# Despliegue

## Desarrollo local

1. Crear `.env` a partir de `.env.example`.
2. Ajustar credenciales locales.
3. Hacer que el dominio `swimmingup` resuelva hacia la maquina Docker.
4. Crear el proyecto Laravel cuando se autorice.
5. Levantar servicios con Docker Compose cuando se autorice.

Comando previsto:

```bash
docker compose --profile dev up -d
```

En desarrollo local, una opcion habitual es anadir una entrada en el archivo `hosts` del sistema:

```text
127.0.0.1 swimmingup
```

## Produccion en Ubuntu/EC2

1. Instalar Docker Engine y el plugin de Docker Compose en Ubuntu.
2. Clonar el repositorio en la EC2.
3. Crear el archivo de entorno de produccion:

```bash
cp .env.production.example .env.production
```

4. Editar `.env.production` y cambiar todas las credenciales `change_me_*`.
5. Generar `APP_KEY` y guardarla en `.env.production`:

```bash
printf 'base64:%s\n' "$(openssl rand -base64 32)"
```

6. Comprobar que el security group de AWS permite SSH y HTTP.
7. Levantar la pila de produccion:

```bash
docker compose -f compose.prod.yaml --env-file .env.production up -d --build
```

8. Ejecutar migraciones cuando corresponda:

```bash
docker compose -f compose.prod.yaml --env-file .env.production exec backend php artisan migrate --force
```

Para el primer despliegue tambien se puede usar `RUN_MIGRATIONS=true` en `.env.production` y volverlo a `false` despues.

## Notas

- phpMyAdmin no se expone publicamente en produccion. Si se necesita depurar, levantarlo con el perfil `debug` y acceder por tunel SSH al puerto local configurado.
- Las bases de datos no deben publicar puertos al host.
- Antes de introducir datos reales, definir politica de backups y restauracion.
