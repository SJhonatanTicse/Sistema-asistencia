<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('Alumno');

$pageTitle = 'Mi asistencia';
$fechaInicio = $_GET['fecha_inicio'] ?? '';
$fechaFin = $_GET['fecha_fin'] ?? '';

$sql = "SELECT v.*
        FROM vw_resumen_asistencia v
        INNER JOIN tb_estudiantes e ON e.id_estudiante = v.id_estudiante
        WHERE e.id_usuario = :id_usuario";
$params = [':id_usuario' => $_SESSION['usuario']['id_usuario']];

if ($fechaInicio !== '') {
    $sql .= " AND v.fecha_asistencia >= :fecha_inicio";
    $params[':fecha_inicio'] = $fechaInicio;
}

if ($fechaFin !== '') {
    $sql .= " AND v.fecha_asistencia <= :fecha_fin";
    $params[':fecha_fin'] = $fechaFin;
}

$sql .= " ORDER BY v.fecha_asistencia DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$asistencias = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>
<div class="app-layout">
    <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
    <main class="content">
        <?php require_once __DIR__ . '/../../includes/topbar.php'; ?>

        <section class="panel">
            <form class="filter-bar" method="GET">
                <label>Desde
                    <input type="date" name="fecha_inicio" value="<?= e($fechaInicio) ?>">
                </label>
                <label>Hasta
                    <input type="date" name="fecha_fin" value="<?= e($fechaFin) ?>">
                </label>
                <button class="primary-button" type="submit">Filtrar</button>
            </form>
        </section>

        <section class="calendar-grid">
            <?php foreach ($asistencias as $row): ?>
                <article class="calendar-day <?= status_class($row['estado']) ?>">
                    <span><?= e((string)$row['fecha_asistencia']) ?></span>
                    <strong><?= status_label($row['estado']) ?></strong>
                    <p><?= e($row['especialidad']) ?></p>
                    <small><?= e($row['especialidad']) ?> | Semestre <?= e((string)$row['semestre']) ?></small>
                </article>
            <?php endforeach; ?>
        </section>
    </main>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
