document.addEventListener('DOMContentLoaded', () => {
    const navToggle = document.querySelector('[data-toggle-nav]');
    const navMenu = document.querySelector('[data-nav-menu]');

    if (navToggle && navMenu) {
        navToggle.addEventListener('click', () => {
            navMenu.classList.toggle('open');
        });
    }

    document.querySelectorAll('[data-dismiss-flash]').forEach((button) => {
        button.addEventListener('click', () => {
            const parent = button.closest('[data-flash]');
            if (parent) {
                parent.remove();
            }
        });
    });

    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.getAttribute('data-confirm') || 'Confirmar acao?';
            if (! window.confirm(message)) {
                event.preventDefault();
            }
        });
    });
});
