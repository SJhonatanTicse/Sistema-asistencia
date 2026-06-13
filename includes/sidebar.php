<?php
$isAdmin = role_name() === 'Administrador';
$sectionPath = $isAdmin ? $basePath . '/admin' : $basePath . '/alumno';
?>
<aside class="sidebar" data-sidebar>
    <div class="brand">
        <img src="<?= $basePath ?>/assets/img/logo_senati.png" alt="Logo Senati" class="login-logo-slidebar">
        <div>
            <strong>SENATI</strong>
            <span>Asistencia</span>
        </div>
    </div>

    <nav class="menu">
        <?php if ($isAdmin): ?>
            <a href="<?= $sectionPath ?>/dashboard.php">Dashboard</a>
            <a href="<?= $sectionPath ?>/consulta_asistencia.php">Consulta de asistencia</a>
            <a href="<?= $sectionPath ?>/estudiantes.php">Estudiantes</a>
            <a href="<?= $sectionPath ?>/especialidades.php">Especialidades</a>
            <a href="<?= $sectionPath ?>/registrar_asistencia.php">Registrar asistencia</a>
        <?php else: ?>
            <a href="<?= $sectionPath ?>/dashboard.php">Mi dashboard</a>
            <a href="<?= $sectionPath ?>/mi_asistencia.php">Mi asistencia</a>
        <?php endif; ?>
    </nav>
    <a class="logout-link" href="<?= $basePath ?>/logout.php">Cerrar sesion</a>
</aside>
