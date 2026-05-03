(() => {
    class FeedbackCard extends HTMLElement {
        constructor() {
            super();
            this.onCloseClick = this.onCloseClick.bind(this);
            this.closeButton = null;
        }

        static get observedAttributes() {
            return ['variant'];
        }

        connectedCallback() {
            this.syncAccessibility();
            this.bindCloseButton();
        }

        disconnectedCallback() {
            this.unbindCloseButton();
        }

        attributeChangedCallback() {
            this.syncAccessibility();
        }

        syncAccessibility() {
            const isError = this.getAttribute('variant') === 'error';

            this.setAttribute('role', isError ? 'alert' : 'status');
            this.setAttribute('aria-live', isError ? 'assertive' : 'polite');
        }

        bindCloseButton() {
            this.unbindCloseButton();
            this.closeButton = this.querySelector('[data-feedback-close]');

            if (this.closeButton) {
                this.closeButton.addEventListener('click', this.onCloseClick);
            }
        }

        unbindCloseButton() {
            if (this.closeButton) {
                this.closeButton.removeEventListener('click', this.onCloseClick);
                this.closeButton = null;
            }
        }

        onCloseClick() {
            this.remove();
        }
    }

    if (!customElements.get('feedback-card')) {
        customElements.define('feedback-card', FeedbackCard);
    }
})();
