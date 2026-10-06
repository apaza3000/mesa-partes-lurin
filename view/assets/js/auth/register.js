'use strict';

const registerForm = document.querySelector('#formRegistro');

if (registerForm) {
    registerForm.addEventListener('submit', (event) => {
        const password = registerForm.querySelector('input[name="password"]').value;
        const passwordConfirmation = registerForm.querySelector('input[name="password_confirm"]').value;

        if (password !== passwordConfirmation) {
            event.preventDefault();
            alert('Las contraseñas no coinciden.');
        }
    });
}
