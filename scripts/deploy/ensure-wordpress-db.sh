#!/bin/sh
set -eu

required_vars="MARIADB_ROOT_PASSWORD WORDPRESS_DB_NAME WORDPRESS_DB_USER WORDPRESS_DB_PASSWORD"

for var_name in $required_vars; do
    eval "value=\${$var_name:-}"

    if [ -z "$value" ]; then
        echo "Missing required variable: $var_name" >&2
        exit 1
    fi
done

sql_string() {
    printf "%s" "$1" | sed "s/'/''/g"
}

sql_identifier() {
    printf "%s" "$1" | sed 's/`/``/g'
}

db_name="$(sql_identifier "$WORDPRESS_DB_NAME")"
db_user="$(sql_string "$WORDPRESS_DB_USER")"
db_password="$(sql_string "$WORDPRESS_DB_PASSWORD")"

until mariadb -h mariadb -uroot -p"${MARIADB_ROOT_PASSWORD}" -e "SELECT 1" >/dev/null 2>&1; do
    sleep 2
done

mariadb -h mariadb -uroot -p"${MARIADB_ROOT_PASSWORD}" <<SQL
CREATE DATABASE IF NOT EXISTS \`${db_name}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '${db_user}'@'%' IDENTIFIED BY '${db_password}';
ALTER USER '${db_user}'@'%' IDENTIFIED BY '${db_password}';
GRANT ALL PRIVILEGES ON \`${db_name}\`.* TO '${db_user}'@'%';
FLUSH PRIVILEGES;
SQL
