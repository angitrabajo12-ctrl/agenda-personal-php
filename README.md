# Agenda personal de contactos

## Descripción del proyecto

Aplicación web que permite registrar usuarios, iniciar y cerrar
sesión y administrar una agenda personal de contactos.

Cada usuario puede crear, consultar, editar y eliminar únicamente
sus propios contactos. La eliminación solicita confirmación.

## Tecnologías utilizadas

- PHP.
- MySQL o MariaDB.
- HTML5.
- CSS3.
- PDO para la conexión y las consultas a la base de datos.
- Apache mediante XAMPP.

## Requisitos de ejecución

- PHP 8.0 o superior.
- Extensión PDO MySQL habilitada.
- MySQL o MariaDB.
- Servidor Apache.
- Navegador web.
- XAMPP como entorno de ejecución local.

## Pasos para instalar la aplicación

1. Descargar el repositorio y descomprimirlo.
2. Crear una carpeta llamada agenda dentro de htdocs,
   en la ubicación donde se instaló XAMPP.
3. Copiar en esa carpeta todos los archivos PHP y CSS.
4. Abrir el panel de XAMPP e iniciar Apache y MySQL.
5. Acceder a http://localhost/phpmyadmin.
6. Importar el archivo con extensión .sql incluido en el
   repositorio. Este crea la base agenda_personal y las
   tablas usuarios y contactos.
7. Abrir conexion.php y ajustar el servidor, puerto, usuario
   y contraseña según la configuración local de MySQL.
8. Acceder a http://localhost/agenda/registro.php.
9. Registrar una cuenta e iniciar sesión.
10. Crear contactos y probar su edición y eliminación.

## Seguridad implementada

- Contraseñas protegidas mediante password_hash().
- Verificación de contraseñas mediante password_verify().
- Control de acceso mediante sesiones.
- Regeneración del identificador de sesión al iniciar sesión.
- Consultas preparadas con PDO.
- Validación de formularios en el servidor.
- Protección de formularios mediante tokens CSRF.
- Escape de datos al mostrarlos en HTML.
- Comprobación del propietario al consultar, editar o
  eliminar un contacto.
