<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('Administrador');

$pageTitle = 'Dashboard administrador';

$totales = [
    'estudiantes' => $pdo->query("SELECT COUNT(*) total FROM tb_estudiantes WHERE vigencia = 1")->fetch()['total'] ?? 0,
    'especialidades' => $pdo->query("SELECT COUNT(*) total FROM tb_especialidades WHERE vigencia = 1")->fetch()['total'] ?? 0,
    'presentes' => $pdo->query("SELECT COUNT(*) total FROM tb_asistencia WHERE estado = 'P' AND fecha_asistencia = CONVERT(DATE, GETDATE())")->fetch()['total'] ?? 0,
    'tardes' => $pdo->query("SELECT COUNT(*) total FROM tb_asistencia WHERE estado = 'T' AND fecha_asistencia = CONVERT(DATE, GETDATE())")->fetch()['total'] ?? 0,
];

$stmt = $pdo->query("SELECT TOP 8 * FROM vw_resumen_asistencia ORDER BY fecha_asistencia DESC, f_hora DESC");
$ultimos = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>
<div class="app-layout">
    <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
    <main class="content">
        <?php require_once __DIR__ . '/../../includes/topbar.php'; ?>

        <section class="stats-grid">
            <article class="stat-card"><span>Estudiantes activos</span><strong><?= e((string)$totales['estudiantes']) ?></strong></article>
            <article class="stat-card"><span>Especialidades</span><strong><?= e((string)$totales['especialidades']) ?></strong></article>
            <article class="stat-card"><span>Temprano hoy</span><strong><?= e((string)$totales['presentes']) ?></strong></article>
            <article class="stat-card"><span>Tardes hoy</span><strong><?= e((string)$totales['tardes']) ?></strong></article>
        </section>

        <section class="panel">
            <div class="panel-heading">
                <div>
                    <p class="muted">Movimiento reciente</p>
                    <h2>Ultimos registros</h2>
                </div>
                <a class="secondary-button" href="consulta_asistencia.php">Consultar alumno</a>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Codigo</th>
                            <th>Alumno</th>
                            <th>Especialidad</th>
                            <th>Semestre</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ultimos as $row): ?>
                            <tr>
                                <td><?= e($row['codigo']) ?></td>
                                <td><?= e($row['estudiante']) ?></td>
                                <td><?= e($row['especialidad']) ?></td>
                                <td><?= e((string)$row['semestre']) ?></td>
                                <td><?= e((string)$row['fecha_asistencia']) ?></td>
                                <td><span class="status-pill <?= status_class($row['estado']) ?>"><?= status_label($row['estado']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
