# Sistema de Control de Acceso de Empleados

Sistema web para la gestión de empleados y el control de asistencia mediante el registro de entradas y salidas. Desarrollado como proyecto académico utilizando PHP, MySQL, HTML5, CSS, Bootstrap 5 y JavaScript.

## Tabla de contenidos

- [Descripción del proyecto](#descripción-del-proyecto)
- [Objetivos](#objetivos)
- [Tecnologías utilizadas](#tecnologías-utilizadas)
- [Funcionalidades](#funcionalidades)
- [Requisitos del sistema](#requisitos-del-sistema)
- [Estructura del proyecto](#estructura-del-proyecto)
- [Instalación y configuración](#instalación-y-configuración)
- [Configuración de la base de datos](#configuración-de-la-base-de-datos)
- [Acceso al sistema](#acceso-al-sistema)
- [Manual de usuario](#manual-de-usuario)
- [Seguridad](#seguridad)
- [Reglas de funcionamiento](#reglas-de-funcionamiento)
- [Pruebas recomendadas](#pruebas-recomendadas)
- [Solución de problemas](#solución-de-problemas)
- [Autoría y licencia](#autoría-y-licencia)

## Descripción del proyecto

El Sistema de Control de Acceso de Empleados es una aplicación web que permite administrar la información de los empleados de una organización y registrar sus horarios de entrada y salida.

El sistema cuenta con una interfaz pública para que los empleados registren su asistencia mediante su número de cédula y un código de acceso individual. Además, dispone de un panel administrativo protegido para gestionar empleados, administrar usuarios, consultar el historial de asistencia y generar reportes descargables.

La aplicación utiliza una base de datos relacional para almacenar la información de los usuarios administrativos, los empleados y sus registros de asistencia.

El proyecto está diseñado para ejecutarse localmente mediante XAMPP y puede instalarse en otros equipos utilizando el código fuente y el archivo SQL de la base de datos.

## Objetivos

### Objetivo general

Desarrollar un sistema web que permita gestionar empleados y controlar sus registros de asistencia de manera organizada, segura y eficiente.

### Objetivos específicos

- Implementar un sistema de autenticación para los usuarios administrativos.
- Permitir el registro, consulta, modificación y control del estado de los empleados.
- Generar automáticamente un código de acceso único para cada empleado.
- Registrar las horas de entrada y salida mediante la validación de la identidad del empleado.
- Consultar y filtrar el historial de asistencia.
- Calcular el tiempo trabajado a partir de las horas registradas.
- Generar reportes de asistencia exportables a formato CSV para su utilización en Excel.
- Proteger las operaciones administrativas y validar los datos enviados por los usuarios.

## Tecnologías utilizadas

| Tecnología | Utilización |
|---|---|
| PHP | Lógica del servidor y procesamiento de solicitudes. |
| MySQL/MariaDB | Almacenamiento de usuarios, empleados y asistencias. |
| PDO | Conexión a la base de datos y consultas preparadas. |
| HTML5 | Estructura de las páginas. |
| CSS3 | Presentación y estilos de la interfaz. |
| Bootstrap 5 | Diseño adaptable y componentes visuales. |
| JavaScript | Interacciones y confirmaciones en la interfaz. |
| PHP Sessions | Gestión de las sesiones de los administradores. |
| Git y GitHub | Control de versiones y almacenamiento del código fuente. |
| XAMPP | Entorno de desarrollo local con Apache, PHP y MySQL/MariaDB. |

No se utilizan frameworks PHP como Laravel o Symfony.

## Funcionalidades

### 1. Autenticación de administradores

- Inicio de sesión mediante nombre de usuario y contraseña.
- Validación de credenciales contra la base de datos.
- Verificación del estado de la cuenta antes de permitir el acceso.
- Protección de las páginas administrativas mediante sesiones.
- Cierre de sesión mediante una solicitud POST protegida.

### 2. Creación del primer administrador

- El formulario de creación inicial está disponible únicamente cuando no existen registros en la tabla de usuarios administrativos.
- Desde el inicio de sesión se muestra la opción de crear el primer administrador cuando corresponde.
- Se validan los datos y se almacena la contraseña mediante un hash seguro.
- Una vez creado el primer administrador, el sistema redirige al inicio de sesión.
- Si ya existe algún administrador, la página de creación inicial bloquea el acceso, incluso cuando se intenta abrir directamente mediante su URL.

### 3. Gestión de empleados

Los administradores pueden:

- Registrar nuevos empleados.
- Consultar el listado de empleados.
- Editar la cédula, el nombre, el correo electrónico y el teléfono.
- Activar o desactivar empleados.
- Consultar el código de acceso asignado a cada empleado.
- Ver la fecha de registro.

Cada empleado recibe automáticamente un código de acceso numérico de seis dígitos. El sistema verifica que el código no esté asignado a otro empleado.

Los empleados inactivos no pueden registrar nuevas asistencias.

### 4. Registro de entradas y salidas

La pantalla pública permite que los empleados registren su asistencia utilizando:

- Número de cédula.
- Código de acceso individual.

El sistema valida que ambos datos correspondan a un empleado activo.

El primer registro del día guarda la hora de entrada. Una segunda marcación válida registra la hora de salida.

Si el empleado ya registró ambas marcaciones, el sistema impide realizar registros adicionales para esa misma fecha.

### 5. Panel administrativo

El panel principal presenta estadísticas de asistencia, entre ellas:

- Total de empleados.
- Cantidad de empleados activos e inactivos.
- Empleados que registraron entrada durante el día.
- Empleados que registraron entrada, pero todavía no salida.
- Cantidad de registros del día.
- Empleados activos que todavía no han marcado asistencia.
- Listado de las últimas asistencias registradas durante el día.

También proporciona accesos a los módulos de gestión de empleados, historial de asistencia, administración de usuarios y pantalla pública de marcación.

### 6. Historial de asistencia

El módulo de historial permite consultar los registros almacenados en la base de datos.

Incluye información como:

- Fecha de asistencia.
- Cédula del empleado.
- Nombre del empleado.
- Hora de entrada.
- Hora de salida.
- Tiempo trabajado cuando existen ambas marcaciones.

Permite filtrar los resultados por nombre o cédula y por un rango de fechas.

### 7. Exportación de reportes

El historial de asistencia puede exportarse a un archivo CSV compatible con Excel.

El reporte contiene:

- Fecha.
- Cédula.
- Nombre del empleado.
- Hora de entrada.
- Hora de salida.
- Horas trabajadas.

La exportación respeta los filtros aplicados en el historial. Si no existe una hora de salida, el registro se identifica como pendiente.

### 8. Administración de usuarios

El módulo administrativo de usuarios permite:

- Registrar nuevas cuentas administrativas.
- Consultar los administradores registrados.
- Editar nombres y nombres de usuario.
- Cambiar contraseñas.
- Activar y desactivar cuentas.
- Eliminar cuentas administrativas.

Se impide desactivar o eliminar la propia cuenta desde el módulo. También se impide desactivar o eliminar al último administrador activo.

## Requisitos del sistema

Para ejecutar el proyecto se necesita:

- Windows, Linux o un sistema operativo compatible con XAMPP.
- XAMPP con Apache, PHP y MySQL/MariaDB.
- PHP con las extensiones PDO y PDO MySQL habilitadas.
- Un navegador web actualizado.
- Un editor de código como Visual Studio Code, opcional para la ejecución.
- Git, opcional para clonar el repositorio.
- Conexión a Internet para cargar Bootstrap desde su CDN, si no se utiliza una copia local.

## Estructura del proyecto

La estructura principal del proyecto es la siguiente:

```text
Sistema-de-Control-de-Acceso-de-Empleados/
│
├── admin/
│   ├── index.php
│   ├── empleados.php
│   ├── empleado-guardar.php
│   ├── empleado-editar.php
│   ├── empleado-estado.php
│   ├── asistencias.php
│   ├── asistencias-exportar.php
│   └── usuarios.php
│
├── assets/
│   └── css/
│       └── estilos.css
│
├── config/
│   ├── app.php
│   └── conexion.php
│
├── database/
│   └── control_asistencia.sql
│
├── includes/
│   └── funciones.php
│
├── index.php
├── login.php
├── logout.php
├── marcar-asistencia.php
├── crear-admin-inicial.php
└── README.md
```

**Nota:** la estructura puede variar si se agregan archivos auxiliares durante el desarrollo. Deben conservarse los nombres y las rutas reales de los archivos existentes en el repositorio.

### Descripción de los principales archivos

| Archivo | Función |
|---|---|
| `index.php` | Pantalla pública para registrar asistencia. |
| `login.php` | Formulario de inicio de sesión administrativo. |
| `logout.php` | Cierre seguro de sesión. |
| `crear-admin-inicial.php` | Registro protegido del primer administrador. |
| `marcar-asistencia.php` | Validación y procesamiento de entradas y salidas. |
| `config/app.php` | Configuración general, URL base y zona horaria. |
| `config/conexion.php` | Conexión a la base de datos mediante PDO. |
| `includes/funciones.php` | Funciones comunes para sesiones, CSRF y protección de datos. |
| `admin/index.php` | Panel principal y estadísticas. |
| `admin/empleados.php` | Listado y gestión de empleados. |
| `admin/empleado-guardar.php` | Registro de nuevos empleados. |
| `admin/empleado-editar.php` | Modificación de empleados. |
| `admin/empleado-estado.php` | Activación y desactivación de empleados. |
| `admin/asistencias.php` | Historial y filtros de asistencia. |
| `admin/asistencias-exportar.php` | Exportación de reportes CSV. |
| `admin/usuarios.php` | Gestión de cuentas administrativas. |
| `database/control_asistencia.sql` | Script SQL para crear la base de datos y sus tablas. |

## Instalación y configuración

### Paso 1. Instalar XAMPP

Descargar e instalar XAMPP desde su sitio oficial:

https://www.apachefriends.org/

Iniciar los servicios de Apache y MySQL desde el panel de control de XAMPP.

Si Apache utiliza un puerto diferente al 80, se deberá utilizar el puerto configurado en la URL del navegador.

### Paso 2. Obtener el código fuente

Clonar el repositorio público de GitHub o descargarlo como archivo ZIP.

Para clonar el proyecto mediante Git:

```bash
git clone URL_DEL_REPOSITORIO
```

Sustituir `URL_DEL_REPOSITORIO` por la dirección real del repositorio.

Colocar la carpeta del proyecto dentro del directorio `htdocs` de XAMPP.

La ruta de ejemplo utilizada durante el desarrollo es:

```text
C:\xampp\htdocs\Sistema-de-Control-de-Acceso-de-Empleados
```

Si XAMPP está instalado en otra ubicación, se debe utilizar su directorio `htdocs` correspondiente.

### Paso 3. Crear la base de datos

1. Iniciar MySQL en XAMPP.
2. Abrir phpMyAdmin en el navegador:

   http://localhost/phpmyadmin/

3. Seleccionar la opción **Importar**.
4. Seleccionar el archivo `database/control_asistencia.sql`.
5. Ejecutar la importación.
6. Verificar que se haya creado la base de datos `control_asistencia`.

El archivo SQL debe incluir la creación de las tablas y sus relaciones. Si el archivo no crea automáticamente la base de datos, crear primero una base de datos llamada `control_asistencia` y después importar el contenido del script.

### Paso 4. Configurar la conexión

Abrir:

```text
config/conexion.php
```

La configuración de desarrollo utiliza los siguientes valores:

- Servidor: `localhost`
- Base de datos: `control_asistencia`
- Usuario: `root`
- Contraseña: vacía, si así está configurado XAMPP localmente.

La conexión debe utilizar PDO con el controlador MySQL.

Si el servidor utiliza otra contraseña, otro puerto o credenciales diferentes, actualizar el archivo de conexión de acuerdo con la configuración local.

**Importante:** no utilizar estas credenciales de desarrollo en un servidor de producción.

### Paso 5. Configurar la URL base

Abrir:

```text
config/app.php
```

Verificar que la URL base corresponda al nombre real de la carpeta del proyecto:

```php
<?php

define(
    'BASE_URL',
    '/Sistema-de-Control-de-Acceso-de-Empleados'
);

date_default_timezone_set('America/Costa_Rica');
```

Si se cambia el nombre de la carpeta, actualizar `BASE_URL` para que los enlaces y las redirecciones funcionen correctamente.

### Paso 6. Abrir la aplicación

Si Apache utiliza el puerto predeterminado:

**Pantalla pública de asistencia:**

http://localhost/Sistema-de-Control-de-Acceso-de-Empleados/

**Inicio de sesión administrativo:**

http://localhost/Sistema-de-Control-de-Acceso-de-Empleados/login.php

Si Apache está configurado en el puerto 8080, utilizar `http://localhost:8080/` seguido de la ruta correspondiente.

### Paso 7. Crear el primer administrador

Si la tabla `usuarios` no contiene registros:

1. Abrir el inicio de sesión.
2. Seleccionar **Crear primer administrador**.
3. Introducir el nombre completo, el nombre de usuario y una contraseña de al menos ocho caracteres.
4. Confirmar la contraseña.
5. Crear la cuenta.
6. Iniciar sesión con las credenciales recién registradas.

Una vez creado el primer administrador, la opción de registro inicial deja de estar disponible.

No es necesario insertar manualmente un usuario en phpMyAdmin.

### Paso 8. Registrar empleados

Después de iniciar sesión:

1. Entrar al módulo de empleados.
2. Registrar la cédula y los datos del empleado.
3. Guardar el registro.
4. Consultar el código de acceso generado automáticamente.
5. Facilitar el código al empleado de manera segura.

El empleado utilizará su cédula y código de acceso en la pantalla pública.

## Configuración de la base de datos

La base de datos utilizada por el sistema se llama `control_asistencia`.

### Tabla `usuarios`

Almacena las cuentas administrativas.

| Campo | Descripción |
|---|---|
| `id` | Identificador único del administrador. |
| `nombre` | Nombre completo. |
| `usuario` | Nombre de usuario único. |
| `password` | Hash de la contraseña. |
| `estado` | Estado de la cuenta: Activo o Inactivo. |
| `fecha_creacion` | Fecha y hora de creación. |

### Tabla `empleados`

Almacena la información de los empleados.

| Campo | Descripción |
|---|---|
| `id` | Identificador único del empleado. |
| `cedula` | Identificación única del empleado. |
| `nombre` | Nombre completo. |
| `correo` | Correo electrónico, opcional. |
| `telefono` | Número de teléfono, opcional. |
| `codigo_acceso` | Código numérico único de seis dígitos. |
| `estado` | Estado del empleado: Activo o Inactivo. |
| `fecha_registro` | Fecha y hora de registro. |

### Tabla `asistencias`

Almacena las marcaciones diarias.

| Campo | Descripción |
|---|---|
| `id` | Identificador único de la asistencia. |
| `empleado_id` | Referencia al empleado correspondiente. |
| `fecha` | Fecha de la asistencia. |
| `hora_entrada` | Hora de entrada registrada. |
| `hora_salida` | Hora de salida, inicialmente nula. |
| `fecha_creacion` | Fecha y hora de creación del registro. |

La tabla de asistencias utiliza una clave foránea para relacionar cada registro con un empleado y una restricción única para evitar más de un registro por empleado y fecha.

## Manual de usuario

### Administrador

1. Iniciar sesión con las credenciales registradas.
2. Consultar las estadísticas del panel principal.
3. Administrar los empleados desde el módulo correspondiente.
4. Crear y gestionar cuentas administrativas.
5. Consultar el historial de asistencia.
6. Aplicar filtros por empleado y fecha.
7. Exportar los resultados a CSV.
8. Cerrar sesión al finalizar.

### Empleado

1. Acceder a la pantalla pública de asistencia.
2. Introducir la cédula y el código de acceso.
3. Registrar la entrada al iniciar la jornada.
4. Volver a introducir sus datos para registrar la salida.
5. Verificar el mensaje mostrado por el sistema.

El empleado no necesita una cuenta administrativa ni una contraseña convencional; utiliza su cédula y código de acceso.

## Seguridad

El sistema incorpora medidas para reducir accesos no autorizados y errores de procesamiento.

- **Contraseñas protegidas:** las contraseñas administrativas se almacenan mediante `password_hash()` y se verifican con `password_verify()`.
- **Consultas preparadas:** se utiliza PDO con consultas preparadas para las operaciones de base de datos.
- **Control de sesiones:** las páginas administrativas requieren una sesión autenticada.
- **Protección CSRF:** los formularios de operaciones sensibles validan un token de seguridad.
- **Validación de entradas:** se verifican campos obligatorios, formatos y longitudes.
- **Escape de salida HTML:** se utiliza una función de escape para reducir el riesgo de inyección de HTML o JavaScript.
- **Control de estado:** las cuentas administrativas inactivas no deben poder iniciar sesión y los empleados inactivos no pueden registrar asistencia.
- **Protección de la creación inicial:** el registro del primer administrador queda bloqueado cuando ya existe una cuenta.
- **Protección de cuentas administrativas:** no se permite eliminar o desactivar la propia cuenta desde el módulo de administración ni dejar el sistema sin un administrador activo mediante esas operaciones.
- **Control de marcaciones:** se impiden registros adicionales cuando el empleado ya tiene entrada y salida para la fecha correspondiente.
- **Validación de códigos:** cada código de acceso debe ser único.

Estas medidas forman parte de la implementación de la aplicación y no sustituyen una auditoría de seguridad independiente.

## Reglas de funcionamiento

- Cada empleado debe tener una cédula única.
- Cada empleado debe tener un código de acceso único de seis dígitos.
- Solamente los empleados activos pueden registrar asistencia.
- El primer registro del día corresponde a la entrada.
- El segundo registro del día corresponde a la salida.
- No se permiten más de dos marcaciones por empleado y fecha.
- Los registros de asistencia se relacionan con empleados existentes mediante una clave foránea.
- Las horas trabajadas se calculan cuando existen entrada y salida.
- Las asistencias sin salida aparecen como pendientes.
- El sistema utiliza la zona horaria `America/Costa_Rica`.
- La implementación actual contempla jornadas que comienzan y terminan en la misma fecha. Los turnos nocturnos que terminan al día siguiente requieren una adaptación adicional del cálculo de horas.
- El código de acceso funciona como un mecanismo sencillo de identificación y no equivale a un sistema de autenticación de alta seguridad.

## Pruebas recomendadas

Antes de presentar el proyecto, verificar los siguientes casos:

### Inicio de sesión

- Iniciar sesión con credenciales correctas.
- Intentar ingresar con una contraseña incorrecta.
- Intentar ingresar con una cuenta inactiva.
- Abrir una página administrativa sin iniciar sesión.
- Cerrar sesión y comprobar que las páginas protegidas soliciten autenticación.

### Registro inicial y gestión administrativa

- Crear el primer administrador cuando no existe ninguno.
- Comprobar que la creación inicial queda bloqueada después del registro.
- Registrar un segundo administrador.
- Editar los datos de un administrador.
- Cambiar su contraseña.
- Activar y desactivar cuentas.
- Eliminar un administrador secundario.
- Comprobar que no se pueda desactivar o eliminar la propia cuenta ni el último administrador activo.

### Gestión de empleados

- Registrar un empleado válido.
- Intentar registrar una cédula duplicada.
- Editar la información de un empleado.
- Desactivar un empleado.
- Comprobar que un empleado inactivo no pueda marcar asistencia.
- Verificar que los códigos de acceso sean únicos.

### Asistencia

- Registrar una entrada.
- Registrar la salida correspondiente.
- Comprobar que se calcule el tiempo trabajado.
- Intentar registrar una tercera marcación el mismo día.
- Intentar marcar con una cédula o un código incorrectos.
- Consultar el historial con diferentes filtros.
- Verificar que una entrada sin salida aparezca como pendiente.

### Reportes y persistencia

- Exportar el historial a CSV.
- Abrir el archivo en Excel y revisar tildes, fechas y horas.
- Reiniciar Apache y MySQL y comprobar que los datos persistan.
- Importar la base de datos en otra computadora y verificar la instalación.

## Solución de problemas

### La página no abre

- Verificar que Apache esté iniciado en XAMPP.
- Comprobar el nombre de la carpeta dentro de `htdocs`.
- Revisar el puerto utilizado por Apache.
- Confirmar que la URL coincida con la carpeta del proyecto.

### Error de conexión a la base de datos

- Comprobar que MySQL esté iniciado.
- Verificar el nombre de la base de datos.
- Revisar el usuario y la contraseña en `config/conexion.php`.
- Confirmar que el archivo SQL se haya importado correctamente.
- Verificar que PDO MySQL esté habilitado en PHP.

### Los enlaces o las redirecciones no funcionan

- Revisar el valor de `BASE_URL` en `config/app.php`.
- Comprobar que coincida con el nombre de la carpeta del proyecto.
- Verificar que se utilice el puerto correcto en el navegador.

### No aparece la opción de crear el primer administrador

La opción solo se muestra cuando no hay registros en la tabla `usuarios`. Si ya existe una cuenta administrativa, este comportamiento es intencional.

### El empleado no puede marcar asistencia

- Comprobar que la cédula y el código sean correctos.
- Verificar que el empleado esté activo.
- Confirmar que no haya completado ya la entrada y salida del día.

### Excel no separa las columnas del CSV

El archivo utiliza el punto y coma como delimitador. Si Excel no separa automáticamente las columnas, importar el archivo mediante la opción de datos desde texto o CSV y seleccionar el delimitador correspondiente.

## Autoría y licencia

Proyecto académico desarrollado con fines educativos para la gestión de empleados y el control de asistencia.

**Autor:** Jordan Rodriguez Murillo

**Repositorio:** agregar aquí la URL real del repositorio público de GitHub.

La utilización, modificación o distribución del código debe ajustarse a las condiciones establecidas por el autor y por la institución académica correspondiente.
