# WashFlow - Sistema de Gestion de Lavadero de Vehiculos (Turnos, Lavados y Operaciones)
Trabajo Practico Integrador N°2 - Modelos y Sistemas II  
7° Ano - Especialidad Programacion  
Escuela de Educacion Secundaria Tecnica N°1 de Vicente Lopez ("Eduardo Ader")
Alumnos: Olea y Caballero

---

## 1. Descripcion del Sistema

**WashFlow** es una aplicacion web integral disenada para la gestion comercial, logistica y operativa de un centro de lavado y estetica vehicular . El sistema ha sido concebido para resolver de manera automatizada y eficiente tanto la atencion espontanea en mostrador como la planificacion previa de turnos mediante autogestion online, garantizando el cumplimiento estricto del modelo formal de diagramas UML (casos de uso, clases, secuencias, estados, actividades, componentes y despliegue).

El sistema administra la capacidad operativa en tiempo real (control de cupo maximo concurrente), el catalogo de tarifas segmentadas por tipo de vehiculo, la agenda diaria del personal de boxes, el ciclo de vida discreto de los lavados, el circuito de solicitudes de modificacion/cancelacion bajo la politica de anticipacion, la auditoria de operaciones criticas, y la publicacion de contenidos institucionales dinamicos (guia de servicios, preguntas frecuentes y galeria de trabajos).

### Stack Tecnologico

- **Backend**: PHP 8.1+ (desarrollado y optimizado sobre PHP 8.2 en entorno XAMPP), arquitectura modular bajo patron **Modelo-Vista-Controlador (MVC)** propio, con autocargador PSR-4 nativo, enrutador frontal de peticiones con parametros dinamicos por expresiones regulares, validacion y sanitizacion estricta en servidor, motor de renderizado con escape contextual anti-XSS y sin dependencias de frameworks externos pesados.
- **Base de datos**: MariaDB 10.4+ / MySQL 8.0+, motor transaccional **InnoDB**, cotejamiento `utf8mb4_unicode_ci`, integridad referencial con restricciones de clave primaria, unicidad y foraneas con acciones en cascada/set null controladas, indices optimizados de busqueda y sentencias preparadas PDO contra inyecciones SQL ejecutadas bajo patron Singleton.
- **Frontend**: Arquitectura web hibrida (Server-Side Rendering enriquecido con micro-interacciones asincronicas via Fetch API), construida sobre HTML5 semantico, CSS3 modular basado integramente en variables nativas (soporte completo para modo claro y modo oscuro persistente en `localStorage`), diseno responsivo para dispositivos moviles y de escritorio, y JavaScript puro (Vanilla JS), sin librerias externas, sin frameworks auxiliares (sin jQuery ni Bootstrap) y sin dependencias de CDN.
- **Identidad visual**: Diseno moderno, sobrio y funcional de centro de detalle automotriz. Iconografia vectorial SVG de alta definicion incrustada directamente en codigo (cero emojis en codigo e interfaz, cero descargas de tipografias o fuentes de iconos externas).

---

## 2. Requisitos de Instalacion

- **Paquete de Servidor**: XAMPP para Windows con modulos de **Apache 2.4+** (con modulo `mod_rewrite` habilitado) y  MySQL 8.0.
- **Interprete**: PHP 8.1 o PHP 8.2 con extensiones activas: `pdo`, `pdo_mysql`, `mbstring`, `openssl`.
- **Navegador web moderno**: Google Chrome, Mozilla Firefox, Microsoft Edge u Opera con JavaScript habilitado.
- **Ubicacion sugerida del proyecto**: `C:\xampp\htdocs\washflow` (o directorio de trabajo con ejecucion mediante servidor embebido).

---

## 3. Guia de Instalacion y Puesta en Marcha Paso a Paso

### Paso 1: Iniciar Apache y MySQL en XAMPP
1. Abra el panel de control de XAMPP (**XAMPP Control Panel**).
2. Haga clic en el boton **Start** correspondiente al servicio **Apache**.
3. Haga clic en el boton **Start** correspondiente al servicio **MySQL**.
4. Compruebe que ambos modulos figuren sombreados en verde con sus puertos asignados por defecto (80/443 y 3306).

### Paso 2: Importar la Base de Datos en phpMyAdmin
1. Abra su navegador web e ingrese a: `http://localhost/phpmyadmin/`
2. En la barra superior, seleccione la pestana **Importar** (o abra la consola **SQL**).
3. Presione el boton para seleccionar archivo y elija en primer termino el esquema relacional:
   `C:\xampp\htdocs\washflow\database\schema.sql`
4. Haga clic en **Continuar** al pie de la pagina para generar la base de datos `washflow` y las 15 tablas relacionales con sus restricciones de clave primaria, unica y foranea.
5. Repita el procedimiento de importacion seleccionando el archivo de datos base:
   `C:\xampp\htdocs\washflow\database\datos_iniciales.sql`

*(Alternativa directa por consola de comandos MySQL):*
```bash
C:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS washflow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
C:\xampp\mysql\bin\mysql.exe -u root washflow < C:\xampp\htdocs\washflow\database\schema.sql
C:\xampp\mysql\bin\mysql.exe -u root washflow < C:\xampp\htdocs\washflow\database\datos_iniciales.sql
```

### Paso 3: Verificar la Configuracion del Servidor
Verifique la existencia del archivo de configuracion `app/config/config.php` (creado a partir de la plantilla `app/config/config.ejemplo.php`). Los parametros por omision para XAMPP son:

```php
return [
    'entorno' => 'development',
    'depuracion' => true,
    'url_base' => 'http://localhost/washflow/public',
    'db' => [
        'host' => '127.0.0.1',
        'puerto' => 3306,
        'nombre' => 'washflow',
        'usuario' => 'root',
        'contrasena' => '',
        'charset' => 'utf8mb4'
    ],
    'negocio' => [
        'capacidad_maxima' => 3,              // Maximo 3 vehiculos en simultaneo
        'hora_apertura' => '10:00',           // 10:00 hs
        'hora_cierre' => '18:00',             // 18:00 hs
        'intervalo_minutos' => 30,            // Franjas de 30 minutos
        'dias_atencion' => [1, 2, 3, 4, 5, 6],// Lunes a Sabado (Domingos cerrado)
        'anticipacion_horas_cambio_directo' => 2 // Regla de las 2 horas
    ],
    'seguridad' => [
        'nombre_sesion' => 'washflow_sess',
        'max_intentos_login' => 5,
        'minutos_bloqueo_login' => 15,
        'tiempo_token_recuperacion_minutos' => 60
    ]
];
```

### Paso 4: Crear el Usuario Administrador Inicial
Por estrictas razones de seguridad y cumplimiento del modelo de permisos, el rol de Administrador no cuenta con registro publico en la interfaz web. Debe generarse de forma segura mediante el script de consola:

```bash
C:\xampp\php\php.exe C:\xampp\htdocs\washflow\scripts\crear_admin.php
```
*Siga las instrucciones interactivas ingresando Nombre, Apellido, Correo y Contrasena (minimo 8 caracteres).*

### Paso 5: Abrir la Aplicacion Web
Abra su navegador web e ingrese a la direccion local de la aplicacion:
`http://localhost/washflow/public/`

*(Gracias a las directivas del archivo `.htaccess` raiz, tambien puede accederse ingresando directamente a `http://localhost/washflow/`).*

### Paso 6: Alternativa de Ejecucion con Servidor Embebido de PHP
Si desea prescindir del servidor Apache de XAMPP, abra una terminal en el directorio raiz del proyecto y ejecute:

```bash
php -S localhost:8000 -t public
```
Luego ingrese desde su navegador a: `http://localhost:8000/`

---

## 4. Usuarios de Prueba Disponibles por Rol

Todas las contrasenas del sistema son procesadas mediante la funcion nativa `password_hash()` con algoritmo **BCRYPT** y verificadas con `password_verify()`. En caso de registrar usuarios o utilizar los perfiles de demostracion generados:

| Rol | Apellido y Nombre | Correo Electronico | Telefono | Contrasena | Estado | Perfil en el Sistema |
|---|---|---|---|---|---|---|
| **ADMINISTRADOR** | Benitez, Carlos | `admin@washflow.com` | `1144556677` | `password123` | Activo | Administrador general: control de usuarios, tarifas, bloqueos, estadisticas, auditoria y CMS |
| **EMPLEADO** | Gomez, Roberto | `carlos.ruiz@washflow.com` | `1133221100` | `password123` | Activo | Operador de boxes: agenda diaria, admision de vehiculos, lavado directo y solicitudes |
| **CLIENTE** | Martinez, Sofia | `Rigby@gmail.com` | `1122334455` | `Rigby8660` | Activo | Cliente registrado: gestion de vehiculos propios, reserva guiada y solicitudes con cuenta |
| **PUBLICO / OCASIONAL** | *(Sin cuenta previa)* | *Opcional en reserva* | *Celular de contacto* | *(Sin clave / Acceso por Token)* | Activo | Reserva simple en 5 pasos sin login, consulta publica por codigo `WF-XXXXX` y token seguro |

*Nota: La herencia de seguridad del sistema establece que todo usuario con rol `ADMINISTRADOR` posee acceso y facultades operativas completas sobre las rutas del rol `EMPLEADO`.*

---

## 5. Arquitectura en Capas y Estructura del Proyecto

El proyecto sigue una estricta separacion de responsabilidades en capas logicas:

```text
washflow/
  .htaccess                     Redireccion amigable hacia la carpeta de publicacion /public
  README.md                     Documentacion tecnica y guia integral del sistema
  app/
    config/
      config.ejemplo.php        Plantilla base de configuracion versionable sin credenciales
      config.php                Configuracion activa local (credenciales BD, reglas de negocio y sesion)
    core/
      BaseDatos.php             Conexion PDO Singleton, manejo transaccional y consultas preparadas
      Controlador.php           Controlador base: renderizado de vistas, redireccion, JSON y control de roles
      Csrf.php                  Generacion, inyeccion en formularios y verificacion estricta de tokens CSRF
      Router.php                Enrutador HTTP por expresiones regulares con soporte para parametros {id}
      Sesion.php                Manejo seguro de sesiones HTTP, HttpOnly, SameSite y proteccion de sesion
      Validador.php             Sanitizacion y validacion semantica de reglas de entrada en servidor
      Vista.php                 Motor de plantillas con inclusion de layouts y escape contextual anti-XSS
    modelos/
      Usuario.php               Entidad de usuario (Cliente, Empleado, Administrador) y autenticacion
      TipoVehiculo.php          Categorias vehiculares (Moto, Auto, Camioneta, 4x4) y duracion base
      Vehiculo.php              Padron de rodados con marca, modelo, patente y validacion de tipo
      ModeloVehiculo.php        Catalogo de 250+ modelos comerciales para buscador interactivo
      Servicio.php              Catalogo de servicios ofrecidos (Lavado normal, Lavado premium)
      PrecioServicio.php        Matriz tarifaria con vigencia temporal y congelamiento de precios
      Reserva.php               Entidad de turno: codigo WF-XXXXX, token publico, estado y franja horaria
      SolicitudReserva.php      Mecanismo de excepcion para reprogramacion o cancelacion < 2 horas
      BloqueoHorario.php        Inhabilitacion administrativa de boxes (mantenimiento, feriados o clima)
      Lavado.php                Ciclo de lavado operativo en box con estados discretos: pendiente y terminado
      Notificacion.php          Mensajeria interna y alertas operativas para clientes y personal
      RegistroOperacion.php     Auditoria forense y trazabilidad de acciones criticas del sistema
      RecuperacionContrasena.php Gestion de tokens seguros de restablecimiento de clave
      PreguntaFrecuente.php     Entidad de preguntas y respuestas frecuentes para el portal publico
      Galeria.php               Entidad de imagenes de trabajos realizados y portfolio institucional
    servicios/
      DisponibilidadServicio.php Motor de calculo de capacidad concurrente (Regla de 3 vehiculos simultaneos)
      CorreoServicio.php        Servicio desacoplado de mensajeria SMTP con soporte TLS y autenticacion
      ReporteServicio.php       Consolidacion de metricas operativas, ocupacion por franjas y exportacion CSV
    controladores/
      InicioControlador.php     Controlador de portada institucional (Landing de 10 secciones) y Guia de Lavados
      AutenticacionControlador.php Inicio de sesion, cierre de sesion, registro y recuperacion de contrasena
      ClienteControlador.php    Panel de autogestion del cliente, padron de vehiculos y notificaciones
      ReservaControlador.php    Asistente guiado de turnos, reserva publica sin cuenta y consulta por enlace
      SolicitudControlador.php  Gestion y resolucion de solicitudes de cambio/cancelacion fuera de termino
      EmpleadoControlador.php   Agenda operativa diaria, admision de vehiculos, lavado directo y cambio de tipo
      AdminControlador.php      Administracion de usuarios, precios, bloqueos, modelos, FAQ, galeria e informes
    vistas/
      layouts/                  Plantillas maestras: main.php, header.php, sidebar.php, footer.php y auth.php
      errores/                  Paginas HTTP de error personalizadas: 403.php, 404.php y 500.php
      inicio/                   Portada institucional (Landing Page interactiva)
      guia/                     Guia comparativa de lavados y tratamientos con clasificador dinamico
      auth/                     Formularios de autenticacion: login, registro, recuperar y restablecer
      reservas/                 Vistas publicas: reserva sin cuenta (publica.php) y comprobante (detalle_publico.php)
      cliente/                  Vistas del cliente: dashboard, mis reservas, mis vehiculos y notificaciones
      empleado/                 Vistas del empleado: agenda diaria, admision, lavado directo y solicitudes
      admin/                    Vistas de administracion: usuarios, precios, bloqueos, modelos, FAQ, galeria y auditoria
  database/
    schema.sql                  Definicion DDL consolidada: 15 tablas relacionales InnoDB con integridad referencial
    datos_iniciales.sql         Semillas DML iniciales: tipos de vehiculo, servicios y matriz de precios
  documentacion/
    diagramas/                  Coleccion completa de 12 diagramas de diseno UML en formato `.drawio` y `.png`
      LEEME.md                  Detalle de convenciones graficas y resumen de cambios respecto del modelo formal
  public/
    .htaccess                   Controlador frontal de Apache, reescritura de URLs y cabeceras de seguridad
    index.php                   Punto unico de entrada de la aplicacion web (Front Controller)
    assets/
      css/
        app.css                 Hoja de estilos modular: variables semanticas, tema oscuro/claro y componentes
      js/
        app.js                  Logica de cliente: asistente interactivo, buscador dinamico y alternador de tema
      img/                      Fotografias institucionales y portfolio de trabajos terminados
  scripts/
    crear_admin.php             Herramienta CLI interactiva para dar de alta administradores en base de datos
```

---

## 6. Endpoints y Catalogo de Rutas del Sistema

Todas las rutas mutantes (`POST`) cuentan con proteccion obligatoria contra falsificacion de peticiones entre sitios cruzados (**CSRF**) y verificacion estricta de sesion y roles:

| Metodo | Ruta de la Aplicacion | Acceso / Rol Minimo | Descripcion Funcional |
|---|---|---|---|
| `GET` | `/` | Publico | Portada institucional (Landing Page de 10 secciones con estado de atencion en vivo) |
| `GET` | `/guia-lavados` | Publico | Articulo tecnico y comparativa de servicios con buscador clasificador de vehiculos |
| `GET` | `/reservar` | Publico | Asistente de 5 pasos para reserva de turnos sin necesidad de crear cuenta |
| `POST` | `/reservar` | Publico | Creacion transaccional de reserva publica con generacion de codigo y token |
| `GET` | `/reserva/{codigo}/{token}` | Publico | Consulta y comprobante interactivo de reserva publica con enlace seguro |
| `POST` | `/reserva/{codigo}/{token}/reprogramar` | Publico | Reprogramacion directa (>= 2 hs) o generacion de solicitud de cambio (< 2 hs) |
| `POST` | `/reserva/{codigo}/{token}/cancelar` | Publico | Cancelacion directa (>= 2 hs) o generacion de solicitud de cancelacion (< 2 hs) |
| `POST` | `/reserva/{codigo}/{token}/crear-cuenta` | Publico | Creacion voluntaria de cuenta de cliente vinculando la reserva existente |
| `GET` | `/mi-reserva` | Publico | Buscador de reservas publicas por codigo alfanumerico y telefono |
| `POST` | `/mi-reserva` | Publico | Validacion de credenciales y redireccion al comprobante seguro de la reserva |
| `GET` | `/login` | Publico | Formulario de autenticacion de usuarios registrados (Cliente, Empleado, Admin) |
| `POST` | `/login` | Publico | Verificacion de credenciales, control de fuerza bruta y regeneracion de sesion |
| `GET` | `/registro` | Publico | Formulario de registro voluntario para nuevos clientes |
| `POST` | `/registro` | Publico | Alta de usuario con rol CLIENTE y cifrado BCRYPT de contrasena |
| `POST` | `/logout` | Autenticado | Cierre de sesion seguro, destruccion de datos en servidor y expiracion de cookie |
| `GET` | `/recuperar` | Publico | Solicitud de recuperacion de contrasena olvidada |
| `POST` | `/recuperar` | Publico | Generacion y envio de token temporal de recuperacion de clave |
| `GET` | `/restablecer` | Publico | Formulario de cambio de clave mediante token de restablecimiento valido |
| `POST` | `/restablecer` | Publico | Actualizacion de la contrasena hasheada e invalidacion del token utilizado |
| `GET` | `/api/modelos` | Publico | Endpoint JSON para busqueda predictiva de marcas y modelos automotrices |
| `GET` | `/api/disponibilidad` | Publico | Endpoint JSON para calculo de franjas horarias libres segun duracion y capacidad |
| `GET` | `/api/precio` | Publico | Endpoint JSON que devuelve el valor congelado segun tipo de rodado y servicio |
| `GET` | `/cliente/dashboard` | CLIENTE | Tablero principal con proximos turnos, resumen de flota y avisos |
| `GET` | `/cliente/vehiculos` | CLIENTE | Administracion del padron de vehiculos vinculados a la cuenta del cliente |
| `POST` | `/cliente/vehiculos/crear` | CLIENTE | Alta de nuevo vehiculo con validacion de patente y categoria |
| `POST` | `/cliente/vehiculos/{id}/editar` | CLIENTE | Modificacion de datos del rodado de propiedad del cliente |
| `POST` | `/cliente/vehiculos/{id}/desactivar` | CLIENTE | Baja logica (soft delete) del vehiculo preservando el historico de lavados |
| `GET` | `/cliente/reservar` | CLIENTE | Asistente de reserva exclusivo para clientes con seleccion de vehiculo propio |
| `POST` | `/cliente/reservas/guardar` | CLIENTE | Confirmacion de reserva vinculada directamente al perfil del usuario |
| `GET` | `/cliente/reservas` | CLIENTE | Historial cronologico de reservas solicitadas con filtros por estado |
| `POST` | `/cliente/reservas/{id}/reprogramar`| CLIENTE | Reprogramacion directa de turno respetando la regla de anticipacion de 2 horas |
| `POST` | `/cliente/reservas/{id}/cancelar` | CLIENTE | Cancelacion directa e inmediata de turno con mas de 2 horas de anticipacion |
| `GET` | `/cliente/reservas/{id}/solicitar` | CLIENTE | Formulario de peticion justificada de cambio o cancelacion dentro de las 2 horas |
| `POST` | `/cliente/reservas/{id}/solicitar` | CLIENTE | Envio de solicitud de excepcion para ser resuelta por el personal |
| `GET` | `/cliente/notificaciones` | CLIENTE | Bandeja de alertas y mensajes del sistema sobre el estado de sus turnos |
| `POST` | `/cliente/notificaciones/marcar-todas`| CLIENTE | Actualizacion masiva del estado de lectura de las notificaciones |
| `GET` | `/empleado/dashboard` | EMPLEADO | Tablero de control de boxes, volumen diario y solicitudes pendientes |
| `GET` | `/empleado/agenda` | EMPLEADO | Sabana horaria operativa del dia con distribucion de vehiculos por franja |
| `GET` | `/empleado/buscar` | EMPLEADO | Buscador rapido de vehiculos en pista por patente, codigo de turno o cliente |
| `POST` | `/empleado/llegada` | EMPLEADO | Registro de recepcion de vehiculo con turno previo y generacion del lavado |
| `POST` | `/empleado/terminar-lavado` | EMPLEADO | Actualizacion de estado del lavado a `terminado` con registro de hora de egreso |
| `POST` | `/empleado/ausencia` | EMPLEADO | Marcacion de cliente ausente liberando la capacidad operativa del box |
| `GET` | `/empleado/lavado-directo` | EMPLEADO | Formulario de admision rapida para clientes espontaneos que llegan sin turno |
| `POST` | `/empleado/lavado-directo` | EMPLEADO | Creacion directa de cliente ocasional, vehiculo y lavado en box |
| `GET` | `/empleado/solicitudes` | EMPLEADO | Panel de evaluacion de solicitudes de reprogramacion o cancelacion urgente |
| `POST` | `/empleado/solicitudes/{id}/aprobar` | EMPLEADO | Aprobacion de solicitud con actualizacion automatica de agenda o anulacion |
| `POST` | `/empleado/solicitudes/{id}/rechazar`| EMPLEADO | Rechazo fundado de solicitud con emision de notificacion explicativa |
| `POST` | `/empleado/reservas/{id}/corregir-categoria`| EMPLEADO | Ajuste presencial de tipo de rodado para reservas con tipo a confirmar |
| `GET` | `/admin/dashboard` | ADMINISTRADOR | Panel de administracion general y resumen ejecutivo del establecimiento |
| `GET` | `/admin/usuarios` | ADMINISTRADOR | Gestion integral del padron de usuarios (Administradores, Empleados, Clientes) |
| `POST` | `/admin/usuarios/crear` | ADMINISTRADOR | Alta administrativa de usuarios con asignacion formal de roles |
| `POST` | `/admin/usuarios/{id}/editar` | ADMINISTRADOR | Modificacion de datos de perfil, correo y rol de seguridad |
| `POST` | `/admin/usuarios/{id}/activar` | ADMINISTRADOR | Habilitacion de usuario para acceso al sistema |
| `POST` | `/admin/usuarios/{id}/desactivar` | ADMINISTRADOR | Bloqueo preventivo de acceso (con proteccion de auto-bloqueo del administrador) |
| `GET` | `/admin/vinculaciones` | ADMINISTRADOR | Herramienta de unificacion de cuentas para clientes ocasionales y registrados |
| `POST` | `/admin/vinculaciones` | ADMINISTRADOR | Traspaso transaccional de vehiculos y reservas hacia un usuario definitivo |
| `GET` | `/admin/precios` | ADMINISTRADOR | Administracion de matriz de tarifas vigentes y duraciones por categoria |
| `POST` | `/admin/precios/modificar` | ADMINISTRADOR | Actualizacion de precios con fecha de vigencia sin alterar reservas pactadas |
| `POST` | `/admin/duraciones/modificar` | ADMINISTRADOR | Configuracion del tiempo estimado de lavado por categoria vehicular |
| `GET` | `/admin/bloqueos` | ADMINISTRADOR | Gestion de periodos de cierre de boxes por mantenimiento, feriado o lluvia |
| `POST` | `/admin/bloqueos` | ADMINISTRADOR | Alta de bloqueo de agenda con cancelacion o reprogramacion de turnos afectados |
| `POST` | `/admin/bloqueos/{id}/eliminar` | ADMINISTRADOR | Levantamiento de bloqueo horario y restitucion de la disponibilidad |
| `GET` | `/admin/informes` | ADMINISTRADOR | Tablero estadistico de ocupacion, servicios mas demandados y afluencia diaria |
| `GET` | `/admin/exportar-csv` | ADMINISTRADOR | Exportacion de registros historicos de turnos y lavados a planilla CSV |
| `GET` | `/admin/auditoria` | ADMINISTRADOR | Bitacora forense de operaciones con fecha, hora, usuario y detalle de accion |
| `GET` | `/admin/modelos` | ADMINISTRADOR | Administracion del catalogo de marcas y modelos para el clasificador inteligente |
| `POST` | `/admin/modelos/crear` | ADMINISTRADOR | Alta de nuevo modelo comercial asociado a una categoria vehicular |
| `POST` | `/admin/modelos/{id}/actualizar` | ADMINISTRADOR | Modificacion o reclasificacion de categoria para un modelo existente |
| `GET` | `/admin/preguntas` | ADMINISTRADOR | Gestion del modulo CMS de preguntas frecuentes (FAQ) de la portada |
| `POST` | `/admin/preguntas/crear` | ADMINISTRADOR | Publicacion de nueva pregunta frecuente con orden de visualizacion |
| `POST` | `/admin/preguntas/{id}/actualizar` | ADMINISTRADOR | Modificacion de enunciado, respuesta y estado de visibilidad |
| `POST` | `/admin/preguntas/{id}/eliminar` | ADMINISTRADOR | Baja de pregunta frecuente del portal publico |
| `GET` | `/admin/galeria` | ADMINISTRADOR | Administracion del portfolio de fotografias de trabajos de lavado |
| `POST` | `/admin/galeria/crear` | ADMINISTRADOR | Incorporacion de nueva imagen con titulo, categoria y orden |
| `POST` | `/admin/galeria/{id}/actualizar`| ADMINISTRADOR | Edicion de metadatos de la imagen cargada |
| `POST` | `/admin/galeria/{id}/eliminar` | ADMINISTRADOR | Eliminacion de elemento de la galeria publica |

---

## 7. Mapa de Trazabilidad: Casos de Uso y Diagramas UML -> Codigo y Vistas

El sistema WashFlow implementa la totalidad de los modelos disenados en la fase de analisis, asegurando correspondencia biunivoca entre la documentacion formal (`documentacion/diagramas/`) y los modulos de software:

| Diagrama UML | Concepto / Caso de Uso Modelado | Tablas de BD Asociadas | Clases y Modelos PHP | Controladores y Servicios | Vistas / Interfaz de Usuario |
|---|---|---|---|---|---|
| **01_casos_uso_general** | Jerarquia de actores del sistema: Cliente, Empleado y Administrador con herencia de permisos. | `usuarios` | `Usuario.php`, `Cliente.php`, `Empleado.php`, `Administrador.php` | `Controlador.php` (filtros `exigirRol` y `verificarAutenticado`) | `layouts/sidebar.php`, menus condicionales |
| **02_casos_uso_reservas** | Flujo completo de turnos: Solicitar, Asignar box, Modificar, Cancelar, Llegada y Atencion espontanea. | `reservas`, `solicitudes_reserva`, `lavados`, `vehiculos` | `Reserva.php`, `SolicitudReserva.php`, `Lavado.php`, `Vehiculo.php` | `ReservaControlador.php`, `SolicitudControlador.php`, `EmpleadoControlador.php` | `vistas/reservas/*`, `cliente/reservas/*`, `empleado/agenda.php` |
| **03_casos_uso_administracion** | Administracion de tarifas, bloqueos de agenda, usuarios, bitacora de auditoria y metricas operativas. | `usuarios`, `precios_servicio`, `bloqueos_horario`, `registro_operaciones` | `PrecioServicio.php`, `BloqueoHorario.php`, `RegistroOperacion.php` | `AdminControlador.php`, `ReporteServicio.php` | `admin/precios/*`, `admin/bloqueos/*`, `admin/usuarios/*`, `admin/auditoria/*` |
| **04a_diagrama_clases_reservas** | Modelo de dominio de reservas, vehiculos, clientes, clasificacion de modelos y bloqueos. | `usuarios`, `tipos_vehiculo`, `modelos_vehiculo`, `vehiculos`, `servicios`, `precios_servicio`, `reservas`, `solicitudes_reserva` | `Usuario.php`, `TipoVehiculo.php`, `ModeloVehiculo.php`, `Vehiculo.php`, `Servicio.php`, `PrecioServicio.php`, `Reserva.php` | `ReservaControlador.php`, `ClienteControlador.php`, `DisponibilidadServicio.php` | `cliente/vehiculos/index.php`, `reservas/publica.php`, `admin/modelos/*` |
| **04b_diagrama_clases_lavados_contenido** | Modelo de dominio operativo: ejecucion de lavados, notificaciones, auditoria y CMS de contenidos. | `lavados`, `notificaciones`, `registro_operaciones`, `preguntas_frecuentes`, `galeria` | `Lavado.php`, `Notificacion.php`, `RegistroOperacion.php`, `PreguntaFrecuente.php`, `Galeria.php` | `EmpleadoControlador.php`, `AdminControlador.php`, `InicioControlador.php` | `empleado/agenda.php`, `inicio/index.php`, `admin/preguntas/*`, `admin/galeria/*` |
| **05_secuencia_reservar_turno** | Solicitud de turno guiada sin cuenta previa con verificacion de cupo y generacion de enlace seguro. | `reservas`, `vehiculos`, `usuarios`, `precios_servicio` | `Reserva.php`, `DisponibilidadServicio.php`, `PrecioServicio.php` | `ReservaControlador::mostrarReservarPublica()`, `ReservaControlador::guardarReservaPublica()` | `reservas/publica.php`, asistente de 5 etapas |
| **06_secuencia_registrar_lavado** | Recepcion en mostrador del vehiculo con turno previo, transicion a box y creacion del lavado en estado `pendiente`. | `reservas`, `lavados` | `Reserva.php`, `Lavado.php` | `EmpleadoControlador::registrarLlegada()`, `EmpleadoControlador::marcarLavadoTerminado()` | `empleado/agenda.php` (acciones 'Ingreso' y 'Finalizar') |
| **07_secuencia_lavado_sin_reserva** | Atencion espontanea por ventanilla con alta inmediata de cliente ocasional, vehiculo y admision directa a box. | `usuarios`, `vehiculos`, `lavados` | `Usuario.php`, `Vehiculo.php`, `Lavado.php` | `EmpleadoControlador::mostrarLavadoDirecto()`, `EmpleadoControlador::guardarLavadoDirecto()` | `empleado/lavado_directo.php` |
| **08_estados_reserva** | Transicion rigurosa de ciclos de vida de reservas (`reservada`, `cancelada`, `ausente`) y lavados (`pendiente`, `terminado`). | `reservas` (`estado`), `lavados` (`estado`) | `Reserva.php`, `Lavado.php` | Validaciones semanticas de transicion de estado en modelos | Insignias visuales de estado en agenda y comprobantes |
| **09_actividades_reservar_turno** | Flujo procedimental particionado en carriles Cliente y Sistema con control de disponibilidad y congelamiento. | `tipos_vehiculo`, `servicios`, `precios_servicio`, `reservas` | `DisponibilidadServicio.php`, `Reserva.php` | `ReservaControlador::apiDisponibilidad()`, `ReservaControlador::guardarReserva()` | `public/assets/js/app.js`, `reservas/publica.php` |
| **10_despliegue** | Arquitectura fisica: Navegador -> Servidor Apache / PHP 8.2 -> MariaDB (XAMPP) y sockets SMTP. | Todas las tablas del esquema | Conector Singleton `BaseDatos.php`, `CorreoServicio.php` | `Router.php`, `public/index.php`, `.htaccess` | Directorio `/public` con reescritura de URLs limpias |
| **11_componentes** | Dependencias internas de software: Front Controller, Enrutador, Filtros de Sesion, Validador, Controladores y Modelos. | Modulos funcionales | Todos los componentes del namespace `WashFlow\` | Autocargador PSR-4 propio sin dependencias externas | Estructura modular de carpetas en `app/` |
| **12_secuencia_gestionar_reserva_con_enlace** | Acceso a comprobante publico mediante codigo y token aleatorio: verificacion de politica de 2 horas. | `reservas`, `solicitudes_reserva` | `Reserva.php`, `SolicitudReserva.php` | `ReservaControlador::mostrarDetallePublico()`, `ReservaControlador::reprogramarPublica()` | `reservas/detalle_publico.php`, `reservas/buscar_publico.php` |

---

## 8. Reglas de Negocio Implementadas

El sistema garantiza el cumplimiento estricto de las politicas operativas del establecimiento:

1. **Capacidad Concurrente Maxima (3 Vehiculos)**:
   - El establecimiento dispone de una capacidad fisica limitada a un maximo de 3 vehiculos procesandose de forma simultanea en cualquier franja horaria.
   - Si cualquiera de los intervalos de 30 minutos comprendidos dentro de la duracion total del servicio solicitado ya posee 3 vehiculos ocupados, el turno completo queda deshabilitado para evitar sobreturnos.
2. **Jornada de Atencion y Horarios Limite**:
   - Atencion operativa de Lunes a Sabados en jornada corrida de 10:00 a 18:00 hs. Domingos cerrado.
   - Ningun turno o servicio puede comenzar o extenderse mas alla de las 18:00:00 hs.
3. **Regla de las 2 Horas (Modificacion y Cancelacion de Turnos)**:
   - **Anticipacion mayor o igual a 2 horas**: El cliente (con cuenta o mediante su enlace seguro) puede modificar el horario o cancelar su turno de forma directa e inmediata.
   - **Anticipacion menor a 2 horas**: El sistema bloquea la alteracion directa y exige la emision de una `solicitud_reserva` fundada. Dicha solicitud ingresa al panel del personal para su aprobacion o rechazo fundado.
4. **Inmutabilidad y Congelamiento Tarifario**:
   - Al registrarse una reserva, el `precio_acordado` y la `duracion_minutos` pactada quedan congelados de forma indeleble en el registro del turno.
   - Las futuras actualizaciones de precios aprobadas por el Administrador rigen estrictamente hacia adelante y jamas modifican los turnos previamente agendados.
5. **Estados Discretos de Lavado**:
   - Para evitar inconsistencias operativas en pista, los lavados admiten unicamente dos estados discretos: `pendiente` (vehiculo ingresado a box) y `terminado` (trabajo finalizado listo para entrega), erradicando estados intermedios ambiguos.
6. **Buscador Inteligente y Tipo a Confirmar**:
   - Incorporacion de una base de datos de mas de 250 modelos de circulacion habitual en Argentina con asociacion directa a su categoria (Moto, Auto, Camioneta, 4x4).
   - Opcion *"No estoy seguro"* para el usuario indeciso, que genera el rodado con marca `tipo_a_confirmar = 1`, alertando al recepcionista para verificar presencialmente la categoria fisica al ingresar el vehiculo al box.
7. **Politica de Integridad y Borrado Logico (Soft Delete)**:
   - Los registros de vehiculos y usuarios nunca son eliminados fisicamente de la base de datos para preservar la trazabilidad de auditoria y la integridad historica de los servicios prestados. Se emplean marcas booleanas de estado (`activo = 0`).
8. **Seguridad Contra Abusos y Ataques de Fuerza Bruta**:
   - Bloqueo temporal automatico de acceso tras 5 intentos fallidos consecutivos de inicio de sesion durante un lapso de 15 minutos.
   - Campo trampa *honeypot* antispam en formularios publicos y restriccion de un maximo de 3 reservas activas simultaneas por numero telefonico.

---

## 9. Limitaciones y Mejoras Futuras

El sistema WashFlow cumple con solvencia el alcance academico del Trabajo Practico Integrador N°2 de Modelos y Sistemas II, abarcando reservas, atencion en pista, gestion administrativa y contenidos institucionales. Para una implementacion a escala real en centros de servicios de alto trafico, se proyectan las siguientes extensiones funcionales:

- **Pasarela de Cobros Online Integrada**:
  - Incorporacion de integraciones oficiales con APIs de cobro (Mercado Pago Checkout Pro / QR dinamico, MODO o Stripe) para permitir el cobro total o de senas bancarizadas al momento de reservar.
  - Webhooks de conciliacion automatica para acreditacion inmediata de pagos y liberacion de turnos pendientes.
- **Facturacion Electronica AFIP / ARCA**:
  - Conexion mediante Web Services (WSFE) para emision automatica de Facturas A, B y Comprobantes Fiscales al consumidor final ante cada lavado finalizado.
- **Mensajeria y Notificaciones Omnicanal en Vivo**:
  - Integracion con la API oficial de WhatsApp Business y pasarelas de SMS para el envio de recordatorios de turnos ("Tu vehiculo ingresa en 1 hora") y avisos de retiro ("Tu auto ya esta terminado y reluciente en el box de entrega").
- **Modulo de Lavadores, Rendimiento y Comisiones**:
  - Asignacion nominal de operarios a cada vehiculo en pista para medicion de tiempos de proceso individuales y liquidacion automatica de porcentajes o propinas por servicio prestado.
- **Reconocimiento Automatico de Matriculas (LPR / ANPR)**:
  - Integracion de camaras de seguridad IP en la rampa de ingreso con software de lectura optica de patentes para apertura automatica de barreras e ingreso desatendido de clientes agendados.
- **Aplicacion Movil Nativa (PWA / Flutter)**:
  - Notificaciones push al telefono inteligente del conductor, geolocalizacion para calculo de demora de llegada y estado del proceso de secado en tiempo real.
- **Modulo Contable y Gestion de Stock de Insumos**:
  - Registro de compras a proveedores, control de consumo de litros de shampoo neutro, ceras, siliconas y microfibras por cada categoria de lavado con alertas de reposicion por stock critico.

---

## 10. Licencia y Uso Academico

Este proyecto fue desarrollado con fines exclusivamente educativos y tecnicos para el **Trabajo Practico Integrador N°2** de la asignatura **Modelos y Sistemas II** correspondiente al 7° Ano de la Escuela de Educacion Secundaria Tecnica N°1 de Vicente Lopez ("Eduardo Ader"). El diseno cumple estrictamente con las especificaciones de ingenieria de software, los esquemas relacionales de bases de datos y la trazabilidad de diagramas UML.
