<?php

if (getenv('DB_CONNECTION') === 'sqlite') {
    return;
}

$map = [
    'DB_HOST' => ['MYSQLHOST', 'MYSQL_HOST'],
    'DB_PORT' => ['MYSQLPORT', 'MYSQL_PORT'],
    'DB_DATABASE' => ['MYSQLDATABASE', 'MYSQL_DATABASE'],
    'DB_USERNAME' => ['MYSQLUSER', 'MYSQL_USER'],
    'DB_PASSWORD' => ['MYSQLPASSWORD', 'MYSQL_PASSWORD'],
    'DB_URL' => ['MYSQL_URL', 'DATABASE_URL'],
];

foreach ($map as $dbKey => $sources) {
    foreach ($sources as $source) {
        $value = getenv($source);

        if (! is_string($value) || $value === '') {
            continue;
        }

        putenv($dbKey.'='.$value);
        $_ENV[$dbKey] = $value;
        $_SERVER[$dbKey] = $value;
        break;
    }
}
