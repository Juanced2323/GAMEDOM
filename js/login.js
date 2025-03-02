// js/login.js

document.getElementById('loginForm').addEventListener('submit', function(e) {
    // Obtener los valores de los campos
    let username = document.getElementById('username').value.trim();
    let password = document.getElementById('password').value.trim();
  
    // Validación básica para asegurar que los campos no estén vacíos
    if(username === '' || password === '') {
      e.preventDefault(); // Evita el envío del formulario
      alert('Por favor, complete todos los campos');
    } else {
      // Aquí podrías agregar validaciones adicionales o incluso una llamada AJAX para autenticar al usuario
      // Por el momento, se permite el envío normal del formulario
    }
  });
  