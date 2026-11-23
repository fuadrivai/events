import QRCode from 'qrcode';

const gallery = document.getElementById('photo-event-gallery');

if (gallery) {
    const qrModal = document.getElementById('photo-qr-modal');
    const qrCanvas = document.getElementById('photo-qr-canvas');
    const qrLink = document.getElementById('photo-qr-link');
    const qrExpiration = document.getElementById('photo-qr-expiration');
    const qrError = document.getElementById('photo-qr-error');
    const bulkForm = document.getElementById('bulk-photo-actions');
    const bulkAction = document.getElementById('bulk-photo-action-value');
    const selectionError = document.getElementById('bulk-photo-error');
    const selectionCount = document.getElementById('selected-photo-count');
    const selectAll = document.getElementById('select-all-photos');
    const photoCheckboxes = [...gallery.querySelectorAll('[data-photo-checkbox]')];

    const updateSelectionCount = () => {
        const selected = photoCheckboxes.filter((checkbox) => checkbox.checked).length;
        selectionCount.textContent = `${selected} dipilih`;
        selectAll.checked = photoCheckboxes.length > 0 && selected === photoCheckboxes.length;
        selectAll.indeterminate = selected > 0 && selected < photoCheckboxes.length;
    };

    selectAll.addEventListener('change', () => {
        photoCheckboxes.forEach((checkbox) => {
            checkbox.checked = selectAll.checked;
        });
        updateSelectionCount();
    });
    photoCheckboxes.forEach((checkbox) => checkbox.addEventListener('change', updateSelectionCount));

    gallery.querySelectorAll('[data-bulk-action]').forEach((button) => {
        button.addEventListener('click', () => {
            bulkAction.value = button.dataset.bulkAction;
            selectionError.textContent = '';
        });
    });

    bulkForm.addEventListener('submit', (event) => {
        const selectedCount = photoCheckboxes.filter((checkbox) => checkbox.checked).length;
        if (!selectedCount) {
            event.preventDefault();
            selectionError.textContent = 'Pilih setidaknya satu foto terlebih dahulu.';
            return;
        }

        if (bulkAction.value === 'delete'
            && !window.confirm(`Hapus ${selectedCount} foto yang dipilih? File foto dan data QR akan dihapus permanen.`)) {
            event.preventDefault();
        }
    });

    gallery.querySelectorAll('[data-confirm-delete]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm('Hapus foto ini? File foto dan data QR akan dihapus permanen.')) {
                event.preventDefault();
            }
        });
    });

    gallery.querySelectorAll('[data-photo-qr-url]').forEach((button) => {
        button.addEventListener('click', async () => {
            const url = button.dataset.photoQrUrl;
            qrError.classList.add('hidden');
            qrError.textContent = '';
            qrLink.href = url;
            qrExpiration.textContent = `QR berlaku sampai ${button.dataset.photoQrExpires}.`;
            qrModal.classList.remove('hidden');
            qrModal.classList.add('flex');

            try {
                await QRCode.toCanvas(qrCanvas, url, {
                    width: 280,
                    margin: 1,
                    errorCorrectionLevel: 'M',
                });
            } catch (error) {
                qrError.textContent = `QR gagal dibuat: ${error.message}`;
                qrError.classList.remove('hidden');
            }
        });
    });

    const closeQrModal = () => {
        qrModal.classList.add('hidden');
        qrModal.classList.remove('flex');
    };
    qrModal.querySelectorAll('[data-close-qr]').forEach((button) => {
        button.addEventListener('click', closeQrModal);
    });
    qrModal.addEventListener('click', (event) => {
        if (event.target === qrModal) closeQrModal();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !qrModal.classList.contains('hidden')) closeQrModal();
    });

    updateSelectionCount();
}
