# Sistema de Asistencia SENATI

Proyecto academico en PHP, HTML, CSS, JavaScript y SQL Server para controlar la asistencia de estudiantes por especialidad y semestre.

## Que incluye

- Login para administrador y alumno.
- Dashboard profesional para administrador.
- Dashboard individual para alumno.
- Consulta de asistencia por codigo o nombre del estudiante.
- Vista tipo calendario para estados de asistencia.
- Registro de asistencia con estados:
  - `P`: Temprano
  - `T`: Tarde
  - `F`: Inasistencia
- Base de datos SQL Server con tablas normalizadas, auditoria y vigencia logica.
- CSS principal separado de responsive.
- Variables de colores para cambiar facilmente el estilo.

## Estructura del proyecto

```text
Proyecto asistencia/
├── config/
│   ├── app.php
│   └── database.php
├── database/
│   └── senati_asistencia.sql
├── includes/
│   ├── auth.php
│   ├── footer.php
│   ├── functions.php
│   ├── header.php
│   ├── sidebar.php
│   └── topbar.php
├── public/
│   ├── actions/
│   │   └── guardar_asistencia.php
│   ├── admin/
│   │   ├── consulta_asistencia.php
│   │   ├── dashboard.php
│   │   ├── estudiantes.php
│   │   └── registrar_asistencia.php
│   ├── alumno/
│   │   ├── dashboard.php
│   │   └── mi_asistencia.php
│   ├── assets/
│   │   ├── css/
│   │   │   ├── main.css
│   │   │   └── responsive.css
│   │   └── js/
│   │       └── app.js
│   ├── index.php
│   └── logout.php
└── README.md
```

## Configuracion de PHP

Edita el archivo `config/database.php`:

```php
$dbServer = 'localhost';
$dbName = 'BD_SENATI_ASISTENCIA';
$dbUser = 'sa';
$dbPassword = 'TU_CLAVE_SQL_SERVER';
```

Debes tener instalado y habilitado el driver de SQL Server para PHP:

- `pdo_sqlsrv`
- `sqlsrv`

Si usas XAMPP, revisa que tu version de PHP sea compatible con los drivers de Microsoft para SQL Server.

## Como crear usuario admin

USE BD_SENATI_ASISTENCIA;
GO

INSERT INTO tb_roles (nombre)
SELECT 'Administrador'
WHERE NOT EXISTS (
    SELECT 1 FROM tb_roles WHERE nombre = 'Administrador'
);
GO

INSERT INTO tb_usuarios (
    id_rol,
    usuario,
    clave_hash,
    nombres,
    apellidos,
    correo,
    vigencia
)
VALUES (
    (SELECT id_rol FROM tb_roles WHERE nombre = 'Administrador'),     <---------Dejar por defecto
    'admin',                                                          <----- Cambiar a un nombre para iniciar sesion
    CONVERT(CHAR(64), HASHBYTES('SHA2_256', 'Admin12345'), 2),        <------ Poner una clave
    'Administrador',                                                  <------- Nombres
    'SENATI',                                                         <------- Apellidos
    'admin@senati.edu.pe',                                            <-------- Correo institucional o de uso personal
    1                                                                 <-------- Vigencia 1 "activo" y 0 "inactivo"
);
GO

## Archivos principales

- `public/index.php`: inicio de sesion.
- `public/admin/dashboard.php`: panel del administrador.
- `public/admin/consulta_asistencia.php`: consulta por codigo o nombre.
- `public/admin/registrar_asistencia.php`: formulario para registrar asistencia.
- `public/alumno/dashboard.php`: panel del alumno.
- `public/alumno/mi_asistencia.php`: asistencia individual del alumno.
- `public/assets/css/main.css`: estilos principales.
- `public/assets/css/responsive.css`: estilos para tablet y celular.
- `public/assets/js/app.js`: interacciones suaves.
