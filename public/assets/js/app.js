// Maneja transiciones suaves de entrada y el menu lateral en pantallas pequenas.
document.addEventListener('DOMContentLoaded', () => {
    document.body.classList.add('fade-in');

    const sidebar = document.querySelector('[data-sidebar]');
    const toggle = document.querySelector('[data-sidebar-toggle]');

    if (sidebar && toggle) {
        toggle.addEventListener('click', () => {
            sidebar.classList.toggle('is-open');
        });
    }

    document.querySelectorAll('form').forEach((form) => {
        form.addEventListener('submit', () => {
            const button = form.querySelector('button[type="submit"]');
            if (button) {
                button.style.opacity = '0.72';
                button.style.transform = 'translateY(1px)';
            }
        });
    });
});
