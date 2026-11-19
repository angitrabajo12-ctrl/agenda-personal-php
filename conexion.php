<?php
declare(strict_types=1);

ini_set('display_errors', '0');

set_exception_handler(function (Throwable $error): void {
    error_log((string) $error);
    http_response_code(500);

    exit(
        'No se pudo completar la operación. '
        . 'Revise la conexión y la instalación de la base de datos.'
    );
});

function conexion(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $archivo = __DIR__ . '/config.local.php';

        if (!is_file($archivo)) {
            throw new RuntimeException(
                'Falta configurar config.local.php.'
            );
        }

        $configuracion = require $archivo;

        $pdo = new PDO(
            "mysql:host={$configuracion['host']};"
            . "port={$configuracion['port']};"
            . "dbname={$configuracion['database']};"
            . "charset=utf8mb4",
            $configuracion['user'],
            $configuracion['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
    }

    return $pdo;
}
