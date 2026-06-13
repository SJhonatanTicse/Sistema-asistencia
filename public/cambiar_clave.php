<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$pageTitle = 'Cambiar contraseña';
$error = $_GET['error'] ?? '';
$ok = $_GET['ok'] ?? '';
$obligatorio = (int)($_SESSION['usuario']['debe_cambiar_clave'] ?? 0) === 1;

require_once __DIR__ . '/../includes/header.php';
?>
<div class="app-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="content">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <section class="panel narrow">
            <div class="panel-heading">
                <div>
                    <p class="muted"><?= $obligatorio ? 'Acceso protegido' : 'Configuracion personal' ?></p>
                    <h2><?= $obligatorio ? 'Actualiza tu contraseña' : 'Cambiar contraseña' ?></h2>
                </div>
            </div>

            <?php if ($obligatorio): ?>
                <div class="alert alert-info">Debes cambiar tu contraseña temporal para continuar.</div>
            <?php endif; ?>

            <?php if ($ok): ?>
                <div class="alert alert-success">Contraseña actualizada correctamente.</div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <form class="form-grid" method="POST" action="actions/cambiar_clave.php">
                <label class="full">Contraseña actual
                    <input type="password" name="clave_actual" required autocomplete="current-password">
                </label>
                <label>Nueva contraseña
                    <input type="password" name="clave_nueva" required minlength="6" autocomplete="new-password">
                </label>
                <label>Confirmar contraseña
                    <input type="password" name="clave_confirmar" required minlength="6" autocomplete="new-password">
                </label>
                <button class="primary-button" type="submit">Guardar contraseña</button>
            </form>
        </section>
    </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
