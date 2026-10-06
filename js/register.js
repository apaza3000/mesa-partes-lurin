        // Validación de coincidencia de contraseñas en cliente
        document.getElementById('formRegistro').addEventListener('submit', function(e) {
            const p1 = document.querySelector('input[name="password"]').value;
            const p2 = document.querySelector('input[name="password_confirm"]').value;
            if (p1 !== p2) {
                e.preventDefault();
                alert('Las contraseñas no coinciden.');
            }
        });
