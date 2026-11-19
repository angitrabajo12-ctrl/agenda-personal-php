<?php
require_once __DIR__ . '/sesion.php';

exigirUsuario();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Método no permitido.');
}

verificarToken();

$_SESSION = [];

$parametros = session_get_cookie_params();

setcookie(session_name(), '', [
    'expires' => time() - 3600,
    'path' => $parametros['path'],
    'domain' => $parametros['domain'],
    'secure' => $parametros['secure'],
    'httponly' => $parametros['httponly'],
    'samesite' => 'Lax'
]);

session_destroy();

redirigir('login.php');
