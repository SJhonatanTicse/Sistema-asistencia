<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_login();

$pageTitle = 'Mi perfil';
$mensaje = $_GET['ok'] ?? '';
$error = $_GET['error'] ?? '';

require_once __DIR__ . '/../includes/header.php';
?>
<div class="app-layout">
    <?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
    <main class="content">
        <?php require_once __DIR__ . '/../includes/topbar.php'; ?>

        <section class="panel profile-panel">
            <div class="panel-heading">
                <div>
                    <p class="muted">Configuracion personal</p>
                    <h2>Foto de perfil</h2>
                </div>
            </div>

            <?php if ($mensaje === 'foto'): ?>
                <div class="alert alert-success">Foto actualizada correctamente.</div>
            <?php endif; ?>

            <?php if ($mensaje === 'eliminada'): ?>
                <div class="alert alert-success">Foto eliminada correctamente.</div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <div class="profile-grid">
                <div class="profile-preview">
                    <?php if (profile_photo()): ?>
                        <img src="<?= $basePath ?>/<?= e(profile_photo()) ?>" alt="Foto actual" class="profile-photo-large">
                    <?php else: ?>
                        <div class="profile-photo-empty"></div>
                    <?php endif; ?>
                </div>

                <form class="form-grid profile-form" action="actions/actualizar_foto.php" method="POST" enctype="multipart/form-data">
                    <label class="full">Subir nueva foto
                        <input type="file" name="foto_perfil" accept="image/png,image/jpeg,image/webp" required>
                    </label>

                    <p class="form-help full">Formatos permitidos: JPG, PNG o WEBP. Peso maximo recomendado: 2 MB.</p>

                    <button class="primary-button" type="submit" name="accion" value="subir">Guardar foto</button>

                    <?php if (profile_photo()): ?>
                        <button class="secondary-button danger-button" type="submit" name="accion" value="eliminar" formnovalidate>Quitar foto</button>
                    <?php endif; ?>
                </form>
            </div>
        </section>

        <section class="panel profile-panel">
            <div class="panel-heading">
                <div>
                    <p class="muted">Seguridad</p>
                    <h2>Contraseña</h2>
                </div>
                <a class="secondary-button" href="cambiar_clave.php">Cambiar contraseña</a>
            </div>
        </section>
    </main>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
