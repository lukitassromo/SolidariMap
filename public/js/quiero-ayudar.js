document.addEventListener("DOMContentLoaded", async function () {
  const unirseBtn = document.getElementById("unirseBtn");
  const locationBtn = document.getElementById("locationButton");
  const searchInput = document.getElementById("searchInput");
  const searchButton = document.getElementById("searchButton");
  let personaSeleccionadaId = null;

  const map = L.map("map").setView([-31.4167, -64.1833], 13);
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "&copy; OpenStreetMap contributors"
  }).addTo(map);

  locationBtn.addEventListener("click", () => {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition((position) => {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        map.setView([lat, lng], 15);
      }, () => {
        alert("No se pudo obtener tu ubicación.");
      });
    } else {
      alert("Tu navegador no soporta geolocalización.");
    }
  });

  searchButton.addEventListener("click", () => {
    const query = searchInput.value.trim();
    if (!query) return;

    const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`;
    fetch(url)
      .then(res => res.json())
      .then(data => {
        if (data.length > 0) {
          const { lat, lon } = data[0];
          map.setView([lat, lon], 16);
        } else {
          alert("Dirección no encontrada.");
        }
      })
      .catch(err => {
        console.error("Error buscando dirección:", err);
        alert("Error en la búsqueda.");
      });
  });

  try {
    const response = await fetch("../backend/api/obtener-personas.php");
    const result = await response.json();
    const personas = result.success ? result.data : result;

    if (Array.isArray(personas) && personas.length > 0) {
      personas.forEach((persona) => {
        if (persona.latitud && persona.longitud) {
          const marker = L.marker([parseFloat(persona.latitud), parseFloat(persona.longitud)]).addTo(map);
          marker.bindPopup(`
            <strong>${persona.nombre}</strong><br>
            Edad: ${persona.edad || "No especificada"}<br>
            ${persona.detalles || ""}<br>
            <em>Horario: ${persona.horario || "No especificado"}</em>
          `);

          marker.on("click", () => {
            personaSeleccionadaId = persona.id;
            unirseBtn.disabled = false;
          });
        }
      });
    }
  } catch (error) {
    console.error("Error al cargar personas:", error);
    alert("Ocurrió un problema al obtener la información.");
  }

  unirseBtn.addEventListener("click", async () => {
    if (!personaSeleccionadaId) return;

    try {
      const res = await fetch("../backend/api/unirse-comunidad.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id_persona: personaSeleccionadaId })
      });

      const data = await res.json();

      if (data.success) {
        window.location.href = `comunidad.html?id=${personaSeleccionadaId}`;
      } else {
        alert("❌ " + (data.error || "No se pudo unir a la comunidad."));
      }
    } catch (err) {
      console.error("Error al unirse:", err);
      alert("⚠️ Ocurrió un problema al intentar unirse.");
    }
  });
});
