document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("form-login");
  const toggle = document.getElementById("menu-toggle");
  const menu = document.getElementById("menu");

  // Menú hamburguesa
  toggle.addEventListener("click", () => {
    menu.classList.toggle("show");
  });

  // Enviar formulario
  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value.trim();
    const recordarme = document.getElementById("recordarme")?.checked || false;

    if (!email || !password) {
      alert("Por favor completá todos los campos.");
      return;
    }

    try {
      const response = await fetch("../backend/api/iniciar-sesion.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json"
        },
        body: JSON.stringify({ email, password, recordarme })
      });

      const data = await response.json();

      if (data.success) {
        window.location.href = "mi-cuenta.html";
      } else {
        alert("❌ " + (data.error || "Credenciales incorrectas."));
      }
    } catch (error) {
      console.error("Error al iniciar sesión:", error);
      alert("⚠️ Error de conexión con el servidor.");
    }
  });
});