<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (isset($_SESSION['usuario'])) {
    redirect_by_role($_SESSION['usuario']['rol']);
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $clave = trim($_POST['clave'] ?? '');

    if (login_user($pdo, $usuario, $clave)) {
        redirect_by_role($_SESSION['usuario']['rol']);
    }

    $error = 'Usuario o clave incorrectos.';
}

$pageTitle = 'Iniciar sesion';
require_once __DIR__ . '/../includes/header.php';
?>
<main class="login-shell">
    <section class="login-panel fade-in">
        <div class="login-brand">
            <img src="assets/img/logo_senati.png" alt="Logo Senati" class="login-logo">
            <div>
                <p>Sistema academico</p>
                <h1>SENATI</h1>
            </div>
        </div>

        <form method="POST" class="login-form">
            <label>
                Usuario
                <input type="text" name="usuario" placeholder="Ingrese su usuario" required autocomplete="username">
            </label>

            <label>
                Contraseña
                <input type="password" name="clave" placeholder="Ingrese su contraseña" required autocomplete="current-password">
            </label>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <button class="primary-button" type="submit">Ingresar</button>
        </form>

        <!--<div class="demo-access">
            <strong>Base de datos limpia</strong>
            <span>Primero crea un usuario administrador para iniciar sesion.</span>
        </div>-->
    </section>

    <section class="login-visual" aria-label="Area visual institucional">
        <img src="assets/img/sede_pasco.png" alt="Sede Senati" class="sede_image">
    </section>
</main>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
