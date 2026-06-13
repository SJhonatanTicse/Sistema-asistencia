<?php
date_default_timezone_set('America/Lima');
// Conexion PDO para SQL Server. Ajusta estos valores segun tu instalacion.
$dbServer = 'localhost\\SQLEXPRESS';
$dbName = 'BD_SENATI_ASISTENCIA';
$dbUser = 'jhona_x';
$dbPassword = '2411';

try {
    $pdo = new PDO(
        "sqlsrv:Server={$dbServer};Database={$dbName};TrustServerCertificate=true",
        $dbUser,
        $dbPassword,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('No se pudo conectar con SQL Server. Revisa config/database.php y el driver sqlsrv.');
}
