document.addEventListener("DOMContentLoaded", async () => {
  try {
    const response = await fetch("../backend/api/obtener-usuario.php");
    const usuario = await response.json();

    if (!usuario.success) {
      window.location.href = "iniciar-sesion.html";
      return;
    }

    document.getElementById("nombre-usuario").textContent = usuario.nombre;

    // ✅ Mostrar el teléfono con ícono 📞
    document.getElementById("telefono-usuario").textContent = usuario.telefono ? "📞 " + usuario.telefono : "";

    document.getElementById("foto-perfil").src = usuario.foto || "../public/img/perfil-defecto.png";

    // Prellenar formulario de edición
    document.getElementById("nuevo-nombre").value = usuario.nombre;
    document.getElementById("nuevo-telefono").value = usuario.telefono || "";
  } catch (error) {
    console.warn("Error al obtener datos del usuario:", error);
    window.location.href = "iniciar-sesion.html";
  }

  cargarComunidades();
});

// Subida de foto de perfil
document.getElementById("subir-foto").addEventListener("change", async function () {
  const archivo = this.files[0];
  if (!archivo) return;

  const formData = new FormData();
  formData.append("foto", archivo);

  try {
    const response = await fetch("../backend/api/subir-foto.php", {
      method: "POST",
      body: formData
    });

    const resultado = await response.json();
    if (resultado.success) {
      const nuevaSrc = resultado.url + "?t=" + new Date().getTime(); // evitar caché
      document.getElementById("foto-perfil").src = nuevaSrc;
    } else {
      alert("❌ " + resultado.error);
    }
  } catch (error) {
    console.error("Error al subir la foto:", error);
    alert("⚠️ Error al subir la foto.");
  }
});

// Cerrar sesión
function cerrarSesion() {
  window.location.href = "../backend/api/cerrar-sesion.php";
}

// Mostrar formulario de edición
document.getElementById("editarPerfilBtn").addEventListener("click", () => {
  document.getElementById("form-editar").style.display = "block";
});

// Guardar cambios de edición
document.getElementById("form-editar").addEventListener("submit", async function (e) {
  e.preventDefault();

  const nombre = document.getElementById("nuevo-nombre").value.trim();
  const telefono = document.getElementById("nuevo-telefono").value.trim();

  const body = { nombre, telefono };

  try {
    const res = await fetch("../backend/api/editar-perfil.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(body)
    });

    const result = await res.json();
    if (result.success) {
      alert("✅ Perfil actualizado correctamente.");
      location.reload();
    } else {
      alert("❌ " + result.error);
    }
  } catch (err) {
    console.error("Error al actualizar perfil:", err);
    alert("⚠️ Error al guardar los cambios.");
  }
});

// Cargar comunidades del usuario
async function cargarComunidades() {
  try {
    const res = await fetch("../backend/api/obtener-comunidades.php");
    const data = await res.json();
    const contenedor = document.getElementById("lista-comunidades");

    if (!data.success || !Array.isArray(data.comunidades)) {
      contenedor.innerHTML = "<p>No se pudieron cargar tus comunidades.</p>";
      return;
    }

    if (data.comunidades.length === 0) {
      contenedor.innerHTML = "<p>No te has unido a ninguna comunidad todavía.</p>";
      return;
    }

    contenedor.innerHTML = "";
    data.comunidades.forEach((c) => {
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
    document.getElementById("lista-comunidades").innerHTML = "<p>Error al cargar comunidades.</p>";
  }
}