<?php
session_start();
session_unset();     // Elimina todas las variables de sesión
session_destroy();   // Destruye la sesión actual

// Redirige al inicio o al login
header("Location: ../../pages/iniciar-sesion.html");
exit;
