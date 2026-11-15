<?php
require_once __DIR__ . '/sesion.php';

$usuario = exigirUsuario($conexion);
$idUsuario = (int) $usuario['id'];
$id = obtenerIdContacto();

$contacto = obtenerContacto(
    $conexion,
    $id,
    $idUsuario
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarToken();

    $consulta = $conexion->prepare(
        'DELETE FROM contactos
         WHERE id = ? AND id_usuario = ?'
    );

    $consulta->execute([$id, $idUsuario]);

    redirigir('panel.php');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">
    <title>Eliminar contacto</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<main class="contenedor pequeno">
    <h1>Eliminar contacto</h1>

    <p>
        ¿Desea eliminar a
        <strong><?= escapar($contacto['nombre']) ?></strong>
        de su agenda?
    </p>

    <p>Esta acción no se puede deshacer.</p>

    <form method="post">
        <?php campoToken(); ?>

        <div class="barra">
            <button type="submit" class="peligro">
                Sí, eliminar
            </button>

            <a href="panel.php">Cancelar</a>
        </div>
    </form>
</main>
</body>
</html>
