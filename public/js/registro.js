let emailValidado = "";

document.getElementById("enviarCodigoBtn").addEventListener("click", async () => {
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value.trim();
  const confirmPassword = document.getElementById("confirmPassword").value.trim();

  // Validaciones
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
    alert("📧 Correo inválido.");
    return;
  }

  if (password.length < 6) {
    alert("🔒 La contraseña debe tener al menos 6 caracteres.");
    return;
  }

  if (password !== confirmPassword) {
    alert("❌ Las contraseñas no coinciden.");
    return;
  }

  try {
    const res = await fetch("../backend/api/enviar-codigo.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ email, password, confirmPassword })
    });

    const data = await res.json();
    if (data.success) {
      alert("📨 Código enviado a tu correo.");
      emailValidado = email;

      // Mostrar sección de código
      const codigoSection = document.getElementById("codigoSection");
      codigoSection.style.display = "block";

      // Hacer el input de código requerido
      const inputCodigo = document.getElementById("codigo");
      inputCodigo.setAttribute("required", "true");
      inputCodigo.focus();
    } else {
      alert("❌ " + (data.error || "Error al enviar el código."));
    }
  } catch (err) {
    console.error("Error al enviar el código:", err);
    alert("⚠️ Error al enviar el código.");
  }
});

document.getElementById("registerForm").addEventListener("submit", async (e) => {
  e.preventDefault();

  const nombre = document.getElementById("nombre").value.trim();
  const email = document.getElementById("email").value.trim();
  const password = document.getElementById("password").value.trim();
  const codigo = document.getElementById("codigo").value.trim();

  // Validación completa
  if (!nombre || !email || !password || !codigo) {
    alert("⚠️ Completá todos los campos.");
    return;
  }

  if (email !== emailValidado) {
    alert("❌ El correo no coincide con el que recibió el código.");
    return;
  }

  if (!/^\d{6}$/.test(codigo)) {
    alert("📩 El código debe tener 6 dígitos.");
    return;
  }

  try {
    const res = await fetch("../backend/api/registrar-usuario.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ nombre, email, password, codigo })
    });

    const data = await res.json();
    if (data.success) {
      alert("✅ Registro exitoso. Ahora podés iniciar sesión.");
      window.location.href = "iniciar-sesion.html";
    } else {
      alert("❌ " + (data.error || "Error al registrar."));
    }
  } catch (err) {
    console.error("Error al registrar usuario:", err);
    alert("⚠️ Error al procesar el registro.");
  }
});