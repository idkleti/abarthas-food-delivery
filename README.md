# Abarthas

Sito di food delivery per un'azienda fittizia.

Stack: PHP 8, MySQL, Bootstrap 5, vanilla JS. Niente framework, niente Composer: gira su un classico XAMPP.

## Cosa c'è dentro

* Catalogo piatti con categorie, ingredienti e allergeni
* Registrazione e login utenti (bcrypt, CSRF, rate limit sul login)
* Ordine personalizzabile: l'utente toglie ingredienti dai piatti e lascia note per la cucina
* Pagina di pagamento finta (i dati carta non vengono salvati né validati realmente)
* Pannello admin con CRUD piatti, upload foto, statistiche e console SQL in sola lettura
* Storico ordini nel profilo utente

## Setup

1. Clona il repo dentro la cartella `htdocs/` di XAMPP.
2. Avvia Apache e MySQL dal pannello XAMPP.
3. Importa il database:

```bash
mysql -u root < consegne.sql
```

4. Apri `http://localhost/NEWfoodDelivery/`.

Le credenziali MySQL stanno in `php/dbConnect.php` (utente `root`, password vuota, default di XAMPP). Se nella tua installazione hai altro, modifica lì.

## Account demo

```
email:    leti@leti.leti
password: letileti2025
```

È un account admin, quindi può accedere anche ad `admin.php`.

## Struttura del progetto

```
admin.php            pannello admin (CRUD piatti, statistiche, console SQL)
allergeni.php        elenco allergeni
faq.php              FAQ
index.php            home
login.php            login + registrazione
menu.php             menu pubblico
payment.php          pagamento + conferma ordine
profile.php          storico ordini utente
reservation.php      ordine + personalizzazione piatti
terms.php            termini e privacy

php/
  dbConnect.php      connessione mysqli
  footer.php         footer condiviso
  header.php         header e nav condivisa
  logout.php         POST che distrugge la sessione
  ristoapi.php       endpoint JSON usato dal form ordine
  security.php       sessione, CSRF, rate limit, header HTTP

js/                  reservation.js, login.js, payment.js, script.js
mycss/               style-clean.css (tutto il sito), login.css (solo /login.php)
fonts/icomoon/       set di icone

images/              asset statici (logo, video sfondo, icone pagamento)
images/cibi/         foto piatti caricate dall'admin (nome generato random)

consegne.sql         dump iniziale del DB
```

## Note di sicurezza

* CSRF token su tutti i POST, confronto a tempo costante (`hash_equals`)
* Rate limit sul login: 5 tentativi falliti, poi 30 secondi di blocco
* `session_regenerate_id(true)` dopo ogni login riuscito, per evitare session fixation
* Header HTTP restrittivi: Content-Security-Policy, X-Frame-Options DENY, X-Content-Type-Options, Referrer-Policy
* Upload immagini: MIME verificato con `finfo`, nome file generato random, dimensione massima 10MB
* La console SQL del pannello admin accetta solo SELECT singole, blocca parole chiave di scrittura, limita a 100 righe

Cose che NON ci sono perché è un progetto di corso: HTTPS forzato, password reset via email, gateway di pagamento vero, paginazione lato server, test automatici.
