<?php
    require 'php/security.php';
    $page_active = '';

    $allergeni = [
        ['icona' => 'icon-spoon',         'nome' => 'Glutine',           'descr' => 'Cereali contenenti glutine: grano, segale, orzo, avena, farro e prodotti derivati (pane, pasta, panificati).'],
        ['icona' => 'icon-spoon',         'nome' => 'Crostacei',         'descr' => 'Granchi, gamberi, gamberetti, aragoste e qualunque preparazione che ne contenga.'],
        ['icona' => 'icon-spoon',         'nome' => 'Uova',              'descr' => 'Uova intere e prodotti a base di uova come maionese, frittate o emulsionanti vari.'],
        ['icona' => 'icon-spoon',         'nome' => 'Pesce',             'descr' => 'Tutti i pesci e i derivati. Tracce possono essere presenti anche in alcuni brodi e salse.'],
        ['icona' => 'icon-spoon',         'nome' => 'Arachidi',          'descr' => 'Arachidi (noccioline americane) e prodotti che ne derivano: oli, burri, snack.'],
        ['icona' => 'icon-spoon',         'nome' => 'Soia',              'descr' => 'Salsa di soia, tofu, latte di soia e additivi a base di soia (es. lecitina E322).'],
        ['icona' => 'icon-spoon',         'nome' => 'Latte e derivati',  'descr' => 'Latte vaccino e tutti i latticini (formaggi, panna, burro, yogurt). Include il lattosio.'],
        ['icona' => 'icon-spoon',         'nome' => 'Frutta a guscio',   'descr' => 'Mandorle, nocciole, noci, anacardi, pistacchi, noci pecan, macadamia e simili.'],
        ['icona' => 'icon-spoon',         'nome' => 'Sedano',            'descr' => 'Sedano e prodotti a base di sedano. Frequente in zuppe, brodi e ragu.'],
        ['icona' => 'icon-spoon',         'nome' => 'Senape',            'descr' => 'Semi di senape, salsa di senape e prodotti che la contengono.'],
        ['icona' => 'icon-spoon',         'nome' => 'Sesamo',            'descr' => 'Semi di sesamo e prodotti derivati (oli, tahini, pane multicereali).'],
        ['icona' => 'icon-spoon',         'nome' => 'Solfiti',           'descr' => 'Anidride solforosa e solfiti in concentrazione > 10 mg/kg, usati come conservanti.'],
        ['icona' => 'icon-spoon',         'nome' => 'Lupini',            'descr' => 'Lupini e prodotti a base di lupini (farine, salse, sostituti vegetali).'],
        ['icona' => 'icon-spoon',         'nome' => 'Molluschi',         'descr' => 'Vongole, cozze, ostriche, calamari, polpi e tutti i mitili.'],
    ];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abarthas | Allergeni</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="mycss/style-clean.css">
    <link rel="stylesheet" href="fonts/icomoon/icomoon.css">
</head>
<body class="bg-light">
    <div class="main-wrapper">
        <?php include 'php/header.php'; ?>

        <section id="billboard" class="billboard-compact">
            <div class="container text-center">
                <div class="text-content heading light">
                    <h1 class="section-title"><strong>I 14 ALLERGENI</strong></h1>
                    <div class="divider"><div class="icon-wrap"><i class="icon icon-spoon"></i></div></div>
                    <div class="slogan mb-0">La tua sicurezza viene prima del nostro menu</div>
                </div>
            </div>
        </section>
    </div>

    <main class="container py-5">
        <p class="text-center text-muted mb-5">
            Elenco degli allergeni dichiarati secondo il Regolamento UE 1169/2011.
            Ogni piatto e' marcato con l'eventuale allergene presente.
            In caso di intolleranze gravi, segnalalo nelle <strong>note dell'ordine</strong>.
        </p>
        <div class="row g-4">
            <?php foreach ($allergeni as $i => $a): ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-body d-flex">
                            <div class="me-3 fs-1 text-danger">
                                <i class="icon <?= htmlspecialchars($a['icona']) ?>"></i>
                            </div>
                            <div>
                                <h2 class="h5 fw-bold mb-1">
                                    <?= $i + 1 ?>. <?= htmlspecialchars($a['nome']) ?>
                                </h2>
                                <p class="mb-0 small text-muted"><?= htmlspecialchars($a['descr']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <?php include 'php/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
