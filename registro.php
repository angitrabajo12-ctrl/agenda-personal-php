<?php
require_once __DIR__ . '/sesion.php';

$errores = [];
$nombre = '';
$correo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarToken();

    $nombre = entrada('nombre');
    $correo = strtolower(entrada('correo'));

    $password = $_POST['password'] ?? '';
    $confirmacion = $_POST['confirmacion'] ?? '';

    $password = is_string($password) ? $password : '';
    $confirmacion = is_string($confirmacion)
        ? $confirmacion
        : '';

    if ($nombre === '' || strlen($nombre) > 150) {
        $errores[] = 'Ingrese un nombre válido, de hasta 150 bytes.';
    }

    if (
        !filter_var($correo, FILTER_VALIDATE_EMAIL)
        || strlen($correo) > 190
    ) {
        $errores[] = 'Ingrese un correo electrónico válido.';
    }

    if (
        strlen($password) < 8
        || strlen($password) > 72
        || strpos($password, "\0") !== false
    ) {
        $errores[] =
            'La contraseña debe tener entre 8 y 72 bytes '
            . 'y no contener caracteres nulos.';
    }

    if ($password !== $confirmacion) {
        $errores[] = 'Las contraseñas no coinciden.';
    }

    if (!$errores) {
        $consulta = $conexion->prepare(
            'SELECT id FROM usuarios WHERE correo = ?'
        );

        $consulta->execute([$correo]);

        if ($consulta->fetch()) {
            $errores[] = 'Este correo ya está registrado.';
        } else {
            $hash = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            try {
                $consulta = $conexion->prepare(
                    'INSERT INTO usuarios
                        (nombre, correo, password)
                     VALUES (?, ?, ?)'
                );

                $consulta->execute([
                    $nombre,
                    $correo,
                    $hash
                ]);

                redirigir('login.php?registro=exitoso');
            } catch (PDOException $error) {
                if (($error->errorInfo[1] ?? 0) == 1062) {
                    $errores[] = 'Este correo ya está registrado.';
                } else {
                    throw $error;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">
    <title>Crear una cuenta</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<main class="contenedor pequeno">
    <h1>Crear una cuenta</h1>
    <p>Regístrese para administrar sus contactos.</p>

    <?php if ($errores): ?>
        <div class="alerta" role="alert">
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= escapar($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="post" action="registro.php">
        <?php campoToken(); ?>

        <label for="nombre">Nombre completo</label>
        <input
            id="nombre"
            type="text"
            name="nombre"
            maxlength="150"
            autocomplete="name"
            value="<?= escapar($nombre) ?>"
            required
        >

        <label for="correo">Correo electrónico</label>
        <input
            id="correo"
            type="email"
            name="correo"
            maxlength="190"
            autocomplete="email"
            value="<?= escapar($correo) ?>"
            required
        >

        <label for="password">Contraseña</label>
        <input
            id="password"
            type="password"
            name="password"
            minlength="8"
            maxlength="72"
            autocomplete="new-password"
            required
        >

        <small>
            Entre 8 y 72 bytes. Los caracteres especiales
            pueden ocupar más de un byte.
        </small>

        <label for="confirmacion">Confirmar contraseña</label>
        <input
            id="confirmacion"
            type="password"
            name="confirmacion"
            minlength="8"
            maxlength="72"
            autocomplete="new-password"
            required
        >

        <button type="submit">Registrarse</button>
    </form>

    <p>
        ¿Ya tiene una cuenta?
        <a href="login.php">Iniciar sesión</a>
    </p>
</main>
</body>
</html>