<?php
function getContentBasedRecommendations($usuario, $conn, $limit = 5) {
    // 1. Obtener id_juego que el usuario ha jugado (historial_juegos)
    $sqlHistorial = "
        SELECT hj.id_juego
        FROM historial_juegos hj
        WHERE hj.usuario = ?
        GROUP BY hj.id_juego
    ";
    $stmt = $conn->prepare($sqlHistorial);
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $resultHistorial = $stmt->get_result();
    
    $juegosJugados = [];
    while ($row = $resultHistorial->fetch_assoc()) {
        $juegosJugados[] = $row['id_juego'];
    }
    $stmt->close();

    // Si no ha jugado a nada, podrías devolver un array vacío o fallback
    if (count($juegosJugados) === 0) {
        return [];
    }

    // 2. Obtener las categorías más frecuentes de esos juegos
    $inPlaceholder = implode(',', array_fill(0, count($juegosJugados), '?'));
    $sqlCatFrecuentes = "
        SELECT jc.id_categoria, COUNT(*) as conteo
        FROM juegos_categorias jc
        WHERE jc.id_juego IN ($inPlaceholder)
        GROUP BY jc.id_categoria
        ORDER BY conteo DESC
    ";
    $stmt = $conn->prepare($sqlCatFrecuentes);
    $types = str_repeat('i', count($juegosJugados));
    $stmt->bind_param($types, ...$juegosJugados);
    $stmt->execute();
    $resultCat = $stmt->get_result();
    
    $categoriasFrecuentes = [];
    while ($row = $resultCat->fetch_assoc()) {
        $categoriasFrecuentes[] = $row['id_categoria'];
    }
    $stmt->close();

    if (count($categoriasFrecuentes) === 0) {
        return [];
    }

    // 3. Buscar otros juegos con esas categorías, excluyendo los que ya jugó
    $inCatPlaceholder = implode(',', array_fill(0, count($categoriasFrecuentes), '?'));
    $inJuegoPlaceholder = implode(',', array_fill(0, count($juegosJugados), '?'));

    // Incluir también el campo icono y ruta_index si quieres mostrar la tarjeta
    $sqlRecomendacion = "
        SELECT DISTINCT j.id_juego, j.nombre, j.descripcion, j.icono, j.ruta_index
        FROM juegos_categorias jc
        JOIN juegos j ON j.id_juego = jc.id_juego
        WHERE jc.id_categoria IN ($inCatPlaceholder)
          AND j.id_juego NOT IN ($inJuegoPlaceholder)
        LIMIT ?
    ";
    
    // Bind param con: las categorías, los juegosJugados y el limit final
    $params = array_merge($categoriasFrecuentes, $juegosJugados);
    $types = str_repeat('i', count($params)) . 'i'; // +1 para el limit
    $stmt = $conn->prepare($sqlRecomendacion);
    $params[] = $limit;
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $resultRecom = $stmt->get_result();

    $recomendados = [];
    while ($row = $resultRecom->fetch_assoc()) {
        $recomendados[] = $row;
    }
    $stmt->close();

    return $recomendados;
}
