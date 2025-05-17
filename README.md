
Este proyecto implementa una funcionalidad para registrar y visualizar un inventario de elementos tecnológicos, asociándolos a personas responsables. El sistema permite el registro de dos maneras: carga automática desde un archivo CSV y registro manual a través de un formulario web.

## Estructura de la Base de Datos

El proyecto utiliza una base de datos relacional (PostgreSQL o MySQL, según la configuración) con las siguientes tablas:

### `inventarios`

| Campo         | Tipo de dato   | Descripción                                  |
|---------------|----------------|----------------------------------------------|
| `id_inventario` | INT (PK)       | Identificador único del elemento             |
| `marca`       | VARCHAR(50)    | Marca del dispositivo (HP, Dell, Lenovo...)    |
| `modelo`      | VARCHAR(50)    | Modelo del dispositivo                       |
| `serial`      | VARCHAR(50)    | Número de serie único                        |
| `categoria`   | VARCHAR(50)    | Tipo de dispositivo (Portátil, Impresora...) |
| `estado`      | VARCHAR(50)    | Estado actual del dispositivo (Operativo...)  |
| `id_persona`  | INT (FK)       | ID de la persona responsable del dispositivo |

### `usuarios`

*(Esta tabla ya existe en el proyecto y contiene información sobre las personas/usuarios del sistema).*

## Funcionalidades

1.  **Registro mediante archivo CSV:**
    * Permite cargar un archivo `.csv` con la información de múltiples elementos del inventario.
    * El archivo CSV debe tener las siguientes columnas en orden: `id_inventario`, `marca`, `modelo`, `serial`, `categoria`, `estado`, `id_persona`.

2.  **Registro manual:**
    * A través de un formulario web, los digitadores pueden registrar elementos del inventario uno por uno.
    * El formulario incluye campos para Marca, Modelo, Serial, Categoría, Estado y Persona responsable (seleccionable de una lista desplegable).

3.  **Visualización del Inventario:**
    * **Para Superusuarios:** Pueden ver una lista de los elementos del inventario que les han sido asignados (basado en su `id`).
    * **Para Digitadores:** Pueden ver toda la información del inventario, incluyendo el responsable de cada elemento.

## Archivos del Proyecto

* `config.php`: Contiene la configuración de la conexión a la base de datos.
* `php/reg_personas.php`: (Mencionado en el contexto, aunque no se proporcionó el código) Posiblemente para registrar nuevas personas/usuarios.
* `php/registrar_inventario.php`: Contiene la lógica para el registro manual y/o la carga desde CSV del inventario.
* `ver_mi_inventario.php`: Muestra los elementos del inventario asignados al usuario logueado (para superusuarios).
* `ver_inventario_completo.php`: Muestra toda la información del inventario (para digitadores).
* `css/style.css`: Archivo de estilos CSS.
* `php/logout.php`: Script para cerrar la sesión del usuario.
* `php/actualizar_contrasena.php`: (Mencionado en el contexto) Posiblemente para que los usuarios cambien su contraseña.
* `index.php`: Página de inicio de sesión.

## Requisitos

* Un servidor web con soporte para PHP.
* Una base de datos PostgreSQL o MySQL configurada.
* Acceso a la base de datos con las credenciales configuradas en `config.php`.

## Instalación

1.  Clona o descarga este repositorio en el directorio raíz de tu servidor web.
2.  Importa las definiciones de las tablas (`inventarios` y `usuarios`) a tu base de datos.
3.  Configura los detalles de la conexión a la base de datos en el archivo `config.php`.
4.  Asegúrate de que la tabla `usuarios` tenga algunos registros para poder asignar responsables al inventario.

## Uso

1.  Accede a la página de inicio de sesión (`index.php`) en tu navegador.
2.  Inicia sesión con un usuario que tenga el rol de 'superusuario' o 'digitador'.
3.  Según el rol, tendrás acceso a las funcionalidades correspondientes para registrar y ver el inventario.

---

Este es un resumen del proyecto basado en la información proporcionada. Si tienes alguna otra pregunta o necesitas más detalles sobre alguna parte específica, no dudes en preguntar.





no pero para el proyecto con todo elcodigo que tu tienes sobre el proyercto no sobre el quizx


Ah, disculpa la confusión. Entiendo que quieres un README.md que describa el proyecto completo del inventario tecnológico, incluyendo el registro de personas, el registro de inventario (manual y CSV), y la visualización diferenciada por roles.

Considerando los archivos y funcionalidades que hemos discutido, aquí tienes un borrador del README.md para el proyecto completo:

Markdown

# Proyecto: Sistema de Gestión de Inventario Tecnológico

Este proyecto es un sistema web desarrollado en PHP para la gestión de un inventario de elementos tecnológicos. Permite el registro de personas, el registro de elementos del inventario (tanto manual como mediante la carga de archivos CSV) y la visualización de la información del inventario adaptada al rol del usuario (superusuario o digitador).

## Estructura de la Base de Datos

El sistema utiliza una base de datos relacional (PostgreSQL o MySQL, según la configuración) con las siguientes tablas:

### `inventarios`

| Campo         | Tipo de dato   | Descripción                                  |
|---------------|----------------|----------------------------------------------|
| `id_inventario` | INT (PK)       | Identificador único del elemento             |
| `marca`       | VARCHAR(50)    | Marca del dispositivo                        |
| `modelo`      | VARCHAR(50)    | Modelo del dispositivo                       |
| `serial`      | VARCHAR(50)    | Número de serie único                        |
| `categoria`   | VARCHAR(50)    | Tipo de dispositivo                          |
| `estado`      | VARCHAR(50)    | Estado actual del dispositivo                |
| `id_persona`  | INT (FK)       | ID de la persona responsable del dispositivo |

### `usuarios`

| Campo                    | Tipo de dato    | Descripción                                      |
|--------------------------|-----------------|--------------------------------------------------|
| `id`                     | SERIAL (PK)     | Identificador único del usuario                  |
| `nombres`                | VARCHAR(100)    | Nombres del usuario                              |
| `apellidos`              | VARCHAR(100)    | Apellidos del usuario                            |
| `email`                  | VARCHAR(100)    | Correo electrónico único del usuario             |
| `username`               | VARCHAR(50)     | Nombre de usuario único                          |
| `password`               | VARCHAR(255)    | Contraseña hasheada del usuario                  |
| `tipo_documento`         | VARCHAR(50)     | Tipo de documento del usuario                    |
| `num_documento`          | VARCHAR(50)     | Número de documento único del usuario            |
| `telefono`               | VARCHAR(20)     | Número de teléfono del usuario                   |
| `direccion`              | VARCHAR(255)    | Dirección del usuario                            |
| `rol`                    | VARCHAR(50)     | Rol del usuario (superusuario, digitador, etc.) |
| `pregunta_recuperacion1` | VARCHAR(255)    | Pregunta de recuperación 1                     |
| `respuesta_recuperacion1`| VARCHAR(255)    | Respuesta de recuperación 1 hasheada            |
| `pregunta_recuperacion2` | VARCHAR(255)    | Pregunta de recuperación 2                     |
| `respuesta_recuperacion2`| VARCHAR(255)    | Respuesta de recuperación 2 hasheada            |
| `pregunta_recuperacion3` | VARCHAR(255)    | Pregunta de recuperación 3                     |
| `respuesta_recuperacion3`| VARCHAR(255)    | Respuesta de recuperación 3 hasheada            |

## Funcionalidades

1.  **Registro de Personas:** Permite registrar nuevas personas/usuarios en el sistema.
2.  **Registro de Inventario:**
    * **Carga desde CSV:** Permite cargar múltiples elementos del inventario desde un archivo CSV.
    * **Registro Manual:** Permite registrar elementos del inventario individualmente a través de un formulario web.
3.  **Visualización del Inventario:**
    * **Superusuarios:** Pueden ver los elementos del inventario que les han sido asignados.
    * **Digitadores:** Pueden ver toda la información del inventario, incluyendo el responsable.
4.  **Gestión de Sesión:** Permite a los usuarios iniciar y cerrar sesión de forma segura.
5.  **Cambio de Contraseña:** Permite a los usuarios cambiar su contraseña.

## Archivos del Proyecto

* `config.php`: Configuración de la conexión a la base de datos.
* `css/style.css`: Archivos de estilos CSS.
* `index.php`: Página de inicio de sesión.
* `php/actualizar_contrasena.php`: Permite a los usuarios cambiar su contraseña.
* `php/logout.php`: Script para cerrar la sesión.
* `php/reg_personas.php`: Formulario y lógica para registrar nuevas personas/usuarios.
* `php/registrar_inventario.php`: Lógica para registrar inventario (manual y CSV).
* `ver_mi_inventario.php`: Visualización del inventario asignado al superusuario.
* `ver_inventario_completo.php`: Visualización completa del inventario para digitadores.

## Requisitos

* Servidor web con soporte para PHP.
* Base de datos PostgreSQL o MySQL.
* Acceso a la base de datos configurado en `config.php`.

## Instalación

1.  Clona o descarga este repositorio en el directorio raíz de tu servidor web.
2.  Importa las definiciones de las tablas (`inventarios` y `usuarios`) a tu base de datos.
3.  Configura la conexión a la base de datos en `config.php`.
4.  Crea algunos usuarios con diferentes roles en la tabla `usuarios`.

## Uso

1.  Accede a `index.php` en tu navegador para iniciar sesión.
2.  Utiliza las funcionalidades disponibles según tu rol (superusuario o digitador).

