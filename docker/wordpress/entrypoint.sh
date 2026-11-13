#!/bin/sh
set -e

if [ -f /var/www/html/wp-config.php ]; then
    php <<'PHP'
<?php

$file = '/var/www/html/wp-config.php';
$config = file_get_contents($file);

if ($config === false) {
    exit(0);
}

$constants = [
    'DB_NAME' => getenv('WORDPRESS_DB_NAME') ?: 'wordpress',
    'DB_USER' => getenv('WORDPRESS_DB_USER') ?: 'example username',
    'DB_PASSWORD' => getenv('WORDPRESS_DB_PASSWORD') ?: 'example password',
    'DB_HOST' => getenv('WORDPRESS_DB_HOST') ?: 'mysql',
];

foreach ($constants as $constant => $value) {
    $replacement = "define( '".$constant."', ".var_export($value, true)." );";
    $pattern = "/define\\(\\s*(['\"])".$constant."\\1\\s*,\\s*[^;]*\\);/";
    $config = preg_replace($pattern, $replacement, $config, 1);
}

file_put_contents($file, $config);
PHP
fi

exec docker-entrypoint.sh "$@"
