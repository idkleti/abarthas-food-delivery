/*
 * script.js - logica JS della homepage
 *
 * Bootstrap 5 e' gia' caricato come bundle separato (bootstrap.bundle.min.js),
 * quindi non c'e' bisogno di reimplementare collapse / modale / transizioni.
 *
 * Cosa fa questo file:
 *   - apre la modale "galleryModal" e imposta l'immagine cliccata
 *     come src dell'<img> dentro la modale.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        const modalImg = document.getElementById('modalImage');
        if (!modalImg) return;

        document.querySelectorAll('.gallery-link').forEach(function (link) {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const src = link.getAttribute('data-img');
                if (src) modalImg.setAttribute('src', src);
            });
        });
    });
})();
