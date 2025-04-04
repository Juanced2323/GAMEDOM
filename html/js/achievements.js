function showAchievementNotification(achievement) {
    // Crear o reutilizar el contenedor de notificaciones
    let container = document.getElementById('achievementContainer');
    if (!container) {
      container = document.createElement('div');
      container.id = 'achievementContainer';
      // Las reglas de estilo básicas se definen en el CSS
      document.body.appendChild(container);
    }
    
    // Crear el elemento de notificación
    const notif = document.createElement('div');
    notif.className = 'achievement-notification';
    notif.innerHTML = `
      <img src="${achievement.imagen}" alt="${achievement.nombre}" class="achievement-icon">
      <div class="achievement-info">
        <strong>${achievement.nombre}</strong>
        <p>${achievement.descripcion}</p>
      </div>
    `;
    container.appendChild(notif);
    
    // Animar la notificación: fade in
    notif.style.opacity = 0;
    setTimeout(() => {
      notif.style.opacity = 1;
    }, 100);
    
    // Después de 5 segundos, fade out y eliminar la notificación
    setTimeout(() => {
      notif.style.opacity = 0;
      setTimeout(() => {
        if (notif.parentNode) {
          notif.parentNode.removeChild(notif);
        }
      }, 1000);
    }, 5000);
  }
  