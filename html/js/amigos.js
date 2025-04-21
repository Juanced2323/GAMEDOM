document.addEventListener('DOMContentLoaded', function () {
    const listaAmigos = document.getElementById('lista-amigos');
    const buscador = document.getElementById('buscador-amigos');
    let amigos = [];
  
    // Función para renderizar los amigos (con filtro)
    function mostrarAmigos(filtro = '') {
        const lista = document.getElementById('lista-amigos');
        lista.innerHTML = '';
      
        const filtrados = amigos.filter(amigo =>
          amigo.usuario.toLowerCase().includes(filtro.toLowerCase()) ||
          amigo.nombre.toLowerCase().includes(filtro.toLowerCase()) ||
          amigo.apellidos.toLowerCase().includes(filtro.toLowerCase())
        );
      
        if (filtrados.length === 0) {
          lista.innerHTML = '<p>No se encontraron amigos.</p>';
          return;
        }
      
        filtrados.forEach(amigo => {
          const div = document.createElement('div');
          div.classList.add('amigo-item');
          div.innerHTML = `
            <input type="checkbox" name="amigos_eliminar[]" value="${amigo.usuario}">
            <img src="${amigo.imagen}" alt="${amigo.usuario}">
            <span>${amigo.usuario}</span>
          `;
          lista.appendChild(div);
        });
      }      
  
    // Obtener los amigos desde PHP
    fetch('php/obtener_amigos_completos.php')
      .then(res => res.json())
      .then(data => {
        if (data.error) {
          listaAmigos.innerHTML = `<p>${data.error}</p>`;
        } else {
          amigos = data;
          mostrarAmigos(); // mostrar todos al inicio
        }
      })
      .catch(err => {
        console.error('Error al obtener amigos:', err);
        listaAmigos.innerHTML = '<p>Error al cargar los amigos.</p>';
      });
  
    // Filtrado en vivo
    buscador.addEventListener('input', () => {
      mostrarAmigos(buscador.value);
    });

    // --------- MOSTRAR SOLICITUDES PENDIENTES ---------
    function cargarSolicitudes() {
        const contenedorSolicitudes = document.getElementById('lista-solicitudes');
        contenedorSolicitudes.innerHTML = '<p>Cargando solicitudes...</p>';
    
        fetch('php/obtener_solicitudes.php')
        .then(res => res.json())
        .then(data => {
            contenedorSolicitudes.innerHTML = '';
    
            if (!Array.isArray(data) || data.length === 0) {
            contenedorSolicitudes.innerHTML = '<p>No tienes solicitudes pendientes.</p>';
            return;
            }
    
            data.forEach(solicitante => {
            const div = document.createElement('div');
            div.className = 'solicitud-item';
            div.innerHTML = `
                <img src="${solicitante.imagen}" alt="Avatar de ${solicitante.usuario}" style="width:50px;height:50px;border-radius:50%;">
                <strong>${solicitante.usuario}</strong>
                <span>${solicitante.nombre} ${solicitante.apellidos}</span>
                <button class="aceptar-btn" data-usuario="${solicitante.usuario}">Aceptar</button>
                <button class="rechazar-btn" data-usuario="${solicitante.usuario}">Rechazar</button>
            `;
            contenedorSolicitudes.appendChild(div);
            });
    
            // Asociar eventos a los botones
            document.querySelectorAll('.aceptar-btn').forEach(btn => {
            btn.addEventListener('click', () => gestionarSolicitud(btn.dataset.usuario, 'aceptada'));
            });
    
            document.querySelectorAll('.rechazar-btn').forEach(btn => {
            btn.addEventListener('click', () => gestionarSolicitud(btn.dataset.usuario, 'rechazada'));
            });
        })
        .catch(err => {
            console.error('Error al obtener solicitudes:', err);
            contenedorSolicitudes.innerHTML = '<p>Error al cargar solicitudes.</p>';
        });
    }
    
    function gestionarSolicitud(usuario, accion) {
        const formData = new FormData();
        formData.append('solicitante', usuario);
        formData.append('accion', accion);
    
        fetch('php/gestionar_solicitud.php', {
        method: 'POST',
        body: formData
        })
        .then(res => res.text())
        .then(respuesta => {
            alert(respuesta);
            cargarSolicitudes(); // Recargar lista después de acción
            cargarAmigos(); // Recargar amigos si fue aceptado
        })
        .catch(err => {
            console.error('Error al gestionar solicitud:', err);
        });
    }
    
    // Ejecutar al cargar la página
    cargarSolicitudes();

    // --------- ENVIAR SOLICITUD ---------
    const formSolicitud = document.getElementById('form-solicitud');
    const mensajeSolicitud = document.getElementById('mensaje-solicitud');

    formSolicitud.addEventListener('submit', function (e) {
    e.preventDefault();

    const usuarioDestino = document.getElementById('destinatario').value.trim();
    if (!usuarioDestino) return;

    const formData = new FormData();
    formData.append('usuario_destino', usuarioDestino);

    fetch('php/enviar_solicitud.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.text())
        .then(mensaje => {
        mensajeSolicitud.textContent = mensaje;
        formSolicitud.reset();
        cargarSolicitudes(); // Recargar por si el otro ya mandó una
        })
        .catch(err => {
        console.error('Error al enviar solicitud:', err);
        mensajeSolicitud.textContent = "Error al enviar solicitud.";
        });
    });

    // --------- ELIMINAR AMIGOS SELECCIONADOS ---------
    document.getElementById('form-eliminar-amigos').addEventListener('submit', function (e) {
        e.preventDefault();
    
        const seleccionados = Array.from(document.querySelectorAll('input[name="amigos_eliminar[]"]:checked'))
        .map(cb => cb.value);
    
        if (seleccionados.length === 0) {
        alert("Selecciona al menos un amigo para eliminar.");
        return;
        }
    
        const formData = new FormData();
        seleccionados.forEach(usuario => {
        formData.append('amigos[]', usuario);
        });
    
        fetch('php/eliminar_amigos.php', {
        method: 'POST',
        body: formData
        })
        .then(res => res.text())
        .then(msg => {
            alert(msg);
            cargarAmigos(); // Recargar lista de amigos
        })
        .catch(err => {
            console.error("Error eliminando amigos:", err);
            alert("Hubo un error al eliminar amigos.");
        });
    });
    
  });
  