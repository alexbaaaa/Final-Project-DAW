# Despliegue

## Desarrollo local

1. Crear `.env` a partir de `.env.example`.
2. Ajustar credenciales locales.
3. Hacer que el dominio `swimmingup` resuelva hacia la maquina Docker.
4. Crear los proyectos reales de React y Laravel cuando se autorice.
5. Levantar servicios con Docker Compose cuando se autorice.

Comando previsto:

```bash
docker compose --profile dev up -d
```

En desarrollo local, una opcion habitual es anadir una entrada en el archivo `hosts` del sistema:

```text
127.0.0.1 swimmingup
```

## Produccion

1. Instalar Docker y Docker Compose en la maquina.
2. Clonar el repositorio.
3. Crear `.env` con credenciales reales.
4. Exponer solo HTTP/HTTPS y SSH en firewall o security group.
5. Levantar servicios sin el perfil `dev`.
6. Configurar HTTPS.
7. Programar backups de MariaDB y PostgreSQL.

Comando previsto:

```bash
docker compose up -d
```

## Notas

- phpMyAdmin no debe exponerse publicamente en produccion.
- Las bases de datos no deben publicar puertos al host.
- Antes de introducir datos reales, definir politica de backups y restauracion.
