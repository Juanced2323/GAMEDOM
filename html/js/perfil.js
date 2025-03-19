// js/perfil.js

// Funciones de edición (ya existentes)
let currentField = '';

function openEditModal(field, currentValue) {
  currentField = field;
  document.getElementById('modalFieldName').innerText = field.charAt(0).toUpperCase() + field.slice(1);
  document.getElementById('modalInput').value = currentValue;
  document.getElementById('editModal').style.display = 'block';
}

function closeModal() {
  document.getElementById('editModal').style.display = 'none';
}

function saveFieldChange() {
  const newValue = document.getElementById('modalInput').value;
  if (currentField) {
    const fieldElement = document.getElementById(currentField);
    if (fieldElement) {
      fieldElement.value = newValue;
    }
  }
  closeModal();
}

function togglePencilIcons() {
  // Mostrar todos los botones de lápiz
  const pencilButtons = document.querySelectorAll('.pencil-btn');
  pencilButtons.forEach(button => {
    button.style.display = 'inline-block';
  });
  // Ocultar el botón "Editar" y mostrar "Guardar Cambios"
  document.getElementById('globalEditBtn').style.display = 'none';
  document.getElementById('saveChangesBtn').style.display = 'block';
}

function saveProfileChanges() {
  const nombre    = document.getElementById('nombre').value;
  const apellidos = document.getElementById('apellidos').value;
  const edad      = document.getElementById('edad').value;
  const telefono  = document.getElementById('telefono').value;
  
  const formData = new FormData();
  formData.append('nombre', nombre);
  formData.append('apellidos', apellidos);
  formData.append('edad', edad);
  formData.append('telefono', telefono);

  fetch('php/update_profile.php', {
    method: 'POST',
    body: formData
  })
  .then(response => response.json())
  .then(data => {
    if (data.status === 'success') {
      alert(data.message);
      // Ocultar los botones de edición y restablecer el botón "Editar"
      const pencilButtons = document.querySelectorAll('.pencil-btn');
      pencilButtons.forEach(button => {
        button.style.display = 'none';
      });
      document.getElementById('globalEditBtn').style.display = 'block';
      document.getElementById('saveChangesBtn').style.display = 'none';
    } else {
      alert("Error: " + data.message);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert("Error al guardar cambios.");
  });
}

// Función para cerrar sesión y redirigir a index.php
function logoutUser() {
  window.location.href = "php/logout.php";
}

// Cerrar el modal si se hace clic fuera de él
window.onclick = function(event) {
  const modal = document.getElementById('editModal');
  if (event.target === modal) {
    closeModal();
  }
}
