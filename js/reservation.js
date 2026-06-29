/*
 * reservation.js
 * - Carica i piatti (con ingredienti) dall'API
 * - Per ogni piatto: quantita, pannello "personalizza" con checkbox ingredienti + nota
 * - Aggiorna in tempo reale il subtotale
 */
(function () {
    'use strict';

    const container = document.getElementById('menu-items-container');
    const form      = document.getElementById('mainform');
    const totalEl   = document.getElementById('cart-total');

    if (!container || !form || !totalEl) return;

    const prezzi = Object.create(null);

    function aggiornaTotale() {
        let totale = 0;
        container.querySelectorAll('input.qty-input').forEach(function (input) {
            const codice = parseInt(input.dataset.codice, 10);
            const qty    = parseInt(input.value, 10) || 0;
            if (prezzi[codice]) totale += prezzi[codice] * qty;
        });
        totalEl.textContent = '€' + totale.toFixed(2);
    }

    function escapeHTML(str) {
        return String(str).replace(/[&<>"']/g, function (c) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c];
        });
    }

    function ingredientiHTML(codice, ingredienti) {
        if (!ingredienti || ingredienti.length === 0) {
            return '<p class="small text-muted mb-0">Nessun ingrediente personalizzabile.</p>';
        }

        const fissi = [];
        const rimovibili = [];

        ingredienti.forEach(function (ing) {
            const allergene = ing.allergene
                ? ' <span class="badge bg-warning text-dark">' + escapeHTML(ing.allergene) + '</span>'
                : '';
            if (ing.rimovibile) {
                rimovibili.push(''
                    + '<div class="form-check form-check-inline mb-1">'
                    +   '<input class="form-check-input ing-toggle" type="checkbox" '
                    +          'id="ing_' + codice + '_' + ing.id + '" '
                    +          'name="rmv' + codice + '[]" value="' + ing.id + '" '
                    +          'data-name="' + escapeHTML(ing.nome) + '">'
                    +   '<label class="form-check-label small" for="ing_' + codice + '_' + ing.id + '">'
                    +     escapeHTML(ing.nome) + allergene
                    +   '</label>'
                    + '</div>');
            } else {
                fissi.push(''
                    + '<span class="badge bg-light text-dark border me-1 mb-1">'
                    +   escapeHTML(ing.nome) + allergene
                    + '</span>');
            }
        });

        let html = '';
        if (fissi.length) {
            html += '<div class="small mb-2"><span class="text-muted me-2">Sempre inclusi:</span>'
                  + fissi.join('') + '</div>';
        }
        if (rimovibili.length) {
            html += '<div class="small"><span class="text-muted d-block mb-1">'
                  + 'Spunta gli ingredienti che vuoi <strong>togliere</strong>:</span>'
                  + rimovibili.join('') + '</div>';
        }
        return html;
    }

    function cardHTML(item, i) {
        const img = item.immagine
            ? 'images/cibi/' + item.immagine
            : 'images/logo.png';
        const customId = 'custom_' + item.codice;
        return ''
            + '<div class="col-12">'
            +   '<div class="card border shadow-sm">'
            +     '<div class="card-body p-3">'
            +       '<div class="d-flex align-items-center gap-3 flex-wrap">'
            +         '<img src="' + img + '" onerror="this.src=\'images/logo.png\'" '
            +              'class="rounded flex-shrink-0" alt="" width="80" height="80" '
            +              'style="object-fit:cover;">'
            +         '<div class="flex-grow-1" style="min-width:200px;">'
            +           '<strong class="text-uppercase d-block">' + escapeHTML(item.nome)  + '</strong>'
            +           '<small class="text-muted">'              + escapeHTML(item.descr) + '</small>'
            +         '</div>'
            +         '<div class="text-end">'
            +           '<div class="price-btn mb-2">€' + item.prezzo.toFixed(2) + '</div>'
            +           '<div class="d-flex align-items-center gap-2">'
            +             '<input type="number" name="quant' + item.codice + '" '
            +                    'data-codice="' + item.codice + '" '
            +                    'min="0" max="10" value="0" '
            +                    'class="form-control form-control-sm text-center qty-input" '
            +                    'style="width:70px;">'
            +             '<button type="button" class="btn btn-sm btn-outline-secondary toggle-custom" '
            +                     'data-bs-toggle="collapse" data-bs-target="#' + customId + '" '
            +                     'aria-expanded="false">'
            +               '<i class="icon icon-spoon"></i>'
            +             '</button>'
            +           '</div>'
            +         '</div>'
            +       '</div>'

            +       '<div class="collapse mt-3" id="' + customId + '">'
            +         '<hr>'
            +         '<h6 class="small fw-bold text-uppercase text-muted">Personalizza</h6>'
            +         '<div class="mb-2">' + ingredientiHTML(item.codice, item.ingredienti) + '</div>'
            +         '<label class="small fw-bold mb-1" for="pn_' + item.codice + '">Nota per la cucina</label>'
            +         '<textarea id="pn_' + item.codice + '" name="pn' + item.codice + '" '
            +                   'class="form-control form-control-sm" rows="2" maxlength="200" '
            +                   'placeholder="Es. ben cotto, extra mozzarella..."></textarea>'
            +       '</div>'

            +     '</div>'
            +   '</div>'
            + '</div>';
    }

    fetch('php/ristoapi.php', { credentials: 'same-origin' })
        .then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(function (data) {
            if (!Array.isArray(data) || data.length === 0) {
                container.innerHTML = '<p class="text-center text-muted">Nessun piatto disponibile.</p>';
                return;
            }
            container.innerHTML = data.map(function (item, i) {
                prezzi[item.codice] = item.prezzo;
                return cardHTML(item, i);
            }).join('');
        })
        .catch(function () {
            container.innerHTML = '<p class="text-center text-danger">Errore nel caricamento del menu.</p>';
        });

    container.addEventListener('input', function (e) {
        if (e.target.classList.contains('qty-input')) aggiornaTotale();
    });

    form.addEventListener('submit', function (e) {
        let totale = 0;
        container.querySelectorAll('input.qty-input').forEach(function (i) {
            totale += parseInt(i.value, 10) || 0;
        });
        if (totale === 0) {
            e.preventDefault();
            alert('Seleziona almeno un piatto per procedere.');
        }
    });
})();
