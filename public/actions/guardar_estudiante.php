<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_role('Administrador');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/estudiantes.php');
    exit;
}

function volver_estudiante(string $mensaje, int $id = 0): void
{
    $url = '../admin/estudiante_form.php?error=' . urlencode($mensaje);
    if ($id > 0) {
        $url .= '&id=' . $id;
    }
    header('Location: ' . $url);
    exit;
}

$idEstudiante = (int)($_POST['id_estudiante'] ?? 0);
$idUsuario = (int)($_POST['id_usuario'] ?? 0);
$codigo = trim($_POST['codigo'] ?? '');
$dni = trim($_POST['dni'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$genero = trim($_POST['genero'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$apoderado = trim($_POST['apoderado'] ?? '');
$telefonoApoderado = trim($_POST['telefono_apoderado'] ?? '');
$idEspecialidad = (int)($_POST['id_especialidad'] ?? 0);
$semestre = (int)($_POST['semestre'] ?? 0);
$usuarioAcceso = trim($_POST['usuario'] ?? '') ?: $codigo;
$claveTemporal = trim($_POST['clave_temporal'] ?? '');
$adminId = (int)$_SESSION['usuario']['id_usuario'];

if ($codigo === '' || $dni === '' || $apellido === '' || $nombre === '' || $idEspecialidad <= 0 || $semestre < 1 || $semestre > 6) {
    volver_estudiante('Completa los campos obligatorios.', $idEstudiante);
}

if (!preg_match('/^[0-9]{8}$/', $dni)) {
    volver_estudiante('El DNI debe tener 8 digitos.', $idEstudiante);
}

if ($idEstudiante === 0 && strlen($claveTemporal) < 6) {
    volver_estudiante('La contraseña temporal debe tener al menos 6 caracteres.', $idEstudiante);
}

try {
    $pdo->beginTransaction();

    $pdo->exec("IF NOT EXISTS (SELECT 1 FROM tb_roles WHERE nombre = 'Alumno') INSERT INTO tb_roles (nombre) VALUES ('Alumno')");
    $idRolAlumno = (int)$pdo->query("SELECT id_rol FROM tb_roles WHERE nombre = 'Alumno'")->fetch()['id_rol'];

    if ($idUsuario <= 0) {
        $stmt = $pdo->prepare("INSERT INTO tb_usuarios
            (id_rol, usuario, clave_hash, nombres, apellidos, correo, debe_cambiar_clave)
            OUTPUT INSERTED.id_usuario
            VALUES
            (:id_rol, :usuario, :clave_hash, :nombres, :apellidos, :correo, 1)");
        $stmt->execute([
            ':id_rol' => $idRolAlumno,
            ':usuario' => $usuarioAcceso,
            ':clave_hash' => strtoupper(hash('sha256', $claveTemporal)),
            ':nombres' => $nombre,
            ':apellidos' => $apellido,
            ':correo' => $correo ?: null,
        ]);
        $idUsuario = (int)$stmt->fetchColumn();
    } else {
        $sqlUsuario = "UPDATE tb_usuarios
                       SET usuario = :usuario,
                           nombres = :nombres,
                           apellidos = :apellidos,
                           correo = :correo,
                           vigencia = 1,
                           usu_modifica = :usu_modifica,
                           f_modificacion = SYSDATETIME()";
        $paramsUsuario = [
            ':usuario' => $usuarioAcceso,
            ':nombres' => $nombre,
            ':apellidos' => $apellido,
            ':correo' => $correo ?: null,
            ':usu_modifica' => $adminId,
            ':id_usuario' => $idUsuario,
        ];

        if ($claveTemporal !== '') {
            if (strlen($claveTemporal) < 6) {
                throw new RuntimeException('La contraseña temporal debe tener al menos 6 caracteres.');
            }
            $sqlUsuario .= ", clave_hash = :clave_hash, debe_cambiar_clave = 1";
            $paramsUsuario[':clave_hash'] = strtoupper(hash('sha256', $claveTemporal));
        }

        $sqlUsuario .= " WHERE id_usuario = :id_usuario";
        $stmt = $pdo->prepare($sqlUsuario);
        $stmt->execute($paramsUsuario);
    }

    if ($idEstudiante > 0) {
        $stmt = $pdo->prepare("UPDATE tb_estudiantes
            SET id_usuario = :id_usuario, codigo = :codigo, dni = :dni, apellido = :apellido, nombre = :nombre,
                telefono = :telefono, correo = :correo, genero = :genero, direccion = :direccion,
                apoderado = :apoderado, telefono_apoderado = :telefono_apoderado,
                usu_modifica = :usu_modifica, f_modificacion = SYSDATETIME()
            WHERE id_estudiante = :id_estudiante");
        $stmt->execute([
            ':id_usuario' => $idUsuario,
            ':codigo' => $codigo,
            ':dni' => $dni,
            ':apellido' => $apellido,
            ':nombre' => $nombre,
            ':telefono' => $telefono ?: null,
            ':correo' => $correo ?: null,
            ':genero' => $genero ?: null,
            ':direccion' => $direccion ?: null,
            ':apoderado' => $apoderado ?: null,
            ':telefono_apoderado' => $telefonoApoderado ?: null,
            ':usu_modifica' => $adminId,
            ':id_estudiante' => $idEstudiante,
        ]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO tb_estudiantes
            (id_usuario, codigo, dni, apellido, nombre, telefono, correo, genero, direccion, apoderado, telefono_apoderado, usuario_registra)
            OUTPUT INSERTED.id_estudiante
            VALUES
            (:id_usuario, :codigo, :dni, :apellido, :nombre, :telefono, :correo, :genero, :direccion, :apoderado, :telefono_apoderado, :usuario_registra)");
        $stmt->execute([
            ':id_usuario' => $idUsuario,
            ':codigo' => $codigo,
            ':dni' => $dni,
            ':apellido' => $apellido,
            ':nombre' => $nombre,
            ':telefono' => $telefono ?: null,
            ':correo' => $correo ?: null,
            ':genero' => $genero ?: null,
            ':direccion' => $direccion ?: null,
            ':apoderado' => $apoderado ?: null,
            ':telefono_apoderado' => $telefonoApoderado ?: null,
            ':usuario_registra' => $adminId,
        ]);
        $idEstudiante = (int)$stmt->fetchColumn();
    }

    $stmt = $pdo->prepare("SELECT id_detalle, id_especialidad, semestre
                           FROM detalle_estudiante_especialidad
                           WHERE id_estudiante = :id_estudiante AND vigencia = 1");
    $stmt->execute([':id_estudiante' => $idEstudiante]);
    $detalle = $stmt->fetch();

    if ($detalle) {
        $stmt = $pdo->prepare("UPDATE detalle_estudiante_especialidad
                               SET id_especialidad = :id_especialidad,
                                   semestre = :semestre,
                                   usu_modifica = :usu_modifica,
                                   f_modificacion = SYSDATETIME()
                               WHERE id_detalle = :id_detalle");
        $stmt->execute([
            ':id_especialidad' => $idEspecialidad,
            ':semestre' => $semestre,
            ':usu_modifica' => $adminId,
            ':id_detalle' => $detalle['id_detalle'],
        ]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO detalle_estudiante_especialidad
            (id_estudiante, id_especialidad, semestre, usuario_registra)
            VALUES (:id_estudiante, :id_especialidad, :semestre, :usuario_registra)");
        $stmt->execute([
            ':id_estudiante' => $idEstudiante,
            ':id_especialidad' => $idEspecialidad,
            ':semestre' => $semestre,
            ':usuario_registra' => $adminId,
        ]);
    }

    $pdo->commit();
    header('Location: ../admin/estudiantes.php?ok=1');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    volver_estudiante('No se pudo guardar. Revisa codigo, DNI o usuario duplicado.', $idEstudiante);
}
exit;
