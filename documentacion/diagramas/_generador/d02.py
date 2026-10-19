# -*- coding: utf-8 -*-
from lib import *


def build():
    d = Dia('02_casos_uso_reservas', 1700, 1220)
    d.shape('lim', 'rect', 170, 30, 1300, 1170, 'Sistema WashFlow — Reservas y lavados', 18, bold=True, valign='top', fill=False)
    d.actor('cli', 60, 480, 'Cliente')
    d.actor('emp', 1560, 640, 'Empleado')
    d.actor('adm', 1560, 940, 'Administrador')

    X1, X2, X3 = 340, 820, 1290
    d.uc('disp', X1, 170, 'Consultar disponibilidad')
    d.uc('crear', X1, 350, 'Crear reserva\n(sin cuenta)')
    d.uc('cont', X1, 480, 'Ingresar datos\nde contacto')
    d.uc('acc', X1, 900, 'Acceder a la reserva\n(enlace o código + teléfono)')

    d.uc('buscar', 700, 108, 'Buscar modelo\nen el catálogo', w=190)
    d.uc('nose', 940, 108, 'Indicar «No estoy\nseguro»', w=190)
    d.uc('veh', X2, 200, 'Seleccionar vehículo', w=250)
    d.uc('serv', X2, 300, 'Seleccionar servicio', w=250)
    d.uc('prec', X2, 400, 'Calcular precio', w=250)
    d.uc('cap', X2, 500, 'Validar capacidad', w=250)
    d.uc('mod', X2, 740, 'Modificar reserva', w=250)
    d.uc('sol_c', X2, 860, 'Solicitar cambio', w=250)
    d.uc('canc', X2, 980, 'Cancelar reserva', w=250)
    d.uc('sol_x', X2, 1100, 'Solicitar cancelación', w=250)

    d.uc('lleg', X3, 170, 'Registrar llegada\ndel cliente', w=250)
    d.uc('conf', X3, 285, 'Confirmar o corregir\ncategoría del vehículo', w=250)
    d.uc('sinres', X3, 400, 'Registrar lavado\nsin reserva', w=250)
    d.uc('term', X3, 540, 'Marcar lavado\ncomo terminado', w=250)
    d.uc('aus', X3, 630, 'Registrar ausencia', w=250)
    d.uc('resol', X3, 860, 'Resolver solicitudes de\ncambio y cancelación', w=250)

    # asociaciones del cliente
    for k in ('disp', 'crear'):
        d.link('cli', 'r:0.45', k, 'l')
    d.link('cli', 'r:0.45', 'mod', 'l:0.3')
    d.link('cli', 'r:0.45', 'canc', 'l')
    # asociaciones del empleado
    for k in ('lleg', 'conf', 'sinres', 'term', 'aus', 'resol'):
        d.link('emp', 'l:0.45', k, 'r')
    d.link('emp', 'l:0.45', 'mod', 'r')

    # include: crear reserva
    d.link('crear', 't', 'disp', 'b', end='open', dashed=True, label='«include»', loff=(60, 0))
    d.link('crear', 'b', 'cont', 't', end='open', dashed=True, label='«include»', loff=(60, 0))
    for k in ('veh', 'serv', 'prec', 'cap'):
        d.link('crear', 'r', k, 'l', end='open', dashed=True, label='«include»')
    # extend: seleccionar vehículo
    d.link('buscar', 'b', 'veh', 't:0.25', end='open', dashed=True, label='«extend»', loff=(-48, 0))
    d.link('nose', 'b', 'veh', 't:0.75', end='open', dashed=True, label='«extend»', loff=(48, 0))
    # include: lavado sin reserva
    for k in ('veh', 'serv', 'prec', 'cap'):
        d.link('sinres', 'l', k, 'r', end='open', dashed=True, label='«include»')
    # include: modificar -> validar capacidad
    d.link('mod', 't:0.15', 'cap', 'b:0.15', end='open', dashed=True, label='«include»', loff=(-62, 0))
    # include: modificar y cancelar -> acceder a la reserva
    d.link('mod', 'l:0.8', 'acc', 'r', end='open', dashed=True, label='«include»', loff=(0, -14))
    d.link('canc', 'l', 'acc', 'r', end='open', dashed=True, label='«include»', loff=(0, 14))
    # extend
    d.link('sol_c', 't', 'mod', 'b', end='open', dashed=True, label='«extend»\n[menos de 2 horas]', loff=(95, 0))
    d.link('sol_x', 't', 'canc', 'b', end='open', dashed=True, label='«extend»\n[menos de 2 horas]', loff=(95, 0))
    d.link('conf', 't', 'lleg', 'b', end='open', dashed=True, label='«extend»\n[categoría a confirmar]', loff=(-0, 0)) if False else \
        d.link('conf', 't', 'lleg', 'b', end='open', dashed=True, label='«extend»', loff=(60, 0))

    # generalización
    d.line([(1575, 935), (1575, 728)], end='triangle')
    d.shape(None, 'note', 1180, 1000, 270, 90,
            'Cambiar o cancelar con 2 horas o más de anticipación se hace directamente. Con menos de 2 horas hay que enviar una solicitud.',
            12, align='left')
    d.shape(None, 'note', 1180, 1100, 270, 80,
            'Reservar no exige cuenta. Se aplican controles anti abuso: campo trampa, límite por IP y máximo de 3 reservas activas por teléfono.',
            12, align='left')
    return d
