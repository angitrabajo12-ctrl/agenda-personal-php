<?php
declare(strict_types=1);

ini_set('display_errors', '0');

set_exception_handler(function (Throwable $error): void {
    error_log((string) $error);
    http_response_code(500);
    exit('No se pudo completar la operación. Intente nuevamente.');
});

$servidor = '127.0.0.1';
$puerto = '3306';
$base = 'agenda_personal';
$usuario = 'root';
$clave = '';

$conexion = new PDO(
    "mysql:host=$servidor;port=$puerto;dbname=$base;charset=utf8mb4",
    $usuario,
    $clave,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]
);