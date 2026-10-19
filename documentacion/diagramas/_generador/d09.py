# -*- coding: utf-8 -*-
from lib import *


def estados():
    d = Dia('08_estados_reserva', 1500, 960)
    d.shape(None, 'rect', 30, 30, 1440, 510, '', 14, dashed=True, fill=False)
    d.text(45, 36, 600, 26, 'Estado de la reserva (estado administrativo)', 16, bold=True, align='left')
    d.shape('i1', 'initial', 70, 248, 24, 24)
    d.shape('res', 'round', 220, 220, 240, 80, 'Reservada', 18, bold=True)
    d.shape('can', 'round', 900, 110, 240, 80, 'Cancelada', 18, bold=True)
    d.shape('aus', 'round', 900, 400, 240, 80, 'Ausente', 18, bold=True)
    d.link('i1', 'r', 'res', 'l', end='block', label='reserva creada\n(precio y duración acordados)', loff=(-2, -42))
    d.link('res', 'r:0.2', 'can', 'l', end='open', label='cancelar [2 horas o más\nde anticipación]', loff=(0, 0))
    d.line([(340, 220), (340, 75), (1020, 75), (1020, 110)], end='open', src='res', sa=(0.5, 0), tgt='can', sb=(0.5, 0),
           label='solicitud de cancelación aprobada [menos de 2 horas]')
    d.link('res', 'r:0.8', 'aus', 'l', end='open', label='registrarAusencia\n[el cliente no se presentó]')
    d.line([(300, 300), (300, 350), (400, 350), (400, 300)], end='open', label='', src='res', sa=(0.33, 1), tgt='res', sb=(0.67, 1))
    d.text(190, 356, 330, 40, 'modificar fecha u horario\n[2 horas o más, o solicitud aprobada]', 12)
    d.shape(None, 'note', 1190, 100, 250, 100, 'Las reservas canceladas y las ausencias permanecen en el historial.', 12, align='left')
    d.shape(None, 'note', 1190, 400, 250, 100, 'Registrar la llegada crea el lavado; la reserva sigue en su estado administrativo.', 12, align='left')
    d.shape(None, 'note', 1190, 250, 250, 110, 'El cliente cancela o modifica con su enlace seguro (o código y teléfono). No necesita cuenta.', 12, align='left')

    d.shape(None, 'rect', 30, 570, 690, 350, '', 14, dashed=True, fill=False)
    d.text(45, 576, 600, 26, 'Estado del lavado (propio, independiente)', 16, bold=True, align='left')
    d.shape('i2', 'initial', 70, 738, 24, 24)
    d.shape('pen', 'round', 160, 715, 190, 70, 'Pendiente', 18, bold=True)
    d.shape('ter', 'round', 480, 715, 190, 70, 'Terminado', 18, bold=True)
    d.link('i2', 'r', 'pen', 'l', end='block', label='llegada\nregistrada', loff=(0, -30))
    d.link('pen', 'r', 'ter', 'l', end='open', label='marcarTerminado()', loff=(0, -14))
    d.text(50, 840, 640, 50, 'Solo existen dos estados: Pendiente y Terminado (no hay estado «En proceso»).', 13, italic=True)

    d.shape(None, 'rect', 780, 570, 690, 350, '', 14, dashed=True, fill=False)
    d.text(795, 576, 650, 26, 'Categoría del vehículo (propia, independiente)', 16, bold=True, align='left')
    d.shape('i4', 'initial', 810, 738, 24, 24)
    d.shape('dec2', 'diamond', 870, 730, 40, 40)
    d.shape('ac', 'round', 1010, 630, 230, 70, 'A confirmar', 18, bold=True)
    d.shape('co', 'round', 1010, 800, 230, 70, 'Confirmada', 18, bold=True)
    d.link('i4', 'r', 'dec2', 'l', end='block')
    d.link('dec2', 't', 'ac', 'l', via=[(890, 665)], end='open', label='[eligió «No estoy seguro»]', loff=(40, -16))
    d.link('dec2', 'b', 'co', 'l', via=[(890, 835)], end='open', label='[eligió una categoría]', loff=(30, 16))
    d.line([(1240, 665), (1330, 665), (1330, 835), (1240, 835)], end='open', src='ac', sa=(1, 0.5), tgt='co', sb=(1, 0.5),
           label='confirmarCategoría()\n[al registrar\nla llegada]', loff=(0, 0))
    d.text(795, 880, 660, 34, 'Si el cambio de categoría modifica el precio, el empleado informa al cliente y queda en el historial de operaciones.', 12, italic=True)
    return d


def actividades():
    d = Dia('09_actividades_reservar_turno', 1150, 1600)
    d.shape(None, 'rect', 40, 30, 460, 1540, 'Cliente (sin cuenta)', 16, bold=True, valign='top', fill=False)
    d.shape(None, 'rect', 500, 30, 610, 1540, 'Sistema WashFlow', 16, bold=True, valign='top', fill=False)
    d.shape('ini', 'initial', 258, 78, 24, 24)
    d.shape('veh', 'round', 160, 135, 220, 64, 'Elegir la categoría o buscar\nmarca y modelo', 14)
    d.shape('srv', 'round', 170, 235, 200, 50, 'Seleccionar servicio', 14)
    d.shape('mrg', 'diamond', 250, 330, 40, 40)
    d.shape('fec', 'round', 170, 400, 200, 50, 'Seleccionar fecha\ny horario', 14)
    d.shape('pre', 'round', 600, 400, 200, 50, 'Calcular precio\ny duración', 14)
    d.shape('hor', 'round', 600, 495, 200, 60, 'Validar horario de atención\n(lun–sáb, 10:00–18:00)', 13)
    d.shape('cap', 'round', 600, 595, 200, 60, 'Comprobar capacidad\n(máx. 3 vehículos)', 13)
    d.shape('dec', 'diamond', 585, 695, 230, 100, '¿Hay\ndisponibilidad?', 14)
    d.shape('inf', 'round', 885, 720, 200, 50, 'Informar que no hay\ndisponibilidad', 13)
    d.shape('cont', 'round', 160, 830, 220, 60, 'Ingresar nombre, teléfono\ny correo (opcional)', 13)
    d.shape('val', 'round', 600, 935, 200, 90, 'Validar datos y límites\n(campo trampa, IP, máx. 3\nreservas activas, teléfono)', 12)
    d.shape('dec2', 'diamond', 585, 1075, 230, 100, '¿Datos\nválidos?', 14)
    d.shape('err', 'round', 885, 1100, 200, 50, 'Informar el error', 13)
    d.shape('oca', 'round', 600, 1225, 200, 60, 'Buscar o crear\ncliente ocasional', 13)
    d.shape('reg', 'round', 600, 1320, 200, 60, 'Registrar la reserva con precio\nacordado, código y token', 12)
    d.shape('con', 'round', 600, 1415, 200, 60, 'Generar notificación y\nconfirmar la reserva', 13)
    d.shape('ver', 'round', 160, 1415, 220, 60, 'Ver código y enlace;\ncrear cuenta (opcional)', 13)
    d.shape('fin', 'final', 258, 1520, 24, 24)
    d.shape(None, 'note', 540, 120, 270, 80, 'Si elige «No estoy seguro», la categoría queda marcada «a confirmar» hasta la llegada.', 12, align='left')
    d.link('ini', 'b', 'veh', 't', end='open')
    d.link('veh', 'b', 'srv', 't', end='open')
    d.link('srv', 'b', 'mrg', 't', end='open')
    d.link('mrg', 'b', 'fec', 't', end='open')
    d.link('fec', 'r', 'pre', 'l', end='open')
    d.link('pre', 'b', 'hor', 't', end='open')
    d.link('hor', 'b', 'cap', 't', end='open')
    d.link('cap', 'b', 'dec', 't', end='open')
    d.link('dec', 'r', 'inf', 'l', end='open', label='[no]', loff=(0, -12))
    d.link('dec', 'l', 'cont', 't', via=[(270, 745)], end='open', label='[sí]', loff=(0, -12))
    d.link('cont', 'b', 'val', 'l', via=[(270, 980)], end='open')
    d.link('val', 'b', 'dec2', 't', end='open')
    d.link('dec2', 'r', 'err', 'l', end='open', label='[no]', loff=(0, -12))
    d.link('dec2', 'b', 'oca', 't', end='open', label='[sí]', loff=(22, 0))
    d.link('oca', 'b', 'reg', 't', end='open')
    d.link('reg', 'b', 'con', 't', end='open')
    d.link('con', 'l', 'ver', 'r', end='open')
    d.link('ver', 'b', 'fin', 't', end='open')
    d.line([(985, 720), (985, 350), (290, 350)], end='open', src='inf', sa=(0.5, 0), tgt='mrg', sb=(1, 0.5), label='volver a elegir horario', loff=(-250, 0))
    d.line([(985, 1100), (985, 860), (380, 860)], end='open', src='err', sa=(0.5, 0), tgt='cont', sb=(1, 0.5), label='corregir los datos', loff=(-250, 0))
    return d


def despliegue():
    d = Dia('10_despliegue', 1800, 900)
    d.shape('cli', 'cube', 40, 100, 330, 240, '«dispositivo»\nEquipo del cliente', 15, bold=True, valign='top')
    d.shape('nav', 'component', 90, 170, 230, 90, '«navegador»\nNavegador web', 14)
    d.shape('srv', 'cube', 520, 40, 640, 780, '«entorno de ejecución»\nServidor local — XAMPP', 15, bold=True, valign='top')
    d.shape('apa', 'component', 570, 170, 280, 90, '«servidor web»\nApache (XAMPP)', 14)
    d.shape('app', 'component', 570, 370, 280, 110, '«aplicación»\nAplicación web WashFlow\n(PHP)', 14)
    d.shape('bd', 'cylinder', 600, 600, 220, 130, '«base de datos»\nMySQL (XAMPP)', 14)
    d.shape('mail', 'cube', 1380, 330, 380, 190, '«servicio externo, opcional»\nServicio de correo electrónico', 15, bold=True, valign='top')
    d.link('nav', 'r', 'apa', 'l', end='open', label='HTTP / HTTPS', loff=(0, -14))
    d.link('apa', 'b', 'app', 't', end='open', label='ejecuta PHP', loff=(60, 0))
    d.link('app', 'b', 'bd', 't', end='open', label='MySQL, puerto 3306', loff=(80, 0))
    d.link('app', 'r', 'mail', A := d.A('mail', 'l', 425), end='open', dashed=True, label='SMTP (opcional)', loff=(0, -14)) if False else None
    d.line([(850, 425), (1380, 425)], end='open', dashed=True, src='app', sa=(1, (425 - 370) / 110), tgt='mail', sb=d.A('mail', 'l', 425),
           label='SMTP (opcional)', loff=(0, -14))
    d.shape('carp', 'component', 900, 600, 230, 110, '«carpeta»\nImágenes de la galería\n(public/assets/img)', 13)
    d.link('app', 'r:0.9', 'carp', 't', via=[(1015, 469)], end='open', label='lee y guarda\narchivos', loff=(70, 0))
    d.shape(None, 'note', 40, 400, 400, 120,
            'Entorno de desarrollo local: Apache y MySQL se ejecutan con XAMPP en la misma computadora (localhost).',
            13, align='left')
    d.shape(None, 'note', 1380, 580, 380, 110,
            'El envío de correos requiere configurar una cuenta y un servidor SMTP. Sin esa configuración se usan solo notificaciones internas.',
            13, align='left')
    return d


def componentes():
    d = Dia('11_componentes', 1500, 940)
    d.shape(None, 'rect', 30, 30, 1440, 750, 'Aplicación web WashFlow (PHP)', 16, bold=True, valign='top', dashed=True, fill=False)
    d.shape('aut', 'component', 80, 80, 1340, 60, 'Autenticación y permisos', 16, bold=True)
    w, h = 280, 80
    d.shape('cli', 'component', 80, 230, w, h, 'Clientes y vehículos', 15)
    d.shape('not', 'component', 420, 230, w, h, 'Notificaciones', 15)
    d.shape('srv', 'component', 760, 230, w, h, 'Servicios, precios y\nmodelos de vehículo', 15)
    d.shape('cont', 'component', 1100, 230, w, h, 'Contenido del sitio\n(preguntas, galería, guía)', 15)
    d.shape('pub', 'component', 80, 430, w, h, 'Reserva pública\n(sin cuenta)', 15)
    d.shape('res', 'component', 420, 430, w, h, 'Reservas', 15)
    d.shape('lav', 'component', 760, 430, w, h, 'Lavados', 15)
    d.shape('reg', 'component', 1100, 430, w, h, 'Registro de operaciones', 15)
    d.shape('inf', 'component', 590, 630, w, h, 'Informes y estadísticas', 15)
    d.shape('bd', 'component', 80, 840, 1340, 60, 'Base de datos (MySQL)', 16, bold=True)
    for k in ('cli', 'not', 'srv', 'cont'):
        cx = d.keys[k]['x'] + 140
        d.line([(cx, 230), (cx, 140)], end='open', dashed=True, src=k, sa=(0.5, 0), tgt='aut', sb=d.A('aut', 'b', cx))
    def dep(a, xa, ya, b, xb, yb, **kw):
        d.line([(xa, ya), (xb, yb)], end='open', dashed=True, src=a, sa=((xa - d.keys[a]['x']) / d.keys[a]['w'], (ya - d.keys[a]['y']) / d.keys[a]['h']),
               tgt=b, sb=((xb - d.keys[b]['x']) / d.keys[b]['w'], (yb - d.keys[b]['y']) / d.keys[b]['h']), **kw)
    dep('res', 480, 430, 'cli', 300, 310)
    dep('res', 560, 430, 'not', 560, 310)
    dep('res', 640, 430, 'srv', 820, 310)
    dep('lav', 960, 430, 'srv', 960, 310)
    dep('lav', 760, 470, 'res', 700, 470)
    dep('pub', 360, 470, 'res', 420, 470)
    dep('pub', 220, 430, 'cli', 220, 310)
    dep('inf', 650, 630, 'res', 540, 510)
    dep('inf', 830, 630, 'lav', 880, 510)
    dep('lav', 1040, 470, 'reg', 1100, 470)
    d.line([(750, 780), (750, 840)], end='open', dashed=True, label='«usa» (SQL)', loff=(70, 0))
    d.shape(None, 'note', 1010, 620, 410, 120,
            'Todos los módulos validan los permisos mediante «Autenticación y permisos» y guardan sus datos en la base de datos. Lavados, reservas y categorías dejan huella en el registro de operaciones.',
            13, align='left')
    return d


def build():
    return [estados(), actividades(), despliegue(), componentes()]
