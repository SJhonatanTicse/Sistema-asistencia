<header class="topbar">
    <button class="icon-button" data-sidebar-toggle aria-label="Abrir menu">
        <span></span>
        <span></span>
        <span></span>
    </button>
    <div>
        <p class="muted">Sesion iniciada</p>
        <h1><?= e($pageTitle ?? APP_NAME) ?></h1>
    </div>
    <a class="user-chip" href="<?= $basePath ?>/perfil.php" title="Editar foto de perfil">
        <?php if (profile_photo()): ?>
            <img src="<?= $basePath ?>/<?= e(profile_photo()) ?>" alt="Foto de perfil" class="avatar-image">
        <?php else: ?>
            <span class="avatar-placeholder"></span>
        <?php endif; ?>
        <div>
            <strong><?= e(current_user_name()) ?></strong>
            <small><?= e(role_name()) ?></small>
        </div>
    </a>
</header>
