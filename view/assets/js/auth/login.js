'use strict';

const loginForm = document.querySelector('#login-form');
const passwordInput = document.querySelector('#password');
const passwordToggle = document.querySelector('#toggle-password');
const submitButton = document.querySelector('#login-submit');

if (passwordInput && passwordToggle) {
    passwordToggle.addEventListener('click', () => {
        const showPassword = passwordInput.type === 'password';
        passwordInput.type = showPassword ? 'text' : 'password';
        passwordToggle.setAttribute('aria-pressed', String(showPassword));
        passwordToggle.setAttribute(
            'aria-label',
            showPassword ? 'Ocultar contraseña' : 'Mostrar contraseña'
        );
        passwordToggle.querySelector('span').textContent = showPassword ? 'Ocultar' : 'Mostrar';
        passwordInput.focus();
    });
}

if (loginForm && submitButton) {
    loginForm.addEventListener('submit', (event) => {
        if (!loginForm.reportValidity()) {
            event.preventDefault();
            return;
        }

        submitButton.disabled = true;
        submitButton.querySelector('.submit-label').textContent = 'Ingresando...';
        submitButton.querySelector('.spinner').classList.remove('hidden');
    });
}
