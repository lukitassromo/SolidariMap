let map;
let marker;

document.addEventListener("DOMContentLoaded", async () => {
  console.log("🟠 Script agregar-persona.js cargado");

  // ✅ Verificar sesión
  try {
    const res = await fetch("../backend/api/obtener-usuario.php");
    const contentType = res.headers.get("content-type");

    if (!contentType || !contentType.includes("application/json")) {
      throw new Error("Respuesta no es JSON. Probablemente no hay sesión.");
    }

    const data = await res.json();
    console.log("🔐 Respuesta del backend:", data);

    if (!data.success) {
      window.location.href = "iniciar-sesion.html";
      return;
    }
  } catch (error) {
    console.warn("⚠️ Error verificando sesión:", error);
    window.location.href = "iniciar-sesion.html";
    return;
  }

  // ✅ Inicializar mapa
  map = L.map("map").setView([-34.6037, -58.3816], 13);
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: "&copy; OpenStreetMap contributors"
  }).addTo(map);

  marker = L.marker([-34.6037, -58.3816], { draggable: true }).addTo(map);

  marker.on("dragend", () => {
    const pos = marker.getLatLng();
    console.log("📍 Nueva ubicación:", pos.lat, pos.lng);
  });

  map.on("click", (e) => {
    marker.setLatLng(e.latlng);
    console.log("📍 Marcador movido a:", e.latlng.lat, e.latlng.lng);
  });

  document.getElementById("locationButton").addEventListener("click", () => {
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition((position) => {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        map.setView([lat, lng], 13);
        marker.setLatLng([lat, lng]);
      }, () => {
        alert("No se pudo obtener la ubicación.");
      });
    } else {
      alert("Tu navegador no soporta geolocalización.");
    }
  });

  document.getElementById("searchButton").addEventListener("click", async () => {
    const address = document.getElementById("searchInput").value;
    if (!address) return;

    try {
      const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`);
      const data = await response.json();

      if (data.length > 0) {
        const lat = parseFloat(data[0].lat);
        const lon = parseFloat(data[0].lon);
        map.setView([lat, lon], 15);
        marker.setLatLng([lat, lon]);
      } else {
        alert("Dirección no encontrada.");
      }
    } catch (error) {
      console.error("Error buscando dirección:", error);
    }
  });

  document.getElementById("agregar-persona-form").addEventListener("submit", async function (e) {
    e.preventDefault();

    const nombre = document.getElementById("nombre").value.trim();
    const edad = parseInt(document.getElementById("edad").value);
    const detalles = document.getElementById("detalles").value.trim();
    const horario = document.getElementById("horario").value.trim();
    const ubicacion = marker.getLatLng();

    if (!nombre || isNaN(edad) || !horario || !ubicacion) {
      alert("Por favor completá todos los campos obligatorios.");
      return;
    }

    const datos = {
      nombre,
      edad,
      detalles,
      horario,
      latitud: ubicacion.lat,
      longitud: ubicacion.lng
    };

    try {
      const response = await fetch("../backend/api/agregar-persona.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify(datos)
      });

      const resultado = await response.json();

      if (resultado.success) {
        alert("✅ Persona agregada correctamente.");
        document.getElementById("agregar-persona-form").reset();
      } else {
        alert("❌ Error del servidor: " + resultado.error);
      }
    } catch (error) {
      console.error("Error en la solicitud:", error);
      alert("⚠️ Hubo un problema al registrar la persona.");
    }
  });
});