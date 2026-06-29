<?php
    require 'php/security.php';
    $page_active = '';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abarthas | Termini e privacy</title>
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
                <h1 class="section-title"><strong>TERMINI E PRIVACY</strong></h1>
                <div class="divider"><div class="icon-wrap"><i class="icon icon-spoon"></i></div></div>
                <div class="slogan mb-0">Come trattiamo i tuoi dati</div>
            </div>
        </div>
    </section>
</div>

<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-9">

            <div class="alert alert-warning border-0 shadow-sm mb-5" role="alert">
                <h2 class="h5 fw-bold mb-2">
                    <i class="icon icon-spoon me-2"></i>Avviso importante
                </h2>
                <p class="mb-0">
                    <strong>Abarthas</strong> e' un <strong>progetto scolastico</strong> realizzato per
                    finalita' didattiche. <strong>Non e' un servizio reale</strong>: non eseguiamo
                    consegne, non incassiamo pagamenti e <strong>non conserviamo realmente i dati</strong>
                    inseriti in questo sito. Il database utilizzato e' una simulazione locale
                    e tutte le informazioni sono inventate o utilizzate solo per dimostrare il
                    funzionamento dell'applicazione.
                </p>
            </div>

            <article class="bg-white p-4 rounded shadow-sm">
                <h2 class="h4 fw-bold mb-3">1. Natura del servizio</h2>
                <p>
                    Il sito Abarthas e' realizzato come progetto universitario.
                    Ogni riferimento a tariffe, tempi di consegna, indirizzi, ordini o
                    pagamenti e' puramente dimostrativo.
                </p>

                <hr>

                <h2 class="h4 fw-bold mb-3">2. Dati personali raccolti</h2>
                <p>Per consentire la simulazione, il sito gestisce localmente:</p>
                <ul>
                    <li>Nome e indirizzo email forniti in fase di registrazione;</li>
                    <li>Password (sempre salvata come <em>hash bcrypt</em>, mai in chiaro);</li>
                    <li>Indirizzo e note di consegna inseriti nel form dell'ordine;</li>
                    <li>Elenco dei piatti scelti e personalizzazioni.</li>
                </ul>
                <p>
                    Questi dati restano sul database locale dell'istanza dove gira il
                    progetto e <strong>non vengono trasmessi a terzi</strong>.
                </p>

                <hr>

                <h2 class="h4 fw-bold mb-3">3. Dati della carta di credito</h2>
                <p>
                    Il pagamento e' <strong>simulato</strong>: i dati della carta inseriti nel form
                    vengono validati solo come formato (lunghezza, scadenza, CVV) e
                    poi <strong>scartati immediatamente</strong> dalla memoria.
                    Nessun dato di pagamento viene mai salvato nel database o nei log
                    dell'applicazione.
                </p>

                <hr>

                <h2 class="h4 fw-bold mb-3">4. Misure di sicurezza</h2>
                <p>
                    Anche se il progetto e' didattico, sono state applicate le pratiche
                    standard del settore:
                </p>
                <ul>
                    <li>password salvate come hash bcrypt cost 12;</li>
                    <li>token CSRF su ogni form di modifica;</li>
                    <li>cookie di sessione con <code>HttpOnly</code> e <code>SameSite=Lax</code>;</li>
                    <li>limite di tentativi di login (rate limiting);</li>
                    <li>header HTTP di sicurezza (CSP, X-Frame-Options, ecc.);</li>
                    <li>tutte le query SQL eseguite tramite prepared statements.</li>
                </ul>

                <hr>

                <h2 class="h4 fw-bold mb-3">5. Cookie</h2>
                <p>
                    Viene utilizzato solo un cookie tecnico di sessione
                    (<code>ABARTHASSESSID</code>) per tenere l'utente loggato.
                    Nessun cookie di profilazione o di terze parti.
                </p>

                <hr>

                <h2 class="h4 fw-bold mb-3">6. Diritti dell'utente</h2>
                <p>
                    Pur essendo un progetto didattico, gli utenti possono richiedere
                    in qualunque momento la cancellazione dei propri dati scrivendo
                    al responsabile del progetto.
                </p>

                <hr>

                <h2 class="h4 fw-bold mb-3">7. Contatti</h2>
                <p class="mb-0">
                    Per qualsiasi richiesta relativa ai dati personali o al funzionamento
                    del sito: <em>info@abarthas.example</em> (indirizzo di fantasia).
                </p>
            </article>

        </div>
    </div>
</main>

<?php include 'php/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
