<?php
    require 'php/security.php';
    $page_active = '';

    $faqs = [
        ['Quanto tempo serve per ricevere il mio ordine?',
        'Dipende dal tipo di spedizione: PRIME+ punta a 20-25 minuti, PRIME a 35-45 minuti, Eco a circa un\'ora.'],
        ['Posso pagare alla consegna?',
        'No, il pagamento avviene online al momento dell\'ordine. Accettiamo Visa, Mastercard e American Express.'],
        ['Posso modificare un ordine dopo averlo confermato?',
        'Una volta confermato non e\' modificabile dall\'app. Contatta il ristorante telefonicamente entro 5 minuti.'],
        ['Posso togliere degli ingredienti dai piatti?',
        'Si, nella pagina di ordine ogni piatto ha una lista di ingredienti opzionali che puoi deselezionare. Puoi anche aggiungere note libere.'],
        ['Come gestite gli allergeni?',
        'Su ogni ingrediente trovi l\'eventuale allergene. Per intolleranze gravi segnalalo nelle note: la cucina viene avvisata.'],
        ['Consegnate in tutta Italia?',
        'Per ora solo nella zona di Ferrara. Stiamo lavorando per ampliare il servizio.'],
        ['Come tutelate i miei dati?',
        'Vedi la pagina <a href="terms.php">Termini e privacy</a> per il dettaglio.'],
        ['Come posso cancellare il mio account?',
        'Scrivi a info@abarthas.example specificando l\'email registrata. La cancellazione e\' completata entro 24 ore.'],
    ];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abarthas | FAQ</title>
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
                    <h1 class="section-title"><strong>DOMANDE FREQUENTI</strong></h1>
                    <div class="divider"><div class="icon-wrap"><i class="icon icon-spoon"></i></div></div>
                    <div class="slogan mb-0">Le risposte alle cose che ci chiedete piu' spesso</div>
                </div>
            </div>
        </section>
    </div>

    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="accordion shadow-sm" id="faqAccordion">
                    <?php foreach ($faqs as $i => $faq):
                        $id = 'faq' . $i; ?>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="head<?= $id ?>">
                                <button class="accordion-button <?= $i === 0 ? '' : 'collapsed' ?>"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#col<?= $id ?>"
                                        aria-expanded="<?= $i === 0 ? 'true' : 'false' ?>"
                                        aria-controls="col<?= $id ?>">
                                    <?= htmlspecialchars($faq[0]) ?>
                                </button>
                            </h2>
                            <div id="col<?= $id ?>"
                                class="accordion-collapse collapse <?= $i === 0 ? 'show' : '' ?>"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body text-muted">
                                    <?= $faq[1] /* alcuni contengono <a>, quindi non escapo */ ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include 'php/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
