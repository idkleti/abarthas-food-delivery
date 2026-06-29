<?php
require 'php/security.php';

if (!isset($_SESSION['email']) || empty($_SESSION['is_admin'])) {
    http_response_code(403);
    header('Location: index.php');
    exit;
}

require 'php/dbConnect.php';
$page_active = 'admin';

if (!$dbConnect) {
    die('Database non disponibile.');
}

// flash message per evitare doppio submit form
$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

function admin_flash(string $type, string $msg): void
{
    $_SESSION['admin_flash'] = ['type' => $type, 'msg' => $msg];
}

// upload img piatti
const CIBO_UPLOAD_DIR  = __DIR__ . '/images/cibi/';
const CIBO_UPLOAD_PATH = 'images/cibi/';
const CIBO_MAX_BYTES   = 10 * 1024 * 1024;
const CIBO_ALLOWED_MIME = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
];

// gestione upload immagine
function handle_cibo_upload(array $file): array
{
    if (empty($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [null, 'Errore upload (codice ' . (int) $file['error'] . ').'];
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        return [null, 'File non valido.'];
    }
    if ($file['size'] > CIBO_MAX_BYTES) {
        return [null, 'Immagine troppo grande (max 10 MB).'];
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset(CIBO_ALLOWED_MIME[$mime])) {
        return [null, 'Tipo immagine non ammesso (jpg, png, webp, gif).'];
    }

    if (!is_dir(CIBO_UPLOAD_DIR) && !mkdir(CIBO_UPLOAD_DIR, 0775, true)) {
        return [null, 'Impossibile creare la cartella di upload.'];
    }

    $name = bin2hex(random_bytes(8)) . '.' . CIBO_ALLOWED_MIME[$mime];
    if (!move_uploaded_file($file['tmp_name'], CIBO_UPLOAD_DIR . $name)) {
        return [null, 'Impossibile salvare l\'immagine.'];
    }
    return [$name, null];
}

// cancella file img se presente in cartella upload
function delete_cibo_image(?string $filename): void
{
    if (!$filename) return;
    $path = CIBO_UPLOAD_DIR . basename($filename);
    if (is_file($path)) @unlink($path);
}

// CRUD cibi e console sql
$sql_result_rows  = null;
$sql_result_cols  = null;
$sql_result_error = null;
$sql_query        = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check_or_die();
    $action = $_POST['action'] ?? '';

    // aggiungi cibo
    if ($action === 'add_cibo') {
        $nome     = trim($_POST['nome']      ?? '');
        $descr    = trim($_POST['descr']     ?? '');
        $prezzo   = (float) ($_POST['prezzo'] ?? 0);
        $categ    = trim($_POST['categoria'] ?? 'Vari');
        $evid     = isset($_POST['in_evidenza']) ? 1 : 0;

        if ($nome === '' || $descr === '' || $prezzo <= 0) {
            admin_flash('danger', 'Nome, descrizione e prezzo positivo sono obbligatori.');
            header('Location: admin.php?tab=cibi');
            exit;
        }

        [$img_name, $img_err] = handle_cibo_upload($_FILES['immagine'] ?? []);
        if ($img_err !== null) {
            admin_flash('danger', $img_err);
            header('Location: admin.php?tab=cibi');
            exit;
        }

        $stmt = $database->prepare(
            'INSERT INTO cibo (nome, descr, prezzo, categoria, in_evidenza, immagine)
            VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('ssdsis', $nome, $descr, $prezzo, $categ, $evid, $img_name);
        if ($stmt->execute()) {
            admin_flash('success', 'Piatto aggiunto.');
        } else {
            delete_cibo_image($img_name);
            admin_flash('danger', 'Errore nell\'inserimento.');
        }
        $stmt->close();
        header('Location: admin.php?tab=cibi');
        exit;
    }

    // modifica cibo
    if ($action === 'edit_cibo') {
        $codice   = (int) ($_POST['codice'] ?? 0);
        $nome     = trim($_POST['nome']      ?? '');
        $descr    = trim($_POST['descr']     ?? '');
        $prezzo   = (float) ($_POST['prezzo'] ?? 0);
        $categ    = trim($_POST['categoria'] ?? 'Vari');
        $evid     = isset($_POST['in_evidenza']) ? 1 : 0;

        if ($codice <= 0 || $nome === '' || $descr === '' || $prezzo <= 0) {
            admin_flash('danger', 'Dati non validi.');
            header('Location: admin.php?tab=cibi');
            exit;
        }

        [$img_name, $img_err] = handle_cibo_upload($_FILES['immagine'] ?? []);
        if ($img_err !== null) {
            admin_flash('danger', $img_err);
            header('Location: admin.php?tab=cibi');
            exit;
        }

        if ($img_name !== null) {
            // nuova immagine: sostituisco la vecchia
            $old_img = null;
            $sel = $database->prepare('SELECT immagine FROM cibo WHERE codice=?');
            $sel->bind_param('i', $codice);
            $sel->execute();
            $sel->bind_result($old_img);
            $sel->fetch();
            $sel->close();

            $stmt = $database->prepare(
                'UPDATE cibo SET nome=?, descr=?, prezzo=?, categoria=?, in_evidenza=?, immagine=?
                WHERE codice=?'
            );
            $stmt->bind_param('ssdsisi', $nome, $descr, $prezzo, $categ, $evid, $img_name, $codice);
            if ($stmt->execute()) {
                delete_cibo_image($old_img);
                admin_flash('success', 'Piatto #' . $codice . ' aggiornato.');
            } else {
                delete_cibo_image($img_name);
                admin_flash('danger', 'Errore nell\'aggiornamento.');
            }
            $stmt->close();
        } else {
            // niente nuova immagine: mantengo la vecchia
            $stmt = $database->prepare(
                'UPDATE cibo SET nome=?, descr=?, prezzo=?, categoria=?, in_evidenza=?
                WHERE codice=?'
            );
            $stmt->bind_param('ssdsii', $nome, $descr, $prezzo, $categ, $evid, $codice);
            if ($stmt->execute()) {
                admin_flash('success', 'Piatto #' . $codice . ' aggiornato.');
            } else {
                admin_flash('danger', 'Errore nell\'aggiornamento.');
            }
            $stmt->close();
        }
        header('Location: admin.php?tab=cibi');
        exit;
    }

    // elimina cibo
    if ($action === 'delete_cibo') {
        $codice = (int) ($_POST['codice'] ?? 0);
        if ($codice > 0) {
            // recupero il filename prima di cancellare la riga
            $old_img = null;
            $sel = $database->prepare('SELECT immagine FROM cibo WHERE codice=?');
            $sel->bind_param('i', $codice);
            $sel->execute();
            $sel->bind_result($old_img);
            $sel->fetch();
            $sel->close();

            $stmt = $database->prepare('DELETE FROM cibo WHERE codice=?');
            $stmt->bind_param('i', $codice);
            if ($stmt->execute()) {
                delete_cibo_image($old_img);
                admin_flash('success', 'Piatto #' . $codice . ' eliminato.');
            } else {
                admin_flash('danger',
                    'Impossibile eliminare il piatto: probabilmente e' . ' presente in ordini esistenti.');
            }
            $stmt->close();
        }
        header('Location: admin.php?tab=cibi');
        exit;
    }

    // console sql con select max 100
    if ($action === 'run_sql') {
        $sql_query = trim($_POST['sql'] ?? '');

        // tolgo ; finale per il check
        $check = rtrim($sql_query, "; \t\n\r");

        // 1. deve iniziare con SELECT
        if (!preg_match('/^\s*select\b/i', $check)) {
            $sql_result_error = 'Sono ammesse solo query SELECT.';
        }
        // 2. niente parole chiave pericolose
        elseif (preg_match(
            '/\b(insert|update|delete|drop|alter|create|truncate|grant|revoke|replace|rename|call|load_file|into\s+outfile|into\s+dumpfile)\b/i',
            $check
        )) {
            $sql_result_error = 'La query contiene parole chiave non ammesse.';
        }
        // 3. niente piu' istruzioni concatenate
        elseif (substr_count($check, ';') > 0) {
            $sql_result_error = 'Una sola istruzione SELECT alla volta.';
        }
        else {
            // aggiungo LIMIT 100 se non c'e' gia'
            if (!preg_match('/\blimit\s+\d/i', $check)) {
                $check .= ' LIMIT 100';
            }
            try {
                $res = $database->query($check);
                if ($res === false) {
                    $sql_result_error = 'Errore: ' . $database->error;
                } else {
                    $sql_result_rows = [];
                    $sql_result_cols = [];
                    while ($r = $res->fetch_assoc()) {
                        if (empty($sql_result_cols)) {
                            $sql_result_cols = array_keys($r);
                        }
                        $sql_result_rows[] = $r;
                    }
                    if (empty($sql_result_cols) && $res->field_count > 0) {
                        while ($f = $res->fetch_field()) $sql_result_cols[] = $f->name;
                    }
                    $res->free();
                }
            } catch (Throwable $e) {
                $sql_result_error = 'Errore: ' . $e->getMessage();
            }
        }
    }
}

// dashboard stats
function scalar_q(mysqli $db, string $sql)
{
    if ($res = $db->query($sql)) {
        $row = $res->fetch_row();
        $res->free();
        return $row[0] ?? null;
    }
    return null;
}

$stat_ord_tot    = (int)   scalar_q($database, 'SELECT COUNT(*) FROM ordine');
$stat_fatt_tot   = (float) scalar_q($database, 'SELECT COALESCE(SUM(prezzotot),0) FROM ordine');
$stat_clienti    = (int)   scalar_q($database, 'SELECT COUNT(*) FROM cliente');
$stat_ord_oggi   = (int)   scalar_q($database, 'SELECT COUNT(*) FROM ordine WHERE DATE(data_ord) = CURDATE()');
$stat_piatto_top =          scalar_q($database,
    'SELECT c.nome
    FROM contiene ct
    INNER JOIN cibo c ON c.codice = ct.codice
    GROUP BY c.codice
    ORDER BY SUM(ct.quantita) DESC LIMIT 1') ?? '-';

// top piatti e clienti
$top_piatti = [];
if ($res = $database->query(
    'SELECT c.nome, SUM(ct.quantita) AS totq, SUM(ct.quantita * c.prezzo) AS fatt
    FROM contiene ct
    INNER JOIN cibo c ON c.codice = ct.codice
    GROUP BY c.codice
    ORDER BY totq DESC LIMIT 5'
)) {
    while ($r = $res->fetch_assoc()) $top_piatti[] = $r;
    $res->free();
}

$top_clienti = [];
if ($res = $database->query(
    'SELECT o.emailc, cli.nome, COUNT(*) AS n_ord, SUM(o.prezzotot) AS spesa
    FROM ordine o
    INNER JOIN cliente cli ON cli.emailc = o.emailc
    GROUP BY o.emailc
    ORDER BY spesa DESC LIMIT 5'
)) {
    while ($r = $res->fetch_assoc()) $top_clienti[] = $r;
    $res->free();
}

$ordini_giorno = [];
if ($res = $database->query(
    'SELECT DATE(data_ord) AS g, COUNT(*) AS n, SUM(prezzotot) AS f
    FROM ordine
    WHERE data_ord >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
    GROUP BY DATE(data_ord)
    ORDER BY g DESC'
)) {
    while ($r = $res->fetch_assoc()) $ordini_giorno[] = $r;
    $res->free();
}

// lista cibi ed edit
$cibi = [];
if ($res = $database->query('SELECT codice, nome, descr, prezzo, categoria, in_evidenza, immagine FROM cibo ORDER BY categoria, codice')) {
    while ($r = $res->fetch_assoc()) $cibi[(int)$r['codice']] = $r;
    $res->free();
}

$edit_id   = isset($_GET['edit'])   ? (int) $_GET['edit']   : 0;
$edit_cibo = $edit_id > 0 ? ($cibi[$edit_id] ?? null) : null;

// tab attiva gestione server side
$valid_tabs = ['dashboard', 'cibi', 'stats', 'console'];
$active_tab = $_GET['tab'] ?? 'dashboard';
if (!in_array($active_tab, $valid_tabs, true)) $active_tab = 'dashboard';
// se sto modificando un piatto OR ho appena eseguito una query SQL, forzo la tab
if ($edit_cibo) $active_tab = 'cibi';
if ($sql_result_rows !== null || $sql_result_error !== null) $active_tab = 'console';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abarthas | Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="mycss/style-clean.css">
    <link rel="stylesheet" href="fonts/icomoon/icomoon.css">
</head>
<body class="bg-light">
    <?php include 'php/header.php'; ?>

    <main class="container py-5">
        <h1 class="h2 fw-bold mb-4">Pannello admin</h1>
        <?php if ($flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($flash['msg']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
            </div>
        <?php endif; ?>

        <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $active_tab === 'dashboard' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#dashboard" type="button">Dashboard</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $active_tab === 'cibi' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#cibi" type="button">Gestione cibi</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $active_tab === 'stats' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#stats" type="button">Statistiche</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $active_tab === 'console' ? 'active' : '' ?>" data-bs-toggle="tab" data-bs-target="#console" type="button">Console SQL</button>
            </li>
        </ul>

        <div class="tab-content">
            <!-- ================= DASHBOARD ================= -->
            <div class="tab-pane fade <?= $active_tab === 'dashboard' ? 'show active' : '' ?>" id="dashboard">
                <div class="row g-3">
                    <div class="col-md-6 col-xl-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <div class="small text-muted text-uppercase">Ordini totali</div>
                                <div class="fs-2 fw-bold"><?= $stat_ord_tot ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <div class="small text-muted text-uppercase">Fatturato</div>
                                <div class="fs-2 fw-bold">&euro;<?= number_format($stat_fatt_tot, 2) ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <div class="small text-muted text-uppercase">Clienti</div>
                                <div class="fs-2 fw-bold"><?= $stat_clienti ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <div class="small text-muted text-uppercase">Ordini oggi</div>
                                <div class="fs-2 fw-bold"><?= $stat_ord_oggi ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <div class="small text-muted text-uppercase mb-1">Piatto piu' ordinato</div>
                                <div class="fs-4 fw-bold"><?= htmlspecialchars($stat_piatto_top) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= GESTIONE CIBI ================= -->
            <div class="tab-pane fade <?= $active_tab === 'cibi' ? 'show active' : '' ?>" id="cibi">
                <div class="row g-4">
                    <div class="col-lg-7">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <h2 class="h5 fw-bold mb-3">Elenco piatti</h2>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th><th>Nome</th><th>Cat.</th>
                                                <th class="text-end">€</th><th>★</th><th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($cibi as $c): ?>
                                                <tr>
                                                    <td><?= (int)$c['codice'] ?></td>
                                                    <td>
                                                        <strong><?= htmlspecialchars($c['nome']) ?></strong>
                                                        <div class="small text-muted">
                                                            <?= htmlspecialchars($c['descr']) ?>
                                                        </div>
                                                    </td>
                                                    <td><?= htmlspecialchars($c['categoria']) ?></td>
                                                    <td class="text-end"><?= number_format((float)$c['prezzo'], 2) ?></td>
                                                    <td><?= $c['in_evidenza'] ? '★' : '' ?></td>
                                                    <td class="text-end">
                                                        <a href="?edit=<?= (int)$c['codice'] ?>"
                                                        class="btn btn-sm btn-outline-secondary">Modifica</a>
                                                        <form method="post" class="d-inline"
                                                            onsubmit="return confirm('Eliminare il piatto?');">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="action" value="delete_cibo">
                                                            <input type="hidden" name="codice"
                                                                value="<?= (int)$c['codice'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">X</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <h2 class="h5 fw-bold mb-3">
                                    <?= $edit_cibo ? 'Modifica piatto #' . (int)$edit_cibo['codice'] : 'Nuovo piatto' ?>
                                </h2>
                                <form method="post" enctype="multipart/form-data">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action"
                                        value="<?= $edit_cibo ? 'edit_cibo' : 'add_cibo' ?>">
                                    <?php if ($edit_cibo): ?>
                                        <input type="hidden" name="codice" value="<?= (int)$edit_cibo['codice'] ?>">
                                    <?php endif; ?>

                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold">Nome</label>
                                        <input type="text" name="nome" class="form-control" maxlength="60" required
                                            value="<?= $edit_cibo ? htmlspecialchars($edit_cibo['nome']) : '' ?>">
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold">Descrizione</label>
                                        <textarea name="descr" class="form-control" rows="2" maxlength="255" required><?php
                                            echo $edit_cibo ? htmlspecialchars($edit_cibo['descr']) : ''; ?></textarea>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-6 mb-2">
                                            <label class="form-label small fw-semibold">Prezzo &euro;</label>
                                            <input type="number" step="0.01" min="0.01" name="prezzo"
                                                class="form-control" required
                                                value="<?= $edit_cibo ? number_format((float)$edit_cibo['prezzo'], 2, '.', '') : '' ?>">
                                        </div>
                                        <div class="col-6 mb-2">
                                            <label class="form-label small fw-semibold">Categoria</label>
                                            <input type="text" name="categoria" class="form-control" maxlength="40"
                                                value="<?= $edit_cibo ? htmlspecialchars($edit_cibo['categoria']) : 'Vari' ?>">
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold">
                                            Foto <span class="text-muted fw-normal">(jpg, png, webp, gif - max 10&nbsp;MB)</span>
                                        </label>
                                        <input type="file" name="immagine" class="form-control"
                                            accept="image/jpeg,image/png,image/webp,image/gif">
                                        <?php if ($edit_cibo && !empty($edit_cibo['immagine'])): ?>
                                            <div class="small text-muted mt-2 d-flex align-items-center gap-2">
                                                Attuale:
                                                <img src="<?= CIBO_UPLOAD_PATH . htmlspecialchars($edit_cibo['immagine']) ?>"
                                                    alt="" style="height:40px;width:40px;object-fit:cover" class="rounded border">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" name="in_evidenza" id="evid"
                                            <?= $edit_cibo && $edit_cibo['in_evidenza'] ? 'checked' : '' ?>>
                                        <label class="form-check-label small" for="evid">In evidenza in homepage</label>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-order">
                                            <?= $edit_cibo ? 'Salva modifiche' : 'Aggiungi piatto' ?>
                                        </button>
                                        <?php if ($edit_cibo): ?>
                                            <a href="admin.php?tab=cibi" class="btn btn-outline-secondary">Annulla</a>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= STATISTICHE ================= -->
            <div class="tab-pane fade <?= $active_tab === 'stats' ? 'show active' : '' ?>" id="stats">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <h2 class="h5 fw-bold mb-3">Top 5 piatti</h2>
                                <table class="table table-sm">
                                    <thead class="table-light">
                                        <tr><th>Piatto</th><th class="text-end">Quantita'</th><th class="text-end">Fatturato</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($top_piatti as $r): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($r['nome']) ?></td>
                                                <td class="text-end"><?= (int) $r['totq'] ?></td>
                                                <td class="text-end">&euro;<?= number_format((float)$r['fatt'], 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($top_piatti)): ?>
                                            <tr><td colspan="3" class="text-muted text-center">Nessun ordine.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <h2 class="h5 fw-bold mb-3">Top 5 clienti</h2>
                                <table class="table table-sm">
                                    <thead class="table-light">
                                        <tr><th>Cliente</th><th class="text-end">Ordini</th><th class="text-end">Spesa</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($top_clienti as $r): ?>
                                            <tr>
                                                <td>
                                                    <?= htmlspecialchars($r['nome']) ?>
                                                    <div class="small text-muted"><?= htmlspecialchars($r['emailc']) ?></div>
                                                </td>
                                                <td class="text-end"><?= (int) $r['n_ord'] ?></td>
                                                <td class="text-end">&euro;<?= number_format((float)$r['spesa'], 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($top_clienti)): ?>
                                            <tr><td colspan="3" class="text-muted text-center">Nessun ordine.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-body">
                                <h2 class="h5 fw-bold mb-3">Ordini ultimi 14 giorni</h2>
                                <table class="table table-sm">
                                    <thead class="table-light">
                                        <tr><th>Giorno</th><th class="text-end">Ordini</th><th class="text-end">Fatturato</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($ordini_giorno as $r): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($r['g']) ?></td>
                                                <td class="text-end"><?= (int) $r['n'] ?></td>
                                                <td class="text-end">&euro;<?= number_format((float)$r['f'], 2) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($ordini_giorno)): ?>
                                            <tr><td colspan="3" class="text-muted text-center">Nessun ordine recente.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= CONSOLE SQL ================= -->
            <div class="tab-pane fade <?= $active_tab === 'console' ? 'show active' : '' ?>" id="console">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h2 class="h5 fw-bold mb-2">Console SQL</h2>
                        <p class="small text-muted">
                            Solo query <code>SELECT</code>. Le query senza <code>LIMIT</code> vengono
                            automaticamente limitate a 100 righe. Parole chiave di scrittura
                            (INSERT, UPDATE, DELETE, DROP, ...) sono bloccate.
                        </p>
                        <form method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="run_sql">
                            <textarea name="sql" class="form-control font-monospace" rows="5"
                                    placeholder="SELECT * FROM cliente"><?= htmlspecialchars($sql_query) ?></textarea>
                            <div class="mt-2 d-flex gap-2">
                                <button type="submit" class="btn btn-order">Esegui</button>
                                <button type="button" class="btn btn-outline-secondary"
                                        onclick="document.querySelector('textarea[name=sql]').value=''">
                                    Pulisci
                                </button>
                            </div>
                        </form>

                        <?php if ($sql_result_error !== null): ?>
                            <div class="alert alert-danger mt-3"><?= htmlspecialchars($sql_result_error) ?></div>
                        <?php endif; ?>

                        <?php if (is_array($sql_result_rows)): ?>
                            <div class="mt-3">
                                <p class="small text-muted">
                                    <?= count($sql_result_rows) ?> riga/righe restituite.
                                </p>
                                <?php if (empty($sql_result_rows)): ?>
                                    <p class="text-muted">Nessun risultato.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped">
                                            <thead class="table-dark">
                                                <tr>
                                                    <?php foreach ($sql_result_cols as $col): ?>
                                                        <th><?= htmlspecialchars($col) ?></th>
                                                    <?php endforeach; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($sql_result_rows as $row): ?>
                                                    <tr>
                                                        <?php foreach ($sql_result_cols as $col):
                                                            $v = $row[$col]; ?>
                                                            <td>
                                                                <?= $v === null ? '<em class="text-muted">NULL</em>'
                                                                                : htmlspecialchars((string)$v) ?>
                                                            </td>
                                                        <?php endforeach; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'php/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
