<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('Administrador');

$pageTitle = 'Estudiantes';
$mensaje = $_GET['ok'] ?? '';
$error = $_GET['error'] ?? '';

$sql = "SELECT e.id_estudiante, e.codigo, e.dni, CONCAT(e.apellido, ' ', e.nombre) estudiante, e.telefono, e.correo,
               esp.nombre especialidad, d.semestre, u.usuario
        FROM tb_estudiantes e
        INNER JOIN detalle_estudiante_especialidad d ON d.id_estudiante = e.id_estudiante AND d.vigencia = 1
        INNER JOIN tb_especialidades esp ON esp.id_especialidad = d.id_especialidad
        LEFT JOIN tb_usuarios u ON u.id_usuario = e.id_usuario
        WHERE e.vigencia = 1
        ORDER BY e.apellido, e.nombre";
$estudiantes = $pdo->query($sql)->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>
<div class="app-layout">
    <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
    <main class="content">
        <?php require_once __DIR__ . '/../../includes/topbar.php'; ?>

        <section class="panel">
            <div class="panel-heading">
                <div>
                    <p class="muted">Control academico</p>
                    <h2>Relacion de estudiantes activos</h2>
                </div>
                <a class="primary-button" href="estudiante_form.php">Nuevo alumno</a>
            </div>

            <?php if ($mensaje): ?>
                <div class="alert alert-success">Operacion realizada correctamente.</div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Codigo</th>
                            <th>Usuario</th>
                            <th>DNI</th>
                            <th>Estudiante</th>
                            <th>Telefono</th>
                            <th>Correo</th>
                            <th>Especialidad</th>
                            <th>Semestre</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($estudiantes as $row): ?>
                            <tr>
                                <td><?= e($row['codigo']) ?></td>
                                <td><?= e($row['usuario']) ?></td>
                                <td><?= e($row['dni']) ?></td>
                                <td><?= e($row['estudiante']) ?></td>
                                <td><?= e($row['telefono']) ?></td>
                                <td><?= e($row['correo']) ?></td>
                                <td><?= e($row['especialidad']) ?></td>
                                <td><?= e((string)$row['semestre']) ?></td>
                                <td class="actions-cell">
                                    <a class="table-action" href="estudiante_form.php?id=<?= e((string)$row['id_estudiante']) ?>">Editar</a>
                                    <form method="POST" action="../actions/desactivar_estudiante.php" onsubmit="return confirm('Deseas desactivar este alumno?');">
                                        <input type="hidden" name="id_estudiante" value="<?= e((string)$row['id_estudiante']) ?>">
                                        <button class="table-action danger-text" type="submit">Desactivar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
