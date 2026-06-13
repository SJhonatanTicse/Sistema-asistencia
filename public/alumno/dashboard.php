<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('Alumno');

$pageTitle = 'Dashboard alumno';

$sql = "SELECT COUNT(CASE WHEN v.estado = 'P' THEN 1 END) presentes,
               COUNT(CASE WHEN v.estado = 'T' THEN 1 END) tardes,
               COUNT(CASE WHEN v.estado = 'F' THEN 1 END) faltas
        FROM vw_resumen_asistencia v
        INNER JOIN tb_estudiantes e ON e.id_estudiante = v.id_estudiante
        WHERE e.id_usuario = :id_usuario";
$stmt = $pdo->prepare($sql);
$stmt->execute([':id_usuario' => $_SESSION['usuario']['id_usuario']]);
$totales = $stmt->fetch() ?: ['presentes' => 0, 'tardes' => 0, 'faltas' => 0];

$sqlUltimos = "SELECT TOP 6 v.*
               FROM vw_resumen_asistencia v
               INNER JOIN tb_estudiantes e ON e.id_estudiante = v.id_estudiante
               WHERE e.id_usuario = :id_usuario
               ORDER BY v.fecha_asistencia DESC";
$stmt = $pdo->prepare($sqlUltimos);
$stmt->execute([':id_usuario' => $_SESSION['usuario']['id_usuario']]);
$ultimos = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>
<div class="app-layout">
    <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
    <main class="content">
        <?php require_once __DIR__ . '/../../includes/topbar.php'; ?>

        <section class="welcome-band">
            <div>
                <p class="muted">Bienvenido</p>
                <h2><?= e(current_user_name()) ?></h2>
                <span>Consulta tu avance de asistencia por fecha y estado.</span>
            </div>
        </section>

        <section class="stats-grid three">
            <article class="stat-card"><span>Temprano</span><strong><?= e((string)$totales['presentes']) ?></strong></article>
            <article class="stat-card"><span>Tarde</span><strong><?= e((string)$totales['tardes']) ?></strong></article>
            <article class="stat-card"><span>Inasistencias</span><strong><?= e((string)$totales['faltas']) ?></strong></article>
        </section>

        <section class="panel">
            <div class="panel-heading">
                <div>
                    <p class="muted">Mis registros</p>
                    <h2>Ultimas asistencias</h2>
                </div>
                <a class="secondary-button" href="mi_asistencia.php">Ver calendario</a>
            </div>

            <div class="calendar-grid compact">
                <?php foreach ($ultimos as $row): ?>
                    <article class="calendar-day <?= status_class($row['estado']) ?>">
                        <span><?= e((string)$row['fecha_asistencia']) ?></span>
                        <strong><?= status_label($row['estado']) ?></strong>
                        <small><?= e($row['especialidad']) ?> | Semestre <?= e((string)$row['semestre']) ?></small>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    </main>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
