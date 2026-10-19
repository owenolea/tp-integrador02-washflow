# -*- coding: utf-8 -*-
from lib import *


def build():
    # =============== 04a: usuarios, vehiculos, servicios y reservas ===============
    a = Dia('04a_diagrama_clases_reservas', 2040, 1160)
    a.klass('Usuario', 30, 440, 270, 'Usuario',
            ['-id : int', '-nombre : String', '-apellido : String', '-telefono : String', '-correo : String',
             '-contrasenaHash : String', '-activo : boolean'],
            ['+iniciarSesion() : boolean', '+cerrarSesion() : void', '+desactivar() : void'],
            stereo='{abstracta}', italic=True)
    a.klass('Cliente', 420, 310, 300, 'Cliente',
            ['-esOcasional : boolean', '-identidadVerificada : boolean'],
            ['+registrarVehiculo() : void', '+crearReserva() : Reserva', '+cancelarReserva() : void',
             '+solicitarCambio() : SolicitudReserva', '+consultarHistorial() : List',
             '+contarReservasActivas() : int', '+crearCuenta() : void'])
    a.klass('Empleado', 420, 650, 300, 'Empleado', [],
            ['+registrarLlegada() : void', '+registrarLavado() : Lavado', '+marcarTerminado() : void',
             '+registrarAusencia() : void', '+resolverSolicitud() : void',
             '+confirmarCategoria(tipo) : void'])
    a.klass('Administrador', 420, 900, 300, 'Administrador', [],
            ['+modificarPrecio() : void', '+configurarDuracion() : void', '+bloquearHorario() : void',
             '+administrarUsuarios() : void', '+consultarInformes() : void',
             '+administrarModelos() : void', '+administrarContenido() : void'])
    a.klass('Bloqueo', 860, 900, 300, 'BloqueoHorario',
            ['-id : int', '-fecha : Date', '-horaInicio : Time', '-horaFin : Time', '-motivo : String'],
            ['+afectaA(fecha, hora) : boolean'])
    a.klass('Modelo', 420, 40, 300, 'ModeloVehiculo', ['-id : int', '-marca : String', '-modelo : String', '-activo : boolean'],
            ['+desactivar() : void'])
    a.klass('Tipo', 860, 40, 300, 'TipoVehiculo', ['-id : int', '-nombre : String', '-duracionMinutos : int'],
            ['+cambiarDuracion(minutos : int) : void'])
    a.klass('Vehiculo', 860, 310, 300, 'Vehiculo', ['-id : int', '-patente : String [0..1]', '-marca : String', '-modelo : String', '-tipoAConfirmar : boolean'],
            ['+obtenerDuracion() : int', '+confirmarTipo(tipo) : void'])
    a.klass('Reserva', 1300, 270, 330, 'Reserva',
            ['-id : int', '-codigo : String', '-tokenPublico : String', '-tokenVence : DateTime', '-fecha : Date',
             '-horaInicio : Time', '-horaFin : Time', '-duracionMinutos : int',
             '-precioAcordado : double', '-estado : EstadoReserva'],
            ['+calcularHoraFin() : Time', '+reprogramar(fecha, hora) : void', '+cancelar() : void',
             '+marcarAusente() : void', '+permiteCambioDirecto() : boolean', '+tokenVigente() : boolean'])
    a.klass('Solicitud', 1300, 700, 330, 'SolicitudReserva',
            ['-id : int', '-tipo : TipoSolicitud', '-nuevaFecha : Date', '-nuevaHora : Time', '-motivo : String',
             '-estado : EstadoSolicitud', '-fechaSolicitud : DateTime'],
            ['+aprobar() : void', '+rechazar() : void'])
    a.klass('Servicio', 1760, 310, 260, 'Servicio', ['-id : int', '-nombre : String', '-descripcion : String', '-activo : boolean'],
            ['+obtenerPrecio(tipo) : double'])
    a.klass('Precio', 1760, 40, 260, 'PrecioServicio', ['-id : int', '-precio : double', '-vigenteDesde : Date'],
            ['+modificarPrecio(nuevo) : void'])

    # herencia Usuario <- Cliente, Empleado
    ux = a.keys['Usuario']['x'] + a.keys['Usuario']['w']
    uy = 440 + 0.5 * a.keys['Usuario']['h']
    # bus vertical en x=360
    a.line([(420, 400), (360, 400), (360, uy)], )
    a.line([(420, 700), (360, 700), (360, uy)], )
    a.line([(360, uy), (ux, uy)], end='triangle')
    # Administrador hereda de Empleado
    a.link('Administrador', 't', 'Empleado', 'b', end='triangle')

    # asociaciones (puntos de conexion alineados para lineas rectas)
    A = a.A
    a.link('Cliente', A('Cliente', 'r', 350), 'Vehiculo', A('Vehiculo', 'l', 350), label='posee', loff=(0, -14), m1='1', m2='0..*')
    a.link('Modelo', A('Modelo', 'r', 100), 'Tipo', A('Tipo', 'l', 100), label='mapea a', loff=(0, -14), m1='*', m2='1')
    a.link('Vehiculo', 't', 'Tipo', 'b', label='es de tipo', loff=(52, 0), m1='*', m2='1')
    a.link('Vehiculo', A('Vehiculo', 'r', 350), 'Reserva', A('Reserva', 'l', 350), label='tiene', loff=(0, -14), m1='1', m2='0..*')
    a.link('Reserva', A('Reserva', 'r', 400), 'Servicio', A('Servicio', 'l', 400), label='contrata', loff=(0, -14), m1='*', m2='1')
    a.link('Servicio', 't', 'Precio', 'b', label='tiene precios', loff=(-58, 0), m1='1', m2='1..*')
    a.link('Tipo', A('Tipo', 'r', 90), 'Precio', A('Precio', 'l', 90), label='aplica a', loff=(0, -14), m1='1', m2='*')
    a.link('Reserva', 'b', 'Solicitud', 't', label='genera', loff=(40, 0), m1='1', m2='0..*')
    a.link('Empleado', A('Empleado', 'r', 760), 'Solicitud', A('Solicitud', 'l', 760), label='resuelve', loff=(0, -14), m1='0..1', m2='0..*')
    a.link('Administrador', A('Administrador', 'r', 960), 'Bloqueo', A('Bloqueo', 'l', 960), label='crea', loff=(0, -14), m1='1', m2='0..*')
    # dependencia: reserva copia el precio acordado
    a.shape(None, 'note', 1260, 960, 560, 180,
            'EstadoReserva = Reservada | Cancelada | Ausente\nEstadoSolicitud = Pendiente | Aprobada | Rechazada\nTipoSolicitud = Cambio | Cancelacion\n\nLa reserva conserva precioAcordado y duracionMinutos del momento en que se creó.\nEl enlace público (tokenPublico) permite ver, cambiar o cancelar la reserva sin cuenta hasta tokenVence. Reservar no exige cuenta: se crea un cliente ocasional.\nSi tipoAConfirmar = verdadero, el empleado confirma la categoría al registrar la llegada.',
            12, align='left')

    # =============== 04b: lavados, notificaciones, trazabilidad y contenido ===============
    b = Dia('04b_diagrama_clases_lavados_contenido', 1880, 980)
    A_ = None
    b.klass('Notificacion', 40, 30, 330, 'Notificacion',
            ['-id : int', '-titulo : String', '-mensaje : String', '-leida : boolean', '-fechaHora : DateTime',
             '-canal : CanalNotificacion'],
            ['+marcarLeida() : void', '+enviarPorCorreo() : void'])
    b.klass('Usuario', 560, 70, 260, 'Usuario', [], [], stereo='(ver 04a)', italic=True, compact=True)
    b.klass('Registro', 1010, 30, 330, 'RegistroOperacion',
            ['-id : int', '-accion : String', '-entidad : String', '-idEntidad : int', '-detalle : String',
             '-fechaHora : DateTime'], ['+registrar() : void'])
    b.klass('Empleado', 560, 330, 260, 'Empleado', [], [], stereo='(ver 04a)', compact=True)
    b.klass('Admin', 560, 560, 260, 'Administrador', [], [], stereo='(ver 04a)', compact=True)
    b.klass('Lavado', 1010, 330, 330, 'Lavado',
            ['-id : int', '-horaLlegada : Time', '-precio : double', '-estado : EstadoLavado'],
            ['+registrarLlegada() : void', '+marcarTerminado() : void'])
    b.klass('Pregunta', 40, 480, 300, 'PreguntaFrecuente',
            ['-id : int', '-pregunta : String', '-respuesta : String', '-orden : int', '-activa : boolean'], [])
    b.klass('Galeria', 40, 680, 300, 'ImagenGaleria',
            ['-id : int', '-archivo : String', '-titulo : String', '-orden : int', '-activa : boolean'], [])
    b.klass('ReservaS', 1560, 330, 260, 'Reserva', [], [], stereo='(ver 04a)', compact=True)
    b.klass('VehiculoS', 1560, 418, 260, 'Vehiculo', [], [], stereo='(ver 04a)', compact=True)
    b.klass('ServicioS', 1010, 600, 330, 'Servicio', [], [], stereo='(ver 04a)', compact=True)
    A = b.A
    b.link('Empleado', 't', 'Usuario', 'b', end='triangle')
    b.link('Admin', 't', 'Empleado', 'b', end='triangle')
    b.link('Usuario', 'l', 'Notificacion', A('Notificacion', 'r', 92), label='recibe', loff=(0, -14), m1='1', m2='0..*')
    b.link('Usuario', 'r', 'Registro', A('Registro', 'l', 92), label='realiza', loff=(0, -14), m1='1', m2='0..*')
    b.link('Empleado', A('Empleado', 'r', 352), 'Lavado', A('Lavado', 'l', 352), label='registra', loff=(0, -14), m1='1', m2='0..*')
    b.link('Lavado', A('Lavado', 'r', 352), 'ReservaS', A('ReservaS', 'l', 352), label='origina', loff=(0, -14), m1='0..1', m2='0..1')
    b.link('Lavado', A('Lavado', 'r', 440), 'VehiculoS', A('VehiculoS', 'l', 440), label='de', loff=(0, -14), m1='*', m2='1')
    b.link('Lavado', 'b', 'ServicioS', 't', label='de', loff=(30, 0), m1='*', m2='1')
    b.link('Admin', A('Admin', 'l', 582), 'Pregunta', A('Pregunta', 'r', 582), label='administra', loff=(0, -14), m1='1', m2='0..*')
    b.link('Admin', A('Admin', 'b', 640), 'Galeria', A('Galeria', 'r', 770), via=[(640, 770)], label='administra', loff=(0, 0), m1='1', m2='0..*')
    b.shape(None, 'note', 560, 820, 330, 110,
            'CanalNotificacion = Interna | Correo\nEstadoLavado = Pendiente | Terminado', 12, align='left')
    b.shape(None, 'note', 1010, 740, 440, 170,
            'RegistroOperacion es de solo inserción: no se edita ni se elimina. Guarda quién hizo cada cambio y los valores anterior y nuevo en detalle.\n\nLas preguntas frecuentes y la galería alimentan la página de inicio y las administra el Administrador.',
            12, align='left')
    return [a, b]
