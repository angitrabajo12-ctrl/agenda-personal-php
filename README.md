# Agenda personal de contactos

Aplicación web desarrollada en PHP y MySQL que permite registrar usuarios, iniciar y cerrar sesión y administrar una agenda privada de contactos.

Cada usuario puede crear, consultar, editar y eliminar únicamente sus propios contactos. Antes de eliminar un contacto, el sistema solicita confirmación.

## URL pública

[Acceder a la agenda personal](https://agendapersonal.infinityfree.me/login.php)

## Funcionalidades

- Registro de usuarios con correo electrónico único.
- Inicio y cierre de sesión.
- Panel privado con los datos del usuario autenticado.
- Creación, consulta, edición y eliminación de contactos.
- Búsqueda de contactos por nombre, correo o ciudad.
- Ordenamiento por nombre, ciudad y fecha de creación.
- Clasificación de contactos por categoría.
- Marcación de contactos favoritos y filtro para visualizarlos.

## Mejoras respecto a la versión anterior

### 1. Búsqueda de contactos

Se incorporó un buscador que permite localizar contactos por nombre, correo electrónico o ciudad. Los resultados corresponden exclusivamente al usuario autenticado.

### 2. Ordenamiento

Se agregaron opciones para ordenar los contactos por nombre, ciudad y fecha de creación, incluyendo los más recientes y los más antiguos.

### 3. Categorías

Se creó la tabla `categorias`, relacionada con la tabla `contactos`. Cada contacto pertenece a una de las siguientes categorías:

- Familia.
- Amigos.
- Trabajo.
- Universidad.
- Otro.

### 4. Contactos favoritos

Se incorporó el campo `favorito`, que permite marcar o desmarcar contactos y mostrar únicamente los favoritos.

### 5. Validaciones adicionales

Se reforzó la validación del teléfono para exigir al menos seis dígitos y rechazar entradas formadas únicamente por símbolos.

También se verifican desde PHP las longitudes máximas del nombre, teléfono, correo y ciudad.

## Capturas de pantalla
### Inicio de sesión
<img width="1365" height="654" alt="Captura de pantalla 2026-11-21 000134" src="https://github.com/user-attachments/assets/82812138-0981-4f20-96d4-d4fb94f1a2b6" />
### Registro de usuarios
<img width="1365" height="663" alt="Captura de pantalla 2026-11-21 000145" src="https://github.com/user-attachments/assets/effbcfa9-027e-42f2-85df-a32d17a40319" />
### Panel principal, búsqueda y ordenamiento
<img width="1365" height="628" alt="Captura de pantalla 2026-11-21 000258" src="https://github.com/user-attachments/assets/9877ae5c-fdcf-40fd-a078-e81840637a28" />
### Creación o edición con categorías y favoritos
<img width="1365" height="575" alt="image" src="https://github.com/user-attachments/assets/4e5c2d84-15b5-42c9-92c8-93ff7b26f496" />

<img width="1037" height="617" alt="Captura de pantalla 2026-11-21 011237" src="https://github.com/user-attachments/assets/9784ad65-2ab9-4917-ba12-1a4497f76bcd" />

<img width="1348" height="588" alt="Captura de pantalla 2026-11-21 011224" src="https://github.com/user-attachments/assets/9c67c623-e888-418c-8bd8-1b3f638cf712" />

<img width="1365" height="594" alt="Captura de pantalla 2026-11-21 011353" src="https://github.com/user-attachments/assets/8dc5d24f-a83a-4cc8-82a8-669cae59cd4e" />

### Filtro de favoritos
<img width="1358" height="680" alt="Captura de pantalla 2026-11-21 011120" src="https://github.com/user-attachments/assets/39ce51fb-2e68-4501-85a6-5f90a71e8d83" />

### Confirmación de eliminación

<img width="1365" height="575" alt="Captura de pantalla 2026-11-21 011432" src="https://github.com/user-attachments/assets/3158cd12-0fa7-4af9-9a4d-ffda9a6ad5bb" />


## Tecnologías utilizadas

- PHP.
- MySQL o MariaDB.
- PDO y consultas preparadas.
- HTML5.
- CSS3.
- Apache.
- XAMPP para la ejecución local.
- InfinityFree para el alojamiento web.
- GitHub para almacenar el código y la documentación.

## Requisitos para ejecutar el proyecto localmente

- PHP 8.1 o superior.
- Extensión PDO MySQL habilitada.
- MySQL o MariaDB.
- Servidor Apache.
- Navegador web.
- XAMPP como entorno local recomendado.

## Instalación local

### 1. Descargar el proyecto

Descargar el repositorio desde GitHub y descomprimirlo.

Crear una carpeta llamada `agenda` dentro de `htdocs` de la instalación de XAMPP.

Ejemplo:

    C:\xampp_nuevo\htdocs\agenda

Copiar los archivos del proyecto dentro de esa carpeta.

### 2. Iniciar los servicios

Abrir el panel de XAMPP e iniciar Apache y MySQL.

### 3. Preparar la base de datos

Acceder a:

http://localhost/phpmyadmin

Para una instalación nueva:

1. Crear una base de datos llamada `agenda_personal`, con cotejamiento `utf8mb4_unicode_ci`.
2. Seleccionar esa base de datos.
3. Entrar en la pestaña **Importar**.
4. Seleccionar el archivo `sql/agenda.sql`.
5. Ejecutar la importación.

El script crea las tablas:

- `usuarios`.
- `categorias`.
- `contactos`.

Además, incorpora las cinco categorías iniciales.

Para actualizar la versión anterior:

1. Exportar una copia de seguridad de la base de datos existente.
2. Seleccionar `agenda_personal` en phpMyAdmin.
3. Importar `sql/actualizar_v1_a_v2.sql`.

Este script agrega las categorías, el campo de favorito y la fecha de creación conservando los usuarios y contactos existentes. Los contactos anteriores reciben inicialmente la categoría `Otro`.

### 4. Configurar la conexión

Copiar `config.example.php` y guardar la copia con el nombre `config.local.php`, en la carpeta principal del proyecto.

Configurar los datos de conexión:

    <?php

    return [
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'agenda_personal',
        'user' => 'root',
        'password' => '',
    ];

Estos valores corresponden a una configuración local habitual. Deben ajustarse si MySQL utiliza otro puerto, usuario o contraseña.

El archivo `conexion.php` utiliza esta configuración para establecer la conexión mediante PDO.

### 5. Abrir la aplicación

Acceder a:

http://localhost/agenda/registro.php

Registrar una cuenta e iniciar sesión en:

http://localhost/agenda/login.php

### 6. Probar las funcionalidades

- Crear, editar y eliminar contactos.
- Buscar por nombre, correo y ciudad.
- Cambiar el orden de la lista.
- Seleccionar categorías.
- Marcar contactos como favoritos.
- Activar el filtro de favoritos.
- Cerrar sesión.
- Ingresar con otra cuenta para comprobar que los contactos sean privados.

## Despliegue en el hosting

1. Crear una base de datos MySQL desde el panel del hosting.
2. Abrir su phpMyAdmin y seleccionar la base creada.
3. Importar `sql/agenda.sql` si la base está vacía.
4. Subir los archivos PHP, `estilos.css` y `.htaccess` a la carpeta pública `htdocs`.
5. Configurar `config.local.php` con el servidor, nombre de base de datos, usuario y contraseña proporcionados por el hosting.
6. Acceder a la URL pública y comprobar las funcionalidades.

El archivo SQL se importa en phpMyAdmin y no debe almacenarse en la carpeta pública.

## Seguridad implementada

- Protección de contraseñas mediante `password_hash()`.
- Comprobación de contraseñas mediante `password_verify()`.
- Control de acceso mediante sesiones.
- Regeneración del identificador de sesión al iniciar sesión.
- Consultas preparadas con PDO.
- Validación de formularios en el servidor.
- Protección de formularios mediante tokens CSRF.
- Escape de datos al mostrarlos en HTML.
- Comprobación del propietario al consultar, editar, eliminar o marcar un contacto como favorito.
- Restricción de las consultas mediante `id_usuario`.
- Eliminación mediante POST y confirmación previa.
- Lista de opciones permitidas para el ordenamiento.
- Mensajes de error que evitan mostrar información sensible.
- Protección de archivos internos mediante `.htaccess`.

## Archivos de configuración privados

`config.local.php` contiene las credenciales de la base de datos y no debe publicarse en GitHub.

El repositorio incluye `config.example.php` como plantilla de configuración.

## Autora

Angi Gissell Rodríguez Duarte.

Proyecto académico de la carrera de Tecnologías de la Información de la Universidad Técnica Particular de Loja.
