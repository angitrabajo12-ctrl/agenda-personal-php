<?php

declare(strict_types=1);

ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');

session_set_cookie_params([
    'path' => '/',
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS'])
        && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
]);

session_start();

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

header(
    "Content-Security-Policy: default-src 'self'; "
    . "style-src 'self'; form-action 'self'; "
    . "frame-ancestors 'none'; base-uri 'none'"
);

require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/consultas.php';

$_SESSION['csrf'] ??= bin2hex(random_bytes(32));

function escapar($texto): string
{
    return htmlspecialchars(
        (string) $texto,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

function entrada(string $campo): string
{
    $valor = $_POST[$campo] ?? '';

    return is_string($valor) ? trim($valor) : '';
}

function parametro(string $campo): string
{
    $valor = $_GET[$campo] ?? '';

    return is_string($valor) ? trim($valor) : '';
}

function redirigir(string $pagina): void
{
    header('Location: ' . $pagina, true, 303);
    exit;
}

function campoToken(): void
{
    echo '<input type="hidden" name="csrf" value="'
        . escapar($_SESSION['csrf'])
        . '">';
}

function verificarToken(): void
{
    $token = $_POST['csrf'] ?? '';

    if (
        !is_string($token)
        || !hash_equals($_SESSION['csrf'], $token)
    ) {
        http_response_code(403);
        exit('Formulario inválido. Regrese y recargue la página.');
    }
}

function exigirUsuario(): array
{
    if (empty($_SESSION['id_usuario'])) {
        redirigir('login.php');
    }

    $consulta = conexion()->prepare(
        'SELECT id, nombre, correo
         FROM usuarios
         WHERE id = ?'
    );

    $consulta->execute([$_SESSION['id_usuario']]);

    $usuario = $consulta->fetch();

    if (!$usuario) {
        $_SESSION = [];
        session_destroy();

        redirigir('login.php');
    }

    return $usuario;
}

function idPositivo($valor): ?int
{
    if (!is_string($valor)) {
        return null;
    }

    $id = filter_var(
        $valor,
        FILTER_VALIDATE_INT,
        ['options' => ['min_range' => 1]]
    );

    return $id === false ? null : (int) $id;
}

function obtenerIdContacto(): int
{
    $id = idPositivo($_GET['id'] ?? null);

    if ($id === null) {
        http_response_code(404);
        exit('Contacto no encontrado.');
    }

    return $id;
}

function obtenerContacto(int $id, int $idUsuario): array
{
    $consulta = conexion()->prepare(
        'SELECT *
         FROM contactos
         WHERE id = ? AND id_usuario = ?'
    );

    $consulta->execute([$id, $idUsuario]);

    $contacto = $consulta->fetch();

    if (!$contacto) {
        http_response_code(404);
        exit('Contacto no encontrado.');
    }

    return $contacto;
}

function categorias(): array
{
    return conexion()->query(
        'SELECT id, nombre
         FROM categorias
         ORDER BY id'
    )->fetchAll();
}

function mensaje(string $texto): void
{
    $_SESSION['aviso'] = $texto;
}

function inicio(string $titulo): void
{
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">

        <meta
            name="viewport"
            content="width=device-width, initial-scale=1"
        >

        <title><?= escapar($titulo) ?> · Mi agenda</title>

        <link rel="stylesheet" href="estilos.css">
    </head>
    <body>
    <main class="contenedor">
        <header class="cabecera">
            <a class="marca" href="index.php">Mi agenda</a>
            <span>CONTACTOS · ESPACIO PERSONAL</span>
        </header>

        <h1><?= escapar($titulo) ?></h1>
    <?php

    if (isset($_SESSION['aviso'])) {
        echo '<p class="exito" role="status">'
            . escapar($_SESSION['aviso'])
            . '</p>';

        unset($_SESSION['aviso']);
    }
}

function errores(array $lista): void
{
    if (!$lista) {
        return;
    }

    echo '<div class="alerta" role="alert"><ul>';

    foreach ($lista as $error) {
        echo '<li>' . escapar($error) . '</li>';
    }

    echo '</ul></div>';
}

function fin(): void
{
    echo '<footer>'
        . 'Agenda personal · PHP + MySQL · Versión 2'
        . '</footer></main></body></html>';
}
