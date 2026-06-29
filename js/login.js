/*
 * login.js - alterna i tab "Accedi" / "Registrati" nella pagina login.php
 *
 * Comportamento corretto: cliccare sul titolo del form NON attivo lo apre.
 * Cliccare sul titolo del form gia' attivo non fa nulla, cosi' non possiamo
 * mai finire nello stato bug in cui entrambi i form sono nascosti.
 */
(function () {
    'use strict';

    const loginSection  = document.querySelector('.login');
    const signupSection = document.querySelector('.signup');
    const loginTitle    = document.getElementById('login');
    const signupTitle   = document.getElementById('signup');

    if (!loginSection || !signupSection || !loginTitle || !signupTitle) return;

    function showLogin() {
        loginSection.classList.remove('slide-up');
        signupSection.classList.add('slide-up');
    }

    function showSignup() {
        signupSection.classList.remove('slide-up');
        loginSection.classList.add('slide-up');
    }

    loginTitle.addEventListener('click', function () {
        if (loginSection.classList.contains('slide-up')) showLogin();
    });

    signupTitle.addEventListener('click', function () {
        if (signupSection.classList.contains('slide-up')) showSignup();
    });
})();
