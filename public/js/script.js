document.addEventListener("DOMContentLoaded", async () => {
  const toggle = document.getElementById("toggleMenu");
  const menu = document.getElementById("menuNav");

  // ✅ Menú hamburguesa
  if (toggle && menu) {
    toggle.addEventListener("click", () => {
      menu.classList.toggle("show");
    });

    const links = menu.querySelectorAll("a");
    links.forEach(link => {
      link.addEventListener("click", () => {
        menu.classList.remove("show");
      });
    });
  }

  // ✅ Ruta relativa según si estamos en /pages o no
  const basePath = location.pathname.includes("/pages/") ? "../" : "";

  try {
    const res = await fetch(basePath + "backend/api/obtener-usuario.php");
    const data = await res.json();

    if (data.success) {
      const loginLink = document.getElementById("link-login");
      const comunidadesLink = document.getElementById("link-comunidades");

      // ✅ Ocultar “Iniciar Sesión” si ya está logueado
      if (loginLink) loginLink.style.display = "none";

      // ✅ Mostrar “Mis Comunidades”
      if (comunidadesLink) comunidadesLink.style.display = "inline-block";
    }
  } catch (error) {
    console.warn("⚠️ No se pudo verificar la sesión:", error);
  }
});