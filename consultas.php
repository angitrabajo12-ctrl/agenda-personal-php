<?php
declare(strict_types=1);

function ordenesPermitidos(): array
{
    return [
        'nombre' => [
            'Nombre (A–Z)',
            'c.nombre ASC, c.id ASC'
        ],
        'ciudad' => [
            'Ciudad (A–Z)',
            'c.ciudad ASC, c.nombre ASC, c.id ASC'
        ],
        'recientes' => [
            'Fecha: más recientes',
            'c.fecha_creacion DESC, c.id DESC'
        ],
        'antiguos' => [
            'Fecha: más antiguos',
            'c.fecha_creacion ASC, c.id ASC'
        ],
    ];
}

function consultaContactos(
    int $idUsuario,
    array $filtros
): array {
    $sql = '
        SELECT c.*, cat.nombre AS categoria_nombre
        FROM contactos c
        JOIN categorias cat ON cat.id = c.id_categoria
        WHERE c.id_usuario = ?
    ';

    $valores = [$idUsuario];

    if ($filtros['q'] !== '') {
        $texto = '%' . str_replace(
            ['!', '%', '_'],
            ['!!', '!%', '!_'],
            $filtros['q']
        ) . '%';

        $sql .= "
            AND (
                c.nombre LIKE ? ESCAPE '!'
                OR c.correo LIKE ? ESCAPE '!'
                OR c.ciudad LIKE ? ESCAPE '!'
            )
        ";

        array_push($valores, $texto, $texto, $texto);
    }

    if ($filtros['categoria'] !== '') {
        $sql .= ' AND c.id_categoria = ?';
        $valores[] = $filtros['categoria'];
    }

    if ($filtros['favoritos'] === '1') {
        $sql .= ' AND c.favorito = 1';
    }

    $ordenes = ordenesPermitidos();

    $orden = array_key_exists($filtros['orden'], $ordenes)
        ? $filtros['orden']
        : 'nombre';

    $sql .= ' ORDER BY ' . $ordenes[$orden][1];

    return [$sql, $valores];
}
