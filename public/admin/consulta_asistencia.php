<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('Administrador');

$pageTitle = 'Consulta de asistencia';
$busqueda = trim($_GET['busqueda'] ?? '');
$fechaInicio = $_GET['fecha_inicio'] ?? '';
$fechaFin = $_GET['fecha_fin'] ?? '';
$idEspecialidad = (int)($_GET['id_especialidad'] ?? 0);
$semestre = (int)($_GET['semestre'] ?? 0);
$estado = $_GET['estado'] ?? '';
$resultados = [];

$especialidades = $pdo->query("SELECT id_especialidad, nombre FROM tb_especialidades WHERE vigencia = 1 ORDER BY nombre")->fetchAll();

$sql = "SELECT v.*
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
$resultados = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>
<div class="app-layout">
    <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
    <main class="content">
        <?php require_once __DIR__ . '/../../includes/topbar.php'; ?>

        <section class="panel">
            <form class="filter-bar" method="GET">
                <label>Codigo o nombre
                    <input type="search" name="busqueda" value="<?= e($busqueda) ?>" placeholder="Ejemplo: A2026001 o Ramirez">
                </label>
                <label>Especialidad
                    <select name="id_especialidad">
                        <option value="0">Todas</option>
                        <?php foreach ($especialidades as $esp): ?>
                            <option value="<?= e((string)$esp['id_especialidad']) ?>" <?= $idEspecialidad === (int)$esp['id_especialidad'] ? 'selected' : '' ?>>
                                <?= e($esp['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Semestre
                    <select name="semestre">
                        <option value="0">Todos</option>
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                            <option value="<?= $i ?>" <?= $semestre === $i ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </label>
                <label>Estado
                    <select name="estado">
                        <option value="">Todos</option>
                        <option value="P" <?= $estado === 'P' ? 'selected' : '' ?>>Temprano</option>
                        <option value="T" <?= $estado === 'T' ? 'selected' : '' ?>>Tarde</option>
                        <option value="F" <?= $estado === 'F' ? 'selected' : '' ?>>Inasistencia</option>
                    </select>
                </label>
                <label>Desde
                    <input type="date" name="fecha_inicio" value="<?= e($fechaInicio) ?>">
                </label>
                <label>Hasta
                    <input type="date" name="fecha_fin" value="<?= e($fechaFin) ?>">
                </label>
                <button class="primary-button" type="submit">Buscar</button>
                <button class="secondary-button" type="submit" formaction="../actions/exportar_asistencia.php">Exportar CSV</button>
            </form>
        </section>

        <section class="calendar-grid">
            <?php if (!$resultados): ?>
                <div class="empty-state">No se encontraron asistencias con los filtros indicados.</div>
            <?php endif; ?>

            <?php foreach ($resultados as $row): ?>
                <article class="calendar-day <?= status_class($row['estado']) ?>">
                    <div class="attendance-header">
                        <span><?= e((string)$row['fecha_asistencia']) ?></span>
                        <span class="attendance-hour">
                            <?= substr((string)$row['hora_registro'], 0, 5) ?>
                        </span>
                    </div>

                    <strong><?= status_label($row['estado']) ?></strong>

                    <p><?= e($row['estudiante']) ?></p>

                    <small>
                        <?= e($row['codigo']) ?> |
                        <?= e($row['especialidad']) ?> |
                        Semestre <?= e((string)$row['semestre']) ?>
                    </small>
                </article>
            <?php endforeach; ?>
        </section>
    </main>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
