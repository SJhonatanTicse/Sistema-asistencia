<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('Administrador');

$pageTitle = 'Registrar asistencia';
$mensaje = $_GET['ok'] ?? '';
$error = $_GET['error'] ?? '';

$sql = "SELECT d.id_detalle, e.codigo, CONCAT(e.apellido, ' ', e.nombre) estudiante, esp.nombre especialidad, d.semestre
        FROM detalle_estudiante_especialidad d
        INNER JOIN tb_estudiantes e ON e.id_estudiante = d.id_estudiante
        INNER JOIN tb_especialidades esp ON esp.id_especialidad = d.id_especialidad
        WHERE d.vigencia = 1 AND e.vigencia = 1
        ORDER BY e.apellido, e.nombre";
$detalles = $pdo->query($sql)->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>
<div class="app-layout">
    <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
    <main class="content">
        <?php require_once __DIR__ . '/../../includes/topbar.php'; ?>

        <section class="panel narrow">
            <div class="panel-heading">
                <div>
                    <p class="muted">Registro diario</p>
                    <h2>Nueva asistencia</h2>
                </div>
            </div>

            <?php if ($mensaje): ?><div class="alert alert-success">Asistencia registrada correctamente.</div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

            <form class="form-grid" method="POST" action="../actions/guardar_asistencia.php">
                <label>Estudiante
                    <select id="buscar-estudiante" name="id_detalle" required>
                        <option value="">Buscar estudiante...</option>

                        <?php foreach ($detalles as $row): ?>
                            <option value="<?= e((string)$row['id_detalle']) ?>">
                                <?= e($row['codigo'] . ' - ' . $row['estudiante'] . ' - S' . $row['semestre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Fecha
                    <input type="date" value="<?= date('Y-m-d') ?>" disabled>
                </label>
                <label>Hora
                    <input type="time" value="<?= date('H:i') ?>" disabled>
                </label>
                <label>Estado
                    <select name="estado" required>
                        <option value="P">Temprano</option>
                        <option value="T">Tarde</option>
                        <option value="F">Inasistencia</option>
                    </select>
                </label>
                <label class="full">Observacion
                    <textarea name="observacion" rows="3" placeholder="Opcional"></textarea>
                </label>
                <button class="primary-button" type="submit">Guardar asistencia</button>
            </form>
        </section>
    </main>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
