<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('Administrador');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/estudiantes.php');
    exit;
}

$idEstudiante = (int)($_POST['id_estudiante'] ?? 0);
$adminId = (int)$_SESSION['usuario']['id_usuario'];

try {
    $stmt = $pdo->prepare("SELECT id_usuario FROM tb_estudiantes WHERE id_estudiante = :id_estudiante");
    $stmt->execute([':id_estudiante' => $idEstudiante]);
    $row = $stmt->fetch();

    if (!$row) {
        header('Location: ../admin/estudiantes.php?error=' . urlencode('Alumno no encontrado.'));
        exit;
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare("UPDATE tb_estudiantes
                           SET vigencia = 0, usu_modifica = :usu_modifica, f_modificacion = SYSDATETIME()
                           WHERE id_estudiante = :id_estudiante");
    $stmt->execute([':usu_modifica' => $adminId, ':id_estudiante' => $idEstudiante]);

    $stmt = $pdo->prepare("UPDATE detalle_estudiante_especialidad
                           SET vigencia = 0, usu_modifica = :usu_modifica, f_modificacion = SYSDATETIME()
                           WHERE id_estudiante = :id_estudiante AND vigencia = 1");
    $stmt->execute([':usu_modifica' => $adminId, ':id_estudiante' => $idEstudiante]);

    if ($row['id_usuario']) {
        $stmt = $pdo->prepare("UPDATE tb_usuarios
                               SET vigencia = 0, usu_modifica = :usu_modifica, f_modificacion = SYSDATETIME()
                               WHERE id_usuario = :id_usuario");
        $stmt->execute([':usu_modifica' => $adminId, ':id_usuario' => $row['id_usuario']]);
    }

    $pdo->commit();
    header('Location: ../admin/estudiantes.php?ok=1');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    header('Location: ../admin/estudiantes.php?error=' . urlencode('No se pudo desactivar el alumno.'));
}
exit;
