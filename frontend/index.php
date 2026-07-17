<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Gestión Documentaria IESTP Lurín</title>
    <link rel="stylesheet" href="styles.php">
</head>
<body>

<div class="login-card">
    <div class="top-bar"></div>
    
    <div class="login-body">
        <div class="brand-header">
            <h1 class="brand-title">IESTP LURÍN</h1>
            <p class="brand-subtitle">Gestión Documentaria</p>
            <span class="badge">Mesa de Partes</span>
        </div>

        <form id="loginForm" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" novalidate>
            
            <div class="form-group">
                <label for="email" class="form-label">Correo Institucional</label>
                <input type="email" name="email" id="email" 
                       class="form-input <?php echo !empty($error_email) ? 'input-error' : ''; ?>" 
                       placeholder="DNI@iestplurin.edu.pe" 
                       value="<?php echo htmlspecialchars($email_value); ?>" 
                       autocomplete="off">
                <div id="emailError" class="error-text" style="display: <?php echo !empty($error_email) ? 'block' : 'none'; ?>;">
                    <?php echo $error_email; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" name="password" id="password" 
                       class="form-input <?php echo !empty($error_password) ? 'input-error' : ''; ?>" 
                       placeholder="••••••••">
                <div id="passwordError" class="error-text" style="display: <?php echo !empty($error_password) ? 'block' : 'none'; ?>;">
                    <?php echo $error_password; ?>
                </div>
            </div>

            <button type="submit" id="btnSubmit" class="btn-submit">
                <span class="btn-text">Ingresar al Sistema</span>
                <span class="spinner hidden"></span>
            </button>
        </form>
    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');

    let formularioValido = true;
    const dniEmailRegex = /^[0-9]{8}@iestplurin\.edu\.pe$/;

    if (emailInput.value.trim() === "") {
        emailInput.classList.add('input-error');
        emailError.textContent = "Por favor, ingrese su correo institucional.";
        emailError.style.display = 'block';
        formularioValido = false;
    } else if (!dniEmailRegex.test(emailInput.value.trim())) {
        emailInput.classList.add('input-error');
        emailError.textContent = "Debe ingresar su DNI seguido de @iestplurin.edu.pe";
        emailError.style.display = 'block';
        formularioValido = false;
    } else {
        emailInput.classList.remove('input-error');
        emailError.style.display = 'none';
    }

    if (passwordInput.value.trim() === "" || passwordInput.value.length < 6) {
        passwordInput.classList.add('input-error');
        passwordError.textContent = "La contraseña debe tener al menos 6 caracteres.";
        passwordError.style.display = 'block';
        formularioValido = false;
    } else {
        passwordInput.classList.remove('input-error');
        passwordError.style.display = 'none';
    }

    if (!formularioValido) {
        e.preventDefault();
    } else {
        document.querySelector('.spinner').classList.remove('hidden');
        document.getElementById('btnSubmit').disabled = true;
    }
});

document.getElementById('email').addEventListener('input', function() {
    this.classList.remove('input-error');
    document.getElementById('emailError').style.display = 'none';
});

document.getElementById('password').addEventListener('input', function() {
    this.classList.remove('input-error');
    document.getElementById('passwordError').style.display = 'none';
});
</script>
</body>
</html>
