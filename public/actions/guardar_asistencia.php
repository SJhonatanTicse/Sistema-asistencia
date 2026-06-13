<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('Administrador');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/registrar_asistencia.php');
    exit;
}

$idDetalle = (int)($_POST['id_detalle'] ?? 0);
$estado = $_POST['estado'] ?? 'P';
$observacion = trim($_POST['observacion'] ?? '');

try {
    $sql = "INSERT INTO tb_asistencia
            (id_detalle, estado, observacion, usuario_registra)
            VALUES
            (:id_detalle, :estado, :observacion, :usuario_registra)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
    ':id_detalle' => $idDetalle,
    ':estado' => $estado,
    ':observacion' => $observacion ?: null,
    ':usuario_registra' => $_SESSION['usuario']['id_usuario']
    ]);

    header('Location: ../admin/registrar_asistencia.php?ok=1');
} catch (PDOException $e) {
    header('Location: ../admin/registrar_asistencia.php?error=' . urlencode('Ya existe un registro para ese estudiante en la fecha seleccionada.'));
}
exit;
