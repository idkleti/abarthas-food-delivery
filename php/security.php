<?php
/**
 * security.php
 * ----------------------------------------------------------------------------
 * Tutto il boilerplate di sicurezza dell'app concentrato in un solo file:
 *   - sessione con cookie httponly/samesite/secure
 *   - token CSRF + helper per stampare il campo nascosto e verificare i POST
 *   - rate limiting per i tentativi di login (per-sessione)
 *   - header di sicurezza HTTP
 *
 * Include questo file PRIMA di qualsiasi output, in modo da poter mandare
 * sia la sessione che gli header.
 */

// sessione
function secure_session_start(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') === '443');

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $secure,    // su HTTPS il cookie viaggia solo in HTTPS
        'httponly' => true,       // niente accesso da JavaScript
        'samesite' => 'Lax',      // niente CSRF cross-site
    ]);
    session_name('ABARTHASSESSID');
    session_start();
}

// csrf
function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

// stampa il campo <input type="hidden" name="_csrf" ...> da mettere nei form
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="'
        . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8')
        . '">';
}

// confronto a tempo costante con il token in sessione
function csrf_verify(?string $token): bool
{
    return !empty($_SESSION['_csrf_token'])
        && is_string($token)
        && hash_equals($_SESSION['_csrf_token'], $token);
}

/*
    da chiamare in cima a ogni handler POST

    se il token non e' valido interrompe con 403, cosi' nessun codice
    pericoloso viene eseguito.
*/
function csrf_check_or_die(): void
{
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        http_response_code(403);
        exit('Richiesta non valida (CSRF).');
    }
}

// rate limiting login
const LOGIN_MAX_ATTEMPTS = 5;
const LOGIN_LOCKOUT_SECS = 30;

function login_is_locked(): int
{
    $until = (int) ($_SESSION['login_lockout_until'] ?? 0);
    $remaining = $until - time();
    return $remaining > 0 ? $remaining : 0;
}

function login_register_failure(): void
{
    $_SESSION['login_attempts'] = (int) ($_SESSION['login_attempts'] ?? 0) + 1;
    if ($_SESSION['login_attempts'] >= LOGIN_MAX_ATTEMPTS) {
        $_SESSION['login_lockout_until'] = time() + LOGIN_LOCKOUT_SECS;
        $_SESSION['login_attempts'] = 0;
    }
    // piccolo rallentamento sempre, per smorzare il brute force
    usleep(500_000);
}

function login_register_success(): void
{
    unset($_SESSION['login_attempts'], $_SESSION['login_lockout_until']);
    // rigenero l'ID di sessione per evitare session fixation
    session_regenerate_id(true);
}

// header http sicurezza
function send_security_headers(): void
{
    if (headers_sent()) return;

    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

    // CSP: limito da dove possono arrivare risorse.
    // unsafe-inline su style-src perche' abbiamo qualche style="..." inline.
    // sugli script ho spostato tutto in file separati, quindi NON serve unsafe-inline su script-src.
    header(
        "Content-Security-Policy: " .
        "default-src 'self'; " .
        "script-src 'self' https://cdn.jsdelivr.net https://code.jquery.com; " .
        "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com; " .
        "font-src 'self' https://fonts.gstatic.com data:; " .
        "img-src 'self' data:; " .
        "media-src 'self'; " .
        "connect-src 'self'; " .
        "object-src 'none'; " .
        "base-uri 'self'; " .
        "form-action 'self'; " .
        "frame-ancestors 'none'"
    );
}

// auto boot
secure_session_start();
send_security_headers();
