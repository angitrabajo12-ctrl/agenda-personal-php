<?php
require_once __DIR__ . '/sesion.php';

if (!empty($_SESSION['id_usuario'])) {
    redirigir('panel.php');
}

$listaErrores = [];
$correo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarToken();

    $correo = strtolower(entrada('correo'));

    $password = is_string($_POST['password'] ?? null)
        ? $_POST['password']
        : '';

    if (
        filter_var($correo, FILTER_VALIDATE_EMAIL)
        && strlen($correo) <= 190
        && $password !== ''
        && strlen($password) <= 72
        && strpos($password, "\0") === false
    ) {
        $consulta = conexion()->prepare(
            'SELECT id, password FROM usuarios WHERE correo = ?'
        );

        $consulta->execute([$correo]);
        $usuario = $consulta->fetch();

        if (
            $usuario
            && password_verify($password, $usuario['password'])
        ) {
            session_regenerate_id(true);

            $_SESSION = [
                'id_usuario' => (int) $usuario['id'],
                'csrf' => bin2hex(random_bytes(32))
            ];

            redirigir('panel.php');
        }
    }

    $listaErrores[] = 'Correo o contraseña incorrectos.';
}

inicio('Bienvenida a su agenda');
errores($listaErrores);
?>

<p>Sus contactos, organizados por categorías y favoritos.</p>

<form method="post" class="formulario">
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
    ¿Es su primera visita?
    <a href="registro.php">Crear una cuenta</a>
</p>

<?php fin(); ?>
