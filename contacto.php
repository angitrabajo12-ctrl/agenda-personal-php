<?php
require_once __DIR__ . '/sesion.php';

$usuario = exigirUsuario($conexion);
$idUsuario = (int) $usuario['id'];

$id = isset($_GET['id'])
    ? obtenerIdContacto()
    : null;

$contacto = [
    'nombre' => '',
    'telefono' => '',
    'correo' => '',
    'ciudad' => ''
];

if ($id !== null) {
    $contacto = obtenerContacto(
        $conexion,
        $id,
        $idUsuario
    );
}

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarToken();

    $contacto['nombre'] = entrada('nombre');
    $contacto['telefono'] = entrada('telefono');
    $contacto['correo'] = entrada('correo');
    $contacto['ciudad'] = entrada('ciudad');

    if (
        $contacto['nombre'] === ''
        || strlen($contacto['nombre']) > 150
    ) {
        $errores[] = 'Ingrese un nombre válido, de hasta 150 bytes.';
    }

    $digitos = preg_replace(
        '/[^0-9]/',
        '',
        $contacto['telefono']
    );

    if (
        !preg_match(
            '/^[+0-9() .-]{6,30}$/D',
            $contacto['telefono']
        )
        || strlen($digitos) < 6
    ) {
        $errores[] =
            'Ingrese un teléfono válido: mínimo 6 dígitos '
            . 'y máximo 30 caracteres.';
    }

    if (
        !filter_var(
            $contacto['correo'],
            FILTER_VALIDATE_EMAIL
        )
        || strlen($contacto['correo']) > 190
    ) {
        $errores[] = 'Ingrese un correo electrónico válido.';
    }

    if (
        $contacto['ciudad'] === ''
        || strlen($contacto['ciudad']) > 100
    ) {
        $errores[] = 'Ingrese una ciudad válida, de hasta 100 bytes.';
    }

    if (!$errores) {
        if ($id !== null) {
            $consulta = $conexion->prepare(
                'UPDATE contactos
                 SET nombre = ?,
                     telefono = ?,
                     correo = ?,
                     ciudad = ?
                 WHERE id = ? AND id_usuario = ?'
            );

            $consulta->execute([
                $contacto['nombre'],
                $contacto['telefono'],
                $contacto['correo'],
                $contacto['ciudad'],
                $id,
                $idUsuario
            ]);
        } else {
            $consulta = $conexion->prepare(
                'INSERT INTO contactos
                    (id_usuario, nombre, telefono, correo, ciudad)
                 VALUES (?, ?, ?, ?, ?)'
            );

            $consulta->execute([
                $idUsuario,
                $contacto['nombre'],
                $contacto['telefono'],
                $contacto['correo'],
                $contacto['ciudad']
            ]);
        }

        redirigir('panel.php');
    }
}

$titulo = $id !== null
    ? 'Editar contacto'
    : 'Nuevo contacto';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">
    <title><?= escapar($titulo) ?></title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<main class="contenedor pequeno">
    <h1><?= escapar($titulo) ?></h1>

    <?php if ($errores): ?>
        <div class="alerta" role="alert">
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= escapar($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post">
        <?php campoToken(); ?>

        <label for="nombre">Nombre del contacto</label>
        <input
            id="nombre"
            type="text"
            name="nombre"
            maxlength="150"
            value="<?= escapar($contacto['nombre']) ?>"
            required
        >

        <label for="telefono">Teléfono</label>
        <input
            id="telefono"
            type="tel"
            name="telefono"
            maxlength="30"
            value="<?= escapar($contacto['telefono']) ?>"
            required
        >

        <label for="correo">Correo electrónico</label>
        <input
            id="correo"
            type="email"
            name="correo"
            maxlength="190"
            value="<?= escapar($contacto['correo']) ?>"
            required
        >

        <label for="ciudad">Ciudad</label>
        <input
            id="ciudad"
            type="text"
            name="ciudad"
            maxlength="100"
            value="<?= escapar($contacto['ciudad']) ?>"
            required
        >

        <div class="barra">
            <button type="submit">Guardar contacto</button>
            <a href="panel.php">Cancelar</a>
        </div>
    </form>
</main>
</body>
</html>