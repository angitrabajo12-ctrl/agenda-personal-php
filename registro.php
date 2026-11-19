<?php
require_once __DIR__ . '/sesion.php';

$listaErrores = [];
$nombre = '';
$correo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarToken();

    $nombre = entrada('nombre');
    $correo = strtolower(entrada('correo'));

    $password = is_string($_POST['password'] ?? null)
        ? $_POST['password']
        : '';

    $confirmacion = is_string($_POST['confirmacion'] ?? null)
        ? $_POST['confirmacion']
        : '';

    if ($nombre === '' || strlen($nombre) > 150) {
        $listaErrores[] = 'Ingrese un nombre válido, de hasta 150 bytes.';
    }

    if (
        !filter_var($correo, FILTER_VALIDATE_EMAIL)
        || strlen($correo) > 190
    ) {
        $listaErrores[] = 'Ingrese un correo válido.';
    }

    if (
        strlen($password) < 8
        || strlen($password) > 72
        || strpos($password, "\0") !== false
    ) {
        $listaErrores[] =
            'Use una contraseña de 8 a 72 bytes sin caracteres nulos.';
    }

    if ($password !== $confirmacion) {
        $listaErrores[] = 'Las contraseñas no coinciden.';
    }

    if (!$listaErrores) {
        $consulta = conexion()->prepare(
            'SELECT id FROM usuarios WHERE correo = ?'
        );

        $consulta->execute([$correo]);

        if ($consulta->fetch()) {
            $listaErrores[] = 'Este correo ya está registrado.';
        } else {
            try {
                $consulta = conexion()->prepare(
                    'INSERT INTO usuarios (nombre, correo, password)
                     VALUES (?, ?, ?)'
                );

                $consulta->execute([
                    $nombre,
                    $correo,
                    password_hash($password, PASSWORD_DEFAULT)
                ]);

                mensaje(
                    'Cuenta creada correctamente. Ya puede iniciar sesión.'
                );

                redirigir('login.php');
            } catch (PDOException $error) {
                if (($error->errorInfo[1] ?? 0) == 1062) {
                    $listaErrores[] = 'Este correo ya está registrado.';
                } else {
                    throw $error;
                }
            }
        }
    }
}

inicio('Crear una cuenta');
errores($listaErrores);
?>

<p>Organice sus contactos en un espacio privado.</p>

<form method="post" class="formulario">
    <?php campoToken(); ?>

    <label for="nombre">Nombre completo</label>
    <input
        id="nombre"
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
        De 8 a 72 bytes. Los caracteres especiales pueden ocupar más de un byte.
    </small>

    <label for="confirmacion">Confirmar contraseña</label>
    <input
        id="confirmacion"
        type="password"
        name="confirmacion"
        maxlength="72"
        autocomplete="new-password"
        required
    >

    <button type="submit">Registrarse</button>
</form>

<p>
    ¿Ya tiene cuenta?
    <a href="login.php">Iniciar sesión</a>
</p>

<?php fin(); ?>
