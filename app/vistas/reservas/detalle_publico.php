<?php
/**
 * WashFlow - Detalle de Reserva Publica con Token de Seguridad (Requisito 3.1)
 * Permite ver el turno, reprogramar o cancelar con la regla de 2 horas,
 * y opcionalmente crear una cuenta al finalizar. Sin emojis.
 */

$flashExito = \WashFlow\Core\Sesion::getFlash('exito');
$flashError = \WashFlow\Core\Sesion::getFlash('error');
$flashAviso = \WashFlow\Core\Sesion::getFlash('aviso');

$permiteDirecto = $reserva['permite_cambio_directo'] ?? false;
$esActiva = ($reserva['estado'] === 'reservada');
$enlacePublico = $urlBase . '/reserva/' . urlencode($reserva['codigo']) . '/' . urlencode($reserva['token_publico']);
?>
<!DOCTYPE html>
<html lang="es" data-tema="oscuro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva <?= htmlspecialchars($reserva['codigo'], ENT_QUOTES, 'UTF-8') ?> — WashFlow</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/assets/css/app.css">
    <style>
        .detalle-contenedor {
            max-width: 900px;
            margin: 3rem auto 4rem;
            padding: 0 1.5rem;
        }
        .tarjeta-ticket-reserva {
            background-color: var(--color-superficie);
            border: 1px solid var(--color-borde-suave);
            border-radius: var(--radio-2xl);
            padding: 2.5rem;
            box-shadow: var(--sombra-lg);
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        .ticket-cabecera {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            flex-wrap: wrap;
            gap: 1.5rem;
            padding-bottom: 1.75rem;
            border-bottom: 1px solid var(--color-borde-suave);
            margin-bottom: 1.75rem;
        }
        .codigo-reserva-display {
            font-family: var(--fuente-titulo);
            font-size: 2.25rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            color: var(--color-acento);
            margin: 0.25rem 0;
        }
        .badge-estado-grande {
            padding: 0.45rem 1.1rem;
            border-radius: var(--radio-full);
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .badge-reservada {
            background: rgba(14, 165, 233, 0.15);
            color: #38bdf8;
            border: 1px solid rgba(14, 165, 233, 0.3);
        }
        .badge-cancelada {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        .badge-ausente {
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.3);
        }
        .grilla-datos-turno {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .dato-bloque {
            background-color: var(--color-superficie-alt);
            border-radius: var(--radio-lg);
            padding: 1.25rem;
            border: 1px solid var(--color-borde-suave);
        }
        .dato-etiqueta {
            font-size: 0.775rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--color-texto-apagado);
            margin-bottom: 0.4rem;
        }
        .dato-valor {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--color-texto);
        }
        .caja-enlace-seguro {
            background: linear-gradient(180deg, rgba(14, 165, 233, 0.08) 0%, rgba(14, 165, 233, 0.02) 100%);
            border: 1px solid rgba(14, 165, 233, 0.25);
            border-radius: var(--radio-lg);
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .acciones-turno-panel {
            background-color: var(--color-superficie-alt);
            border-radius: var(--radio-xl);
            padding: 1.75rem;
            border: 1px solid var(--color-borde-suave);
            margin-bottom: 2rem;
        }
        .formulario-accion-colapsable {
            display: none;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--color-borde);
        }
    </style>
</head>
<body>
    <?php $paginaActual = 'detalle'; include dirname(__DIR__) . '/layouts/public_navbar.php'; ?>

    <main class="detalle-contenedor">
        <?php if (!empty($flashExito)): ?>
            <div class="alerta alerta-exito" style="margin-bottom: 1.75rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                <span><?= htmlspecialchars($flashExito, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($flashAviso)): ?>
            <div class="alerta alerta-aviso" style="margin-bottom: 1.75rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span><?= htmlspecialchars($flashAviso, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($flashError)): ?>
            <div class="alerta alerta-error" style="margin-bottom: 1.75rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($solicitudPendiente) && $solicitudPendiente['estado'] === 'pendiente'): ?>
            <div class="alerta alerta-aviso" style="margin-bottom: 1.75rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                <div>
                    <strong>Solicitud de <?= htmlspecialchars($solicitudPendiente['tipo'], ENT_QUOTES, 'UTF-8') ?> en evaluacion:</strong>
                    Tu peticion fue recibida y esta siendo revisada por el personal del lavadero. Te notificaremos a la brevedad.
                </div>
            </div>
        <?php endif; ?>

        <div class="tarjeta-ticket-reserva">
            <div class="ticket-cabecera">
                <div>
                    <span style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-texto-apagado);">Comprobante de Reserva</span>
                    <div class="codigo-reserva-display"><?= htmlspecialchars($reserva['codigo'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div style="font-size: 0.9rem; color: var(--color-texto-secundario);">
                        Titular: <strong><?= htmlspecialchars($reserva['contacto_nombre'] ?? ($reserva['cliente_nombre'] . ' ' . $reserva['cliente_apellido']), ENT_QUOTES, 'UTF-8') ?></strong> &bull; <?= htmlspecialchars($reserva['contacto_telefono'] ?? $reserva['cliente_telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </div>

                <div style="text-align: right;">
                    <?php
                    $claseBadge = match ($reserva['estado']) {
                        'cancelada' => 'badge-cancelada',
                        'ausente' => 'badge-ausente',
                        default => 'badge-reservada'
                    };
                    ?>
                    <span class="badge-estado-grande <?= $claseBadge ?>">
                        <?= htmlspecialchars(ucfirst($reserva['estado']), ENT_QUOTES, 'UTF-8') ?>
                    </span>
                    <div style="margin-top: 0.5rem; font-size: 0.825rem; color: var(--color-texto-apagado);">
                        Lavado: <strong><?= ucfirst($reserva['lavado_estado'] ?? 'pendiente') ?></strong> &bull; Pago: <strong><?= ucfirst($reserva['estado_pago'] ?? 'pendiente') ?></strong>
                    </div>
                </div>
            </div>

            <div class="caja-enlace-seguro">
                <div>
                    <div style="font-size: 0.85rem; font-weight: 700; color: #7dd3fc; margin-bottom: 0.2rem;">Enlace Privado de Gestion</div>
                    <div style="font-size: 0.8rem; color: var(--color-texto-secundario);">Guarda o comparti este enlace para consultar, reprogramar o cancelar tu turno en cualquier momento sin cuenta.</div>
                </div>
                <button type="button" class="btn btn-secundario btn-sm" id="btn-copiar-enlace" data-enlace="<?= htmlspecialchars($enlacePublico, ENT_QUOTES, 'UTF-8') ?>">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                    <span>Copiar Enlace</span>
                </button>
            </div>

            <!-- Grilla con datos del turno -->
            <div class="grilla-datos-turno">
                <div class="dato-bloque">
                    <div class="dato-etiqueta">Vehiculo y Categoria</div>
                    <div class="dato-valor">
                        <?= htmlspecialchars($reserva['marca'] . ' ' . $reserva['modelo'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <div style="margin-top: 0.35rem; font-size: 0.85rem; color: var(--color-acento); font-weight: 600;">
                        Categoria: <?= htmlspecialchars($reserva['tipo_nombre'], ENT_QUOTES, 'UTF-8') ?>
                        <?php if (!empty($reserva['tipo_a_confirmar'])): ?>
                            <span class="badge badge-aviso" style="margin-left: 0.35rem;">A confirmar en recepcion</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size: 0.825rem; color: var(--color-texto-apagado); margin-top: 0.2rem;">
                        Patente: <?= !empty($reserva['patente']) ? htmlspecialchars($reserva['patente'], ENT_QUOTES, 'UTF-8') : 'A informar al llegar' ?>
                    </div>
                </div>

                <div class="dato-bloque">
                    <div class="dato-etiqueta">Servicio Contratado</div>
                    <div class="dato-valor"><?= htmlspecialchars($reserva['servicio_nombre'], ENT_QUOTES, 'UTF-8') ?></div>
                    <div style="font-size: 0.85rem; color: var(--color-texto-secundario); margin-top: 0.35rem;">
                        <?= htmlspecialchars($reserva['servicio_descripcion'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </div>

                <div class="dato-bloque">
                    <div class="dato-etiqueta">Dia y Horario Pactado</div>
                    <div class="dato-valor">
                        <?= date('d/m/Y', strtotime($reserva['fecha'])) ?>
                    </div>
                    <div style="font-size: 0.95rem; color: var(--color-acento); font-weight: 700; margin-top: 0.25rem;">
                        <?= substr($reserva['hora_inicio'], 0, 5) ?> a <?= substr($reserva['hora_fin'], 0, 5) ?> hs
                    </div>
                    <div style="font-size: 0.8rem; color: var(--color-texto-apagado); margin-top: 0.2rem;">
                        Duracion asignada: <?= (int)$reserva['duracion_minutos'] ?> min
                    </div>
                </div>

                <div class="dato-bloque">
                    <div class="dato-etiqueta">Tarifa Acordada (Fija)</div>
                    <div class="dato-valor" style="color: var(--color-acento); font-size: 1.5rem;">
                        $<?= number_format((float)$reserva['precio_acordado'], 0, ',', '.') ?>
                    </div>
                    <div style="font-size: 0.8rem; color: var(--color-texto-apagado); margin-top: 0.25rem;">
                        Pago presencial al retirar (Efectivo, QR o Transferencia).
                    </div>
                </div>
            </div>

            <!-- Acciones de modificacion o cancelacion -->
            <?php if ($esActiva): ?>
                <div class="acciones-turno-panel">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                        <div>
                            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--color-texto);">Gestion del Turno</h3>
                            <p style="font-size: 0.875rem; color: var(--color-texto-secundario); margin-top: 0.2rem;">
                                <?php if ($permiteDirecto): ?>
                                    Tenes mas de 2 horas de anticipacion: podes reprogramar o cancelar directamente.
                                <?php else: ?>
                                    Faltan menos de 2 horas: el cambio o cancelacion se envia como solicitud a confirmar.
                                <?php endif; ?>
                            </p>
                        </div>
                        <div style="display: flex; gap: 0.75rem;">
                            <button type="button" class="btn btn-secundario btn-sm" id="btn-toggle-reprogramar">
                                Reprogramar Fecha/Hora
                            </button>
                            <button type="button" class="btn btn-peligro btn-sm" id="btn-toggle-cancelar">
                                Cancelar Turno
                            </button>
                        </div>
                    </div>

                    <!-- Formulario Reprogramar -->
                    <div id="form-reprogramar-seccion" class="formulario-accion-colapsable">
                        <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/reserva/<?= urlencode($reserva['codigo']) ?>/<?= urlencode($reserva['token_publico']) ?>/reprogramar" method="POST">
                            <?= $csrfCampo ?>
                            <h4 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.75rem; color: var(--color-texto);">Seleccionar Nueva Fecha y Horario</h4>
                            
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                                <div>
                                    <label for="input-reprogramar-fecha" style="font-size: 0.825rem; font-weight: 600; display: block; margin-bottom: 0.35rem;">Nueva Fecha:</label>
                                    <input type="date" name="nueva_fecha" id="input-reprogramar-fecha" class="campo-control" min="<?= date('Y-m-d') ?>" required>
                                </div>
                                <div>
                                    <label for="select-reprogramar-hora" style="font-size: 0.825rem; font-weight: 600; display: block; margin-bottom: 0.35rem;">Nuevo Horario:</label>
                                    <select name="nueva_hora" id="select-reprogramar-hora" class="campo-control" required>
                                        <option value="">Seleccione fecha primero...</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="input-reprogramar-motivo" style="font-size: 0.825rem; font-weight: 600; display: block; margin-bottom: 0.35rem;">Motivo del cambio (opcional):</label>
                                    <input type="text" name="motivo" id="input-reprogramar-motivo" class="campo-control" placeholder="Ej: Cambio de planes laborales">
                                </div>
                                <div>
                                    <button type="submit" class="btn btn-primario" style="width: 100%;">
                                        <?= $permiteDirecto ? 'Confirmar Reprogramacion' : 'Enviar Solicitud de Cambio' ?>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Formulario Cancelar -->
                    <div id="form-cancelar-seccion" class="formulario-accion-colapsable">
                        <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/reserva/<?= urlencode($reserva['codigo']) ?>/<?= urlencode($reserva['token_publico']) ?>/cancelar" method="POST">
                            <?= $csrfCampo ?>
                            <h4 style="font-size: 1rem; font-weight: 700; margin-bottom: 0.75rem; color: var(--color-peligro);">Confirmar Cancelacion de Reserva</h4>
                            <p style="font-size: 0.875rem; color: var(--color-texto-secundario); margin-bottom: 1rem;">
                                <?= $permiteDirecto ? 'Al cancelar con anticipacion, liberas el box para otro cliente. Esta accion es definitiva.' : 'Como faltan menos de 2 horas para el turno, el pedido sera enviado para aprobacion del lavadero.' ?>
                            </p>
                            <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                                <input type="text" name="motivo" class="campo-control" placeholder="Motivo de cancelacion (opcional)" style="flex: 1; min-width: 200px;">
                                <button type="submit" class="btn btn-peligro">
                                    <?= $permiteDirecto ? 'Si, Cancelar Mi Turno' : 'Enviar Solicitud de Cancelacion' ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Opcion de crear cuenta (sin obligar) -->
            <?php if (!empty($reserva['es_ocasional'])): ?>
                <div style="background: var(--color-superficie-alt); border: 1px dashed var(--color-borde); border-radius: var(--radio-lg); padding: 1.5rem; margin-top: 2rem;">
                    <h3 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 0.35rem; color: var(--color-texto);">Queres guardar tu historial para futuras reservas?</h3>
                    <p style="font-size: 0.875rem; color: var(--color-texto-secundario); line-height: 1.5; margin-bottom: 1.25rem;">
                        La cuenta es totalmente opcional. Si creas una contrasena, tus proximas reservas seran mas rapidas y podras ver tus turnos anteriores.
                    </p>

                    <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/reserva/<?= urlencode($reserva['codigo']) ?>/<?= urlencode($reserva['token_publico']) ?>/crear-cuenta" method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
                        <?= $csrfCampo ?>
                        <div>
                            <label for="input-cuenta-correo" style="font-size: 0.8rem; font-weight: 600; display: block; margin-bottom: 0.3rem;">Correo Electronico:</label>
                            <input type="email" name="correo" id="input-cuenta-correo" class="campo-control" required value="<?= htmlspecialchars($reserva['contacto_correo'] ?? '', ENT_QUOTES, 'UTF-8') ?>" placeholder="tu@correo.com">
                        </div>
                        <div>
                            <label for="input-cuenta-pass" style="font-size: 0.8rem; font-weight: 600; display: block; margin-bottom: 0.3rem;">Crear Contrasena (min 8 caracteres):</label>
                            <input type="password" name="contrasena" id="input-cuenta-pass" class="campo-control" minlength="8" required placeholder="********">
                        </div>
                        <div>
                            <button type="submit" class="btn btn-secundario" style="width: 100%;">Crear Cuenta Opcional</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include dirname(__DIR__) . '/layouts/public_footer.php'; ?>

    <script src="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/assets/js/app.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Copiar enlace
            const btnCopiar = document.getElementById('btn-copiar-enlace');
            if (btnCopiar) {
                btnCopiar.addEventListener('click', function() {
                    const enlace = this.getAttribute('data-enlace');
                    navigator.clipboard.writeText(enlace).then(() => {
                        const original = this.innerHTML;
                        this.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg><span>Enlace Copiado</span>';
                        setTimeout(() => {
                            this.innerHTML = original;
                        }, 2500);
                    });
                });
            }

            // Toggle formularios de reprogramacion y cancelacion
            const btnReprog = document.getElementById('btn-toggle-reprogramar');
            const secReprog = document.getElementById('form-reprogramar-seccion');
            const btnCancel = document.getElementById('btn-toggle-cancelar');
            const secCancel = document.getElementById('form-cancelar-seccion');

            if (btnReprog && secReprog) {
                btnReprog.addEventListener('click', function() {
                    secReprog.style.display = (secReprog.style.display === 'block') ? 'none' : 'block';
                    if (secCancel) secCancel.style.display = 'none';
                });
            }

            if (btnCancel && secCancel) {
                btnCancel.addEventListener('click', function() {
                    secCancel.style.display = (secCancel.style.display === 'block') ? 'none' : 'block';
                    if (secReprog) secReprog.style.display = 'none';
                });
            }

            // Consultar disponibilidad al cambiar fecha en reprogramar
            const inputFecha = document.getElementById('input-reprogramar-fecha');
            const selectHora = document.getElementById('select-reprogramar-hora');
            const duracion = <?= (int)$reserva['duracion_minutos'] ?>;
            const reservaId = <?= (int)$reserva['id'] ?>;

            if (inputFecha && selectHora) {
                inputFecha.addEventListener('change', function() {
                    selectHora.innerHTML = '<option value="">Cargando horarios...</option>';
                    fetch('<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/api/disponibilidad?fecha=' + encodeURIComponent(this.value) + '&duracion=' + duracion + '&excluir_id=' + reservaId)
                        .then(r => r.json())
                        .then(data => {
                            selectHora.innerHTML = '';
                            if (!data.franjas || data.franjas.length === 0) {
                                selectHora.innerHTML = '<option value="">Sin turnos libres</option>';
                                return;
                            }
                            let count = 0;
                            data.franjas.forEach(f => {
                                if (f.disponible) {
                                    const hora = f.hora || f.hora_inicio || '';
                                    const opt = document.createElement('option');
                                    opt.value = hora;
                                    opt.textContent = hora.substring(0, 5) + ' hs';
                                    selectHora.appendChild(opt);
                                    count++;
                                }
                            });
                            if (count === 0) {
                                selectHora.innerHTML = '<option value="">Sin turnos libres</option>';
                            }
                        })
                        .catch(() => {
                            selectHora.innerHTML = '<option value="">Error al cargar</option>';
                        });
                });
            }
        });
    </script>
</body>
</html>
