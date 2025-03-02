// js/registro.js

document.getElementById('registerForm').addEventListener('submit', function(e) {
    // Obtenemos los valores de los campos
    const email = document.getElementById('email').value.trim();
    const username = document.getElementById('username').value.trim();
    const nombre = document.getElementById('nombre').value.trim();
    const apellidos = document.getElementById('apellidos').value.trim();
    const edad = document.getElementById('edad').value.trim();
    const telefono = document.getElementById('telefono').value.trim();
    const password = document.getElementById('password').value.trim();
    const confirmPassword = document.getElementById('confirmPassword').value.trim();
  
    // Verificamos que los campos no estén vacíos
    if (!email || !username || !nombre || !apellidos || !edad || !telefono || !password || !confirmPassword) {
      e.preventDefault();
      alert('Por favor, complete todos los campos.');
      return;
    }
  
    // Verificamos que las contraseñas coincidan
    if (password !== confirmPassword) {
      e.preventDefault();
      alert('Las contraseñas no coinciden. Por favor, verifíquelas.');
      return;
    }
  
    // Validación adicional de la edad (ejemplo simple)
    if (isNaN(edad) || edad < 0) {
      e.preventDefault();
      alert('Ingrese una edad válida.');
      return;
    }
  
    // Validación de teléfono (ejemplo muy básico)
    // Podrías usar expresiones regulares o librerías de validación
    if (telefono.length < 7) {
      e.preventDefault();
      alert('Ingrese un teléfono válido.');
      return;
    }
  
    // Si todo está correcto, aquí podrías hacer una petición AJAX
    // para registrar al usuario en la base de datos.
    // Por ahora, se permitirá el envío normal del formulario.
  });
  