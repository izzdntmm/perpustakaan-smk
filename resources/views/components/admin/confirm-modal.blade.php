<dialog id="confirm-modal" class="modal modal-bottom sm:modal-middle">
    <div class="modal-box">
        <h3 id="confirm-title" class="text-lg font-semibold text-neutral"></h3>
        <p id="confirm-message" class="mt-2 text-sm leading-relaxed text-base-content/70"></p>

        <form id="confirm-form" method="POST" class="modal-action">
            @csrf
            @method('PATCH')
            <button type="button" class="btn" onclick="this.closest('dialog').close()">Batal</button>
            <button id="confirm-submit" type="submit" class="btn btn-primary"></button>
        </form>
    </div>

    <form method="dialog" class="modal-backdrop"><button aria-label="Tutup">tutup</button></form>
</dialog>

@push('scripts')
    <script>
        (() => {
            const modal = document.getElementById('confirm-modal');
            const form = document.getElementById('confirm-form');
            const submit = document.getElementById('confirm-submit');

            document.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-confirm]');
                if (!btn) return;

                form.action = btn.dataset.url;
                document.getElementById('confirm-title').textContent = btn.dataset.title;
                document.getElementById('confirm-message').textContent = btn.dataset.message;

                submit.textContent = btn.dataset.label;
                submit.disabled = false;
                submit.classList.toggle('btn-error', btn.dataset.tone === 'error');
                submit.classList.toggle('btn-primary', btn.dataset.tone !== 'error');

                modal.showModal();
            });

            // Cegah klik ganda
            form.addEventListener('submit', () => { submit.disabled = true; });
        })();
    </script>
@endpush