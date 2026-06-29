<?php
require 'php/security.php';
require 'php/dbConnect.php';

$errore = '';
$mode   = 'login';

if (!$dbConnect) {
    $errore = 'Servizio momentaneamente non disponibile. Riprova piu tardi.';
}

/* ---------------------------------------------------------------
   Helper: dopo successo, fai il redirect ed esci.
   --------------------------------------------------------------- */
function redirect_after_auth(string $url, $database): void
{
    if ($database) $database->close();
    header('Location: ' . $url);
    exit;
}

/* ---------------------------------------------------------------
   REGISTRAZIONE
   --------------------------------------------------------------- */
if ($dbConnect && isset($_POST['names'], $_POST['emails'], $_POST['psws'])) {
    csrf_check_or_die();
    $mode = 'signup';

    // Honeypot: campo nascosto. I bot di solito lo riempiono, gli umani no.
    if (!empty($_POST['website'])) {
        // Silenziosamente fingo un successo per non dare info utili al bot
        $errore = '';
    } else {
        $nome  = trim($_POST['names']);
        $email = trim($_POST['emails']);
        $psw   = $_POST['psws'];

        if ($nome === '' || $email === '' || $psw === '') {
            $errore = 'Compila tutti i campi.';
        } elseif (mb_strlen($nome) > 60) {
            $errore = 'Il nome e' . ' troppo lungo (max 60 caratteri).';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 120) {
            $errore = 'Email non valida.';
        } elseif (strlen($psw) < 8 || strlen($psw) > 128) {
            $errore = 'La password deve avere fra 8 e 128 caratteri.';
        } else {
            $check = $database->prepare('SELECT 1 FROM cliente WHERE emailc = ?');
            $check->bind_param('s', $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $errore = 'Email gia' . ' registrata. Effettua il login.';
            } else {
                $hash = password_hash($psw, PASSWORD_DEFAULT);
                $stmt = $database->prepare(
                    'INSERT INTO cliente (emailc, nome, psw) VALUES (?, ?, ?)'
                );
                $stmt->bind_param('sss', $email, $nome, $hash);
                if ($stmt->execute()) {
                    login_register_success(); // regen session id
                    $_SESSION['email']    = $email;
                    $_SESSION['nome']     = $nome;
                    $_SESSION['is_admin'] = 0;
                    $stmt->close();
                    $check->close();
                    redirect_after_auth('index.php', $database);
                }
                $errore = 'Impossibile completare la registrazione.';
                $stmt->close();
            }
            $check->close();
        }
    }
}

/* ---------------------------------------------------------------
   LOGIN
   --------------------------------------------------------------- */
if ($dbConnect && isset($_POST['emaill'], $_POST['pswl'])) {
    csrf_check_or_die();
    $mode = 'login';

    $locked = login_is_locked();
    if ($locked > 0) {
        $errore = 'Troppi tentativi. Riprova fra ' . $locked . ' secondi.';
    } else {
        $email = trim($_POST['emaill']);
        $psw   = $_POST['pswl'];

        if ($email === '' || $psw === '') {
            $errore = 'Compila tutti i campi.';
        } else {
            $stmt = $database->prepare(
                'SELECT emailc, nome, psw, is_admin FROM cliente WHERE emailc = ?'
            );
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();

            $ok = false;
            if ($row = $result->fetch_assoc()) {
                if (password_verify($psw, $row['psw'])) {
                    $ok = true;
                    login_register_success();
                    $_SESSION['email']    = $row['emailc'];
                    $_SESSION['nome']     = $row['nome'];
                    $_SESSION['is_admin'] = (int) $row['is_admin'];
                    $stmt->close();
                    redirect_after_auth('index.php', $database);
                }
            } else {
                // emulo il costo di password_verify anche se l'utente non esiste,
                // per non dare info via timing-attack
                password_verify($psw, '$2y$12$invalidinvalidinvalidinvalidinvalidinvalidinvalidinvalidinva');
            }

            if (!$ok) {
                login_register_failure();
                $errore = 'Credenziali non valide.';
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abarthas | Accedi</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="mycss/login.css">
</head>
<body>
    <div class="overlay"></div>
    <video src="images/background.mp4" muted autoplay loop playsinline></video>

    <main id="main">
      <div class="form-wrapper">
        <div class="form-structor" data-mode="<?= htmlspecialchars($mode) ?>">
            <!-- REGISTRAZIONE -->
            <section class="signup<?= $mode === 'login' ? ' slide-up' : '' ?>">
                <h2 class="form-title" id="signup">
                    <span class="form-title-hint">oppure</span>Registrati
                </h2>
                <form method="post" action="login.php" novalidate>
                    <?= csrf_field() ?>
                    <!-- Honeypot: invisibile per gli umani -->
                    <input type="text" name="website" tabindex="-1" autocomplete="off"
                           style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0;">
                    <div class="form-holder">
                        <input type="text"     name="names"  class="input" placeholder="Nome"
                               maxlength="60"  required autocomplete="name">
                        <input type="email"    name="emails" class="input" placeholder="Email"
                               maxlength="120" required autocomplete="email">
                        <input type="password" name="psws"   class="input" placeholder="Password (min 8)"
                               minlength="8"   maxlength="128" required autocomplete="new-password">
                    </div>
                    <button type="submit" class="submit-btn">Registrati</button>
                </form>
            </section>

            <!-- LOGIN -->
            <section class="login<?= $mode === 'signup' ? ' slide-up' : '' ?>">
                <div class="center">
                    <h2 class="form-title" id="login">
                        <span class="form-title-hint">oppure</span>Accedi
                    </h2>
                    <form method="post" action="login.php" novalidate>
                        <?= csrf_field() ?>
                        <div class="form-holder">
                            <input type="email"    name="emaill" class="input" placeholder="Email"
                                   maxlength="120" required autocomplete="email">
                            <input type="password" name="pswl"   class="input" placeholder="Password"
                                   maxlength="128" required autocomplete="current-password">
                        </div>
                        <button type="submit" class="submit-btn">Accedi</button>
                    </form>
                </div>
            </section>

        </div>
        <?php if ($errore !== ''): ?>
            <div class="login-error" role="alert"><?= htmlspecialchars($errore) ?></div>
        <?php endif; ?>
      </div>
    </main>

    <script src="js/login.js"></script>
</body>
</html>
