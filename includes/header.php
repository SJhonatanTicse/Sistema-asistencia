<?php
$basePath = strpos($_SERVER['PHP_SELF'], '/admin/') !== false || strpos($_SERVER['PHP_SELF'], '/alumno/') !== false
    ? '..'
    : '.';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? APP_NAME) ?></title>
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?= $basePath ?>/assets/css/responsive.css">
</head>
<body>
