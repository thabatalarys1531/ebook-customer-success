(function () {
    'use strict';

    // Menu lateral em telas pequenas
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebar-overlay');
    var toggle = document.getElementById('sidebar-toggle');

    function openSidebar() {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
    }
    function closeSidebar() {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    }
    if (toggle) toggle.addEventListener('click', openSidebar);
    if (overlay) overlay.addEventListener('click', closeSidebar);

    // Modais genéricos: <button data-modal-open="id-do-modal">, <div id="id-do-modal" x-modal>,
    // e qualquer elemento dentro do modal com [data-modal-close] fecha.
    document.querySelectorAll('[data-modal-open]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var modal = document.getElementById(btn.getAttribute('data-modal-open'));
            if (modal) modal.classList.add('is-open');
        });
    });
    document.querySelectorAll('[x-modal]').forEach(function (modal) {
        modal.querySelectorAll('[data-modal-close]').forEach(function (el) {
            el.addEventListener('click', function () {
                modal.classList.remove('is-open');
            });
        });
        modal.addEventListener('click', function (event) {
            if (event.target === modal) modal.classList.remove('is-open');
        });
    });

    // Formulários que enviam via fetch (sem recarregar a página até salvar)
    document.querySelectorAll('form.js-ajax-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            event.preventDefault();

            var errorBox = form.querySelector('.js-form-error');
            var submitBtn = form.querySelector('button[type="submit"]');
            if (errorBox) { errorBox.textContent = ''; errorBox.classList.add('hidden'); }
            if (submitBtn) { submitBtn.disabled = true; submitBtn.dataset.originalText = submitBtn.textContent; submitBtn.textContent = 'Salvando...'; }

            fetch(form.getAttribute('action'), {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'fetch' }
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        if (errorBox) {
                            errorBox.textContent = data.message || 'Não foi possível salvar. Tente novamente.';
                            errorBox.classList.remove('hidden');
                        }
                        if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = submitBtn.dataset.originalText; }
                    }
                })
                .catch(function () {
                    if (errorBox) {
                        errorBox.textContent = 'Erro de conexão. Verifique sua internet e tente novamente.';
                        errorBox.classList.remove('hidden');
                    }
                    if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = submitBtn.dataset.originalText; }
                });
        });
    });

    // Botões de "marcar alerta como resolvido"
    document.querySelectorAll('.js-resolve-alert').forEach(function (btn) {
        btn.addEventListener('click', function () {
            btn.disabled = true;
            fetch('api/alerts_resolve.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + encodeURIComponent(btn.dataset.alertId) + '&csrf_token=' + encodeURIComponent(btn.dataset.csrf)
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        btn.disabled = false;
                    }
                })
                .catch(function () { btn.disabled = false; });
        });
    });
})();
