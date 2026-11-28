/* ---------- Pembaca PDF: hanya dimuat di halamannya ---------- */
if (document.getElementById('pdf-reader')) {
    import('./pdf-reader.js');
}

/* ---------- Loading state + cegah kirim ganda ---------- */
const SPINNER = '<span class="loading loading-spinner loading-sm" aria-hidden="true"></span>';
const RESET_AFTER_MS = 20000; // pengaman bila server tidak merespons

function resetForm(form) {
    delete form.dataset.submitting;

    form.querySelectorAll('[data-original-html]').forEach((btn) => {
        btn.innerHTML = btn.dataset.originalHtml;
        btn.disabled = false;
        btn.removeAttribute('aria-busy');
        delete btn.dataset.originalHtml;
    });
}

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || event.defaultPrevented) return;

    const method = (form.getAttribute('method') || 'get').toLowerCase();
    if (method === 'get' || method === 'dialog' || form.hasAttribute('data-no-loading')) return;

    // Klik kedua saat permintaan masih berjalan diabaikan
    if (form.dataset.submitting === '1') {
        event.preventDefault();
        return;
    }
    form.dataset.submitting = '1';

    const buttons = [...form.querySelectorAll('button[type="submit"], button:not([type])')];
    const main = event.submitter && buttons.includes(event.submitter) ? event.submitter : buttons[0];

    // Ditunda sejenak supaya data form ikut terkirim sebelum tombol dinonaktifkan
    setTimeout(() => {
        buttons.forEach((btn) => {
            btn.dataset.originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.setAttribute('aria-busy', 'true');
        });

        if (main) {
            main.innerHTML = SPINNER + ' ' + main.textContent.trim();
        }
    }, 0);

    setTimeout(() => resetForm(form), RESET_AFTER_MS);
});

// Tombol Back browser: kembalikan tombol yang sempat berubah jadi spinner
window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
        document.querySelectorAll('form[data-submitting]').forEach(resetForm);
    }
});

/* ---------- Form gagal validasi: fokus ke kolom yang salah ---------- */
document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelector('dialog[open]')) return; // modal sudah mengatur fokusnya sendiri

    const invalid = document.querySelector('.input-error, .select-error, .textarea-error, .file-input-error');
    if (!invalid) return;

    const field = invalid.matches('label') ? invalid.querySelector('input') : invalid;

    invalid.scrollIntoView({ block: 'center' });
    field?.focus({ preventScroll: true });
});