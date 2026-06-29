<?php
/*
    API JSON dei piatti.
    Restituisce ogni piatto con la sua composizione (lista ingredienti +
    eventuale allergene + flag rimovibile).
*/
require __DIR__ . '/dbConnect.php';

header('Content-Type: application/json; charset=utf-8');

if (!$dbConnect) {
    http_response_code(503);
    echo json_encode(['error' => 'Database non disponibile.']);
    exit;
}

// piatti
$piatti = [];
$res = $database->query(
    'SELECT codice, nome, descr, prezzo, categoria, immagine FROM cibo ORDER BY categoria, codice'
);
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $piatti[(int)$r['codice']] = [
            'codice'      => (int)   $r['codice'],
            'nome'        =>          $r['nome'],
            'descr'       =>          $r['descr'],
            'prezzo'      => (float) $r['prezzo'],
            'categoria'   =>          $r['categoria'],
            'immagine'    =>          $r['immagine'],
            'ingredienti' => [],
        ];
    }
    $res->free();
}

// composizione scomposta
$sql = 'SELECT c.codice_cibo, c.id_ingrediente, c.rimovibile, i.nome, i.allergene
        FROM composizione c
        INNER JOIN ingrediente i ON i.id = c.id_ingrediente';
$res = $database->query($sql);
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $cod = (int) $r['codice_cibo'];
        if (isset($piatti[$cod])) {
            $piatti[$cod]['ingredienti'][] = [
                'id'         => (int) $r['id_ingrediente'],
                'nome'       =>        $r['nome'],
                'allergene'  =>        $r['allergene'],
                'rimovibile' => (int) $r['rimovibile'] === 1,
            ];
        }
    }
    $res->free();
}

$database->close();
echo json_encode(array_values($piatti));
