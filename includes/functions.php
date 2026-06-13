<?php
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function current_user_name(): string
{
    $name = trim(($_SESSION['usuario']['nombres'] ?? '') . ' ' . ($_SESSION['usuario']['apellidos'] ?? ''));
    return $name !== '' ? $name : 'Usuario';
}

function role_name(): string
{
    return $_SESSION['usuario']['rol'] ?? '';
}

function profile_photo(): ?string
{
    $photo = $_SESSION['usuario']['foto_perfil'] ?? '';
    return $photo !== '' ? $photo : null;
}

function status_label(string $estado): string
{
    switch ($estado) {
        case 'P':
            return 'Temprano';
        case 'T':
            return 'Tarde';
        case 'F':
            return 'Inasistencia';
        default:
            return 'Sin estado';
    }
}

function status_class(string $estado): string
{
    switch ($estado) {
        case 'P':
            return 'status-present';
        case 'T':
            return 'status-late';
        case 'F':
            return 'status-absent';
        default:
            return 'status-neutral';
    }
}

function redirect_by_role(string $rol): void
{
    if ((int)($_SESSION['usuario']['debe_cambiar_clave'] ?? 0) === 1) {
        header('Location: cambiar_clave.php');
        exit;
    }

    if ($rol === 'Administrador') {
        header('Location: admin/dashboard.php');
        exit;
    }

    header('Location: alumno/dashboard.php');
    exit;
}
