<?php
require_once __DIR__ . '/sesion.php';

$usuario = exigirUsuario($conexion);

$consulta = $conexion->prepare(
    'SELECT id, nombre, telefono, correo, ciudad
     FROM contactos
     WHERE id_usuario = ?
     ORDER BY nombre, id'
);

$consulta->execute([$usuario['id']]);
$contactos = $consulta->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1">
    <title>Mis contactos</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<main class="contenedor">
    <header class="barra">
        <div>
            <h1>Mi agenda personal</h1>

            <p>
                <strong>Nombre:</strong>
                <?= escapar($usuario['nombre']) ?>
                <br>
                <strong>Correo:</strong>
                <?= escapar($usuario['correo']) ?>
            </p>
        </div>

        <form method="post" action="logout.php">
            <?php campoToken(); ?>

            <button type="submit" class="secundario">
                Cerrar sesión
            </button>
        </form>
    </header>

    <div class="barra">
        <h2>Mis contactos (<?= count($contactos) ?>)</h2>

        <a href="contacto.php" class="boton">
            + Nuevo contacto
        </a>
    </div>

    <?php if (!$contactos): ?>
        <p class="vacio">
            Todavía no tiene contactos registrados.
            Seleccione «Nuevo contacto» para comenzar.
        </p>
    <?php else: ?>
        <div class="tabla-responsive">
            <table>
                <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th>Ciudad</th>
                    <th>Acciones</th>
                </tr>
                </thead>

                <tbody>
                <?php foreach ($contactos as $contacto): ?>
                    <tr>
                        <td>
                            <?= escapar($contacto['nombre']) ?>
                        </td>
                        <td>
                            <?= escapar($contacto['telefono']) ?>
                        </td>
                        <td>
                            <?= escapar($contacto['correo']) ?>
                        </td>
                        <td>
                            <?= escapar($contacto['ciudad']) ?>
                        </td>
                        <td class="acciones">
                            <a
                                href="contacto.php?id=<?= (int) $contacto['id'] ?>"
                            >
                                Editar
                            </a>

                            <a
                                href="eliminar.php?id=<?= (int) $contacto['id'] ?>"
                                class="enlace-peligro"
                            >
                                Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>
</body>
</html>