/* ============================================================
   Sistema de Asistencia SENATI
   Motor: Microsoft SQL Server
   Ejecutar este archivo completo en SQL Server Management Studio.
   ============================================================ */

IF DB_ID('BD_SENATI_ASISTENCIA') IS NULL
BEGIN
    CREATE DATABASE BD_SENATI_ASISTENCIA;
END
GO

USE BD_SENATI_ASISTENCIA;
GO

/* Limpieza opcional para recrear el proyecto academico desde cero. */
IF OBJECT_ID('dbo.vw_resumen_asistencia', 'V') IS NOT NULL DROP VIEW dbo.vw_resumen_asistencia;
IF OBJECT_ID('dbo.sp_buscar_asistencia_estudiante', 'P') IS NOT NULL DROP PROCEDURE dbo.sp_buscar_asistencia_estudiante;
IF OBJECT_ID('dbo.tb_asistencia', 'U') IS NOT NULL DROP TABLE dbo.tb_asistencia;
IF OBJECT_ID('dbo.detalle_estudiante_especialidad', 'U') IS NOT NULL DROP TABLE dbo.detalle_estudiante_especialidad;
IF OBJECT_ID('dbo.tb_estudiantes', 'U') IS NOT NULL DROP TABLE dbo.tb_estudiantes;
IF OBJECT_ID('dbo.tb_especialidades', 'U') IS NOT NULL DROP TABLE dbo.tb_especialidades;
IF OBJECT_ID('dbo.tb_usuarios', 'U') IS NOT NULL DROP TABLE dbo.tb_usuarios;
IF OBJECT_ID('dbo.tb_roles', 'U') IS NOT NULL DROP TABLE dbo.tb_roles;
GO

CREATE TABLE dbo.tb_roles (
    id_rol INT IDENTITY(1,1) PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL UNIQUE
);
GO

CREATE TABLE dbo.tb_usuarios (
    id_usuario INT IDENTITY(1,1) PRIMARY KEY,
    id_rol INT NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    clave_hash CHAR(64) NOT NULL,
    nombres VARCHAR(80) NOT NULL,
    apellidos VARCHAR(80) NOT NULL,
    correo VARCHAR(120) NULL,
    foto_perfil VARCHAR(180) NULL,
    debe_cambiar_clave BIT NOT NULL DEFAULT 0,
    vigencia BIT NOT NULL DEFAULT 1,
    f_registro DATETIME2(0) NOT NULL DEFAULT SYSDATETIME(),
    usu_modifica INT NULL,
    f_modificacion DATETIME2(0) NULL,
    CONSTRAINT FK_usuarios_roles FOREIGN KEY (id_rol) REFERENCES dbo.tb_roles(id_rol),
    CONSTRAINT FK_usuarios_modifica FOREIGN KEY (usu_modifica) REFERENCES dbo.tb_usuarios(id_usuario)
);
GO

CREATE TABLE dbo.tb_especialidades (
    id_especialidad INT IDENTITY(1,1) PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    caracteristicas VARCHAR(300) NULL,
    usuario_registra INT NOT NULL,
    f_registro DATETIME2(0) NOT NULL DEFAULT SYSDATETIME(),
    usu_modifica INT NULL,
    f_modificacion DATETIME2(0) NULL,
    vigencia BIT NOT NULL DEFAULT 1,
    CONSTRAINT FK_especialidades_registra FOREIGN KEY (usuario_registra) REFERENCES dbo.tb_usuarios(id_usuario),
    CONSTRAINT FK_especialidades_modifica FOREIGN KEY (usu_modifica) REFERENCES dbo.tb_usuarios(id_usuario)
);
GO

CREATE TABLE dbo.tb_estudiantes (
    id_estudiante INT IDENTITY(1,1) PRIMARY KEY,
    id_usuario INT NULL,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    dni CHAR(8) NOT NULL UNIQUE,
    apellido VARCHAR(80) NOT NULL,
    nombre VARCHAR(80) NOT NULL,
    telefono VARCHAR(20) NULL,
    correo VARCHAR(120) NULL,
    genero CHAR(1) NULL,
    direccion VARCHAR(180) NULL,
    apoderado VARCHAR(120) NULL,
    telefono_apoderado VARCHAR(20) NULL,
    usuario_registra INT NOT NULL,
    f_registro DATETIME2(0) NOT NULL DEFAULT SYSDATETIME(),
    usu_modifica INT NULL,
    f_modificacion DATETIME2(0) NULL,
    vigencia BIT NOT NULL DEFAULT 1,
    CONSTRAINT CK_estudiantes_dni CHECK (dni NOT LIKE '%[^0-9]%' AND LEN(dni) = 8),
    CONSTRAINT CK_estudiantes_genero CHECK (genero IS NULL OR genero IN ('M', 'F')),
    CONSTRAINT FK_estudiantes_usuario FOREIGN KEY (id_usuario) REFERENCES dbo.tb_usuarios(id_usuario),
    CONSTRAINT FK_estudiantes_registra FOREIGN KEY (usuario_registra) REFERENCES dbo.tb_usuarios(id_usuario),
    CONSTRAINT FK_estudiantes_modifica FOREIGN KEY (usu_modifica) REFERENCES dbo.tb_usuarios(id_usuario)
);
GO

CREATE TABLE dbo.detalle_estudiante_especialidad (
    id_detalle INT IDENTITY(1,1) PRIMARY KEY,
    id_estudiante INT NOT NULL,
    id_especialidad INT NOT NULL,
    semestre TINYINT NOT NULL,
    fecha_inicio DATE NOT NULL DEFAULT CONVERT(DATE, GETDATE()),
    fecha_fin DATE NULL,
    vigencia BIT NOT NULL DEFAULT 1,
    usuario_registra INT NOT NULL,
    f_registro DATETIME2(0) NOT NULL DEFAULT SYSDATETIME(),
    usu_modifica INT NULL,
    f_modificacion DATETIME2(0) NULL,
    CONSTRAINT CK_detalle_semestre CHECK (semestre BETWEEN 1 AND 6),
    CONSTRAINT FK_detalle_estudiante FOREIGN KEY (id_estudiante) REFERENCES dbo.tb_estudiantes(id_estudiante),
    CONSTRAINT FK_detalle_especialidad FOREIGN KEY (id_especialidad) REFERENCES dbo.tb_especialidades(id_especialidad),
    CONSTRAINT FK_detalle_registra FOREIGN KEY (usuario_registra) REFERENCES dbo.tb_usuarios(id_usuario),
    CONSTRAINT FK_detalle_modifica FOREIGN KEY (usu_modifica) REFERENCES dbo.tb_usuarios(id_usuario)
);
GO

CREATE TABLE dbo.tb_asistencia (
    id_asistencia INT IDENTITY(1,1) PRIMARY KEY,
    id_detalle INT NOT NULL,
    estado CHAR(1) NOT NULL,
    fecha_asistencia DATE NOT NULL,
    hora_registro TIME(0) NULL,
    f_hora DATETIME2(0) NOT NULL DEFAULT SYSDATETIME(),
    observacion VARCHAR(200) NULL,
    usuario_registra INT NOT NULL,
    f_registro DATETIME2(0) NOT NULL DEFAULT SYSDATETIME(),
    usu_modifica INT NULL,
    f_modificacion DATETIME2(0) NULL,
    vigencia BIT NOT NULL DEFAULT 1,
    CONSTRAINT CK_asistencia_estado CHECK (estado IN ('P', 'T', 'F')),
    CONSTRAINT FK_asistencia_detalle FOREIGN KEY (id_detalle) REFERENCES dbo.detalle_estudiante_especialidad(id_detalle),
    CONSTRAINT FK_asistencia_registra FOREIGN KEY (usuario_registra) REFERENCES dbo.tb_usuarios(id_usuario),
    CONSTRAINT FK_asistencia_modifica FOREIGN KEY (usu_modifica) REFERENCES dbo.tb_usuarios(id_usuario),
    CONSTRAINT UQ_asistencia_detalle_fecha UNIQUE (id_detalle, fecha_asistencia)
);
GO

CREATE INDEX IX_estudiantes_nombre_codigo ON dbo.tb_estudiantes(codigo, apellido, nombre);
CREATE UNIQUE INDEX UQ_estudiantes_usuario_activo
ON dbo.tb_estudiantes(id_usuario)
WHERE id_usuario IS NOT NULL;
CREATE INDEX IX_asistencia_fecha_estado ON dbo.tb_asistencia(fecha_asistencia, estado);
GO

CREATE VIEW dbo.vw_resumen_asistencia
AS
SELECT
    a.id_asistencia,
    e.id_estudiante,
    e.codigo,
    CONCAT(e.apellido, ' ', e.nombre) AS estudiante,
    esp.nombre AS especialidad,
    d.semestre,
    a.estado,
    CASE a.estado
        WHEN 'P' THEN 'Temprano'
        WHEN 'T' THEN 'Tarde'
        WHEN 'F' THEN 'Inasistencia'
    END AS estado_texto,
    a.fecha_asistencia,
    a.hora_registro,
    a.f_hora,
    a.observacion
FROM dbo.tb_asistencia a
INNER JOIN dbo.detalle_estudiante_especialidad d ON d.id_detalle = a.id_detalle
INNER JOIN dbo.tb_estudiantes e ON e.id_estudiante = d.id_estudiante
INNER JOIN dbo.tb_especialidades esp ON esp.id_especialidad = d.id_especialidad
WHERE a.vigencia = 1
  AND d.vigencia = 1
  AND e.vigencia = 1
  AND esp.vigencia = 1;
GO

CREATE PROCEDURE dbo.sp_buscar_asistencia_estudiante
    @busqueda VARCHAR(100),
    @fecha_inicio DATE = NULL,
    @fecha_fin DATE = NULL
AS
BEGIN
    SET NOCOUNT ON;

    SELECT *
    FROM dbo.vw_resumen_asistencia
    WHERE (
        codigo LIKE '%' + @busqueda + '%'
        OR estudiante LIKE '%' + @busqueda + '%'
    )
      AND (@fecha_inicio IS NULL OR fecha_asistencia >= @fecha_inicio)
      AND (@fecha_fin IS NULL OR fecha_asistencia <= @fecha_fin)
    ORDER BY fecha_asistencia DESC;
END;
GO
