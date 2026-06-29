<?php
    require 'php/security.php';
    require 'php/dbConnect.php';
    $page_active = 'home';

    // piatti giornalieri
    $piatti_home = [];
    if ($dbConnect) {
        $sql = "SELECT codice, nome, descr, prezzo, categoria, immagine
                FROM cibo
                WHERE in_evidenza = 1
                ORDER BY RAND()
                LIMIT 4";
        if ($res = $database->query($sql)) {
            while ($r = $res->fetch_assoc()) $piatti_home[] = $r;
            $res->free();
        }
        // fallback: se non ce ne sono abbastanza in evidenza, prendo i primi
        if (count($piatti_home) < 4) {
            $piatti_home = [];
            if ($res = $database->query('SELECT codice, nome, descr, prezzo, categoria, immagine FROM cibo ORDER BY codice LIMIT 4')) {
                while ($r = $res->fetch_assoc()) $piatti_home[] = $r;
                $res->free();
            }
        }

        // galleria scopri i nostri piatti, prima prendo quelli con foto e se non bastano ho quelli senza
        $gallery_piatti = [];
        if ($res = $database->query(
            'SELECT codice, nome, immagine FROM cibo
            ORDER BY (immagine IS NULL), RAND() LIMIT 4'
        )) {
            while ($r = $res->fetch_assoc()) $gallery_piatti[] = $r;
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
    <meta name="author" content="Letizia Bolognesi">
    <title>Abarthas | Home</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="mycss/style-clean.css">
    <link rel="stylesheet" href="fonts/icomoon/icomoon.css">
</head>
<body>
    <div class="main-wrapper">
        <?php include 'php/header.php'; ?>

        <section id="billboard">
            <div class="container text-center">
                <div class="text-content heading light">
                    <h1 class="section-title"><strong>ABARTHAS</strong> l'origine del sapore</h1>
                    <div class="divider">
                        <div class="icon-wrap"><i class="icon icon-spoon"></i></div>
                    </div>
                    <div class="slogan mb-5">Esplora il nostro menu</div>
                </div>
                <a href="#scroll-here" class="btn button btn-effect">Scopri i nostri piatti</a>
            </div>
        </section>
    </div>

    <section class="company-intro pt-60">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-md-7 section-image" id="scroll-here">
                    <img src="images/piadina.webp" class="introImg img-fluid" alt="Piadina">
                </div>
                <div class="col-md-5 text-content text-center heading dark">
                    <h2 class="section-title"><strong>Abarthas Delivery</strong> un servizio offerto da UNIFE</h2>
                    <div class="divider dark mb-4">
                        <div class="icon-wrap"><i class="icon icon-spoon"></i></div>
                    </div>
                    <p>Portiamo direttamente a casa tua il <b>gusto autentico e delizioso</b> dei nostri piatti.
                    Con Abarthas, il buon cibo e' a portata di click!</p>
                </div>
            </div>
        </div>
    </section>

    <section class="featured-food-wrap heading text-center py-5">
        <div class="container">
            <h2 class="section-title"><strong>Menu </strong>I piatti in evidenza</h2>
            <div class="divider dark mb-4">
                <div class="icon-wrap"><i class="icon icon-spoon"></i></div>
            </div>
            <?php if (empty($piatti_home)): ?>
                <p class="text-muted">Nessun piatto in evidenza al momento.</p>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($piatti_home as $i => $p): ?>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="box d-flex flex-column h-100">
                                <figure class="mb-3">
                                    <?php
                                        $img_src = !empty($p['immagine'])
                                            ? 'images/cibi/' . $p['immagine']
                                            : 'images/logo.png';
                                    ?>
                                    <img src="<?= htmlspecialchars($img_src) ?>"
                                        onerror="this.src='images/logo.png';"
                                        class="img-fluid bg-white p-2 border shadow-sm w-100"
                                        alt="<?= htmlspecialchars($p['nome']) ?>">
                                </figure>
                                <div class="text-content d-flex flex-column flex-grow-1">
                                    <div class="category"><?= htmlspecialchars($p['categoria']) ?></div>
                                    <div class="content mb-3">
                                        <h3><?= htmlspecialchars($p['nome']) ?></h3>
                                        <p><?= htmlspecialchars($p['descr']) ?></p>
                                    </div>
                                    <span class="price-tags mt-auto">&euro;<?= number_format((float)$p['prezzo'], 2) ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section class="gallery text-center mb-5">
        <div class="heading">
            <h2 class="section-title"><strong>Scopri </strong>i nostri piatti</h2>
            <div class="divider dark mb-4">
                <div class="icon-wrap"><i class="icon icon-spoon"></i></div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="row g-0">
                <?php foreach ($gallery_piatti as $g):
                    $img_url = !empty($g['immagine'])
                        ? 'images/cibi/' . $g['immagine']
                        : 'images/logo.png';
                ?>
                    <div class="col-6 col-lg-3">
                        <figure class="m-0">
                            <a href="#" class="gallery-link" data-bs-toggle="modal"
                            data-bs-target="#galleryModal" data-img="<?= htmlspecialchars($img_url) ?>">
                                <img src="<?= htmlspecialchars($img_url) ?>"
                                    class="img-fluid w-100"
                                    alt="<?= htmlspecialchars($g['nome']) ?>">
                            </a>
                        </figure>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <?php include 'php/footer.php'; ?>

    <div class="modal fade" id="galleryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body text-center position-relative p-0">
                    <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                            data-bs-dismiss="modal" aria-label="Chiudi"></button>
                    <img src="" id="modalImage" class="img-fluid rounded shadow-lg" alt="Ingrandimento piatto">
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>
