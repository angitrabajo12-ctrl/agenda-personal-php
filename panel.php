<?php
require_once __DIR__ . '/sesion.php';

$usuario = exigirUsuario();
$categorias = categorias();
$ordenes = ordenesPermitidos();

$qTexto = parametro('q');

if (strlen($qTexto) > 190) {
    http_response_code(400);
    exit('La búsqueda debe tener hasta 190 bytes.');
}

$orden = parametro('orden');

if (!array_key_exists($orden, $ordenes)) {
    $orden = 'nombre';
}

$categoria = parametro('categoria');
$ids = array_map('strval', array_column($categorias, 'id'));

if (
    $categoria !== ''
    && !in_array($categoria, $ids, true)
) {
    http_response_code(400);
    exit('Categoría inválida.');
}

$filtros = [
    'q' => $qTexto,
    'orden' => $orden,
    'categoria' => $categoria,
    'favoritos' => parametro('favoritos') === '1' ? '1' : ''
];

$urlPanel = 'panel.php?' . http_build_query($filtros);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarToken();

    if (entrada('accion') !== 'favorito') {
        http_response_code(400);
        exit('Acción inválida.');
    }

    $id = idPositivo($_POST['id'] ?? null);

    if ($id === null) {
        http_response_code(404);
        exit('Contacto no encontrado.');
    }

    obtenerContacto($id, (int) $usuario['id']);

    $consulta = conexion()->prepare(
        'UPDATE contactos
         SET favorito = 1 - favorito
         WHERE id = ? AND id_usuario = ?'
    );

    $consulta->execute([$id, $usuario['id']]);

    mensaje('Favorito actualizado.');
    redirigir($urlPanel);
}

[$sql, $valores] = consultaContactos(
    (int) $usuario['id'],
    $filtros
);

$consulta = conexion()->prepare($sql);
$consulta->execute($valores);
$contactos = $consulta->fetchAll();

inicio('Mis contactos');
?>

<section class="perfil">
    <div>
        <strong><?= escapar($usuario['nombre']) ?></strong>
        <span><?= escapar($usuario['correo']) ?></span>
    </div>

    <form method="post" action="logout.php">
        <?php campoToken(); ?>
        <button class="secundario">Cerrar sesión</button>
    </form>
</section>

<div class="barra">
    <p><?= count($contactos) ?> contacto(s) en esta vista</p>
    <a class="boton" href="contacto.php">+ Nuevo contacto</a>
</div>

<form method="get" action="panel.php" class="filtros">
    <div>
        <label for="q">Buscar por nombre, correo o ciudad</label>
        <input
            id="q"
            name="q"
            type="search"
            maxlength="190"
            placeholder="Ej.: Ana, @example.com o Quito"
            value="<?= escapar($qTexto) ?>"
        >
    </div>

    <div>
        <label for="orden">Ordenar por</label>
        <select id="orden" name="orden">
            <?php foreach ($ordenes as $clave => $datos): ?>
                <option
                    value="<?= escapar($clave) ?>"
                    <?= $orden === $clave ? 'selected' : '' ?>
                >
                    <?= escapar($datos[0]) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="categoria">Categoría</label>
        <select id="categoria" name="categoria">
            <option value="">Todas</option>

            <?php foreach ($categorias as $cat): ?>
                <option
                    value="<?= (int) $cat['id'] ?>"
                    <?= $categoria === (string) $cat['id']
                        ? 'selected'
                        : '' ?>
                >
                    <?= escapar($cat['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <label class="check">
        <input
            type="checkbox"
            name="favoritos"
            value="1"
            <?= $filtros['favoritos'] === '1' ? 'checked' : '' ?>
        >
        Solo favoritos
    </label>

    <div class="barra-filtros">
        <button>Aplicar</button>
        <a href="panel.php">Limpiar filtros</a>
    </div>
</form>

<?php if (!$contactos): ?>
    <p class="vacio">
        No hay contactos para esta vista.
        Puede agregar uno o limpiar los filtros.
    </p>
<?php else: ?>
    <div class="tabla-responsive">
        <table>
            <thead>
                <tr>
                    <th>Favorito</th>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th>Ciudad</th>
                    <th>Categoría</th>
                    <th>Fecha de creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>

            <tbody>
            <?php foreach ($contactos as $contacto): ?>
                <tr>
                    <td>
                        <form
                            method="post"
                            action="<?= escapar($urlPanel) ?>"
                        >
                            <?php campoToken(); ?>

                            <input
                                type="hidden"
                                name="accion"
                                value="favorito"
                            >

                            <input
                                type="hidden"
                                name="id"
                                value="<?= (int) $contacto['id'] ?>"
                            >

                            <button
                                class="estrella"
                                aria-label="<?= escapar(
                                    (
                                        (int) $contacto['favorito'] === 1
                                            ? 'Quitar de favoritos: '
                                            : 'Marcar favorito: '
                                    ) . $contacto['nombre']
                                ) ?>"
                                aria-pressed="<?=
                                    (int) $contacto['favorito'] === 1
                                        ? 'true'
                                        : 'false'
                                ?>"
                            >
                                <?= (int) $contacto['favorito'] === 1
                                    ? '★'
                                    : '☆' ?>
                            </button>
                        </form>
                    </td>

                    <td>
                        <strong>
                            <?= escapar($contacto['nombre']) ?>
                        </strong>
                    </td>

                    <td><?= escapar($contacto['telefono']) ?></td>
                    <td><?= escapar($contacto['correo']) ?></td>
                    <td><?= escapar($contacto['ciudad']) ?></td>

                    <td>
                        <span class="etiqueta">
                            <?= escapar($contacto['categoria_nombre']) ?>
                        </span>
                    </td>

                    <td>
                        <?= escapar($contacto['fecha_creacion']) ?>
                    </td>

                    <td class="acciones">
                        <a href="contacto.php?id=<?= (int) $contacto['id'] ?>">
                            Editar
                        </a>

                        <a
                            class="enlace-peligro"
                            href="eliminar.php?id=<?= (int) $contacto['id'] ?>"
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

<?php fin(); ?>
