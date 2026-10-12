<?php
// MODELO Usuario: tabla "usuario"

class Usuario
{
    // Busca un usuario por correo, junto con el nombre de su rol (para el login)
    public static function buscarPorEmail($email)
    {
        $sql = "SELECT u.*, r.nombre_rol FROM usuario u
                JOIN rol r ON u.id_rol = r.id_rol
                WHERE u.email = ?";
        $stmt = Conexion::conectar()->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public static function todos()
    {
        $sql = "SELECT u.*, r.nombre_rol FROM usuario u
                JOIN rol r ON u.id_rol = r.id_rol
                ORDER BY u.nombre";
        return Conexion::conectar()->query($sql)->fetchAll();
    }

    // La contraseña nunca se guarda en texto plano, se guarda su hash
    public static function crear($nombre, $cedula, $email, $contrasena, $id_rol)
    {
        $sql = "INSERT INTO usuario (nombre, cedula, email, contrasena_hash, id_rol) VALUES (?, ?, ?, ?, ?)";
        $pdo = Conexion::conectar();
        $pdo->prepare($sql)->execute([
            $nombre, $cedula, $email, password_hash($contrasena, PASSWORD_DEFAULT), $id_rol
        ]);
        return $pdo->lastInsertId();
    }

    public static function eliminar($id)
    {
        Conexion::conectar()->prepare("DELETE FROM usuario WHERE id_usuario = ?")->execute([$id]);
    }

    public static function contarVecinos()
    {
        return Conexion::conectar()->query("SELECT COUNT(*) FROM usuario WHERE id_rol = 2")->fetchColumn();
    }
}
