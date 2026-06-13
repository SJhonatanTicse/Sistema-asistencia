<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('Administrador');

$busqueda = trim($_GET['busqueda'] ?? '');
$fechaInicio = $_GET['fecha_inicio'] ?? '';
$fechaFin = $_GET['fecha_fin'] ?? '';
$idEspecialidad = (int)($_GET['id_especialidad'] ?? 0);
$semestre = (int)($_GET['semestre'] ?? 0);
$estado = $_GET['estado'] ?? '';

$sql = "SELECT v.codigo, v.estudiante, v.especialidad, v.semestre, v.estado_texto, v.fecha_asistencia, v.hora_registro, v.observacion
        FROM vw_resumen_asistencia v
        WHERE 1 = 1";
$params = [];

if ($busqueda !== '') {
    $sql .= " AND (v.codigo LIKE ? OR v.estudiante LIKE ?)";
    $params[] = '%' . $busqueda . '%';
    $params[] = '%' . $busqueda . '%';
}

if ($fechaInicio !== '') {
    $sql .= " AND v.fecha_asistencia >= ?";
    $params[] = $fechaInicio;
}

if ($fechaFin !== '') {
    $sql .= " AND v.fecha_asistencia <= ?";
    $params[] = $fechaFin;
}

if ($idEspecialidad > 0) {
    $sql .= " AND EXISTS (
        SELECT 1
        FROM tb_especialidades esp
        WHERE esp.nombre = v.especialidad
          AND esp.id_especialidad = ?
    )";
    $params[] = $idEspecialidad;
}

if ($semestre >= 1 && $semestre <= 6) {
    $sql .= " AND v.semestre = ?";
    $params[] = $semestre;
}

if (in_array($estado, ['P', 'T', 'F'], true)) {
    $sql .= " AND v.estado = ?";
    $params[] = $estado;
}

$sql .= " ORDER BY v.fecha_asistencia DESC, v.estudiante ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=asistencia_senati_' . date('Ymd_His') . '.csv');

$output = fopen('php://output', 'w');
// Excel reconozca UTF-8 correctamente
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
fputcsv(
    $output,
    ['Codigo', 'Estudiante', 'Especialidad', 'Semestre', 'Estado', 'Fecha', 'Hora', 'Observacion'],
    ';'
);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

    fputcsv($output, [
        $row['codigo'],
        $row['estudiante'],
        $row['especialidad'],
        $row['semestre'],
        $row['estado_texto'],
        $row['fecha_asistencia'],
        substr((string)$row['hora_registro'], 0, 5),
        $row['observacion'],
    ], ';');
}

fclose($output);
exit;
