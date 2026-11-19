<?php
require_once __DIR__ . '/sesion.php';

$usuario = exigirUsuario();
$idUsuario = (int) $usuario['id'];

$id = isset($_GET['id'])
    ? obtenerIdContacto()
    : null;

$categorias = categorias();

$contacto = $id !== null
    ? obtenerContacto($id, $idUsuario)
    : [
        'nombre' => '',
        'telefono' => '',
        'correo' => '',
        'ciudad' => '',
        'id_categoria' => '',
        'favorito' => 0
    ];

$listaErrores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarToken();

    foreach (
        ['nombre', 'telefono', 'correo', 'ciudad', 'id_categoria']
        as $campo
    ) {
        $contacto[$campo] = entrada($campo);
    }

    $contacto['favorito'] = entrada('favorito') === '1' ? 1 : 0;

    if (
        $contacto['nombre'] === ''
        || strlen($contacto['nombre']) > 150
    ) {
        $listaErrores[] = 'Ingrese un nombre válido, de hasta 150 bytes.';
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
        $listaErrores[] =
            'Ingrese un teléfono de 6 a 30 caracteres '
            . 'con al menos 6 dígitos.';
    }

    if (
        !filter_var($contacto['correo'], FILTER_VALIDATE_EMAIL)
        || strlen($contacto['correo']) > 190
    ) {
        $listaErrores[] = 'Ingrese un correo válido.';
    }

    if (
        $contacto['ciudad'] === ''
        || strlen($contacto['ciudad']) > 100
    ) {
        $listaErrores[] = 'Ingrese una ciudad válida, de hasta 100 bytes.';
    }

    $ids = array_map(
        'strval',
        array_column($categorias, 'id')
    );

    if (!in_array($contacto['id_categoria'], $ids, true)) {
        $listaErrores[] = 'Seleccione una categoría válida.';
    }

    if (!$listaErrores) {
        $valores = [
            $contacto['nombre'],
            $contacto['telefono'],
            $contacto['correo'],
            $contacto['ciudad'],
            $contacto['id_categoria'],
            $contacto['favorito']
        ];

        if ($id !== null) {
            $consulta = conexion()->prepare(
                'UPDATE contactos
                 SET nombre = ?,
                     telefono = ?,
                     correo = ?,
                     ciudad = ?,
                     id_categoria = ?,
                     favorito = ?
                 WHERE id = ? AND id_usuario = ?'
            );

            $valores[] = $id;
            $valores[] = $idUsuario;
        } else {
            $consulta = conexion()->prepare(
                'INSERT INTO contactos
                    (
                        nombre,
                        telefono,
                        correo,
                        ciudad,
                        id_categoria,
                        favorito,
                        id_usuario
                    )
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            );

            $valores[] = $idUsuario;
        }

        $consulta->execute($valores);

        mensaje(
            $id !== null
                ? 'Contacto actualizado.'
                : 'Contacto creado.'
        );

        redirigir('panel.php');
    }
}

inicio($id !== null ? 'Editar contacto' : 'Nuevo contacto');
errores($listaErrores);
?>

<form method="post" class="formulario">
    <?php campoToken(); ?>

    <?php
    $campos = [
        'nombre' => ['Nombre', 'text', 150],
        'telefono' => ['Teléfono', 'tel', 30],
        'correo' => ['Correo electrónico', 'email', 190],
        'ciudad' => ['Ciudad', 'text', 100]
    ];
    ?>

    <?php foreach ($campos as $campo => $datos): ?>
        <label for="<?= escapar($campo) ?>">
            <?= escapar($datos[0]) ?>
        </label>

        <input
            id="<?= escapar($campo) ?>"
            name="<?= escapar($campo) ?>"
            type="<?= escapar($datos[1]) ?>"
            maxlength="<?= (int) $datos[2] ?>"
            value="<?= escapar($contacto[$campo]) ?>"
            required
        >
    <?php endforeach; ?>

    <label for="id_categoria">Categoría</label>
    <select id="id_categoria" name="id_categoria" required>
        <option value="">Seleccione una categoría</option>

        <?php foreach ($categorias as $cat): ?>
            <option
                value="<?= (int) $cat['id'] ?>"
                <?= (string) $contacto['id_categoria']
                    === (string) $cat['id']
                        ? 'selected'
                        : '' ?>
            >
                <?= escapar($cat['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label class="check">
        <input
            type="checkbox"
            name="favorito"
            value="1"
            <?= (int) $contacto['favorito'] === 1
                ? 'checked'
                : '' ?>
        >
        Marcar como favorito
    </label>

    <div class="barra">
        <button>Guardar contacto</button>
        <a href="panel.php">Cancelar</a>
    </div>
</form>

<?php fin(); ?>
