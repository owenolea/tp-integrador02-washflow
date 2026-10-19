<?php
/**
 * WashFlow - Asistente de Reserva Publica en 5 Pasos Sin Cuenta (Requisito 3.1 y 3.2)
 * Asistente en una sola pantalla, con resumen siempre visible. Sin emojis.
 */

$flashExito = \WashFlow\Core\Sesion::getFlash('exito');
$flashError = \WashFlow\Core\Sesion::getFlash('error');
$flashFormData = \WashFlow\Core\Sesion::getFlash('form_data') ?? [];
?>
<!DOCTYPE html>
<html lang="es" data-tema="oscuro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar Turno Online — WashFlow</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/assets/css/app.css">
    <style>
        .reserva-contenedor-principal {
            max-width: 1200px;
            margin: 2.5rem auto 4rem;
            padding: 0 1.5rem;
        }
        .reserva-layout-grid {
            display: grid;
            grid-template-columns: 1fr 360px;
            gap: 2.5rem;
            align-items: start;
        }
        @media (max-width: 960px) {
            .reserva-layout-grid {
                grid-template-columns: 1fr;
            }
        }
        .paso-card {
            background-color: var(--color-superficie);
            border: 1px solid var(--color-borde-suave);
            border-radius: var(--radio-xl);
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: var(--sombra-sm);
            transition: border-color 0.2s ease;
        }
        .paso-card.activo {
            border-color: var(--color-acento);
            box-shadow: 0 0 0 1px var(--color-acento), var(--sombra-md);
        }
        .paso-encabezado {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .paso-numero {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--color-acento-claro);
            color: var(--color-acento);
            border: 1px solid var(--color-acento);
            font-family: var(--fuente-titulo);
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }
        .paso-titulo-texto {
            font-family: var(--fuente-titulo);
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--color-texto);
        }
        .grilla-categorias-vehiculo {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .boton-categoria-vehiculo {
            background-color: var(--color-superficie-alt);
            border: 2px solid var(--color-borde);
            border-radius: var(--radio-lg);
            padding: 1.25rem 0.75rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
            user-select: none;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }
        .boton-categoria-vehiculo:hover {
            border-color: var(--color-acento);
            background-color: rgba(14, 165, 233, 0.05);
            transform: translateY(-2px);
        }
        .boton-categoria-vehiculo.seleccionado {
            border-color: var(--color-acento);
            background: rgba(14, 165, 233, 0.12);
            box-shadow: 0 0 0 1px var(--color-acento);
        }
        .boton-categoria-vehiculo .cat-nombre {
            font-family: var(--fuente-titulo);
            font-weight: 800;
            font-size: 1.1rem;
            color: var(--color-texto);
        }
        .boton-categoria-vehiculo .cat-duracion {
            font-size: 0.8rem;
            color: var(--color-texto-apagado);
        }
        .buscador-modelos-box {
            background-color: var(--color-superficie-alt);
            border: 1px solid var(--color-borde-suave);
            border-radius: var(--radio-lg);
            padding: 1.25rem;
            margin-top: 1rem;
            position: relative;
        }
        .input-buscador-modelos {
            width: 100%;
            background-color: var(--color-fondo);
            border: 1px solid var(--color-borde);
            border-radius: var(--radio-md);
            padding: 0.75rem 1rem;
            color: var(--color-texto);
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.2s ease;
        }
        .input-buscador-modelos:focus {
            outline: none;
            border-color: var(--color-acento);
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.25);
        }
        .dropdown-modelos-resultados {
            position: absolute;
            top: 100%;
            left: 1.25rem;
            right: 1.25rem;
            background-color: var(--color-fondo);
            border: 1px solid var(--color-borde);
            border-radius: var(--radio-md);
            max-height: 220px;
            overflow-y: auto;
            z-index: 50;
            box-shadow: var(--sombra-xl);
            display: none;
        }
        .dropdown-item-modelo {
            padding: 0.75rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--color-borde-suave);
            cursor: pointer;
            transition: background-color 0.15s ease;
        }
        .dropdown-item-modelo:hover,
        .dropdown-item-modelo.activo-teclado {
            background-color: var(--color-superficie-alt);
        }
        .dropdown-item-modelo:last-child {
            border-bottom: none;
        }
        .aviso-confirmacion-modelo {
            margin-top: 0.85rem;
            padding: 0.85rem 1rem;
            border-radius: var(--radio-md);
            background: rgba(14, 165, 233, 0.15);
            border: 1px solid var(--color-acento);
            color: #7dd3fc;
            font-size: 0.925rem;
            font-weight: 600;
            display: none;
        }
        .grilla-servicios-reserva {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.25rem;
        }
        .tarjeta-seleccion-servicio {
            background-color: var(--color-superficie-alt);
            border: 2px solid var(--color-borde);
            border-radius: var(--radio-lg);
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }
        .tarjeta-seleccion-servicio:hover {
            border-color: var(--color-acento);
            background-color: rgba(14, 165, 233, 0.04);
        }
        .tarjeta-seleccion-servicio.seleccionado {
            border-color: var(--color-acento);
            background: rgba(14, 165, 233, 0.1);
            box-shadow: 0 0 0 1px var(--color-acento);
        }
        .horarios-grilla-slots {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
            gap: 0.65rem;
            margin-top: 1rem;
        }
        .slot-horario-btn {
            background-color: var(--color-superficie-alt);
            border: 1px solid var(--color-borde);
            border-radius: var(--radio-md);
            padding: 0.65rem 0.25rem;
            text-align: center;
            font-family: var(--fuente-titulo);
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--color-texto);
            cursor: pointer;
            transition: all 0.15s ease;
            user-select: none;
        }
        .slot-horario-btn:hover:not(.deshabilitado) {
            border-color: var(--color-acento);
            background-color: rgba(14, 165, 233, 0.15);
        }
        .slot-horario-btn.seleccionado {
            background-color: var(--color-acento);
            border-color: var(--color-acento);
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(14, 165, 233, 0.4);
        }
        .slot-horario-btn.deshabilitado {
            opacity: 0.35;
            cursor: not-allowed;
            background-color: var(--color-borde-suave);
            text-decoration: line-through;
        }
        .resumen-lateral-fijo {
            position: sticky;
            top: 5rem;
            background-color: var(--color-superficie);
            border: 1px solid var(--color-borde-suave);
            border-radius: var(--radio-xl);
            padding: 1.75rem;
            box-shadow: var(--sombra-md);
        }
        .resumen-titulo {
            font-family: var(--fuente-titulo);
            font-size: 1.2rem;
            font-weight: 800;
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid var(--color-borde-suave);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .resumen-item-fila {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 0.6rem 0;
            border-bottom: 1px dashed var(--color-borde-suave);
            font-size: 0.925rem;
        }
        .resumen-item-fila:last-child {
            border-bottom: none;
        }
        .resumen-item-etiqueta {
            color: var(--color-texto-apagado);
        }
        .resumen-item-valor {
            font-weight: 700;
            color: var(--color-texto);
            text-align: right;
            max-width: 180px;
        }
        .resumen-total-bloque {
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 2px solid var(--color-borde);
            display: flex;
            justify-content: space-between;
            align-items: baseline;
        }
        .resumen-total-valor {
            font-family: var(--fuente-titulo);
            font-size: 1.85rem;
            font-weight: 800;
            color: var(--color-acento);
        }
    </style>
</head>
<body>
    <?php $paginaActual = 'reservar'; include dirname(__DIR__) . '/layouts/public_navbar.php'; ?>

    <main class="reserva-contenedor-principal">
        <div style="text-align: center; margin-bottom: 2.5rem;">
            <span style="font-family: var(--fuente-titulo); font-size: 0.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-acento);">Reserva Inmediata</span>
            <h1 style="font-size: 2.5rem; font-weight: 800; margin-top: 0.35rem;">Agendar Turno Sin Registrarse</h1>
            <p style="color: var(--color-texto-apagado); font-size: 1.05rem; margin-top: 0.5rem; max-width: 600px; margin-left: auto; margin-right: auto;">
                Completa 4 pasos simples. Capacidad maxima de 3 vehiculos en simultaneo para garantizar atencion inmediata sin demoras.
            </p>
        </div>

        <?php if (!empty($flashError)): ?>
            <div class="alerta alerta-error" style="margin-bottom: 2rem;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <span><?= htmlspecialchars($flashError, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
        <?php endif; ?>

        <form action="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/reservar" method="POST" id="form-reserva-publica">
            <?= $csrfCampo ?>

            <!-- Campo trampa antispam Honeypot (invisible para humanos) -->
            <div style="display: none;" aria-hidden="true">
                <input type="text" name="sitio_web" tabindex="-1" autocomplete="off">
            </div>

            <div class="reserva-layout-grid">
                <!-- Columna de Pasos -->
                <div>
                    <!-- Paso 1: Vehiculo -->
                    <section class="paso-card activo" id="paso-1-card">
                        <div class="paso-encabezado">
                            <div class="paso-numero">1</div>
                            <div>
                                <h2 class="paso-titulo-texto">Selecciona la Categoria del Vehiculo</h2>
                                <p style="font-size: 0.9rem; color: var(--color-texto-secundario); margin-top: 0.2rem;">
                                    El tipo de rodado define el tiempo exacto de lavado y la tarifa fija pactada.
                                </p>
                            </div>
                        </div>

                        <div class="grilla-categorias-vehiculo">
                            <?php foreach ($tiposVehiculo as $tv): ?>
                                <div class="boton-categoria-vehiculo" data-tipo-id="<?= $tv['id'] ?>" data-nombre="<?= htmlspecialchars($tv['nombre'], ENT_QUOTES, 'UTF-8') ?>" data-duracion="<?= $tv['duracion_minutos'] ?>">
                                    <div class="cat-nombre"><?= htmlspecialchars($tv['nombre'], ENT_QUOTES, 'UTF-8') ?></div>
                                    <div class="cat-duracion"><?= $tv['duracion_minutos'] ?> min</div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <input type="hidden" name="tipo_vehiculo_id" id="input-tipo-vehiculo-id" value="">
                        <input type="hidden" name="marca" id="input-marca" value="Generico">
                        <input type="hidden" name="modelo" id="input-modelo" value="Vehiculo">

                        <!-- Buscador de modelos para clientes con dudas -->
                        <div class="buscador-modelos-box">
                            <label for="buscador-modelo-asistente" style="font-size: 0.85rem; font-weight: 700; color: var(--color-texto); display: block; margin-bottom: 0.45rem;">
                                No estas seguro de la categoria? Busca tu marca o modelo:
                            </label>
                            <input type="text" id="buscador-modelo-asistente" class="input-buscador-modelos" placeholder="Escribi ej: Ford Ka, Hilux, Cronos, 208, Amarok..." autocomplete="off">

                            <div id="dropdown-modelos" class="dropdown-modelos-resultados"></div>

                            <div id="aviso-modelo-confirmado" class="aviso-confirmacion-modelo">
                                <span id="texto-aviso-modelo"></span>
                            </div>

                            <div style="margin-top: 1rem; display: flex; align-items: center; gap: 0.65rem;">
                                <input type="checkbox" name="tipo_a_confirmar" id="check-a-confirmar" value="1" style="width: 17px; height: 17px; accent-color: var(--color-acento);">
                                <label for="check-a-confirmar" style="font-size: 0.875rem; color: var(--color-texto-secundario); cursor: pointer;">
                                    No estoy seguro / Mi modelo no aparece (El recepcionista confirmara la categoria al ingresar).
                                </label>
                            </div>
                        </div>

                        <!-- Patente opcional -->
                        <div style="margin-top: 1.25rem;">
                            <label for="input-patente" style="font-size: 0.85rem; font-weight: 600; color: var(--color-texto-secundario); display: block; margin-bottom: 0.35rem;">
                                Patente (Opcional — podes informarla al momento de llegar):
                            </label>
                            <input type="text" name="patente" id="input-patente" class="campo-control input-patente" placeholder="Ej: AB123CD o ABC123" maxlength="7" pattern="[a-zA-Z0-9]{6,7}" title="Patente argentina valida (6 o 7 caracteres alfanumericos)" style="max-width: 240px; text-transform: uppercase;">
                        </div>
                    </section>

                    <!-- Paso 2: Servicio -->
                    <section class="paso-card" id="paso-2-card">
                        <div class="paso-encabezado">
                            <div class="paso-numero">2</div>
                            <div>
                                <h2 class="paso-titulo-texto">Elegi el Servicio</h2>
                                <p style="font-size: 0.9rem; color: var(--color-texto-secundario); margin-top: 0.2rem;">
                                    Ambos servicios insumen la misma duracion de turno para cada vehiculo.
                                </p>
                            </div>
                        </div>

                        <div class="grilla-servicios-reserva">
                            <?php foreach ($servicios as $s): ?>
                                <div class="tarjeta-seleccion-servicio" data-servicio-id="<?= $s['id'] ?>" data-nombre="<?= htmlspecialchars($s['nombre'], ENT_QUOTES, 'UTF-8') ?>">
                                    <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--color-texto);">
                                        <?= htmlspecialchars($s['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                    </h3>
                                    <p style="font-size: 0.875rem; color: var(--color-texto-secundario); line-height: 1.5; margin-bottom: 1rem;">
                                        <?= htmlspecialchars($s['descripcion'], ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                    <div class="servicio-precio-tag" style="font-family: var(--fuente-titulo); font-size: 1.15rem; font-weight: 800; color: var(--color-acento);">
                                        <span class="precio-dinamico-servicio" data-servicio="<?= $s['id'] ?>">Seleccione categoria</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="servicio_id" id="input-servicio-id" value="">
                    </section>

                    <!-- Paso 3: Fecha y Horario -->
                    <section class="paso-card" id="paso-3-card">
                        <div class="paso-encabezado">
                            <div class="paso-numero">3</div>
                            <div>
                                <h2 class="paso-titulo-texto">Fecha y Horario del Turno</h2>
                                <p style="font-size: 0.9rem; color: var(--color-texto-secundario); margin-top: 0.2rem;">
                                    Atendemos de lunes a sabados de 10:00 a 18:00 hs. Solo franjas con boxes disponibles.
                                </p>
                            </div>
                        </div>

                        <div style="max-width: 320px; margin-bottom: 1.25rem;">
                            <label for="input-fecha-reserva" style="font-size: 0.85rem; font-weight: 700; color: var(--color-texto); display: block; margin-bottom: 0.45rem;">
                                Seleccionar Fecha:
                            </label>
                            <input type="date" name="fecha" id="input-fecha-reserva" class="campo-control" min="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d', strtotime('+30 days')) ?>">
                        </div>

                        <div>
                            <span style="font-size: 0.85rem; font-weight: 700; color: var(--color-texto); display: block; margin-bottom: 0.45rem;">
                                Horarios Disponibles:
                            </span>
                            <div id="contenedor-slots-horarios" class="horarios-grilla-slots">
                                <div style="color: var(--color-texto-apagado); font-size: 0.9rem; grid-column: 1 / -1; padding: 1rem 0;">
                                    Selecciona una categoria y una fecha para calcular los turnos libres.
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="hora_inicio" id="input-hora-inicio" value="">
                    </section>

                    <!-- Paso 4: Datos de Contacto -->
                    <section class="paso-card" id="paso-4-card">
                        <div class="paso-encabezado">
                            <div class="paso-numero">4</div>
                            <div>
                                <h2 class="paso-titulo-texto">Datos de Contacto</h2>
                                <p style="font-size: 0.9rem; color: var(--color-texto-secundario); margin-top: 0.2rem;">
                                    Te enviaremos el enlace de gestion y un aviso en caso de imprevistos.
                                </p>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.25rem;">
                            <div>
                                <label for="input-nombre" style="font-size: 0.85rem; font-weight: 700; color: var(--color-texto); display: block; margin-bottom: 0.35rem;">
                                    Nombre y Apellido *:
                                </label>
                                <input type="text" name="nombre" id="input-nombre" class="campo-control" placeholder="Ej: Martin Gomez" required value="<?= htmlspecialchars($flashFormData['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div>
                                <label for="input-telefono" style="font-size: 0.85rem; font-weight: 700; color: var(--color-texto); display: block; margin-bottom: 0.35rem;">
                                    Telefono Celular *:
                                </label>
                                <input type="tel" name="telefono" id="input-telefono" class="campo-control" placeholder="Ej: 11 4567 8900" required value="<?= htmlspecialchars($flashFormData['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                <span style="font-size: 0.775rem; color: var(--color-texto-apagado); display: block; margin-top: 0.25rem;">Maximo 3 reservas activas por telefono.</span>
                            </div>
                            <div style="grid-column: 1 / -1;">
                                <label for="input-correo" style="font-size: 0.85rem; font-weight: 600; color: var(--color-texto-secundario); display: block; margin-bottom: 0.35rem;">
                                    Correo Electronico (Opcional — para respaldo del enlace):
                                </label>
                                <input type="email" name="correo" id="input-correo" class="campo-control" placeholder="Ej: cliente@correo.com" value="<?= htmlspecialchars($flashFormData['correo'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                        </div>
                    </section>

                    <!-- Paso 5: Confirmar -->
                    <section class="paso-card" id="paso-5-card">
                        <div class="paso-encabezado">
                            <div class="paso-numero">5</div>
                            <div>
                                <h2 class="paso-titulo-texto">Confirmacion y Politicas</h2>
                                <p style="font-size: 0.9rem; color: var(--color-texto-secundario); margin-top: 0.2rem;">
                                    Revisa el resumen lateral antes de confirmar tu solicitud.
                                </p>
                            </div>
                        </div>

                        <div style="background-color: var(--color-superficie-alt); border-radius: var(--radio-md); padding: 1.25rem; font-size: 0.9rem; line-height: 1.6; color: var(--color-texto-secundario); margin-bottom: 1.5rem;">
                            <strong>Terminos del servicio:</strong> Al confirmar, tu turno queda reservado y el precio pactado congelado. Recorda que tenes modificacion o cancelacion directa hasta 2 horas antes. Con menos de 2 horas se genera una solicitud. El pago se efectua en el lavadero (Efectivo, QR o Transferencia).
                        </div>

                        <button type="submit" id="btn-confirmar-reserva" class="btn btn-primario btn-lg" style="width: 100%; padding: 1.15rem; font-size: 1.1rem; justify-content: center;">
                            <span>Confirmar Reserva y Obtener Enlace</span>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </button>
                    </section>
                </div>

                <!-- Columna Resumen Lateral Fijo (Sticky) -->
                <div>
                    <aside class="resumen-lateral-fijo">
                        <div class="resumen-titulo">
                            <span>Resumen del Turno</span>
                            <span class="badge badge-activo" id="resumen-estado-badge">En curso</span>
                        </div>

                        <div class="resumen-item-fila">
                            <span class="resumen-item-etiqueta">Vehiculo:</span>
                            <span class="resumen-item-valor" id="resumen-vehiculo-texto">Sin seleccionar</span>
                        </div>
                        <div class="resumen-item-fila">
                            <span class="resumen-item-etiqueta">Categoria:</span>
                            <span class="resumen-item-valor" id="resumen-categoria-texto">-</span>
                        </div>
                        <div class="resumen-item-fila">
                            <span class="resumen-item-etiqueta">Servicio:</span>
                            <span class="resumen-item-valor" id="resumen-servicio-texto">Sin seleccionar</span>
                        </div>
                        <div class="resumen-item-fila">
                            <span class="resumen-item-etiqueta">Fecha:</span>
                            <span class="resumen-item-valor" id="resumen-fecha-texto">-</span>
                        </div>
                        <div class="resumen-item-fila">
                            <span class="resumen-item-etiqueta">Horario:</span>
                            <span class="resumen-item-valor" id="resumen-horario-texto">-</span>
                        </div>
                        <div class="resumen-item-fila">
                            <span class="resumen-item-etiqueta">Duracion:</span>
                            <span class="resumen-item-valor" id="resumen-duracion-texto">-</span>
                        </div>

                        <div class="resumen-total-bloque">
                            <div>
                                <span style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--color-texto-apagado); display: block;">Total Acordado:</span>
                                <span style="font-size: 0.75rem; color: var(--color-texto-apagado);">Pago presencial al retirar</span>
                            </div>
                            <div class="resumen-total-valor" id="resumen-total-precio">$0</div>
                        </div>
                    </aside>
                </div>
            </div>
        </form>
    </main>

    <?php include dirname(__DIR__) . '/layouts/public_footer.php'; ?>

    <!-- Matriz de precios en formato JSON para el frontend reactivo -->
    <script>
        const MATRIZ_PRECIOS = <?= json_encode($precios, JSON_UNESCAPED_UNICODE) ?>;
        const URL_BASE = '<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>';
    </script>
    <script src="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/assets/js/app.js"></script>
    <script>
        // Logica del asistente de reserva publica
        document.addEventListener('DOMContentLoaded', function() {
            let estadoReserva = {
                tipoVehiculoId: null,
                tipoVehiculoNombre: '',
                duracion: 60,
                servicioId: null,
                servicioNombre: '',
                precio: 0,
                fecha: '',
                horaInicio: '',
                modeloTexto: 'Vehiculo'
            };

            const botonesCat = document.querySelectorAll('.boton-categoria-vehiculo');
            const inputTipoId = document.getElementById('input-tipo-vehiculo-id');
            const inputMarca = document.getElementById('input-marca');
            const inputModelo = document.getElementById('input-modelo');
            const inputServicioId = document.getElementById('input-servicio-id');
            const inputFecha = document.getElementById('input-fecha-reserva');
            const inputHora = document.getElementById('input-hora-inicio');
            const slotsContenedor = document.getElementById('contenedor-slots-horarios');
            const tarjetasServicio = document.querySelectorAll('.tarjeta-seleccion-servicio');
            const tagsPreciosServicio = document.querySelectorAll('.precio-dinamico-servicio');

            // Elementos del resumen lateral
            const resVehiculo = document.getElementById('resumen-vehiculo-texto');
            const resCategoria = document.getElementById('resumen-categoria-texto');
            const resServicio = document.getElementById('resumen-servicio-texto');
            const resFecha = document.getElementById('resumen-fecha-texto');
            const resHorario = document.getElementById('resumen-horario-texto');
            const resDuracion = document.getElementById('resumen-duracion-texto');
            const resPrecio = document.getElementById('resumen-total-precio');

            function actualizarResumen() {
                resVehiculo.textContent = estadoReserva.modeloTexto || 'Sin especificar';
                resCategoria.textContent = estadoReserva.tipoVehiculoNombre || '-';
                resServicio.textContent = estadoReserva.servicioNombre || 'Sin seleccionar';
                resFecha.textContent = estadoReserva.fecha || '-';
                resHorario.textContent = estadoReserva.horaInicio ? estadoReserva.horaInicio.substring(0, 5) + ' hs' : '-';
                resDuracion.textContent = estadoReserva.tipoVehiculoId ? estadoReserva.duracion + ' min' : '-';
                resPrecio.textContent = estadoReserva.precio > 0 ? '$' + estadoReserva.precio.toLocaleString('es-AR') : '$0';
            }

            function actualizarPreciosServicios() {
                if (!estadoReserva.tipoVehiculoId) return;

                tagsPreciosServicio.forEach(tag => {
                    const servId = tag.getAttribute('data-servicio');
                    if (MATRIZ_PRECIOS[servId] && MATRIZ_PRECIOS[servId][estadoReserva.tipoVehiculoId]) {
                        const val = MATRIZ_PRECIOS[servId][estadoReserva.tipoVehiculoId].precio;
                        tag.textContent = '$' + val.toLocaleString('es-AR');
                    } else {
                        tag.textContent = 'Consultar';
                    }
                });

                if (estadoReserva.servicioId && MATRIZ_PRECIOS[estadoReserva.servicioId] && MATRIZ_PRECIOS[estadoReserva.servicioId][estadoReserva.tipoVehiculoId]) {
                    estadoReserva.precio = MATRIZ_PRECIOS[estadoReserva.servicioId][estadoReserva.tipoVehiculoId].precio;
                }
            }

            function seleccionarCategoria(id, nombre, duracion) {
                estadoReserva.tipoVehiculoId = id;
                estadoReserva.tipoVehiculoNombre = nombre;
                estadoReserva.duracion = duracion;
                inputTipoId.value = id;

                botonesCat.forEach(b => {
                    if (b.getAttribute('data-tipo-id') === String(id)) {
                        b.classList.add('seleccionado');
                    } else {
                        b.classList.remove('seleccionado');
                    }
                });

                actualizarPreciosServicios();
                actualizarResumen();

                if (estadoReserva.fecha) {
                    cargarDisponibilidad();
                }
            }

            botonesCat.forEach(b => {
                b.addEventListener('click', function() {
                    const id = parseInt(this.getAttribute('data-tipo-id'), 10);
                    const nom = this.getAttribute('data-nombre');
                    const dur = parseInt(this.getAttribute('data-duracion'), 10);
                    seleccionarCategoria(id, nom, dur);
                });
            });

            // Buscador interactivo de modelos
            const inputBusq = document.getElementById('buscador-modelo-asistente');
            const dropResultados = document.getElementById('dropdown-modelos');
            const avisoModelo = document.getElementById('aviso-modelo-confirmado');
            const textoAviso = document.getElementById('texto-aviso-modelo');
            let debounce = null;
            let indiceFoco = -1;

            inputBusq.addEventListener('input', function() {
                const q = this.value.trim();
                clearTimeout(debounce);
                indiceFoco = -1;

                if (q.length < 2) {
                    dropResultados.style.display = 'none';
                    dropResultados.innerHTML = '';
                    return;
                }

                debounce = setTimeout(() => {
                    fetch(URL_BASE + '/api/modelos?q=' + encodeURIComponent(q))
                        .then(r => r.json())
                        .then(data => {
                            dropResultados.innerHTML = '';
                            if (!data.modelos || data.modelos.length === 0) {
                                dropResultados.innerHTML = '<div style="padding: 0.85rem 1rem; color: var(--color-texto-apagado); font-size: 0.875rem;">No se encontro ese modelo exacto. Podes elegir la categoria mas parecida en los botones superiores.</div>';
                                dropResultados.style.display = 'block';
                                return;
                            }

                            data.modelos.forEach((m, idx) => {
                                const row = document.createElement('div');
                                row.className = 'dropdown-item-modelo';
                                row.setAttribute('data-idx', idx);
                                row.innerHTML = '<span><strong>' + m.marca + ' ' + m.modelo + '</strong></span><span class="badge badge-activo">' + m.tipo_nombre + '</span>';
                                row.addEventListener('click', function() {
                                    aplicarModeloSeleccionado(m);
                                });
                                dropResultados.appendChild(row);
                            });
                            dropResultados.style.display = 'block';
                        })
                        .catch(() => {
                            dropResultados.style.display = 'none';
                        });
                }, 250);
            });

            // Navegacion con teclado en resultados
            inputBusq.addEventListener('keydown', function(e) {
                const items = dropResultados.querySelectorAll('.dropdown-item-modelo');
                if (!items.length || dropResultados.style.display === 'none') return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    indiceFoco = (indiceFoco + 1) % items.length;
                    actualizarFocoTeclado(items);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    indiceFoco = (indiceFoco - 1 + items.length) % items.length;
                    actualizarFocoTeclado(items);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (indiceFoco >= 0 && items[indiceFoco]) {
                        items[indiceFoco].click();
                    }
                }
            });

            function actualizarFocoTeclado(items) {
                items.forEach((it, idx) => {
                    if (idx === indiceFoco) {
                        it.classList.add('activo-teclado');
                        it.scrollIntoView({ block: 'nearest' });
                    } else {
                        it.classList.remove('activo-teclado');
                    }
                });
            }

            function aplicarModeloSeleccionado(m) {
                inputBusq.value = m.marca + ' ' + m.modelo;
                inputMarca.value = m.marca;
                inputModelo.value = m.modelo;
                estadoReserva.modeloTexto = m.marca + ' ' + m.modelo;
                dropResultados.style.display = 'none';

                // Seleccionar categoria automaticamente
                seleccionarCategoria(parseInt(m.tipo_vehiculo_id, 10), m.tipo_nombre, parseInt(m.duracion_minutos, 10));

                // Aviso de confirmacion visual
                avisoModelo.style.display = 'block';
                textoAviso.textContent = 'Tu ' + m.marca + ' ' + m.modelo + ' se considera categoria ' + m.tipo_nombre + '.';
            }

            // Opcion "No estoy seguro"
            const checkAConfirmar = document.getElementById('check-a-confirmar');
            checkAConfirmar.addEventListener('change', function() {
                if (this.checked) {
                    if (!estadoReserva.tipoVehiculoId) {
                        // Por defecto asigna Auto
                        const btnAuto = Array.from(botonesCat).find(b => b.getAttribute('data-nombre') === 'Auto');
                        if (btnAuto) btnAuto.click();
                    }
                    avisoModelo.style.display = 'block';
                    textoAviso.textContent = 'Marcado a confirmar. El recepcionista verificara la categoria al recibir el vehiculo.';
                }
            });

            // Seleccion de servicio
            tarjetasServicio.forEach(ts => {
                ts.addEventListener('click', function() {
                    tarjetasServicio.forEach(t => t.classList.remove('seleccionado'));
                    this.classList.add('seleccionado');

                    const id = parseInt(this.getAttribute('data-servicio-id'), 10);
                    const nom = this.getAttribute('data-nombre');

                    estadoReserva.servicioId = id;
                    estadoReserva.servicioNombre = nom;
                    inputServicioId.value = id;

                    actualizarPreciosServicios();
                    actualizarResumen();
                });
            });

            // Fecha y horarios
            inputFecha.addEventListener('change', function() {
                estadoReserva.fecha = this.value;
                estadoReserva.horaInicio = '';
                inputHora.value = '';
                actualizarResumen();
                cargarDisponibilidad();
            });

            function cargarDisponibilidad() {
                if (!estadoReserva.fecha) return;
                slotsContenedor.innerHTML = '<div style="color: var(--color-texto-apagado); padding: 1rem 0;">Consultando turnos libres...</div>';

                const url = URL_BASE + '/api/disponibilidad?fecha=' + encodeURIComponent(estadoReserva.fecha) + '&duracion=' + estadoReserva.duracion;
                fetch(url)
                    .then(r => r.json())
                    .then(data => {
                        slotsContenedor.innerHTML = '';
                        if (!data.franjas || data.franjas.length === 0) {
                            slotsContenedor.innerHTML = '<div style="color: var(--color-texto-apagado); padding: 1rem 0; grid-column: 1 / -1;">No hay atencion o no quedan turnos disponibles para esta fecha.</div>';
                            return;
                        }

                        data.franjas.forEach(f => {
                            const hora = f.hora || f.hora_inicio || '';
                            const btn = document.createElement('div');
                            btn.className = 'slot-horario-btn' + (f.disponible ? '' : ' deshabilitado');
                            btn.textContent = hora.substring(0, 5);

                            if (f.disponible) {
                                btn.addEventListener('click', function() {
                                    document.querySelectorAll('.slot-horario-btn').forEach(s => s.classList.remove('seleccionado'));
                                    this.classList.add('seleccionado');
                                    estadoReserva.horaInicio = hora;
                                    inputHora.value = hora;
                                    actualizarResumen();
                                });
                            }
                            slotsContenedor.appendChild(btn);
                        });
                    })
                    .catch(() => {
                        slotsContenedor.innerHTML = '<div style="color: var(--color-peligro); padding: 1rem 0; grid-column: 1 / -1;">Error al consultar disponibilidad.</div>';
                    });
            }

            // Validacion antes de enviar el formulario
            const formReserva = document.getElementById('form-reserva-publica');
            formReserva.addEventListener('submit', function(e) {
                if (!estadoReserva.tipoVehiculoId) {
                    e.preventDefault();
                    alert('Por favor selecciona la categoria de tu vehiculo (Paso 1).');
                    document.getElementById('paso-1-card').scrollIntoView({ behavior: 'smooth' });
                    return;
                }
                if (!estadoReserva.servicioId) {
                    e.preventDefault();
                    alert('Por favor selecciona el servicio de lavado (Paso 2).');
                    document.getElementById('paso-2-card').scrollIntoView({ behavior: 'smooth' });
                    return;
                }
                if (!estadoReserva.fecha || !estadoReserva.horaInicio) {
                    e.preventDefault();
                    alert('Por favor selecciona la fecha y un horario disponible (Paso 3).');
                    document.getElementById('paso-3-card').scrollIntoView({ behavior: 'smooth' });
                    return;
                }
            });
        });
    </script>
</body>
</html>
