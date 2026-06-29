<?php
    require 'php/security.php';
    if (!isset($_SESSION['email'])) {
        header('Location: index.php');
        exit;
    }
    require 'php/dbConnect.php';
    $page_active = 'profile';

    $ordini = [];
    if ($dbConnect) {
        $emailc = $_SESSION['email'];

        $stmt = $database->prepare(
            'SELECT id, indirizzo, note, data_ord, prezzotot
            FROM ordine WHERE emailc = ?
            ORDER BY data_ord DESC, id DESC'
        );
        $stmt->bind_param('s', $emailc);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $ordini[$row['id']] = $row + ['items' => []];
        }
        $stmt->close();

        if (!empty($ordini)) {
            $ids   = array_keys($ordini);
            $place = implode(',', array_fill(0, count($ids), '?'));
            $tipi  = str_repeat('i', count($ids));

            $stmt2 = $database->prepare(
                "SELECT ct.id, ct.note AS nota_piatto, ct.ingredienti_rimossi,
                        c.nome, c.prezzo, ct.quantita
                FROM contiene ct
                INNER JOIN cibo c ON c.codice = ct.codice
                WHERE ct.id IN ($place)
                ORDER BY ct.riga_id"
            );
            $stmt2->bind_param($tipi, ...$ids);
            $stmt2->execute();
            $r2 = $stmt2->get_result();
            while ($row = $r2->fetch_assoc()) {
                $ordini[$row['id']]['items'][] = $row;
            }
            $stmt2->close();
        }
        $database->close();
    }
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abarthas | Il mio profilo</title>

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
                    <h1 class="section-title"><strong>IL TUO PROFILO</strong></h1>
                    <div class="divider"><div class="icon-wrap"><i class="icon icon-spoon"></i></div></div>
                    <div class="slogan mb-0">
                        Benvenuto, <?= htmlspecialchars($_SESSION['nome'] ?? $_SESSION['email']) ?>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <main>
        <section class="py-5">
            <div class="container">
                <h2 class="text-center fw-bold mb-4">I tuoi ordini</h2>

                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <?php if (empty($ordini)): ?>
                            <div class="text-center py-5">
                                <i class="icon icon-shopping-cart display-1 text-muted"></i>
                                <p class="mt-3">Non hai ancora effettuato ordini.</p>
                                <a href="reservation.php" class="btn btn-order mt-2">Ordina ora</a>
                            </div>
                        <?php else: ?>
                            <div class="accordion shadow-sm" id="ordersAccordion">
                                <?php foreach ($ordini as $id => $ord):
                                    $totale_visualizzato = (float) $ord['prezzotot']; ?>
                                    <div class="accordion-item mb-3 border rounded">
                                        <h2 class="accordion-header" id="heading<?= $id ?>">
                                            <button class="accordion-button collapsed" type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#collapse<?= $id ?>"
                                                    aria-expanded="false"
                                                    aria-controls="collapse<?= $id ?>">
                                                <div class="d-flex justify-content-between w-100 me-3 flex-wrap gap-2">
                                                    <span><strong>Ordine #<?= $id ?></strong></span>
                                                    <span class="badge rounded-pill order-badge">
                                                        &euro;<?= number_format($totale_visualizzato, 2) ?>
                                                    </span>
                                                </div>
                                            </button>
                                        </h2>
                                        <div id="collapse<?= $id ?>" class="accordion-collapse collapse" data-bs-parent="#ordersAccordion">
                                            <div class="accordion-body bg-white">
                                                <p class="mb-2">
                                                    <i class="icon icon-map-marker me-2"></i>
                                                    <strong>Indirizzo:</strong>
                                                    <?= htmlspecialchars($ord['indirizzo']) ?>
                                                </p>
                                                <?php if (!empty($ord['note'])): ?>
                                                    <p class="mb-2"><strong>Note rider:</strong>
                                                        <?= htmlspecialchars($ord['note']) ?>
                                                    </p>
                                                <?php endif; ?>
                                                <p class="mb-2 text-muted small">
                                                    <?= htmlspecialchars($ord['data_ord']) ?>
                                                </p>
                                                <hr>
                                                <h6 class="fw-bold text-uppercase small text-muted">Dettaglio piatti</h6>
                                                <ul class="list-group list-group-flush">
                                                    <?php foreach ($ord['items'] as $it):
                                                        $sub = $it['prezzo'] * $it['quantita']; ?>
                                                        <li class="list-group-item ps-0">
                                                            <div class="d-flex justify-content-between">
                                                                <span><?= (int)$it['quantita'] ?>x
                                                                    <strong><?= htmlspecialchars($it['nome']) ?></strong></span>
                                                                <span class="text-muted small">
                                                                    &euro;<?= number_format($sub, 2) ?>
                                                                </span>
                                                            </div>
                                                            <?php if (!empty($it['ingredienti_rimossi'])): ?>
                                                                <div class="small text-danger ms-3 mt-1">
                                                                    Senza: <?= htmlspecialchars($it['ingredienti_rimossi']) ?>
                                                                </div>
                                                            <?php endif; ?>
                                                            <?php if (!empty($it['nota_piatto'])): ?>
                                                                <div class="small text-muted ms-3 fst-italic">
                                                                    Nota: <?= htmlspecialchars($it['nota_piatto']) ?>
                                                                </div>
                                                            <?php endif; ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                    <?php if (empty($ord['items'])): ?>
                                                        <li class="list-group-item ps-0 text-muted">
                                                            Dettaglio non disponibile.
                                                        </li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'php/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
