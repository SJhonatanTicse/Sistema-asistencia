<?php
session_start();

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/functions.php';

function require_login(): void
{
    if (!isset($_SESSION['usuario'])) {
        $path = $_SERVER['PHP_SELF'] ?? '';
        $loginPath = (strpos($path, '/admin/') !== false || strpos($path, '/alumno/') !== false || strpos($path, '/actions/') !== false)
            ? '../index.php'
            : 'index.php';
        header('Location: ' . $loginPath);
        exit;
    }

    enforce_password_change();
}

function enforce_password_change(): void
{
    $path = str_replace('\\', '/', $_SERVER['PHP_SELF'] ?? '');
    $mustChange = (int)($_SESSION['usuario']['debe_cambiar_clave'] ?? 0) === 1;
    $allowedPath = strpos($path, '/cambiar_clave.php') !== false
        || strpos($path, '/logout.php') !== false
        || strpos($path, '/actions/cambiar_clave.php') !== false;

    if ($mustChange && !$allowedPath) {
        $prefix = (strpos($path, '/admin/') !== false || strpos($path, '/alumno/') !== false || strpos($path, '/actions/') !== false)
            ? '../'
            : '';
        header('Location: ' . $prefix . 'cambiar_clave.php');
        exit;
    }
}

function require_role(string $rol): void
{
    require_login();

    if (($_SESSION['usuario']['rol'] ?? '') !== $rol) {
        header('Location: ../index.php');
        exit;
    }
}

function login_user(PDO $pdo, string $usuario, string $clave): bool
{
    $sql = "SELECT u.id_usuario, u.usuario, u.nombres, u.apellidos, u.correo, u.foto_perfil, u.debe_cambiar_clave, r.nombre AS rol
            FROM tb_usuarios u
            INNER JOIN tb_roles r ON r.id_rol = u.id_rol
            WHERE u.usuario = :usuario
              AND u.clave_hash = :clave_hash
              AND u.vigencia = 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':usuario' => $usuario,
        ':clave_hash' => strtoupper(hash('sha256', $clave)),
    ]);

    $user = $stmt->fetch();

    if (!$user) {
        return false;
    }

    $_SESSION['usuario'] = $user;
    return true;
}
