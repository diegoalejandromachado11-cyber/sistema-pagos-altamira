<?php
require "conexion.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Buscamos al usuario por su correo, junto con el nombre de su rol
    $sql = "SELECT u.*, r.nombre_rol FROM usuario u
            JOIN rol r ON u.id_rol = r.id_rol
            WHERE u.email = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([$_POST['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // password_verify compara la clave escrita con el hash guardado
    if ($user && password_verify($_POST['contrasena'], $user['contrasena_hash'])) {
        $_SESSION['id_usuario'] = $user['id_usuario'];
        $_SESSION['nombre']     = $user['nombre'];
        $_SESSION['id_rol']     = $user['id_rol'];
        $_SESSION['nombre_rol'] = $user['nombre_rol'];

        registrarAuditoria($conexion, "LOGIN", "usuario", $user['id_usuario'], "Inicio de sesión");
        header("Location: index.php");
        exit;
    } else {
        $error = "Correo o contraseña incorrectos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingresar - Urbanización Altamira</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-primary-subtle">
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h4 class="text-center mb-1">Urbanización Altamira</h4>
                    <p class="text-center text-muted mb-4">Sistema de Gestión de Pagos</p>

                    <?php if ($error) { ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php } ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Correo</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="contrasena" class="form-control" required>
                        </div>
                        <button class="btn btn-primary w-100">Ingresar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
