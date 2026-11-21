<?php
require_once __DIR__ . '/sesion.php';

redirigir(
    empty($_SESSION['id_usuario'])
        ? 'login.php'
        : 'panel.php'
);
