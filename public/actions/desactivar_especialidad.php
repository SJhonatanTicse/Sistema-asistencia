<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('Administrador');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/especialidades.php');
    exit;
}

$id = (int)($_POST['id_especialidad'] ?? 0);
$adminId = (int)$_SESSION['usuario']['id_usuario'];

try {
    $stmt = $pdo->prepare("UPDATE tb_especialidades
                           SET vigencia = 0,
                               usu_modifica = :usu_modifica,
                               f_modificacion = SYSDATETIME()
                           WHERE id_especialidad = :id_especialidad");
    $stmt->execute([
        ':usu_modifica' => $adminId,
        ':id_especialidad' => $id,
    ]);

    header('Location: ../admin/especialidades.php?ok=1');
} catch (Throwable $e) {
    header('Location: ../admin/especialidades.php?error=' . urlencode('No se pudo desactivar la especialidad.'));
}
exit;
