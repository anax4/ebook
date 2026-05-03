(() => {
    const selectors = {
        form: '[data-delete-confirmable]',
        toggle: '[data-delete-toggle]',
        modal: '[data-delete-modal]',
        backdrop: '[data-delete-backdrop]',
        message: '[data-feedback-message="delete-modal-message"]',
        cancel: '[data-delete-cancel]',
        confirm: '[data-delete-confirm]',
        close: '[data-feedback-close]',
    };

    let activeForm = null;

    function getModal() {
        const modal = document.querySelector(selectors.modal);

        if (!modal) {
            return null;
        }

        return {
            root: modal,
            message: modal.querySelector(selectors.message),
            cancel: modal.querySelector(selectors.cancel),
        };
    }

    function closeModal() {
        const modal = getModal();

        if (!modal) {
            return;
        }

        modal.root.classList.add('hidden');
        modal.root.classList.remove('flex');
        modal.root.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        activeForm = null;
    }

    function openModal(form) {
        const modal = getModal();

        if (!modal) {
            return;
        }

        activeForm = form;

        if (modal.message) {
            modal.message.textContent = form.getAttribute('data-delete-message') || 'Tem certeza que deseja continuar?';
        }

        modal.root.classList.remove('hidden');
        modal.root.classList.add('flex');
        modal.root.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');

        if (modal.cancel) {
            modal.cancel.focus();
        }
    }

    function submitActiveForm() {
        if (!activeForm) {
            return;
        }

        const formToSubmit = activeForm;
        closeModal();

        if (typeof formToSubmit.requestSubmit === 'function') {
            formToSubmit.requestSubmit();
            return;
        }

        formToSubmit.submit();
    }

    document.addEventListener('click', (event) => {
        const toggleButton = event.target.closest(selectors.toggle);

        if (toggleButton) {
            const form = toggleButton.closest(selectors.form);

            if (!form) {
                return;
            }

            event.preventDefault();
            openModal(form);
            return;
        }

        if (event.target.closest(selectors.backdrop) || event.target.closest(selectors.cancel) || event.target.closest(selectors.close)) {
            event.preventDefault();
            closeModal();
            return;
        }

        if (event.target.closest(selectors.confirm)) {
            event.preventDefault();
            submitActiveForm();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeModal();
        }
    });
})();
