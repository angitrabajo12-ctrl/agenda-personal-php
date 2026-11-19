<?php

require_once __DIR__ . '/sesion.php';

// Verificar que exista una sesión activa.
$usuario = exigirUsuario();
$idUsuario = (int) $usuario['id'];

// Si existe un identificador, se editará el contacto.
$id = isset($_GET['id'])
    ? obtenerIdContacto()
    : null;

$listaCategorias = categorias();

// Consultar únicamente un contacto del usuario autenticado.
$contacto = $id !== null
    ? obtenerContacto($id, $idUsuario)
    : [
        'nombre' => '',
        'telefono' => '',
        'correo' => '',
        'ciudad' => '',
        'id_categoria' => '',
        'favorito' => 0,
    ];

$listaErrores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificarToken();

    $contacto['nombre'] = entrada('nombre');
    $contacto['telefono'] = entrada('telefono');
    $contacto['correo'] = entrada('correo');
    $contacto['ciudad'] = entrada('ciudad');
    $contacto['id_categoria'] = entrada('id_categoria');

    $contacto['favorito'] = entrada('favorito') === '1'
        ? 1
        : 0;

    // Validar el nombre en el servidor.
    if (
        $contacto['nombre'] === ''
        || strlen($contacto['nombre']) > 150
    ) {
        $listaErrores[] =
            'Ingrese un nombre válido de hasta 150 bytes.';
    }

    // Contar los números del teléfono.
    $digitos = preg_replace(
        '/[^0-9]/',
        '',
        $contacto['telefono']
    );

    // Rechazar teléfonos que solo contengan símbolos.
    if (
        !preg_match(
            '/^[+0-9() .-]{6,30}$/D',
            $contacto['telefono']
        )
        || strlen($digitos) < 6
    ) {
        $listaErrores[] =
            'El teléfono debe contener al menos 6 dígitos '
            . 'y no superar los 30 caracteres. '
            . 'Solo se permiten números, espacios, '
            . 'paréntesis y los símbolos +, - y punto.';
    }

    // Validar el formato y la longitud del correo.
    if (
        !filter_var(
            $contacto['correo'],
            FILTER_VALIDATE_EMAIL
        )
        || strlen($contacto['correo']) > 190
    ) {
        $listaErrores[] =
            'Ingrese un correo electrónico válido '
            . 'de hasta 190 bytes.';
    }

    // Validar la ciudad en el servidor.
    if (
        $contacto['ciudad'] === ''
        || strlen($contacto['ciudad']) > 100
    ) {
        $listaErrores[] =
            'Ingrese una ciudad válida de hasta 100 bytes.';
    }

    // Comprobar que la categoría exista.
    $idsCategorias = array_map(
        'strval',
        array_column($listaCategorias, 'id')
    );

    if (
        !in_array(
            $contacto['id_categoria'],
            $idsCategorias,
            true
        )
    ) {
        $listaErrores[] = 'Seleccione una categoría válida.';
    }

    if (!$listaErrores) {
        $valores = [
            $contacto['nombre'],
            $contacto['telefono'],
            $contacto['correo'],
            $contacto['ciudad'],
            $contacto['id_categoria'],
            $contacto['favorito'],
        ];

        if ($id !== null) {
            // Actualizar únicamente un contacto propio.
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
            // El propietario se obtiene de la sesión.
            $consulta = conexion()->prepare(
                'INSERT INTO contactos (
                    nombre,
                    telefono,
                    correo,
                    ciudad,
                    id_categoria,
                    favorito,
                    id_usuario
                 ) VALUES (?, ?, ?, ?, ?, ?, ?)'
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

inicio(
    $id !== null
        ? 'Editar contacto'
        : 'Nuevo contacto'
);

errores($listaErrores);

?>

<form method="post" class="formulario">
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
        placeholder="Ejemplo: 0991234567"
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

    <label for="id_categoria">Categoría</label>
    <select id="id_categoria" name="id_categoria" required>
        <option value="">Seleccione una categoría</option>

        <?php foreach ($listaCategorias as $categoria): ?>
            <option
                value="<?= (int) $categoria['id'] ?>"
                <?= (string) $contacto['id_categoria']
                    === (string) $categoria['id']
                    ? 'selected'
                    : '' ?>
            >
                <?= escapar($categoria['nombre']) ?>
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
        <button type="submit">Guardar contacto</button>
        <a href="panel.php">Cancelar</a>
    </div>
</form>

<?php fin(); ?>
