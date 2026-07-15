/**
 * Alsendo - PrestaShop shipping module
 *
 * @author    Innovation Software
 * @copyright 2026 Innovation Software
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

(function () {
    'use strict';

    if (!document.getElementById('info-modal')) {
        var overlay = document.createElement('div');
        overlay.id = 'info-modal';
        var box = document.createElement('div');
        var msg = document.createElement('p');
        msg.id = 'info-modal-message';
        var footer = document.createElement('div');
        footer.className = 'text-end';
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-secondary';
        btn.id = 'info-modal-ok';
        btn.textContent = 'OK';
        footer.appendChild(btn);
        box.appendChild(msg);
        box.appendChild(footer);
        overlay.appendChild(box);
        document.body.appendChild(overlay);
    }

    function stripHtml(str) {
        if (!str) return '';
        var tmp = document.createElement('div');
        tmp.innerHTML = str;
        return tmp.textContent || tmp.innerText || '';
    }

    function closeModal() {
        var modal = document.getElementById('info-modal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('alsendo-modal-error', 'alsendo-modal-success', 'alsendo-modal-info');
        }
    }

    function showInfoModal(msg, type) {
        var modal = document.getElementById('info-modal');
        var el = document.getElementById('info-modal-message');
        if (!modal || !el) return;

        var clean = stripHtml(String(msg));
        el.textContent = '';
        clean.split('\n').forEach(function(line, i) {
            if (i > 0) el.appendChild(document.createElement('br'));
            el.appendChild(document.createTextNode(line));
        });

        modal.classList.remove('alsendo-modal-error', 'alsendo-modal-success', 'alsendo-modal-info');
        if (type === 'error') {
            modal.classList.add('alsendo-modal-error');
        } else if (type === 'success') {
            modal.classList.add('alsendo-modal-success');
        } else {
            modal.classList.add('alsendo-modal-info');
        }

        modal.style.display = 'flex';
    }

    function showErrorModal(msg) {
        showInfoModal(msg, 'error');
    }

    function showSuccessModal(msg) {
        showInfoModal(msg, 'success');
    }

    document.addEventListener('click', function (e) {
        if (e.target && e.target.id === 'info-modal-ok') {
            closeModal();
        }
    });

    document.addEventListener('click', function (e) {
        if (e.target && e.target.id === 'info-modal') {
            closeModal();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            var modal = document.getElementById('info-modal');
            if (modal && modal.style.display === 'flex') {
                closeModal();
            }
        }
    });

    window.showInfoModal = showInfoModal;
    window.showErrorModal = showErrorModal;
    window.showSuccessModal = showSuccessModal;
})();
