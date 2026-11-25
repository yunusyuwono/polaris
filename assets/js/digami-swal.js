(function () {
    function hasSwal() {
        return typeof window.Swal !== 'undefined';
    }

    function extractConfirmMessage(onclick) {
        var match = onclick.match(/confirm\s*\(\s*(['"])(.*?)\1\s*\)/);
        return match ? match[2] : 'Lanjutkan aksi ini?';
    }

    var nativeAlert = window.alert;
    window.alert = function (message) {
        if (!hasSwal()) {
            nativeAlert(message);
            return;
        }

        try {
            window.sessionStorage.setItem('digamiPendingAlert', String(message || ''));
        } catch (error) {}

        Swal.fire({
            title: 'Informasi',
            text: String(message || ''),
            icon: 'info',
            confirmButtonText: 'OK'
        });
    };

    document.addEventListener('click', function (event) {
        var target = event.target.closest('a, button, input[type="submit"]');
        if (!target) return;

        var onclick = target.getAttribute('onclick') || '';
        if (!/confirm\s*\(/.test(onclick) || !hasSwal()) return;

        event.preventDefault();
        event.stopImmediatePropagation();

        Swal.fire({
            title: 'Konfirmasi',
            text: extractConfirmMessage(onclick),
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya',
            cancelButtonText: 'Batal'
        }).then(function (result) {
            if (!result.isConfirmed) return;

            target.removeAttribute('onclick');
            if (target.tagName === 'A' && target.href) {
                window.location.href = target.href;
                return;
            }

            if (target.form) {
                target.form.submit();
            }
        });
    }, true);

    document.addEventListener('DOMContentLoaded', function () {
        if (!hasSwal()) return;

        try {
            var pendingAlert = window.sessionStorage.getItem('digamiPendingAlert');
            if (pendingAlert) {
                window.sessionStorage.removeItem('digamiPendingAlert');
                Swal.fire({
                    title: 'Informasi',
                    text: pendingAlert,
                    icon: 'info',
                    confirmButtonText: 'OK'
                });
            }
        } catch (error) {}

        document.querySelectorAll('.alert-success, .alert-danger, .alert-warning, .alert-info').forEach(function (alertEl) {
            if (alertEl.dataset.swalShown === '1') return;
            var text = alertEl.textContent.trim();
            if (!text) return;

            alertEl.dataset.swalShown = '1';
            var icon = 'info';
            if (alertEl.classList.contains('alert-success')) icon = 'success';
            if (alertEl.classList.contains('alert-danger')) icon = 'error';
            if (alertEl.classList.contains('alert-warning')) icon = 'warning';

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: icon,
                title: text,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        });
    });
})();
