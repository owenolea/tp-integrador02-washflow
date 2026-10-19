# -*- coding: utf-8 -*-
from lib import *


class Seq:
    def __init__(self, d, names, xs, y0=130, step=50, w=220, head=50, yend=0):
        self.d, self.y, self.step = d, y0, step
        self.ids = {}
        for k, (n, x) in zip(names, xs):
            d.lifeline(k, x, 30, w, n, yend, head=head)
        self.rec = []

    def m(self, a, b, text, kind='sync', extra=0):
        y = self.y
        self.d.msg(a, b, y, text, kind)
        self.rec.append((a, b, y))
        self.y += self.step + extra
        return y

    def me(self, a, text, h=34, wtxt=240, kind='sync'):
        y = self.y
        self.d.selfmsg(a, y, text, h=h, wtxt=wtxt, reply=(kind == 'reply'))
        self.rec.append((a, a, y))
        self.y += h + self.step - 10
        return y

    def close(self, extra=36):
        yend = self.y + extra
        for it in self.d.items:
            if it['t'] == 'shape' and it['kind'] == 'lifeline':
                it['h'] = yend - it['y']
        self.d.h = int(yend + 40)

    def bar(self, k, y1, y2):
        s = self.d.keys[k]
        self.d.bar(s['x'] + s['w'] / 2, y1, y2)


def seq5():
    d = Dia('05_secuencia_reservar_turno', 1650, 1900)
    names = ['cli', 'web', 'sis', 'bd', 'not']
    xs = [('Cliente (sin cuenta)', 170), ('Interfaz web', 490), ('Sistema de reservas', 810), ('Base de datos', 1130), ('Servicio de notificaciones', 1450)]
    s = Seq(d, names, xs, yend=1860, w=230)
    # paso 1: vehiculo
    a1 = s.m('cli', 'web', 'Elige la categoría o busca\nsu marca y modelo', extra=14)
    a2 = s.m('web', 'sis', 'buscarModelo(texto)')
    a3 = s.m('sis', 'bd', 'consultarModelos(texto)')
    a4 = s.m('bd', 'sis', 'modelos y categorías', 'reply')
    a5 = s.m('sis', 'web', 'coincidencias (marca, modelo,\ncategoría)', 'reply', extra=14)
    a6 = s.m('web', 'cli', 'Muestra la categoría, la duración\ny el precio del vehículo', 'reply', extra=14)
    # pasos 2 y 3: servicio, fecha y horario
    b1 = s.m('cli', 'web', 'Elige el servicio, la fecha\ny el horario', extra=14)
    b2 = s.m('web', 'sis', 'consultarDisponibilidad(categoría,\nservicio, fecha)', extra=14)
    b3 = s.m('sis', 'bd', 'consultarReservasYBloqueos(fecha)')
    b4 = s.m('bd', 'sis', 'reservas y bloqueos', 'reply')
    b5 = s.m('sis', 'web', 'franjas disponibles, precio\ny duración', 'reply', extra=14)
    b6 = s.m('web', 'cli', 'Muestra solo los horarios libres', 'reply')
    # paso 4: contacto
    c1 = s.m('cli', 'web', 'Ingresa nombre, teléfono y\ncorreo (opcional)', extra=14)
    c2 = s.m('web', 'sis', 'solicitarReserva(vehículo, servicio,\nfecha, horario, contacto)', extra=14)
    c3 = s.me('sis', 'Valida horario (lun–sáb, 10:00–18:00), capacidad (máx. 3 vehículos), campo trampa, límite por IP y máx. 3 reservas activas por teléfono', h=60, wtxt=250)
    ya = s.y + 4
    s.y = ya + 66
    d1 = s.m('sis', 'bd', 'buscarOCrearClienteOcasional(\nteléfono, correo)', extra=14)
    d2 = s.m('bd', 'sis', 'cliente', 'reply')
    d3 = s.m('sis', 'bd', 'guardarReserva(precioAcordado, código,\ntokenPúblico, estado = Reservada)', extra=14)
    d4 = s.m('bd', 'sis', 'reserva registrada', 'reply')
    d5 = s.m('sis', 'not', 'generarNotificación(reserva creada)')
    d6 = s.m('not', 'sis', 'notificación generada', 'reply')
    d7 = s.m('sis', 'web', 'reserva confirmada\n(código y enlace seguro)', 'reply', extra=14)
    d8 = s.m('web', 'cli', 'Muestra el código, el enlace y\nofrece crear una cuenta (opcional)', 'reply', extra=14)
    ydiv = s.y - 4
    s.y = ydiv + 70
    e1 = s.m('sis', 'web', 'turno no disponible o\nlímite de reservas superado', 'reply', extra=14)
    e2 = s.m('web', 'cli', 'Informa el problema y permite\nelegir otro horario', 'reply', extra=14)
    yfin = s.y - 20
    d.frag(40, ya, 1570, yfin - ya, 'alt', 'datos válidos y turno disponible', divs=[(ydiv, 'turno no disponible o límite superado')])
    s.bar('web', a1, a6 + 1); s.bar('sis', a2, a5); s.bar('bd', a3, a4)
    s.bar('web', b1, b6 + 1); s.bar('sis', b2, b5); s.bar('bd', b3, b4)
    s.bar('web', c1, d8 + 1); s.bar('sis', c2, d8)
    s.bar('bd', d1, d2); s.bar('bd', d3, d4); s.bar('not', d5, d6)
    s.bar('web', e1, e2 + 1); s.bar('sis', e1 - 8, e1 + 10)
    s.close(0)
    return d


def seq6():
    d = Dia('06_secuencia_registrar_lavado', 1540, 1500)
    names = ['emp', 'web', 'sis', 'bd']
    xs = [('Empleado', 200), ('Interfaz web', 590), ('Sistema de lavados', 980), ('Base de datos', 1340)]
    s = Seq(d, names, xs, yend=1460, w=230)
    a1 = s.m('emp', 'web', 'Busca la reserva (cliente, teléfono\no patente)', extra=14)
    a2 = s.m('web', 'sis', 'buscarReserva(criterio)')
    a3 = s.m('sis', 'bd', 'consultarReserva(criterio)')
    a4 = s.m('bd', 'sis', 'datos de la reserva', 'reply')
    a5 = s.m('sis', 'web', 'datos: cliente, vehículo,\nservicio y horario', 'reply', extra=14)
    a6 = s.m('web', 'emp', 'Muestra los datos de la reserva', 'reply')
    s.y += 10
    b1 = s.m('emp', 'web', 'Registra la llegada del cliente')
    b2 = s.m('web', 'sis', 'registrarLlegada(idReserva)')
    b3 = s.m('sis', 'bd', 'guardarLlegada(idReserva, hora)\ncrear lavado (estado = Pendiente)', extra=14)
    b4 = s.m('bd', 'sis', 'llegada guardada', 'reply')
    b5 = s.m('sis', 'web', 'llegada registrada', 'reply')
    b6 = s.m('web', 'emp', 'Confirma la llegada (lavado Pendiente)', 'reply')
    # categoria a confirmar (reserva con "No estoy seguro")
    yo = s.y + 4
    s.y = yo + 66
    k1 = s.m('emp', 'web', 'Confirma o corrige la categoría\ndel vehículo', extra=14)
    k2 = s.m('web', 'sis', 'confirmarCategoría(idVehículo, tipo,\nclienteInformado)', extra=14)
    k3 = s.m('sis', 'bd', 'actualizarTipo(idVehículo, tipo)\nregistrar operación (valor anterior y nuevo)', extra=14)
    k4 = s.m('bd', 'sis', 'categoría actualizada', 'reply')
    k5 = s.me('sis', 'Si cambia el precio: notifica al Administrador', h=40, wtxt=250)
    k6 = s.m('sis', 'web', 'categoría confirmada', 'reply')
    k7 = s.m('web', 'emp', 'Muestra la categoría y el precio vigente', 'reply')
    yoe = s.y - 16
    d.frag(40, yo, 1470, yoe - yo, 'opt', 'tipo a confirmar')
    s.bar('web', k1, k7 + 1); s.bar('sis', k2, k6); s.bar('bd', k3, k4)
    s.y = yoe + 34
    yl = s.y - 10
    d.text(200 - 110, yl - 6, 1120 + 220, 28, '— Se realiza el lavado del vehículo —', 13, italic=True)
    s.y += 36
    c1 = s.m('emp', 'web', 'Marca el lavado como Terminado')
    c2 = s.m('web', 'sis', 'marcarTerminado(idLavado)')
    c3 = s.m('sis', 'bd', 'actualizarEstado(idLavado,\nTerminado)', extra=14)
    c4 = s.m('bd', 'sis', 'estado actualizado', 'reply')
    c5 = s.m('sis', 'web', 'operación confirmada', 'reply')
    c6 = s.m('web', 'emp', 'Muestra el lavado como Terminado', 'reply')
    s.close(10)
    s.bar('web', a1, a6 + 1); s.bar('sis', a2, a5); s.bar('bd', a3, a4)
    s.bar('web', b1, b6 + 1); s.bar('sis', b2, b5); s.bar('bd', b3, b4)
    s.bar('web', c1, c6 + 1); s.bar('sis', c2, c5); s.bar('bd', c3, c4)
    return d


def seq7():
    d = Dia('07_secuencia_lavado_sin_reserva', 1540, 2010)
    names = ['emp', 'web', 'sis', 'bd']
    xs = [('Empleado', 200), ('Interfaz web', 590), ('Sistema de lavados', 980), ('Base de datos', 1340)]
    s = Seq(d, names, xs, yend=1970, w=230)
    bars = []
    a1 = s.m('emp', 'web', 'Busca al cliente por\nteléfono o correo', extra=14)
    a2 = s.m('web', 'sis', 'buscarCliente(teléfono o correo)')
    a3 = s.m('sis', 'bd', 'consultarCliente(teléfono o correo)')
    a4 = s.m('bd', 'sis', 'resultado de la búsqueda', 'reply')
    y_alt1 = s.y - 6
    s.y = y_alt1 + 62
    b1 = s.m('sis', 'web', 'datos del cliente encontrado', 'reply')
    b2 = s.m('web', 'emp', 'Muestra el cliente y el empleado\nlo selecciona', 'reply', extra=14)
    y_div1 = s.y - 6
    s.y = y_div1 + 62
    c1 = s.m('sis', 'web', 'cliente no encontrado', 'reply')
    c2 = s.m('emp', 'web', 'Registra un cliente ocasional\n(nombre y teléfono)', extra=14)
    c3 = s.m('web', 'sis', 'registrarClienteOcasional(datos)')
    c4 = s.m('sis', 'bd', 'guardarCliente(esOcasional = true)')
    c5 = s.m('bd', 'sis', 'cliente guardado', 'reply')
    y_end1 = s.y - 16
    d.frag(40, y_alt1, 1470, y_end1 - y_alt1, 'alt', 'cliente existente', divs=[(y_div1, 'cliente nuevo: se registra como ocasional')])
    s.y = y_end1 + 44
    d1 = s.m('emp', 'web', 'Selecciona o registra el vehículo')
    d2 = s.m('web', 'sis', 'asociarVehículo(cliente, patente, tipo)')
    d3 = s.m('sis', 'bd', 'consultarOGuardarVehículo(patente, tipo)')
    d4 = s.m('bd', 'sis', 'vehículo del cliente', 'reply')
    e1 = s.m('emp', 'web', 'Selecciona el servicio')
    e2 = s.m('web', 'sis', 'calcularPrecio(servicio, tipoVehículo)')
    e3 = s.m('sis', 'bd', 'consultarPrecioYDuración(servicio,\ntipoVehículo)', extra=14)
    e4 = s.m('bd', 'sis', 'precio y duración', 'reply')
    f1 = s.m('sis', 'bd', 'consultarLavadosSimultáneos(horario)')
    f2 = s.m('bd', 'sis', 'lavados que se superponen', 'reply')
    f3 = s.me('sis', 'Comprueba la capacidad: máximo 3 vehículos simultáneos', h=40, wtxt=250)
    y_alt2 = s.y + 2
    s.y = y_alt2 + 62
    g1 = s.m('sis', 'bd', 'guardarLavado(precio, estado = Pendiente)')
    g2 = s.m('bd', 'sis', 'lavado guardado', 'reply')
    g3 = s.m('sis', 'web', 'registro confirmado', 'reply')
    g4 = s.m('web', 'emp', 'Muestra la confirmación del registro', 'reply')
    y_div2 = s.y - 6
    s.y = y_div2 + 62
    h1 = s.m('sis', 'web', 'no hay capacidad disponible', 'reply')
    h2 = s.m('web', 'emp', 'Informa que no hay lugar para\notro vehículo en ese horario', 'reply', extra=14)
    y_end2 = s.y - 16
    d.frag(40, y_alt2, 1470, y_end2 - y_alt2, 'alt', 'hay capacidad', divs=[(y_div2, 'sin capacidad (3 vehículos en el horario)')])
    s.bar('web', a1, b2 + 1); s.bar('sis', a2, b1); s.bar('bd', a3, a4)
    s.bar('web', c2, c3 + 1); s.bar('sis', c3, c5); s.bar('bd', c4, c5)
    s.bar('web', d1, d2 + 1); s.bar('sis', d2, d4); s.bar('bd', d3, d4)
    s.bar('web', e1, e2 + 1); s.bar('sis', e2, f3 + 40); s.bar('bd', e3, e4); s.bar('bd', f1, f2)
    s.bar('bd', g1, g2); s.bar('web', g3, g4 + 1); s.bar('web', h1, h2 + 1); s.bar('sis', h1 - 8, h1 + 10)
    s.close(0)
    return d


def seq13():
    d = Dia('12_secuencia_gestionar_reserva_con_enlace', 1650, 1500)
    names = ['cli', 'web', 'sis', 'bd', 'not']
    xs = [('Cliente (sin cuenta)', 170), ('Interfaz web', 490), ('Sistema de reservas', 810), ('Base de datos', 1130), ('Servicio de notificaciones', 1450)]
    s = Seq(d, names, xs, yend=1460, w=230)
    a1 = s.m('cli', 'web', 'Abre el enlace seguro o ingresa\ncódigo y teléfono', extra=14)
    a2 = s.m('web', 'sis', 'obtenerReserva(token o\ncódigo + teléfono)', extra=14)
    a3 = s.m('sis', 'bd', 'consultarReserva(token o\ncódigo + teléfono)', extra=14)
    a4 = s.m('bd', 'sis', 'reserva y vencimiento del token', 'reply')
    a5 = s.me('sis', 'Valida el token (vigente), la anticipación (2 horas) y la disponibilidad', h=40, wtxt=250)
    ya = s.y + 4
    s.y = ya + 66
    # rama 1: 2 horas o mas
    b1 = s.m('sis', 'bd', 'actualizarReserva(nuevo horario o\nestado = Cancelada)', extra=14)
    b2 = s.m('bd', 'sis', 'reserva actualizada', 'reply')
    b3 = s.m('sis', 'not', 'generarNotificación(reserva\nmodificada o cancelada)', extra=14)
    b4 = s.m('not', 'sis', 'notificación generada', 'reply')
    b5 = s.m('sis', 'web', 'cambio aplicado', 'reply')
    b6 = s.m('web', 'cli', 'Muestra la reserva actualizada\no cancelada', 'reply', extra=14)
    yd1 = s.y - 4
    s.y = yd1 + 70
    # rama 2: menos de 2 horas
    c1 = s.m('sis', 'bd', 'guardarSolicitud(tipo, motivo,\nestado = Pendiente)', extra=14)
    c2 = s.m('bd', 'sis', 'solicitud registrada', 'reply')
    c3 = s.m('sis', 'not', 'generarNotificación(solicitud enviada)')
    c4 = s.m('not', 'sis', 'notificación generada', 'reply')
    c5 = s.m('sis', 'web', 'solicitud pendiente', 'reply')
    c6 = s.m('web', 'cli', 'Informa que un empleado resolverá\nla solicitud', 'reply', extra=14)
    yd2 = s.y - 4
    s.y = yd2 + 70
    # rama 3: token vencido
    e1 = s.m('sis', 'web', 'enlace vencido o datos incorrectos', 'reply')
    e2 = s.m('web', 'cli', 'Informa el error', 'reply')
    yfin = s.y - 10
    d.frag(40, ya, 1570, yfin - ya, 'alt', '2 horas o más de anticipación y hay disponibilidad',
           divs=[(yd1, 'menos de 2 horas: se envía una solicitud'), (yd2, 'enlace vencido o datos incorrectos')])
    s.bar('web', a1, e2 + 1); s.bar('sis', a2, c6)
    s.bar('bd', a3, a4); s.bar('bd', b1, b2); s.bar('bd', c1, c2)
    s.bar('not', b3, b4); s.bar('not', c3, c4)
    s.close(0)
    return d


def build():
    return [seq5(), seq6(), seq7(), seq13()]
