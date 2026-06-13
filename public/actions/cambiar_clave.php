<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../cambiar_clave.php');
    exit;
}

$idUsuario = (int)$_SESSION['usuario']['id_usuario'];
$actual = trim($_POST['clave_actual'] ?? '');
$nueva = trim($_POST['clave_nueva'] ?? '');
$confirmar = trim($_POST['clave_confirmar'] ?? '');

function volver_clave(string $mensaje, bool $ok = false): void
{
    header('Location: ../cambiar_clave.php?' . ($ok ? 'ok=' : 'error=') . urlencode($mensaje));
    exit;
}

if (strlen($nueva) < 6) {
    volver_clave('La nueva contraseña debe tener al menos 6 caracteres.');
}

if ($nueva !== $confirmar) {
    volver_clave('La confirmacion no coincide con la nueva contraseña.');
}

$stmt = $pdo->prepare("SELECT clave_hash FROM tb_usuarios WHERE id_usuario = :id_usuario AND vigencia = 1");
$stmt->execute([':id_usuario' => $idUsuario]);
$usuario = $stmt->fetch();

if (!$usuario || strtoupper(hash('sha256', $actual)) !== $usuario['clave_hash']) {
    volver_clave('La contraseña actual no es correcta.');
}

$stmt = $pdo->prepare("UPDATE tb_usuarios
                       SET clave_hash = :clave_hash,
                           debe_cambiar_clave = 0,
                           usu_modifica = :usu_modifica,
                           f_modificacion = SYSDATETIME()
                       WHERE id_usuario = :id_usuario");
$stmt->execute([
    ':clave_hash' => strtoupper(hash('sha256', $nueva)),
    ':usu_modifica' => $idUsuario,
    ':id_usuario' => $idUsuario,
]);

$_SESSION['usuario']['debe_cambiar_clave'] = 0;

volver_clave('1', true);
