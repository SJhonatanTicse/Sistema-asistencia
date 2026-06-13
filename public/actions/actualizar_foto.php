<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_login();

$accion = $_POST['accion'] ?? 'subir';
$idUsuario = (int)$_SESSION['usuario']['id_usuario'];
$uploadRelativeDir = 'assets/uploads/perfiles';
$uploadAbsoluteDir = __DIR__ . '/../' . $uploadRelativeDir;

if (!is_dir($uploadAbsoluteDir)) {
    mkdir($uploadAbsoluteDir, 0775, true);
}

function volver_perfil(string $query): void
{
    header('Location: ../perfil.php?' . $query);
    exit;
}

function eliminar_foto_actual(): void
{
    $actual = $_SESSION['usuario']['foto_perfil'] ?? '';
    if ($actual === '') {
        return;
    }

    $ruta = __DIR__ . '/../' . $actual;
    $carpetaPermitida = realpath(__DIR__ . '/../assets/uploads/perfiles');
    $archivoActual = realpath($ruta);

    if ($carpetaPermitida && $archivoActual && strpos($archivoActual, $carpetaPermitida) === 0 && is_file($archivoActual)) {
        unlink($archivoActual);
    }
}

if ($accion === 'eliminar') {
    eliminar_foto_actual();

    $stmt = $pdo->prepare("UPDATE tb_usuarios SET foto_perfil = NULL, f_modificacion = SYSDATETIME() WHERE id_usuario = :id_usuario");
    $stmt->execute([':id_usuario' => $idUsuario]);
    $_SESSION['usuario']['foto_perfil'] = null;

    volver_perfil('ok=eliminada');
}

if (!isset($_FILES['foto_perfil']) || $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_OK) {
    volver_perfil('error=' . urlencode('Selecciona una imagen valida.'));
}

$file = $_FILES['foto_perfil'];
$maxSize = 2 * 1024 * 1024;

if ($file['size'] > $maxSize) {
    volver_perfil('error=' . urlencode('La imagen no debe superar los 2 MB.'));
}

$imageInfo = getimagesize($file['tmp_name']);

if ($imageInfo === false) {
    volver_perfil('error=' . urlencode('El archivo seleccionado no es una imagen.'));
}

$allowedTypes = [
    IMAGETYPE_JPEG => 'jpg',
    IMAGETYPE_PNG => 'png',
    IMAGETYPE_WEBP => 'webp',
];

if (!isset($allowedTypes[$imageInfo[2]])) {
    volver_perfil('error=' . urlencode('Solo se permiten imagenes JPG, PNG o WEBP.'));
}

$extension = $allowedTypes[$imageInfo[2]];
$fileName = 'usuario_' . $idUsuario . '_' . date('YmdHis') . '.' . $extension;
$destination = $uploadAbsoluteDir . '/' . $fileName;
$relativePath = $uploadRelativeDir . '/' . $fileName;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    volver_perfil('error=' . urlencode('No se pudo guardar la imagen.'));
}

eliminar_foto_actual();

$stmt = $pdo->prepare("UPDATE tb_usuarios SET foto_perfil = :foto_perfil, f_modificacion = SYSDATETIME() WHERE id_usuario = :id_usuario");
$stmt->execute([
    ':foto_perfil' => $relativePath,
    ':id_usuario' => $idUsuario,
]);

$_SESSION['usuario']['foto_perfil'] = $relativePath;

volver_perfil('ok=foto');
