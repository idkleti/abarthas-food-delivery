<?php
    require 'php/security.php';

    if (!isset($_SESSION['email'])) {
        header('Location: index.php');
        exit;
    }

    require 'php/dbConnect.php';

    $messaggio_err = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $dbConnect) {
        csrf_check_or_die();

        $indirizzo = trim($_POST['indirizzo'] ?? '');
        $note      = trim($_POST['note']      ?? '');
        $data      = $_POST['data']           ?? '';
        $ora       = $_POST['ora']            ?? '';
        $tipo      = $_POST['tipo']           ?? 'prime';

        $tipi_validi = ['prime+', 'prime', 'eco'];
        if (!in_array($tipo, $tipi_validi, true)) $tipo = 'prime';

        // codice => quantita
        $quantita = [];
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'quant') === 0) {
                $codice = (int) substr($key, 5);
                $qty    = (int) $value;
                if ($codice > 0 && $qty > 0 && $qty <= 10) {
                    $quantita[$codice] = $qty;
                }
            }
        }

        if ($indirizzo === '' || mb_strlen($indirizzo) > 150 || $data === '' || $ora === '') {
            $messaggio_err = 'Compila tutti i campi di consegna.';
        } elseif (mb_strlen($note) > 255) {
            $messaggio_err = 'Note generali troppo lunghe (max 255 caratteri).';
        } elseif (empty($quantita)) {
            $messaggio_err = 'Seleziona almeno un piatto per procedere.';
        } else {

            // prezzi presi dal db
            $codici = array_keys($quantita);
            $place  = implode(',', array_fill(0, count($codici), '?'));
            $tipi_q = str_repeat('i', count($codici));

            $stmt = $database->prepare("SELECT codice, nome, prezzo FROM cibo WHERE codice IN ($place)");
            $stmt->bind_param($tipi_q, ...$codici);
            $stmt->execute();
            $res = $stmt->get_result();

            $items  = [];
            $totale = 0.0;
            while ($r = $res->fetch_assoc()) {
                $cod = (int) $r['codice'];
                $items[$cod] = [
                    'nome'     => $r['nome'],
                    'prezzo'   => (float) $r['prezzo'],
                    'quantita' => $quantita[$cod],
                    'rimossi'  => [],   // nomi degli ingredienti rimossi
                    'nota'     => '',
                ];
                $totale += $items[$cod]['prezzo'] * $quantita[$cod];
            }
            $stmt->close();

            if (count($items) !== count($quantita)) {
                $messaggio_err = 'Uno dei piatti selezionati non e' . ' piu' . ' disponibile.';
            } else {

                // ingredienti rimovibili e validazione
                $sql = "SELECT c.codice_cibo, c.id_ingrediente, i.nome
                        FROM composizione c
                        INNER JOIN ingrediente i ON i.id = c.id_ingrediente
                        WHERE c.rimovibile = 1 AND c.codice_cibo IN ($place)";
                $stmt = $database->prepare($sql);
                $stmt->bind_param($tipi_q, ...$codici);
                $stmt->execute();
                $res = $stmt->get_result();

                $rimovibili = []; // [codice_cibo => [id => nome, ...]]
                while ($r = $res->fetch_assoc()) {
                    $cod = (int) $r['codice_cibo'];
                    $rimovibili[$cod][(int)$r['id_ingrediente']] = $r['nome'];
                }
                $stmt->close();

                // applico pers
                foreach ($items as $cod => &$info) {
                    // ingredienti rimossi
                    $key_rmv = 'rmv' . $cod;
                    if (isset($_POST[$key_rmv]) && is_array($_POST[$key_rmv])) {
                        foreach ($_POST[$key_rmv] as $rid) {
                            $rid = (int) $rid;
                            if (isset($rimovibili[$cod][$rid])) {
                                $info['rimossi'][] = $rimovibili[$cod][$rid];
                            }
                        }
                    }
                    // Nnta piatto
                    $key_pn = 'pn' . $cod;
                    if (isset($_POST[$key_pn])) {
                        $info['nota'] = mb_substr(trim($_POST[$key_pn]), 0, 200);
                    }
                }
                unset($info);
                $database->close();

                $_SESSION['pending_order'] = [
                    'indirizzo'    => $indirizzo,
                    'note'         => $note,
                    'data'         => $data,
                    'ora'          => $ora,
                    'tipo'         => $tipo,
                    'items'        => $items,
                    'totale_items' => $totale,
                ];
                header('Location: payment.php');
                exit;
            }
        }
    }

    $page_active = 'reservation';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abarthas | Ordina</title>

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
                <h1 class="section-title"><strong>EFFETTUA IL TUO ORDINE</strong></h1>
                <div class="divider"><div class="icon-wrap"><i class="icon icon-spoon"></i></div></div>
                <div class="slogan mb-0">Scegli la comodita', la qualita' arriva a casa tua</div>
            </div>
        </div>
    </section>
</div>

<main class="container py-5">

    <?php if ($messaggio_err !== ''): ?>
        <div class="alert alert-danger text-center" role="alert">
            <?= htmlspecialchars($messaggio_err) ?>
        </div>
    <?php endif; ?>

    <form id="mainform" method="post" action="reservation.php" novalidate>
        <?= csrf_field() ?>

        <div class="card shadow-sm border-0 mb-5">
            <div class="card-body p-4">
                <h2 class="h4 fw-bold mb-4 border-bottom pb-2">1. Dati di consegna</h2>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Data</label>
                        <input type="date" class="form-control" name="data" required
                            min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Orario</label>
                        <input type="time" class="form-control" name="ora" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Spedizione</label>
                        <select class="form-select" name="tipo">
                            <option value="prime+">PRIME+ (consegna flash) +&euro;5.00</option>
                            <option value="prime" selected>PRIME (standard) +&euro;2.50</option>
                            <option value="eco">Eco (risparmio) +&euro;1.00</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Indirizzo completo</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="icon icon-map-marker"></i></span>
                            <input type="text" name="indirizzo" class="form-control"
                                placeholder="Via, civico, citta'..." maxlength="150" required>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Note generali per il rider (facoltativo)</label>
                        <textarea name="note" class="form-control" rows="2" maxlength="255"
                                placeholder="Citofono, piano, indicazioni..."></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mb-4">
            <h2 class="fw-bold">2. Scegli e personalizza i piatti</h2>
            <div class="divider"><i class="icon icon-spoon text-primary"></i></div>
            <p class="small text-muted mt-2">
                Clicca <i class="icon icon-spoon"></i> accanto al piatto per togliere ingredienti o aggiungere una nota.
            </p>
        </div>

        <div class="row g-3" id="menu-items-container">
            <div class="col-12 text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Caricamento menu...</p>
            </div>
        </div>

        <div class="sticky-bottom bg-white p-3 border-top shadow-lg mt-5 text-center rounded">
            <span class="me-4 fs-5">
                Subtotale piatti:
                <strong id="cart-total" class="text-danger">&euro;0.00</strong>
            </span>
            <button type="submit" class="btn btn-primary btn-lg px-5 py-3 shadow btn-order">
                <i class="icon icon-shopping-cart me-2"></i> PROCEDI AL PAGAMENTO
            </button>
        </div>
    </form>
</main>

<?php include 'php/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/reservation.js"></script>

</body>
</html>
