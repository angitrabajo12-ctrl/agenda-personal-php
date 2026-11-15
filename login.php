<?php
require_once __DIR__ . '/sesion.php';

if (!empty($_SESSION['id_usuario'])) {
    redirigir('panel.php');
}

$mensaje = '';
$correo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarToken();

    $correo = strtolower(entrada('correo'));

    $password = $_POST['password'] ?? '';
    $password = is_string($password) ? $password : '';

    if (
        !filter_var($correo, FILTER_VALIDATE_EMAIL)
        || strlen($correo) > 190
        || $password === ''
        || strlen($password) > 72
        || strpos($password, "\0") !== false
    ) {
        $mensaje = 'Correo o contraseña incorrectos.';
    } else {
        $consulta = $conexion->prepare(
            'SELECT id, password
             FROM usuarios
             WHERE correo = ?'
        );

        $consulta->execute([$correo]);
        $usuario = $consulta->fetch();

        if (
            $usuario
            && password_verify(
                $password,
                $usuario['password']
            )
        ) {
            session_regenerate_id(true);

            $_SESSION = [
                'id_usuario' => (int) $usuario['id'],
                'csrf' => bin2hex(random_bytes(32))
            ];

            redirigir('panel.php');
        }

        $mensaje = 'Correo o contraseña incorrectos.';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">
    <title>Iniciar sesión</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<main class="contenedor pequeno">
    <h1>Mi agenda personal</h1>
    <p>Ingrese para consultar y administrar sus contactos.</p>

    <?php if (($_GET['registro'] ?? '') === 'exitoso'): ?>
        <p class="exito">
            Cuenta creada correctamente. Ya puede iniciar sesión.
        </p>
    <?php endif; ?>

    <?php if ($mensaje !== ''): ?>
        <p class="alerta" role="alert">
            <?= escapar($mensaje) ?>
        </p>
    <?php endif; ?>

    <form method="post" action="login.php">
        <?php campoToken(); ?>

        <label for="correo">Correo electrónico</label>
        <input
            id="correo"
            type="email"
            name="correo"
            maxlength="190"
            autocomplete="username"
            value="<?= escapar($correo) ?>"
            required
        >

        <label for="password">Contraseña</label>
        <input
            id="password"
            type="password"
            name="password"
            maxlength="72"
            autocomplete="current-password"
            required
        >

        <button type="submit">Iniciar sesión</button>
    </form>

    <p>
        ¿No tiene una cuenta?
        <a href="registro.php">Registrarse</a>
    </p>
</main>
</body>
</html>