# -*- coding: utf-8 -*-
from lib import *


def build():
    d = Dia('03_casos_uso_administracion', 1400, 1090)
    d.shape('lim', 'rect', 230, 30, 1100, 1030, 'Sistema WashFlow — Funciones administrativas', 18, bold=True, valign='top', fill=False)
    d.actor('emp', 60, 130, 'Empleado')
    d.actor('adm', 60, 620, 'Administrador')

    X = 560
    comp = [(110, 'Gestionar reservas'), (190, 'Confirmar o corregir la\ncategoría de un vehículo')]
    for i, (y, t) in enumerate(comp):
        d.uc('s%d' % i, X, y, t, w=300, h=56)
        d.link('emp', 'r:0.45', 's%d' % i, 'l')
    solo = [(330, 'Administrar clientes'), (400, 'Administrar empleados'), (470, 'Modificar precios'),
            (540, 'Configurar duraciones'), (610, 'Bloquear horarios'),
            (680, 'Administrar modelos\nde vehículo'), (750, 'Administrar preguntas\nfrecuentes'),
            (820, 'Administrar galería\nde trabajos'),
            (890, 'Consultar informes\ny estadísticas'), (960, 'Consultar historial\nde operaciones')]
    for i, (y, t) in enumerate(solo):
        d.uc('a%d' % i, X, y, t, w=300, h=56)
        d.link('adm', 'r:0.45', 'a%d' % i, 'l')

    d.line([(75, 615), (75, 215)], end='triangle')

    d.shape(None, 'note', 800, 90, 490, 80,
            'Empleado y Administrador pueden consultar y modificar reservas.', 13, align='left')
    d.shape(None, 'note', 800, 185, 490, 100,
            'Si corregir la categoría cambia el precio, el empleado informa al cliente y el Administrador recibe una notificación.',
            13, align='left')
    d.shape(None, 'note', 800, 340, 490, 80,
            'Desactivar una cuenta no borra su historial. Solo el Administrador crea y desactiva empleados.', 13, align='left')
    d.shape(None, 'note', 800, 880, 490, 110,
            'Los informes muestran la ocupación por día y los servicios más solicitados, y se pueden exportar a CSV.',
            13, align='left')
    d.shape(None, 'note', 800, 960, 490, 70,
            'El Administrador hereda los casos de uso del Empleado. El historial es de solo consulta.', 13, align='left')
    return d
