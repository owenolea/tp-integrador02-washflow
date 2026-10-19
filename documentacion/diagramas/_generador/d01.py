# -*- coding: utf-8 -*-
from lib import *


def build():
    d = Dia('01_casos_uso_general', 1450, 1030)
    d.shape('lim', 'rect', 230, 30, 1040, 960, 'Sistema WashFlow', 18, bold=True, valign='top', fill=False)
    d.actor('cli', 60, 353, 'Cliente')
    d.actor('emp', 1340, 353, 'Empleado')
    d.actor('adm', 1340, 720, 'Administrador')

    izq = [(119, 'Crear cuenta (opcional)'), (207, 'Gestionar vehículos\n(con cuenta)'),
           (295, 'Consultar servicios, precios\ny guía del lavado'),
           (471, 'Consultar categoría\nde mi vehículo'), (559, 'Reservar turno\n(sin cuenta)'),
           (647, 'Ver, modificar o cancelar\nreserva (enlace o código)'),
           (735, 'Solicitar cambio o\ncancelación fuera de plazo'), (823, 'Consultar reservas\ne historial (con cuenta)'),
           (911, 'Recibir notificaciones\n(con cuenta)')]
    for i, (y, t) in enumerate(izq):
        d.uc('c%d' % i, 440, y, t, w=250)
        d.link('cli', 'r:0.45', 'c%d' % i, 'l')

    d.uc('login', 760, 383, 'Iniciar sesión')
    d.link('cli', 'r:0.45', 'login', 'l')
    d.link('emp', 'l:0.45', 'login', 'r')

    der_e = [(95, 'Consultar y modificar\nreservas'), (163, 'Buscar clientes (teléfono,\ncorreo o patente)'),
             (231, 'Registrar lavados con\ny sin reserva'), (299, 'Registrar ausencias'),
             (467, 'Resolver solicitudes de cambio\ny cancelación'),
             (535, 'Confirmar o corregir la\ncategoría de un vehículo')]
    for i, (y, t) in enumerate(der_e):
        d.uc('e%d' % i, 1090, y, t, w=250)
        d.link('emp', 'l:0.45', 'e%d' % i, 'r')
    der_a = [(627, 'Administrar clientes\ny empleados'), (695, 'Configurar precios, duraciones\ny bloqueos de horario'),
             (763, 'Administrar modelos, preguntas\nfrecuentes y galería'),
             (831, 'Consultar informes y\nestadísticas (exportar CSV)'),
             (899, 'Consultar historial\nde operaciones')]
    for i, (y, t) in enumerate(der_a):
        d.uc('a%d' % i, 1090, y, t, w=250)
        d.link('adm', 'l:0.45', 'a%d' % i, 'r')

    d.line([(1355, 715), (1355, 448)], end='triangle')
    d.shape(None, 'note', 1285, 905, 155, 100, 'El Administrador también puede realizar los casos de uso del Empleado.', 12,
            align='left', valign='middle')
    return d
