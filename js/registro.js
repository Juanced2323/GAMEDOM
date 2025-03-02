document.getElementById('registerForm').addEventListener('submit', function(e) {
  const email = document.getElementById('email').value.trim();
  const username = document.getElementById('username').value.trim();
  const nombre = document.getElementById('nombre').value.trim();
  const apellidos = document.getElementById('apellidos').value.trim();
  const edad = document.getElementById('edad').value.trim();
  const telefono = document.getElementById('telefono').value.trim();
  const password = document.getElementById('password').value.trim();
  const confirmPassword = document.getElementById('confirmPassword').value.trim();

  // Ejemplo de validaciones sencillas
  if (!email || !username || !nombre || !apellidos || !edad || !telefono || !password || !confirmPassword) {
    e.preventDefault();
    showError("Por favor, complete todos los campos.");
    return;
  }

  if (password !== confirmPassword) {
    e.preventDefault();
    showError("Las contraseñas no coinciden. Por favor, verifíquelas.");
    return;
  }

  if (isNaN(edad) || edad < 0) {
    e.preventDefault();
    showError("Ingrese una edad válida.");
    return;
  }

  if (telefono.length < 7) {
    e.preventDefault();
    showError("Ingrese un teléfono válido.");
    return;
  }
});

// Detectar el parámetro ?error en la URL y mostrar un mensaje en el contenedor
window.addEventListener('DOMContentLoaded', function() {
  const urlParams = new URLSearchParams(window.location.search);
  const error = urlParams.get('error');
  
  if (error) {
    let message = '';
    switch (error) {
      case 'password':
        message = 'Las contraseñas no coinciden.';
        break;
      case 'exists':
        message = 'El correo o el usuario ya existen.';
        break;
      case 'insert':
        message = 'Error al registrar el usuario. Intente de nuevo.';
        break;
    }
    if (message) {
      showError(message);
    }
  }
});

// Función auxiliar para mostrar el texto en el div #errorMessage
function showError(text) {
  const errorDiv = document.getElementById('errorMessage');
  errorDiv.textContent = text;            // Insertamos el texto
  errorDiv.classList.remove('hidden');    // Mostramos el contenedor
}
