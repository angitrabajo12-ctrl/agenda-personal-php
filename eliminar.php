<?php
require_once __DIR__ . '/sesion.php';

$usuario = exigirUsuario();
$idUsuario = (int) $usuario['id'];
$id = obtenerIdContacto();

$contacto = obtenerContacto($id, $idUsuario);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarToken();

    $consulta = conexion()->prepare(
        'DELETE FROM contactos
         WHERE id = ? AND id_usuario = ?'
    );

    $consulta->execute([$id, $idUsuario]);

    mensaje('Contacto eliminado.');
    redirigir('panel.php');
}

inicio('Confirmar eliminación');
?>

<p>
    ¿Desea eliminar a
    <strong><?= escapar($contacto['nombre']) ?></strong>
    de su agenda?
</p>

<p>Esta acción no se puede deshacer.</p>

<form method="post">
    <?php campoToken(); ?>

    <div class="barra">
        <button class="peligro">Sí, eliminar</button>
        <a href="panel.php">Cancelar</a>
    </div>
</form>

<?php fin(); ?>
