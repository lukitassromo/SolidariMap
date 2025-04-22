document.addEventListener("DOMContentLoaded", async () => {
  const params = new URLSearchParams(window.location.search);
  const id = params.get("id");

  const seccionInfo = document.getElementById("info-comunidad");
  const seccionBotones = document.querySelector(".botones-comunidad");
  const seccionForo = document.querySelector(".foro");
  const listaComunidades = document.getElementById("seccion-mis-comunidades");

  if (id) {
    seccionInfo.style.display = "block";
    seccionBotones.style.display = "flex";
    seccionForo.style.display = "block";
    listaComunidades.style.display = "none";

    await cargarComunidad(id);
    await cargarMensajes(id);
    manejarFormularioMensajes(id);
  } else {
    seccionInfo.style.display = "none";
    seccionBotones.style.display = "none";
    seccionForo.style.display = "none";
    listaComunidades.style.display = "block";

    cargarListaComunidades();
  }
});

async function cargarComunidad(id) {
  const contenedor = document.getElementById("info-comunidad");
  const boton = document.getElementById("comunidad-action");

  try {
    const res = await fetch(`../backend/api/obtener-persona.php?id=${id}`);
    const data = await res.json();

    if (data.success) {
      const p = data.persona;
      contenedor.innerHTML = `
        <h2>${p.nombre}</h2>
        <p><strong>Edad:</strong> ${p.edad || "No especificada"}</p>
        <p><strong>Detalles:</strong> ${p.detalles || "Sin información adicional"}</p>
        <p><strong>Horario:</strong> ${p.horario}</p>
        <p><strong>Ubicación:</strong> Lat: ${p.latitud}, Lng: ${p.longitud}</p>
        <p><strong>Miembros en esta comunidad:</strong> ${data.miembros}</p>
      `;

      if (data.ya_unido) {
        boton.textContent = "Abandonar Comunidad";
        boton.onclick = async () => {
          const confirmar = confirm("¿Estás seguro de que querés abandonar esta comunidad?");
          if (!confirmar) return;

          const res = await fetch("../backend/api/abandonar-comunidad.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id_persona: id })
          });
          const result = await res.json();
          if (result.success) {
            alert("Has abandonado la comunidad.");
            location.reload();
          } else {
            alert("Error: " + result.error);
          }
        };
      } else {
        boton.textContent = "Unirse a esta Comunidad";
        boton.onclick = async () => {
          const res = await fetch("../backend/api/unirse-comunidad.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id_persona: id })
          });
          const result = await res.json();
          if (result.success) {
            alert("¡Te uniste a la comunidad!");
            location.reload();
          } else {
            alert("Error: " + result.error);
          }
        };
      }
    } else {
      contenedor.innerHTML = `<p>❌ ${data.error}</p>`;
      boton.style.display = "none";
    }
  } catch (error) {
    console.error("Error al cargar comunidad:", error);
    contenedor.innerHTML = "<p>⚠️ No se pudo cargar la comunidad.</p>";
  }
}

async function cargarMensajes(id) {
  const mensajesContainer = document.getElementById("mensajes-comunidad");
  try {
    const res = await fetch(`../backend/api/obtener-mensajes.php?id_persona=${id}`);
    const data = await res.json();
    mensajesContainer.innerHTML = "";

    if (data.success && Array.isArray(data.mensajes)) {
      data.mensajes.forEach(m => {
        const mensaje = document.createElement("div");
        mensaje.classList.add("mensaje-burbuja");
        mensaje.innerHTML = `
          <img src="${m.foto || '../public/img/perfil-defecto.png'}" class="foto-perfil-msg" />
          <div class="contenido-mensaje">
            <p class="autor"><strong>${m.nombre}</strong> <span class="hora">${m.enviado_en.slice(11, 16)}</span></p>
            <p>${m.texto}</p>
          </div>
        `;
        mensajesContainer.appendChild(mensaje);
      });
    } else {
      mensajesContainer.innerHTML = "<p>No hay mensajes.</p>";
    }
  } catch (e) {
    mensajesContainer.innerHTML = "<p>Error al cargar mensajes.</p>";
  }
}

function manejarFormularioMensajes(id) {
  const formMensaje = document.getElementById("form-mensaje");
  formMensaje.addEventListener("submit", async (e) => {
    e.preventDefault();
    const texto = document.getElementById("mensaje").value.trim();
    if (!texto) return;

    try {
      const res = await fetch("../backend/api/enviar-mensaje.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id_persona: id, texto })
      });

      const result = await res.json();
      if (result.success) {
        formMensaje.reset();
        cargarMensajes(id);
      } else {
        alert("❌ " + result.error);
      }
    } catch (e) {
      alert("⚠️ Error al enviar mensaje.");
    }
  });
}

async function cargarListaComunidades() {
  const contenedor = document.getElementById("lista-comunidades");
  try {
    const res = await fetch("../backend/api/obtener-comunidades.php");
    const data = await res.json();

    if (!data.success || !Array.isArray(data.comunidades)) {
      contenedor.innerHTML = "<p>No se pudieron cargar tus comunidades.</p>";
      return;
    }

    if (data.comunidades.length === 0) {
      contenedor.innerHTML = "<p>No te has unido a ninguna comunidad todavía.</p>";
      return;
    }

    contenedor.innerHTML = "";
    data.comunidades.forEach(c => {
      const card = document.createElement("div");
      card.className = "comunidad-card";
      card.innerHTML = `
        <h3>${c.nombre}</h3>
        <p><strong>Edad:</strong> ${c.edad || "No especificada"}</p>
        <p><strong>Horario:</strong> ${c.horario}</p>
        <p>${c.detalles || ""}</p>
        <button class="btn-naranja" onclick="window.location.href='comunidad.html?id=${c.id}'">Ver Comunidad</button>
      `;
      contenedor.appendChild(card);
    });
  } catch (error) {
    console.error("Error al cargar comunidades:", error);
    contenedor.innerHTML = "<p>Error al cargar comunidades.</p>";
  }
}
