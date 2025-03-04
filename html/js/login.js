// 1. Validación de campos vacíos al enviar el formulario
document.getElementById('loginForm').addEventListener('submit', function(e) {
  let username = document.getElementById('username').value.trim();
  let password = document.getElementById('password').value.trim();

  // Validación básica
  if (username === '' || password === '') {
    e.preventDefault(); 
    alert('Por favor, complete todos los campos.');
    return;
  }
  // Puedes agregar otras validaciones aquí
});

// 2. Detectar el parámetro ?error en la URL y mostrar mensaje
window.addEventListener('DOMContentLoaded', function() {
  const urlParams = new URLSearchParams(window.location.search);
  const errorParam = urlParams.get('error');
  
  if (errorParam) {
    // Opción A: Muestras un alert:
    switch (errorParam) {
      case '1':
        // Credenciales inválidas
        alert("Credenciales inválidas. Intente de nuevo.");
        break;
      case 'password':
        alert("Las contraseñas no coinciden (esto es más de registro).");
        break;
      // Agrega más casos según necesites
    }

    // Opción B (si quisieras usar un div oculto en login.html):
    // document.getElementById('errorMessage').classList.remove('hidden');
  }
});
