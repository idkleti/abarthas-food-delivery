<?php
    require 'php/security.php';

    if (!isset($_SESSION['email'])) {
        header('Location: index.php');
        exit;
    }

    require 'php/dbConnect.php';
    $page_active = 'reservation';

    $tariffe = [
        'prime+' => ['label' => 'Prime+ (consegna flash)', 'costo' => 5.00],
        'prime'  => ['label' => 'Prime (standard)',         'costo' => 2.50],
        'eco'    => ['label' => 'Eco (risparmio)',          'costo' => 1.00],
    ];

    // stato 1: successo dopo pagamento
    if (isset($_SESSION['last_order_id'])) {
        $order_id = (int)   $_SESSION['last_order_id'];
        $totale   = (float) ($_SESSION['last_order_total'] ?? 0);
        unset($_SESSION['last_order_id'], $_SESSION['last_order_total']);
?>
    <!DOCTYPE html>
    <html lang="it">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Abarthas | Pagamento completato</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="mycss/style-clean.css">
        <link rel="stylesheet" href="fonts/icomoon/icomoon.css">
    </head>
    <body class="bg-light">
        <?php include 'php/header.php'; ?>
        <main class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow-sm border-0 text-center p-5">
                        <div class="mb-3">
                            <i class="icon icon-spoon display-1 text-success"></i>
                        </div>
                        <h1 class="h3 fw-bold">Pagamento completato</h1>
                        <p class="text-muted mb-4">
                            Grazie! Il tuo ordine <strong>#<?= $order_id ?></strong>
                            di <strong>&euro;<?= number_format($totale, 2) ?></strong>
                            e' stato registrato e affidato al rider.
                        </p>
                        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                            <a href="profile.php"     class="btn btn-order">Vedi i tuoi ordini</a>
                            <a href="reservation.php" class="btn btn-outline-secondary">Ordina ancora</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        <?php include 'php/footer.php'; ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
    exit;
}

// stato 2 nessun ordine in sospeso e torno a reservation
if (!isset($_SESSION['pending_order'])) {
    header('Location: reservation.php');
    exit;
}

$ordine = $_SESSION['pending_order'];
$tipo   = isset($tariffe[$ordine['tipo']]) ? $ordine['tipo'] : 'prime';
$fee    = $tariffe[$tipo]['costo'];
$totale_finale = $ordine['totale_items'] + $fee;

$errore_pay = '';

// stato 3 pagamento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check_or_die();

    $numero       = preg_replace('/\D/', '', $_POST['card_numero']      ?? '');
    $scadenza     = trim($_POST['card_scadenza']                        ?? '');
    $cvv          = preg_replace('/\D/', '', $_POST['card_cvv']         ?? '');
    $intestatario = trim($_POST['card_intestatario']                    ?? '');

    if (strlen($numero) < 13 || strlen($numero) > 19) {
        $errore_pay = 'Numero carta non valido.';
    } elseif (!preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $scadenza, $m)) {
        $errore_pay = 'Data di scadenza non valida (formato MM/AA).';
    } elseif (strlen($cvv) < 3 || strlen($cvv) > 4) {
        $errore_pay = 'CVV non valido.';
    } elseif ($intestatario === '' || mb_strlen($intestatario) > 80) {
        $errore_pay = 'Nome intestatario non valido.';
    } else {
        $anno_scad = 2000 + (int) $m[2];
        $mese_scad = (int) $m[1];
        if ($anno_scad <  (int) date('Y') || ($anno_scad === (int) date('Y') && $mese_scad < (int) date('n'))) {
            $errore_pay = 'La carta risulta scaduta.';
        } elseif (!$dbConnect) {
            $errore_pay = 'Servizio momentaneamente non disponibile.';
        } else {
            // scarto subito i dati carta dalla memoria
            $numero = $cvv = $scadenza = $intestatario = '';

            $emailc = $_SESSION['email'];
            $database->begin_transaction();
            try {
                $ins_ord = $database->prepare(
                    'INSERT INTO ordine (prezzotot, indirizzo, note, emailc) VALUES (?, ?, ?, ?)'
                );
                $ins_ord->bind_param(
                    'dsss',
                    $totale_finale,
                    $ordine['indirizzo'],
                    $ordine['note'],
                    $emailc
                );
                $ins_ord->execute();
                $order_id = $database->insert_id;
                $ins_ord->close();

                $ins_item = $database->prepare(
                    'INSERT INTO contiene (id, codice, quantita, note, ingredienti_rimossi)
                    VALUES (?, ?, ?, ?, ?)'
                );
                foreach ($ordine['items'] as $codice => $info) {
                    $cod = (int) $codice;
                    $qty = (int) $info['quantita'];
                    $nota_piatto = $info['nota'] !== '' ? $info['nota'] : null;
                    $rimossi_str = !empty($info['rimossi'])
                        ? implode(', ', $info['rimossi'])
                        : null;
                    $ins_item->bind_param('iiiss',
                        $order_id, $cod, $qty, $nota_piatto, $rimossi_str);
                    $ins_item->execute();
                }
                $ins_item->close();

                $database->commit();

                $_SESSION['last_order_id']    = $order_id;
                $_SESSION['last_order_total'] = $totale_finale;
                unset($_SESSION['pending_order']);

                $database->close();
                header('Location: payment.php');
                exit;
            } catch (Throwable $e) {
                $database->rollback();
                error_log('Order save failed: ' . $e->getMessage());
                $errore_pay = 'Errore nel salvataggio dell\'ordine.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abarthas | Pagamento</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="mycss/style-clean.css">
    <link rel="stylesheet" href="fonts/icomoon/icomoon.css">
</head>
<body class="bg-light">
<?php include 'php/header.php'; ?>

<main class="container py-5">
    <h1 class="h3 fw-bold text-center mb-4">Pagamento</h1>
    <p class="text-center text-muted mb-5">
        <small>
            <i class="icon icon-spoon me-1"></i>
            Questa e' una pagina di pagamento <strong>fittizia</strong>: non inserire dati reali.
        </small>
    </p>

    <?php if ($errore_pay !== ''): ?>
        <div class="alert alert-danger text-center" role="alert">
            <?= htmlspecialchars($errore_pay) ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- ============ RIEPILOGO ============ -->
        <div class="col-lg-5 order-lg-2">
            <div class="card shadow-sm border-0 sticky-lg-top" style="top: 100px;">
                <div class="card-body">
                    <h2 class="h5 fw-bold border-bottom pb-2">Riepilogo ordine</h2>

                    <ul class="list-group list-group-flush mb-3">
                        <?php foreach ($ordine['items'] as $info):
                            $sub = $info['prezzo'] * $info['quantita']; ?>
                            <li class="list-group-item ps-0">
                                <div class="d-flex justify-content-between">
                                    <span>
                                        <?= (int) $info['quantita'] ?>x
                                        <strong><?= htmlspecialchars($info['nome']) ?></strong>
                                    </span>
                                    <span class="text-muted small">
                                        &euro;<?= number_format($sub, 2) ?>
                                    </span>
                                </div>
                                <?php if (!empty($info['rimossi'])): ?>
                                    <div class="small text-danger ms-3 mt-1">
                                        <i class="icon icon-spoon"></i>
                                        Senza: <?= htmlspecialchars(implode(', ', $info['rimossi'])) ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($info['nota'])): ?>
                                    <div class="small text-muted ms-3 fst-italic">
                                        Nota: <?= htmlspecialchars($info['nota']) ?>
                                    </div>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <div class="d-flex justify-content-between small">
                        <span>Subtotale</span>
                        <span>&euro;<?= number_format($ordine['totale_items'], 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span>Spedizione (<?= htmlspecialchars($tariffe[$tipo]['label']) ?>)</span>
                        <span>&euro;<?= number_format($fee, 2) ?></span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-5">
                        <span>Totale</span>
                        <span class="text-danger">&euro;<?= number_format($totale_finale, 2) ?></span>
                    </div>

                    <hr>
                    <p class="mb-1 small">
                        <i class="icon icon-map-marker me-1"></i>
                        <strong>Indirizzo:</strong><br>
                        <?= htmlspecialchars($ordine['indirizzo']) ?>
                    </p>
                    <p class="mb-1 small">
                        <strong>Data / orario:</strong>
                        <?= htmlspecialchars($ordine['data']) ?>
                        alle <?= htmlspecialchars($ordine['ora']) ?>
                    </p>
                    <?php if (!empty($ordine['note'])): ?>
                        <p class="mb-0 small">
                            <strong>Note rider:</strong> <?= htmlspecialchars($ordine['note']) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ============ FORM PAGAMENTO ============ -->
        <div class="col-lg-7 order-lg-1">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h2 class="h5 fw-bold border-bottom pb-2 mb-4">Dati della carta</h2>

                    <div class="fake-card mb-4">
                        <div class="fake-card-bg"></div>
                        <div class="fake-card-chip"></div>
                        <div class="fake-card-number" id="cardPreviewNumber">
                            &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;
                            &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;
                        </div>
                        <div class="d-flex justify-content-between mt-3 small text-uppercase">
                            <div>
                                <div class="text-white-50">Intestatario</div>
                                <div id="cardPreviewName">Nome Cognome</div>
                            </div>
                            <div class="text-end">
                                <div class="text-white-50">Scadenza</div>
                                <div id="cardPreviewExpiry">MM/AA</div>
                            </div>
                        </div>
                    </div>

                    <form method="post" action="payment.php" novalidate id="paymentForm" autocomplete="off">
                        <?= csrf_field() ?>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Numero carta</label>
                                <input type="text" name="card_numero" id="card_numero"
                                    class="form-control" inputmode="numeric"
                                    maxlength="19" autocomplete="off"
                                    placeholder="1234 5678 9012 3456" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Intestatario</label>
                                <input type="text" name="card_intestatario" id="card_intestatario"
                                    class="form-control" autocomplete="off" maxlength="80"
                                    placeholder="Mario Rossi" required>
                            </div>
                            <div class="col-7">
                                <label class="form-label fw-semibold">Scadenza</label>
                                <input type="text" name="card_scadenza" id="card_scadenza"
                                    class="form-control" inputmode="numeric"
                                    maxlength="5" autocomplete="off"
                                    placeholder="MM/AA" required>
                            </div>
                            <div class="col-5">
                                <label class="form-label fw-semibold">CVV</label>
                                <input type="text" name="card_cvv" id="card_cvv"
                                    class="form-control" inputmode="numeric"
                                    maxlength="4" autocomplete="off"
                                    placeholder="123" required>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-order btn-lg py-3">
                                <i class="icon icon-shopping-cart me-2"></i>
                                PAGA &euro;<?= number_format($totale_finale, 2) ?>
                            </button>
                        </div>
                        <p class="text-center text-muted small mt-3 mb-0">
                            <a href="reservation.php" class="text-muted">&laquo; Torna all'ordine</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'php/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/payment.js"></script>
</body>
</html>
