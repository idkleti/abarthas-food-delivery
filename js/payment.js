/*
 * payment.js
 * - Formatta il numero carta a gruppi di 4 cifre
 * - Formatta la scadenza in MM/AA
 * - Aggiorna in tempo reale la "carta finta" mostrata sopra al form
 */
(function () {
    'use strict';

    const numero       = document.getElementById('card_numero');
    const scadenza     = document.getElementById('card_scadenza');
    const cvv          = document.getElementById('card_cvv');
    const intestatario = document.getElementById('card_intestatario');

    const previewNumber = document.getElementById('cardPreviewNumber');
    const previewName   = document.getElementById('cardPreviewName');
    const previewExpiry = document.getElementById('cardPreviewExpiry');

    const PLACEHOLDER_DIGITS = '•••• •••• •••• ••••';

    // ---- Numero carta: solo cifre, gruppi da 4 ----
    if (numero) {
        numero.addEventListener('input', function () {
            const cifre = numero.value.replace(/\D/g, '').slice(0, 16);
            numero.value = cifre.replace(/(.{4})/g, '$1 ').trim();

            if (previewNumber) {
                const visualizzato = numero.value || PLACEHOLDER_DIGITS;
                previewNumber.textContent = visualizzato.padEnd(PLACEHOLDER_DIGITS.length, ' ');
            }
        });
    }

    // ---- Scadenza: MM/AA ----
    if (scadenza) {
        scadenza.addEventListener('input', function (e) {
            let v = scadenza.value.replace(/\D/g, '').slice(0, 4);
            if (v.length >= 3) v = v.slice(0, 2) + '/' + v.slice(2);
            scadenza.value = v;
            if (previewExpiry) {
                previewExpiry.textContent = v || 'MM/AA';
            }
        });
    }

    // ---- CVV: solo cifre ----
    if (cvv) {
        cvv.addEventListener('input', function () {
            cvv.value = cvv.value.replace(/\D/g, '').slice(0, 4);
        });
    }

    // ---- Intestatario: aggiorna anteprima ----
    if (intestatario && previewName) {
        intestatario.addEventListener('input', function () {
            previewName.textContent = intestatario.value.trim() || 'Nome Cognome';
        });
    }
})();
