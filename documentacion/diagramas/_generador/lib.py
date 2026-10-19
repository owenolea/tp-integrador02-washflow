# -*- coding: utf-8 -*-
"""Mini-librería para generar diagramas UML en draw.io (.drawio) y PNG
con el mismo modelo: fondo negro, líneas y textos blancos."""
import math, os, html
from PIL import Image, ImageDraw, ImageFont

WHITE = '#FFFFFF'
BLACK = '#000000'
SC = 3      # supermuestreo para suavizar
OUT = 2     # escala final del PNG
LW = 2      # grosor de línea

FD = '/usr/share/fonts/truetype/liberation/LiberationSans-'
_fc = {}


def font(size, bold=False, italic=False):
    key = (size, bold, italic)
    if key not in _fc:
        n = 'BoldItalic' if (bold and italic) else 'Bold' if bold else 'Italic' if italic else 'Regular'
        _fc[key] = ImageFont.truetype(FD + n + '.ttf', int(round(size * SC)))
    return _fc[key]


def wrap(text, f, maxw):
    lines = []
    for para in text.split('\n'):
        cur = ''
        for w in para.split(' '):
            t = (cur + ' ' + w) if cur else w
            if f.getlength(t) <= maxw or not cur:
                cur = t
            else:
                lines.append(cur)
                cur = w
        lines.append(cur)
    return lines


SIDES = {'r': (1, .5), 'l': (0, .5), 't': (.5, 0), 'b': (.5, 1)}


def parse_side(s):
    if isinstance(s, tuple):
        return s
    if ':' in s:
        a, v = s.split(':')
        v = float(v)
        if a in 'rl':
            return (1 if a == 'r' else 0, v)
        return (v, 0 if a == 't' else 1)
    return SIDES[s]


def esc_html(text):
    return '<br>'.join(html.escape(l, quote=False) for l in text.split('\n'))


class Dia:
    def __init__(self, name, w, h):
        self.name, self.w, self.h = name, w, h
        self.items, self.keys, self.cnt = [], {}, 1
        self.bars = {}      # cx -> [(y1,y2)]
        self.warn = []

    # ---------- base ----------
    def _add(self, it):
        self.cnt += 1
        it['id'] = 'n%d' % self.cnt
        self.items.append(it)
        if it.get('key'):
            self.keys[it['key']] = it
        return it

    def shape(self, key, kind, x, y, w, h, text='', fs=14, bold=False, italic=False,
              dashed=False, align='center', valign='middle', fill=True, extra='', pad=6):
        return self._add(dict(t='shape', key=key, kind=kind, x=x, y=y, w=w, h=h, text=text, fs=fs,
                              bold=bold, italic=italic, dashed=dashed, align=align, valign=valign,
                              fill=fill, extra=extra, pad=pad))

    def text(self, x, y, w, h, text, fs=12, bold=False, italic=False, align='center', valign='middle', key=None, bg=False):
        it = self.shape(key, 'text', x, y, w, h, text, fs, bold, italic, align=align, valign=valign)
        it['bg'] = bg
        return it

    def uc(self, key, cx, cy, text, w=230, h=54, fs=13):
        return self.shape(key, 'ellipse', cx - w / 2, cy - h / 2, w, h, text, fs)

    def actor(self, key, x, y, label, w=30, h=60, fs=14):
        return self.shape(key, 'actor', x, y, w, h, label, fs)

    def klass(self, key, x, y, w, name, attrs=(), meths=(), stereo='', fs=13, italic=False, compact=False):
        lh = round(fs * 1.3)
        hh = 28 + (16 if stereo else 0)
        ah = 0 if compact else max(12, len(attrs) * lh + 10)
        mh = 0 if compact else max(12, len(meths) * lh + 10)
        return self._add(dict(t='class', key=key, kind='class', x=x, y=y, w=w, h=hh + ah + mh, name=name,
                              attrs=list(attrs), meths=list(meths), stereo=stereo, fs=fs, hh=hh, ah=ah,
                              mh=mh, lh=lh, italic=italic))

    def lifeline(self, key, cx, y, w, text, yend, head=40, fs=14):
        return self.shape(key, 'lifeline', cx - w / 2, y, w, yend - y, text, fs, extra=str(head))

    def bar(self, cx, y1, y2, w=12):
        self.bars.setdefault(cx, []).append((y1, y2))
        return self.shape(None, 'rect', cx - w / 2, y1, w, y2 - y1, '', 10)

    # ---------- bordes ----------
    def A(self, key, side, v):
        """Punto de conexión alineado: para l/r, v es la coordenada y absoluta; para t/b, la x absoluta."""
        s = self.keys[key]
        if side in 'lr':
            return (1 if side == 'r' else 0, (v - s['y']) / s['h'])
        return ((v - s['x']) / s['w'], 1 if side == 'b' else 0)

    def P(self, key, side):
        s = self.keys[key]
        fx, fy = parse_side(side)
        return (s['x'] + fx * s['w'], s['y'] + fy * s['h'])

    def line(self, pts, end='none', start='none', dashed=False, label='', loff=(0, 0), fs=12,
             src=None, sa=None, tgt=None, sb=None, m1='', m2='', msg=None, lblpos=None):
        e = self._add(dict(t='edge', key=None, pts=[tuple(p) for p in pts], end=end, start=start, dashed=dashed,
                           label=label, loff=loff, fs=fs, src=src, sa=sa, tgt=tgt, sb=sb, msg=msg, lblpos=lblpos))
        if m1:
            self._endlab(e['pts'][0], e['pts'][1], m1, fs)
        if m2:
            self._endlab(e['pts'][-1], e['pts'][-2], m2, fs)
        return e

    def link(self, a, sa, b, sb, via=(), **kw):
        p0, p1 = self.P(a, sa), self.P(b, sb)
        return self.line([p0] + list(via) + [p1], src=a, sa=parse_side(sa), tgt=b, sb=parse_side(sb), **kw)

    def _endlab(self, p, q, txt, fs):
        dx, dy = q[0] - p[0], q[1] - p[1]
        L = math.hypot(dx, dy) or 1
        d = (dx / L, dy / L)
        n = (-d[1], d[0])
        if abs(d[0]) >= abs(d[1]):
            if n[1] > 0:
                n = (-n[0], -n[1])      # arriba
        else:
            if n[0] < 0:
                n = (-n[0], -n[1])      # derecha
        wt = 40
        cx = p[0] + d[0] * 22 + n[0] * 12
        cy = p[1] + d[1] * 22 + n[1] * 12
        self.text(cx - wt / 2, cy - 9, wt, 18, txt, fs)

    # ---------- secuencia ----------
    def msg(self, k1, k2, y, text, kind='sync', fs=12, lw_pad=14):
        x1, x2 = self.keys[k1]['x'] + self.keys[k1]['w'] / 2, self.keys[k2]['x'] + self.keys[k2]['w'] / 2
        reply = kind == 'reply'
        e = self.line([(x1, y), (x2, y)], end='open' if reply else 'block', dashed=reply,
                      msg=dict(cx1=x1, cx2=x2, y=y))
        lo, hi = min(x1, x2), max(x1, x2)
        tw = hi - lo - 2 * lw_pad
        f = font(fs)
        need = max(f.getlength(l) for l in text.split('\n')) / SC
        nl = len(text.split('\n'))
        if need > tw:
            self.warn.append('mensaje largo (%d>%d): %s' % (need, tw, text))
        wt = min(tw, need + 14)
        lhh = round(fs * 1.25)
        self.text((lo + hi) / 2 - wt / 2, y - 6 - nl * lhh - 2, wt, nl * lhh + 2, text, fs, valign='bottom', bg=True)
        return e

    def selfmsg(self, k, y, text, h=26, fs=12, reply=False, wtxt=250):
        x = self.keys[k]['x'] + self.keys[k]['w'] / 2
        self.line([(x, y), (x + 46, y), (x + 46, y + h), (x, y + h)], end='open' if reply else 'block',
                  dashed=reply, msg=dict(cx1=x, cx2=x, y=y, selfm=True))
        f = font(fs)
        wl = min(wtxt, max(f.getlength(l) for l in wrap(text, f, wtxt * SC)) / SC + 12)
        self.text(x + 54, y - 2, wl, h + 4, text, fs, align='left', bg=True)

    def frag(self, x, y, w, h, kind, guard, divs=()):
        self.shape(None, 'rect', x, y, w, h, '', 12, fill=False)
        tw = 56
        self.shape(None, 'tab', x, y, tw, 22, kind, 12, bold=True)
        self.text(x + tw + 8, y + 2, w - tw - 16, 20, '[%s]' % guard, 12, align='left')
        for dy, g in divs:
            self.line([(x, dy), (x + w, dy)], dashed=True)
            self.text(x + 8, dy + 3, w - 16, 20, '[%s]' % g, 12, align='left')

    # ---------- finalización ----------
    def _resolve_msgs(self):
        for it in self.items:
            if it['t'] == 'edge' and it.get('msg'):
                m = it['msg']
                def on_bar(cx):
                    for (a, b) in self.bars.get(cx, []):
                        if a - 1 <= m['y'] <= b + 1:
                            return True
                    return False
                p = it['pts']
                if m.get('selfm'):
                    if on_bar(m['cx1']):
                        for i in (0, -1):
                            p[i] = (p[i][0] + 6, p[i][1])
                    continue
                s = 1 if m['cx2'] > m['cx1'] else -1
                if on_bar(m['cx1']):
                    p[0] = (p[0][0] + 6 * s, p[0][1])
                if on_bar(m['cx2']):
                    p[-1] = (p[-1][0] - 6 * s, p[-1][1])

    def save(self, folder):
        self._resolve_msgs()
        os.makedirs(folder, exist_ok=True)
        self._check()
        with open(os.path.join(folder, self.name + '.drawio'), 'w', encoding='utf-8') as fh:
            fh.write(self._xml())
        self._png(os.path.join(folder, self.name + '.png'))
        for w in self.warn:
            print('  ⚠ [%s] %s' % (self.name, w))

    # ---------- verificación de texto ----------
    def _inner(self, it):
        k = it['kind']
        w, h = it['w'], it['h']
        if k == 'ellipse':
            return w * 0.74, h * 0.72
        if k == 'diamond':
            return w * 0.58, h * 0.58
        if k in ('component', 'cube', 'cylinder'):
            return w - 40, h - 20
        return w - 2 * it['pad'], h - 4

    def _check(self):
        for it in self.items:
            if it['t'] == 'shape' and it['text'] and it['kind'] not in ('text', 'actor', 'lifeline', 'tab'):
                f = font(it['fs'], it['bold'], it['italic'])
                iw, ih = self._inner(it)
                lines = wrap(it['text'], f, iw * SC)
                lh = round(it['fs'] * 1.25)
                if len(lines) * lh > ih + 1:
                    self.warn.append('texto no entra en alto: "%s" (%d líneas)' % (it['text'].replace('\n', '/'), len(lines)))
                if max(f.getlength(l) for l in lines) > iw * SC + 1:
                    self.warn.append('texto no entra en ancho: "%s"' % it['text'].replace('\n', '/'))
            if it['t'] == 'shape' and it['kind'] == 'text' and it['text']:
                f = font(it['fs'], it['bold'], it['italic'])
                lines = wrap(it['text'], f, it['w'] * SC)
                lh = round(it['fs'] * 1.25)
                if len(lines) * lh > it['h'] + 3:
                    self.warn.append('texto libre desborda alto: "%s"' % it['text'].replace('\n', '/'))
            if it['t'] == 'class':
                f = font(it['fs'])
                for l in it['attrs'] + it['meths']:
                    if f.getlength(l) / SC > it['w'] - 14:
                        self.warn.append('línea de clase ancha: %s' % l)
            for k in ('x', 'y'):
                if it['t'] != 'edge' and it[k] < 0:
                    self.warn.append('elemento fuera de página')
            if it['t'] != 'edge' and (it['x'] + it['w'] > self.w + 0.5 or it['y'] + it['h'] > self.h + 0.5):
                self.warn.append('elemento excede la página: %s' % (it.get('text') or it.get('name') or it['kind']))

    # ================= DRAW.IO =================
    def _style_shape(self, it):
        base = 'html=1;whiteSpace=wrap;fontColor=%s;strokeColor=%s;fillColor=%s;strokeWidth=%d;fontFamily=Helvetica;fontSize=%d;' % (
            WHITE, WHITE, BLACK if it['fill'] else 'none', LW, it['fs'])
        fs_ = (1 if it['bold'] else 0) | (2 if it['italic'] else 0)
        if fs_:
            base += 'fontStyle=%d;' % fs_
        if it['dashed']:
            base += 'dashed=1;'
        k = it['kind']
        al = 'align=%s;verticalAlign=%s;' % (it['align'], it['valign'])
        sp = 'spacingLeft=%d;spacingRight=%d;' % (it['pad'], it['pad']) if it['align'] != 'center' else ''
        if k == 'rect':
            return base + 'rounded=0;' + al + sp + it['extra']
        if k == 'tab':
            return base + 'rounded=0;' + al
        if k == 'round':
            return base + 'rounded=1;arcSize=30;' + al + sp + it['extra']
        if k == 'ellipse':
            return base + 'ellipse;' + al
        if k == 'diamond':
            return base + 'rhombus;' + al
        if k == 'text':
            return ('text;html=1;whiteSpace=wrap;strokeColor=none;fillColor=%s;fontColor=%s;fontFamily=Helvetica;fontSize=%d;' % (BLACK if it.get('bg') else 'none', WHITE, it['fs'])
                    + ('fontStyle=%d;' % fs_ if fs_ else '') + al + 'spacingLeft=2;spacingRight=2;spacingTop=0;spacingBottom=0;')
        if k == 'actor':
            return ('shape=umlActor;verticalLabelPosition=bottom;verticalAlign=top;html=1;outlineConnect=0;fillColor=%s;strokeColor=%s;fontColor=%s;strokeWidth=%d;fontFamily=Helvetica;fontSize=%d;'
                    % (BLACK, WHITE, WHITE, LW, it['fs']))
        if k == 'initial':
            return 'ellipse;html=1;fillColor=%s;strokeColor=%s;strokeWidth=%d;' % (WHITE, WHITE, LW)
        if k == 'final':
            return 'ellipse;html=1;shape=endState;fillColor=%s;strokeColor=%s;strokeWidth=%d;' % (BLACK, WHITE, LW)
        if k == 'note':
            return base + 'shape=note;size=14;backgroundOutline=1;darkOpacity=0;darkOpacity2=0;' + al + sp + 'spacingTop=4;'
        if k == 'component':
            return base + 'shape=module;jettyWidth=16;jettyHeight=8;align=center;verticalAlign=middle;spacingLeft=14;'
        if k == 'cube':
            return base + 'shape=cube;size=16;boundedLbl=1;backgroundOutline=1;darkOpacity=0;darkOpacity2=0;' + al
        if k == 'cylinder':
            return base + 'shape=cylinder3;boundedLbl=1;backgroundOutline=1;size=14;' + al
        if k == 'lifeline':
            return (base + 'shape=umlLifeline;perimeter=lifelinePerimeter;container=1;dropTarget=0;collapsible=0;recursiveResize=0;'
                    'outlineConnect=0;portConstraint=eastwest;size=%s;' % it['extra'])
        raise ValueError(k)

    def _val_shape(self, it):
        return esc_html(it['text'])

    def _xml(self):
        o = ['<mxfile host="app.diagrams.net" agent="WashFlow generador UML" version="24.0.0">',
             '<diagram name="%s" id="%s">' % (html.escape(self.name), self.name[:20]),
             '<mxGraphModel dx="%d" dy="%d" grid="1" gridSize="10" guides="1" tooltips="1" connect="1" arrows="1" fold="1" page="1" pageScale="1" pageWidth="%d" pageHeight="%d" math="0" shadow="0" background="#000000">' % (self.w, self.h, self.w, self.h),
             '<root><mxCell id="0"/><mxCell id="1" parent="0"/>']
        for it in self.items:
            if it['t'] == 'shape':
                o.append('<mxCell id="%s" value="%s" style="%s" vertex="1" parent="1"><mxGeometry x="%.1f" y="%.1f" width="%.1f" height="%.1f" as="geometry"/></mxCell>'
                         % (it['id'], html.escape(self._val_shape(it), quote=True), self._style_shape(it), it['x'], it['y'], it['w'], it['h']))
            elif it['t'] == 'class':
                st = ('swimlane;fontStyle=1;align=center;verticalAlign=top;childLayout=stackLayout;horizontal=1;startSize=%d;horizontalStack=0;'
                      'resizeParent=1;resizeParentMax=0;resizeLast=0;collapsible=0;marginBottom=0;html=1;fillColor=%s;strokeColor=%s;'
                      'swimlaneFillColor=%s;fontColor=%s;strokeWidth=%d;fontFamily=Helvetica;fontSize=%d;' % (
                          it['hh'], BLACK, WHITE, BLACK, WHITE, LW, it['fs'] + 1))
                head = ''
                if it['stereo']:
                    head += '<span style="font-weight:normal;font-size:%dpx">%s</span><br>' % (it['fs'] - 1, html.escape(it['stereo'], quote=False))
                nm = html.escape(it['name'], quote=False)
                head += '<i>%s</i>' % nm if it['italic'] else nm
                o.append('<mxCell id="%s" value="%s" style="%s" vertex="1" parent="1"><mxGeometry x="%.1f" y="%.1f" width="%.1f" height="%.1f" as="geometry"/></mxCell>'
                         % (it['id'], html.escape(head, quote=True), st, it['x'], it['y'], it['w'], it['h']))
                if it['ah'] == 0:
                    continue
                cs = ('rounded=0;whiteSpace=wrap;html=1;fillColor=%s;strokeColor=%s;fontColor=%s;strokeWidth=%d;align=left;verticalAlign=top;'
                      'spacingLeft=8;spacingTop=4;spacingRight=4;fontFamily=Helvetica;fontSize=%d;overflow=hidden;' % (BLACK, WHITE, WHITE, LW, it['fs']))
                o.append('<mxCell id="%sa" value="%s" style="%s" vertex="1" parent="%s"><mxGeometry y="%d" width="%.1f" height="%d" as="geometry"/></mxCell>'
                         % (it['id'], html.escape(esc_html('\n'.join(it['attrs'])), quote=True), cs, it['id'], it['hh'], it['w'], it['ah']))
                o.append('<mxCell id="%sm" value="%s" style="%s" vertex="1" parent="%s"><mxGeometry y="%d" width="%.1f" height="%d" as="geometry"/></mxCell>'
                         % (it['id'], html.escape(esc_html('\n'.join(it['meths'])), quote=True), cs, it['id'], it['hh'] + it['ah'], it['w'], it['mh']))
            else:
                o.append(self._xml_edge(it))
        o.append('</root></mxGraphModel></diagram></mxfile>')
        return '\n'.join(o)

    ARW = {'none': 'endArrow=none;', 'open': 'endArrow=open;endFill=0;endSize=10;', 'block': 'endArrow=block;endFill=1;endSize=8;',
           'triangle': 'endArrow=block;endFill=0;endSize=12;', 'diamond': 'endArrow=diamondThin;endFill=0;endSize=16;',
           'diamondf': 'endArrow=diamondThin;endFill=1;endSize=16;'}

    def _xml_edge(self, it):
        e = self.ARW[it['end']]
        s = self.ARW[it['start']].replace('endArrow', 'startArrow').replace('endFill', 'startFill').replace('endSize', 'startSize')
        if it['start'] == 'none':
            s = 'startArrow=none;'
        st = 'edgeStyle=none;rounded=0;html=1;strokeColor=%s;strokeWidth=%d;fontColor=%s;fontFamily=Helvetica;fontSize=%d;labelBackgroundColor=%s;%s%s' % (
            WHITE, LW, WHITE, it['fs'], BLACK, e, s)
        if it['dashed']:
            st += 'dashed=1;'
        attrs = ''
        pts = it['pts']
        geo = ''
        if it['src']:
            a = self.keys[it['src']]
            st += 'exitX=%s;exitY=%s;exitPerimeter=0;' % (it['sa'][0], it['sa'][1])
            attrs += ' source="%s"' % a['id']
        else:
            geo += '<mxPoint x="%.1f" y="%.1f" as="sourcePoint"/>' % pts[0]
        if it['tgt']:
            b = self.keys[it['tgt']]
            st += 'entryX=%s;entryY=%s;entryPerimeter=0;' % (it['sb'][0], it['sb'][1])
            attrs += ' target="%s"' % b['id']
        else:
            geo += '<mxPoint x="%.1f" y="%.1f" as="targetPoint"/>' % pts[-1]
        mid = pts[1:-1]
        if mid:
            geo += '<Array as="points">' + ''.join('<mxPoint x="%.1f" y="%.1f"/>' % p for p in mid) + '</Array>'
        if it['loff'] != (0, 0):
            geo += '<mxPoint x="%d" y="%d" as="offset"/>' % it['loff']
        return '<mxCell id="%s" value="%s" style="%s" edge="1" parent="1"%s><mxGeometry relative="1" as="geometry">%s</mxGeometry></mxCell>' % (
            it['id'], html.escape(esc_html(it['label']), quote=True), st, attrs, geo)

    # ================= PNG =================
    def _png(self, path):
        im = Image.new('RGB', (self.w * SC, self.h * SC), BLACK)
        d = ImageDraw.Draw(im)
        for it in self.items:
            if it['t'] == 'shape':
                self._draw_shape(d, it)
            elif it['t'] == 'class':
                self._draw_class(d, it)
            else:
                self._draw_edge(d, it)
        im = im.resize((self.w * OUT, self.h * OUT), Image.LANCZOS)
        im.save(path)

    def _text(self, d, text, x, y, w, h, fs, bold=False, italic=False, align='center', valign='middle', pad=0, maxw=None):
        f = font(fs, bold, italic)
        lh = round(fs * 1.25) * SC
        lines = wrap(text, f, (maxw if maxw else w - 2 * pad) * SC)
        th = len(lines) * lh
        if valign == 'middle':
            ty = y * SC + (h * SC - th) / 2
        elif valign == 'top':
            ty = y * SC + 2 * SC
        else:
            ty = (y + h) * SC - th
        for i, l in enumerate(lines):
            lw = f.getlength(l)
            if align == 'center':
                tx = x * SC + (w * SC - lw) / 2
            elif align == 'left':
                tx = x * SC + pad * SC
            else:
                tx = (x + w) * SC - lw - pad * SC
            d.text((tx, ty + i * lh + (lh - fs * SC) / 2 - fs * SC * 0.12), l, font=f, fill=WHITE)

    def _dash_poly(self, d, pts, width, dash=(9, 6)):
        on, off = dash[0] * SC, dash[1] * SC
        for a, b in zip(pts, pts[1:]):
            L = math.hypot(b[0] - a[0], b[1] - a[1])
            if L == 0:
                continue
            ux, uy = (b[0] - a[0]) / L, (b[1] - a[1]) / L
            pos = 0
            while pos < L:
                e = min(pos + on, L)
                d.line([(a[0] + ux * pos, a[1] + uy * pos), (a[0] + ux * e, a[1] + uy * e)], fill=WHITE, width=width)
                pos += on + off

    def _draw_shape(self, d, it):
        x, y, w, h = [it[k] * SC for k in ('x', 'y', 'w', 'h')]
        k = it['kind']
        lw = LW * SC
        fill = BLACK if it['fill'] else None
        def outline(box_fn, *a, **kw):
            pass
        if k in ('rect', 'tab', 'note') or k == 'round':
            if k == 'round':
                d.rounded_rectangle([x, y, x + w, y + h], radius=min(w, h) * 0.3 * 0.5 * 1.0, outline=WHITE, fill=fill, width=lw)
            elif k == 'note':
                c = 14 * SC
                d.polygon([(x, y), (x + w - c, y), (x + w, y + c), (x + w, y + h), (x, y + h)], outline=WHITE, fill=BLACK)
                d.line([(x + w - c, y), (x + w - c, y + c), (x + w, y + c)], fill=WHITE, width=lw)
            else:
                if it['dashed']:
                    self._dash_poly(d, [(x, y), (x + w, y), (x + w, y + h), (x, y + h), (x, y)], lw)
                    if fill:
                        pass
                else:
                    d.rectangle([x, y, x + w, y + h], outline=WHITE, fill=fill, width=lw)
            if it['text']:
                self._text(d, it['text'], it['x'], it['y'], it['w'], it['h'], it['fs'], it['bold'], it['italic'],
                           it['align'], it['valign'], pad=it['pad'], maxw=it['w'] - 2 * it['pad'] if it['align'] == 'center' else None)
        elif k == 'ellipse':
            d.ellipse([x, y, x + w, y + h], outline=WHITE, fill=BLACK, width=lw)
            if it['dashed']:
                pass
            self._text(d, it['text'], it['x'], it['y'], it['w'], it['h'], it['fs'], it['bold'], it['italic'], maxw=it['w'] * 0.74)
        elif k == 'diamond':
            d.polygon([(x + w / 2, y), (x + w, y + h / 2), (x + w / 2, y + h), (x, y + h / 2)], outline=WHITE, fill=BLACK, width=lw)
            self._text(d, it['text'], it['x'], it['y'], it['w'], it['h'], it['fs'], it['bold'], it['italic'], maxw=it['w'] * 0.6)
        elif k == 'text':
            if it.get('bg'):
                d.rectangle([x, y, x + w, y + h], fill=BLACK)
            self._text(d, it['text'], it['x'], it['y'], it['w'], it['h'], it['fs'], it['bold'], it['italic'], it['align'], it['valign'], pad=2)
        elif k == 'actor':
            cx = x + w / 2
            r = h * 0.15
            d.ellipse([cx - r, y, cx + r, y + 2 * r], outline=WHITE, fill=BLACK, width=lw)
            by = y + 2 * r
            hip = y + h * 0.62
            d.line([(cx, by), (cx, hip)], fill=WHITE, width=lw)
            d.line([(x, y + h * 0.40), (x + w, y + h * 0.40)], fill=WHITE, width=lw)
            d.line([(cx, hip), (x, y + h)], fill=WHITE, width=lw)
            d.line([(cx, hip), (x + w, y + h)], fill=WHITE, width=lw)
            self._text(d, it['text'], it['x'] + it['w'] / 2 - 70, it['y'] + it['h'] + 3, 140, 22, it['fs'], valign='top')
        elif k == 'initial':
            d.ellipse([x, y, x + w, y + h], fill=WHITE, outline=WHITE)
        elif k == 'final':
            d.ellipse([x, y, x + w, y + h], outline=WHITE, fill=BLACK, width=lw)
            g = w * 0.22
            d.ellipse([x + g, y + g, x + w - g, y + h - g], fill=WHITE)
        elif k == 'component':
            d.rectangle([x, y, x + w, y + h], outline=WHITE, fill=BLACK, width=lw)
            for ty in (y + 12 * SC, y + 30 * SC):
                d.rectangle([x - 8 * SC, ty, x + 8 * SC, ty + 8 * SC], outline=WHITE, fill=BLACK, width=lw)
            self._text(d, it['text'], it['x'] + 14, it['y'], it['w'] - 14, it['h'], it['fs'], it['bold'], it['italic'], maxw=it['w'] - 40)
        elif k == 'cube':
            c = 16 * SC
            d.polygon([(x, y + c), (x + c, y), (x + w, y), (x + w - c, y + c)], outline=WHITE, fill=BLACK, width=lw)
            d.polygon([(x + w - c, y + c), (x + w, y), (x + w, y + h - c), (x + w - c, y + h)], outline=WHITE, fill=BLACK, width=lw)
            d.rectangle([x, y + c, x + w - c, y + h], outline=WHITE, fill=BLACK, width=lw)
            self._text(d, it['text'], it['x'], it['y'] + 16, it['w'] - 16, it['h'] - 16, it['fs'], it['bold'], it['italic'], align=it['align'], valign=it['valign'], pad=it['pad'], maxw=it['w'] - 40)
        elif k == 'cylinder':
            c = 14 * SC
            d.rectangle([x, y + c / 2, x + w, y + h - c / 2], fill=BLACK)
            d.line([(x, y + c / 2), (x, y + h - c / 2)], fill=WHITE, width=lw)
            d.line([(x + w, y + c / 2), (x + w, y + h - c / 2)], fill=WHITE, width=lw)
            d.arc([x, y + h - c, x + w, y + h], 0, 180, fill=WHITE, width=lw)
            d.ellipse([x, y, x + w, y + c], outline=WHITE, fill=BLACK, width=lw)
            self._text(d, it['text'], it['x'], it['y'] + 14, it['w'], it['h'] - 14, it['fs'], it['bold'], it['italic'], maxw=it['w'] - 16)
        elif k == 'lifeline':
            hd = int(it['extra']) * SC
            d.rectangle([x, y, x + w, y + hd], outline=WHITE, fill=BLACK, width=lw)
            self._text(d, it['text'], it['x'], it['y'], it['w'], int(it['extra']), it['fs'], it['bold'], maxw=it['w'] - 10)
            self._dash_poly(d, [(x + w / 2, y + hd), (x + w / 2, y + h)], int(lw * 0.8), dash=(8, 6))
        else:
            raise ValueError(k)

    def _draw_class(self, d, it):
        x, y, w, h = [it[k] * SC for k in ('x', 'y', 'w', 'h')]
        lw = LW * SC
        d.rectangle([x, y, x + w, y + h], outline=WHITE, fill=BLACK, width=lw)
        hh, ah = it['hh'] * SC, it['ah'] * SC
        if ah:
            d.line([(x, y + hh), (x + w, y + hh)], fill=WHITE, width=lw)
            d.line([(x, y + hh + ah), (x + w, y + hh + ah)], fill=WHITE, width=lw)
        fs = it['fs']
        if it['stereo']:
            self._text(d, it['stereo'], it['x'], it['y'] + 2, it['w'], 16, fs - 1, valign='middle')
            self._text(d, it['name'], it['x'], it['y'] + 16, it['w'], 28, fs + 1, bold=True, italic=it['italic'])
        else:
            self._text(d, it['name'], it['x'], it['y'], it['w'], 28, fs + 1, bold=True, italic=it['italic'])
        f = font(fs)
        lh = it['lh'] * SC
        for i, l in enumerate(it['attrs']):
            d.text((x + 8 * SC, y + hh + 5 * SC + i * lh), l, font=f, fill=WHITE)
        for i, l in enumerate(it['meths']):
            d.text((x + 8 * SC, y + hh + ah + 5 * SC + i * lh), l, font=f, fill=WHITE)

    def _arrow(self, d, tip, frm, kind, lw):
        dx, dy = tip[0] - frm[0], tip[1] - frm[1]
        L = math.hypot(dx, dy) or 1
        ux, uy = dx / L, dy / L
        nx, ny = -uy, ux
        def pt(a, b):
            return (tip[0] - ux * a + nx * b, tip[1] - uy * a + ny * b)
        s = SC
        if kind == 'open':
            d.line([pt(11 * s, 6 * s), tip, pt(11 * s, -6 * s)], fill=WHITE, width=lw, joint='curve')
        elif kind == 'block':
            d.polygon([tip, pt(14 * s, 6 * s), pt(14 * s, -6 * s)], fill=WHITE, outline=WHITE)
        elif kind == 'triangle':
            d.polygon([tip, pt(16 * s, 8 * s), pt(16 * s, -8 * s)], fill=BLACK, outline=WHITE, width=lw)
        elif kind in ('diamond', 'diamondf'):
            d.polygon([tip, pt(13 * s, 7 * s), pt(26 * s, 0), pt(13 * s, -7 * s)],
                      fill=WHITE if kind == 'diamondf' else BLACK, outline=WHITE, width=lw)

    def _draw_edge(self, d, it):
        pts = [(p[0] * SC, p[1] * SC) for p in it['pts']]
        lw = LW * SC
        if it['dashed']:
            self._dash_poly(d, pts, lw)
        else:
            d.line(pts, fill=WHITE, width=lw, joint='curve')
        if it['end'] != 'none':
            self._arrow(d, pts[-1], pts[-2], it['end'], lw)
        if it['start'] != 'none':
            self._arrow(d, pts[0], pts[1], it['start'], lw)
        if it['label']:
            tot = sum(math.hypot(b[0] - a[0], b[1] - a[1]) for a, b in zip(pts, pts[1:]))
            half, acc = tot / 2, 0
            for a, b in zip(pts, pts[1:]):
                L = math.hypot(b[0] - a[0], b[1] - a[1])
                if acc + L >= half:
                    t = (half - acc) / L if L else 0
                    mx, my = a[0] + (b[0] - a[0]) * t, a[1] + (b[1] - a[1]) * t
                    break
                acc += L
            mx += it['loff'][0] * SC
            my += it['loff'][1] * SC
            f = font(it['fs'])
            lines = it['label'].split('\n')
            lh = round(it['fs'] * 1.25) * SC
            wmax = max(f.getlength(l) for l in lines)
            th = lh * len(lines)
            d.rectangle([mx - wmax / 2 - 3 * SC, my - th / 2, mx + wmax / 2 + 3 * SC, my + th / 2], fill=BLACK)
            for i, l in enumerate(lines):
                d.text((mx - f.getlength(l) / 2, my - th / 2 + i * lh + (lh - it['fs'] * SC) / 2 - it['fs'] * SC * 0.12), l, font=f, fill=WHITE)
