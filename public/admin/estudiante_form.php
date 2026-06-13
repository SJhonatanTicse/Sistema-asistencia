<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('Administrador');

$id = (int)($_GET['id'] ?? 0);
$pageTitle = $id > 0 ? 'Editar alumno' : 'Nuevo alumno';
$error = $_GET['error'] ?? '';

$especialidades = $pdo->query("SELECT id_especialidad, nombre FROM tb_especialidades WHERE vigencia = 1 ORDER BY nombre")->fetchAll();

$alumno = [
    'id_estudiante' => 0,
    'id_usuario' => null,
    'codigo' => '',
    'dni' => '',
    'apellido' => '',
    'nombre' => '',
    'telefono' => '',
    'correo' => '',
    'genero' => '',
    'direccion' => '',
    'apoderado' => '',
    'telefono_apoderado' => '',
    'id_especialidad' => '',
    'semestre' => '1',
    'usuario' => '',
];

if ($id > 0) {
    $sql = "SELECT e.*, d.id_especialidad, d.semestre, u.usuario
            FROM tb_estudiantes e
            LEFT JOIN detalle_estudiante_especialidad d ON d.id_estudiante = e.id_estudiante AND d.vigencia = 1
            LEFT JOIN tb_usuarios u ON u.id_usuario = e.id_usuario
            WHERE e.id_estudiante = :id_estudiante AND e.vigencia = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_estudiante' => $id]);
    $data = $stmt->fetch();

    if (!$data) {
        header('Location: estudiantes.php?error=' . urlencode('Alumno no encontrado.'));
        exit;
    }

    $alumno = array_merge($alumno, $data);
}

require_once __DIR__ . '/../../includes/header.php';
?>
<div class="app-layout">
    <?php require_once __DIR__ . '/../../includes/sidebar.php'; ?>
    <main class="content">
        <?php require_once __DIR__ . '/../../includes/topbar.php'; ?>

        <section class="panel">
            <div class="panel-heading">
                <div>
                    <p class="muted">Gestion academica</p>
                    <h2><?= e($pageTitle) ?></h2>
                </div>
                <a class="secondary-button" href="estudiantes.php">Volver</a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <?php if (!$especialidades): ?>
                <div class="alert alert-error">Primero registra una especialidad activa.</div>
            <?php endif; ?>

            <form class="form-grid student-form" method="POST" action="../actions/guardar_estudiante.php">
                <input type="hidden" name="id_estudiante" value="<?= e((string)$alumno['id_estudiante']) ?>">
                <input type="hidden" name="id_usuario" value="<?= e((string)$alumno['id_usuario']) ?>">

                <label>Codigo
                    <input type="text" name="codigo" value="<?= e($alumno['codigo']) ?>" required maxlength="20">
                </label>
                <label>DNI
                    <input type="text" name="dni" value="<?= e($alumno['dni']) ?>" required maxlength="8" pattern="[0-9]{8}">
                </label>
                <label>Apellidos
                    <input type="text" name="apellido" value="<?= e($alumno['apellido']) ?>" required maxlength="80">
                </label>
                <label>Nombres
                    <input type="text" name="nombre" value="<?= e($alumno['nombre']) ?>" required maxlength="80">
                </label>
                <label>Telefono
                    <input type="text" name="telefono" value="<?= e($alumno['telefono']) ?>" maxlength="20">
                </label>
                <label>Correo
                    <input type="email" name="correo" value="<?= e($alumno['correo']) ?>" maxlength="120">
                </label>
                <label>Genero
                    <select name="genero">
                        <option value="">Seleccione</option>
                        <option value="M" <?= $alumno['genero'] === 'M' ? 'selected' : '' ?>>Masculino</option>
                        <option value="F" <?= $alumno['genero'] === 'F' ? 'selected' : '' ?>>Femenino</option>
                    </select>
                </label>
                <label>Especialidad
                    <select name="id_especialidad" required>
                        <option value="">Seleccione</option>
                        <?php foreach ($especialidades as $esp): ?>
                            <option value="<?= e((string)$esp['id_especialidad']) ?>" <?= (int)$alumno['id_especialidad'] === (int)$esp['id_especialidad'] ? 'selected' : '' ?>>
                                <?= e($esp['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Semestre
                    <select name="semestre" required>
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                            <option value="<?= $i ?>" <?= (int)$alumno['semestre'] === $i ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </label>
                <label>Usuario de acceso
                    <input type="text" name="usuario" value="<?= e($alumno['usuario'] ?: $alumno['codigo']) ?>" maxlength="50" placeholder="Por defecto usa el codigo">
                </label>
                <label class="full">Direccion
                    <input type="text" name="direccion" value="<?= e($alumno['direccion']) ?>" maxlength="180">
                </label>
                <label>Apoderado
                    <input type="text" name="apoderado" value="<?= e($alumno['apoderado']) ?>" maxlength="120">
                </label>
                <label>Telefono apoderado
                    <input type="text" name="telefono_apoderado" value="<?= e($alumno['telefono_apoderado']) ?>" maxlength="20">
                </label>
                <label>Contraseña temporal
                    <input type="text" name="clave_temporal" placeholder="<?= $id > 0 ? 'Solo si deseas cambiarla' : 'Ejemplo: DNI o clave temporal' ?>" <?= $id > 0 ? '' : 'required' ?>>
                </label>
                <p class="form-help">Al crear o cambiar la contraseña temporal, el alumno debera actualizarla al iniciar sesion.</p>
                <button class="primary-button" type="submit">Guardar alumno</button>
            </form>
        </section>
    </main>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
