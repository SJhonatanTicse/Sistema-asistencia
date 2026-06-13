<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('Administrador');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/especialidades.php');
    exit;
}

$id = (int)($_POST['id_especialidad'] ?? 0);
$nombre = trim($_POST['nombre'] ?? '');
$caracteristicas = trim($_POST['caracteristicas'] ?? '');
$adminId = (int)$_SESSION['usuario']['id_usuario'];

if ($nombre === '') {
    header('Location: ../admin/especialidades.php?error=' . urlencode('El nombre es obligatorio.'));
    exit;
}

try {
    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE tb_especialidades
                               SET nombre = :nombre,
                                   caracteristicas = :caracteristicas,
                                   usu_modifica = :usu_modifica,
                                   f_modificacion = SYSDATETIME()
                               WHERE id_especialidad = :id_especialidad");
        $stmt->execute([
            ':nombre' => $nombre,
            ':caracteristicas' => $caracteristicas ?: null,
            ':usu_modifica' => $adminId,
            ':id_especialidad' => $id,
        ]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO tb_especialidades (nombre, caracteristicas, usuario_registra)
                               VALUES (:nombre, :caracteristicas, :usuario_registra)");
        $stmt->execute([
            ':nombre' => $nombre,
            ':caracteristicas' => $caracteristicas ?: null,
            ':usuario_registra' => $adminId,
        ]);
    }

    header('Location: ../admin/especialidades.php?ok=1');
} catch (Throwable $e) {
    header('Location: ../admin/especialidades.php?error=' . urlencode('No se pudo guardar. Revisa si el nombre ya existe.'));
}
exit;
