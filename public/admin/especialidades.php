<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('Administrador');

$pageTitle = 'Especialidades';
$id = (int)($_GET['id'] ?? 0);
$mensaje = $_GET['ok'] ?? '';
$error = $_GET['error'] ?? '';

$especialidad = [
    'id_especialidad' => 0,
    'nombre' => '',
    'caracteristicas' => '',
];

if ($id > 0) {
    $stmt = $pdo->prepare("SELECT id_especialidad, nombre, caracteristicas
                           FROM tb_especialidades
                           WHERE id_especialidad = :id_especialidad AND vigencia = 1");
    $stmt->execute([':id_especialidad' => $id]);
    $data = $stmt->fetch();

    if ($data) {
        $especialidad = array_merge($especialidad, $data);
    }
}

$especialidades = $pdo->query("SELECT id_especialidad, nombre, caracteristicas
                               FROM tb_especialidades
                               WHERE vigencia = 1
                               ORDER BY nombre")->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
?>
<div class="app-layout">
    <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
    <main class="content">
        <?php require_once __DIR__ . '/../../includes/topbar.php'; ?>

        <section class="panel narrow">
            <div class="panel-heading">
                <div>
                    <p class="muted">Catalogo academico</p>
                    <h2><?= $especialidad['id_especialidad'] ? 'Editar especialidad' : 'Nueva especialidad' ?></h2>
                </div>
                <?php if ($especialidad['id_especialidad']): ?>
                    <a class="secondary-button" href="especialidades.php">Nuevo registro</a>
                <?php endif; ?>
            </div>

            <?php if ($mensaje): ?>
                <div class="alert alert-success">Operacion realizada correctamente.</div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <form class="form-grid" method="POST" action="../actions/guardar_especialidad.php">
                <input type="hidden" name="id_especialidad" value="<?= e((string)$especialidad['id_especialidad']) ?>">
                <label class="full">Nombre
                    <input type="text" name="nombre" value="<?= e($especialidad['nombre']) ?>" required maxlength="100">
                </label>
                <label class="full">Caracteristicas
                    <textarea name="caracteristicas" rows="3" maxlength="300"><?= e($especialidad['caracteristicas']) ?></textarea>
                </label>
                <button class="primary-button" type="submit">Guardar especialidad</button>
            </form>
        </section>

        <section class="panel">
            <div class="panel-heading">
                <div>
                    <p class="muted">Especialidades activas</p>
                    <h2>Listado</h2>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Caracteristicas</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($especialidades as $row): ?>
                            <tr>
                                <td><?= e($row['nombre']) ?></td>
                                <td><?= e($row['caracteristicas']) ?></td>
                                <td class="actions-cell">
                                    <a class="table-action" href="especialidades.php?id=<?= e((string)$row['id_especialidad']) ?>">Editar</a>
                                    <form method="POST" action="../actions/desactivar_especialidad.php" onsubmit="return confirm('Deseas desactivar esta especialidad?');">
                                        <input type="hidden" name="id_especialidad" value="<?= e((string)$row['id_especialidad']) ?>">
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
