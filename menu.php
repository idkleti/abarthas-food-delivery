<?php
    require 'php/security.php';
    require 'php/dbConnect.php';
    $page_active = 'menu';

    $piatti = [];
    if ($dbConnect) {
        $res = $database->query('SELECT codice, nome, descr, prezzo, immagine FROM cibo ORDER BY codice');
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $piatti[] = $row;
            }
            $res->free();
        }
        $database->close();
    }
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abarthas | Menu</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="mycss/style-clean.css">
    <link rel="stylesheet" href="fonts/icomoon/icomoon.css">
</head>
<body>
<div class="main-wrapper">
    <?php include 'php/header.php'; ?>

    <section id="billboard" class="billboard-compact">
        <div class="container text-center">
            <div class="text-content heading light">
                <h1 class="section-title"><strong>IL NOSTRO MENU</strong></h1>
                <div class="divider"><div class="icon-wrap"><i class="icon icon-spoon"></i></div></div>
                <div class="slogan mb-0">Solo ingredienti freschi e selezionati</div>
            </div>
        </div>
    </section>
</div>

<section class="featured-food-wrap py-5">
    <div class="container">
        <h2 class="section-title text-center"><strong>Le nostre </strong>specialita'</h2>
        <div class="divider dark mb-5">
            <div class="icon-wrap"><i class="icon icon-spoon"></i></div>
        </div>

        <?php if (empty($piatti)): ?>
            <p class="text-center text-muted">Nessun piatto disponibile al momento.</p>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($piatti as $i => $item): ?>
                    <div class="col-12 col-md-6">
                        <div class="d-flex align-items-center p-3 border rounded shadow-sm bg-white h-100">
                            <?php
                                $img_src = !empty($item['immagine'])
                                    ? 'images/cibi/' . $item['immagine']
                                    : 'images/logo.png';
                            ?>
                            <img src="<?= htmlspecialchars($img_src) ?>"
                                onerror="this.src='images/logo.png';"
                                class="rounded me-3 flex-shrink-0"
                                alt="<?= htmlspecialchars($item['nome']) ?>"
                                style="width:80px;height:80px;object-fit:cover;">
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-baseline border-bottom mb-1">
                                    <strong class="text-uppercase"><?= htmlspecialchars($item['nome']) ?></strong>
                                    <div class="price-btn">
                                        &euro;<?= number_format((float)$item['prezzo'], 2) ?>
                                    </div>
                                </div>
                                <small class="text-muted"><?= htmlspecialchars($item['descr']) ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'php/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
