#!/bin/sh
set -eu

COMPOSE_FILE="${COMPOSE_FILE:-compose.prod.yaml}"
ENV_FILE="${ENV_FILE:-.env.production}"

if [ ! -f "$COMPOSE_FILE" ]; then
    echo "Missing compose file: $COMPOSE_FILE" >&2
    exit 1
fi

if [ ! -f "$ENV_FILE" ]; then
    echo "Missing env file: $ENV_FILE" >&2
    exit 1
fi

compose() {
    docker compose -f "$COMPOSE_FILE" --env-file "$ENV_FILE" "$@"
}

check() {
    name="$1"
    shift

    printf '%s... ' "$name"

    if "$@" >/tmp/swimmingup-db-check.out 2>&1; then
        echo "OK"
        return 0
    fi

    echo "FAIL"
    sed 's/^/  /' /tmp/swimmingup-db-check.out >&2
    return 1
}

status=0

check "MariaDB app credentials" \
    compose exec -T mariadb sh -c 'mariadb -h 127.0.0.1 -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" "$MARIADB_DATABASE" -e "SELECT 1" >/dev/null' \
    || status=1

check "MariaDB WordPress credentials" \
    compose exec -T mariadb sh -c 'mariadb -h 127.0.0.1 -u"$WORDPRESS_DB_USER" -p"$WORDPRESS_DB_PASSWORD" "$WORDPRESS_DB_NAME" -e "SELECT 1" >/dev/null' \
    || status=1

check "PostgreSQL auth credentials" \
    compose exec -T postgres sh -c 'PGPASSWORD="$POSTGRES_PASSWORD" psql -h 127.0.0.1 -U "$POSTGRES_USER" -d "$POSTGRES_DB" -Atc "SELECT 1" >/dev/null' \
    || status=1

check "Backend to MariaDB" \
    compose exec -T backend php -r '$dsn = "mysql:host=".getenv("DB_HOST").";port=".getenv("DB_PORT").";dbname=".getenv("DB_DATABASE").";charset=utf8mb4"; new PDO($dsn, getenv("DB_USERNAME"), getenv("DB_PASSWORD"), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); echo "OK\n";' \
    || status=1

check "Backend to PostgreSQL auth" \
    compose exec -T backend php -r '$dsn = "pgsql:host=".getenv("AUTH_DB_HOST").";port=".getenv("AUTH_DB_PORT").";dbname=".getenv("AUTH_DB_DATABASE"); new PDO($dsn, getenv("AUTH_DB_USERNAME"), getenv("AUTH_DB_PASSWORD"), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); echo "OK\n";' \
    || status=1

check "WordPress to MariaDB" \
    compose exec -T wordpress php -r '$host = getenv("WORDPRESS_DB_HOST") ?: "mariadb:3306"; $parts = explode(":", $host, 2); $hostname = $parts[0]; $port = isset($parts[1]) ? (int) $parts[1] : 3306; $db = new mysqli($hostname, getenv("WORDPRESS_DB_USER"), getenv("WORDPRESS_DB_PASSWORD"), getenv("WORDPRESS_DB_NAME"), $port); if ($db->connect_errno) { fwrite(STDERR, $db->connect_error.PHP_EOL); exit(1); } echo "OK\n";' \
    || status=1

rm -f /tmp/swimmingup-db-check.out
exit "$status"
