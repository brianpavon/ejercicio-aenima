# ejercicio-aenima
Ejercicio backen para Aenima

1)Ejecutar composer install dentro del directorio api, asi se pueden instalar las dependencias.

2)Como servidor local uso xampp.

3)Utilice el framework Slim, para armar las rutas de los endpoint.

4)Empleé el ORM de Laravel, Eloquent, para todo lo referido a base de datos.

5)En la carpeta de Postman están las variables del entorno que utilicé y además la colección con las urls.

Si bien no lo solicitaba el ejercicio, simule que hay un usuario administrador que realiza los ABM, y un usuario
que mediante un token, le limito el acceso a ciertos endpoints.

Credenciales:

email: admin@admin.com

password: admin

email: user@user.com

password: user

Los token de acceso los pueden encontrar en las variables del entorno de postman, de todos modos utilizando el endpoint
de login, devuelve el token de acceso.

Primero hay que registrar un nuevo usuario y luego loguearse.