<?php
/**
 * WashFlow - Pagina de Inicio Publica (Landing Page Completa - Requisito 3.4)
 * 10 secciones completas con estetica refinada oscura/petroleo y cyan. Sin emojis.
 */
?>
<!DOCTYPE html>
<html lang="es" data-tema="oscuro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WashFlow — Lavadero Profesional de Vehiculos</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/assets/css/app.css">
</head>
<body>
    <?php $paginaActual = 'inicio'; include dirname(__DIR__) . '/layouts/public_navbar.php'; ?>

    <main>
        <!-- 1. Hero con propuesta de valor e insignia de apertura en tiempo real -->
        <section class="hero">
            <div class="hero-contenedor">
                <?php if ($abierto): ?>
                    <div class="hero-badge-pill abierto">
                        <span class="punto-pulso-activo"></span>
                        <span>LAVADERO OPERATIVO &bull; Atendiendo hoy hasta las 18:00 hs</span>
                    </div>
                <?php else: ?>
                    <div class="hero-badge-pill cerrado">
                        <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#94a3b8; margin-right:4px;"></span>
                        <span>CERRADO EN ESTE MOMENTO &bull; Atencion de Lunes a Sabados de 10:00 a 18:00 hs</span>
                    </div>
                <?php endif; ?>

                <h1 class="hero-titulo">Cuidado automotor profesional con atencion programada</h1>
                <p class="hero-subtitulo">
                    Pocas pantallas, textos claros y sin esperas. Reserva online en 2 minutos sin necesidad de crear cuenta previa, con capacidad maxima de 3 vehiculos en simultaneo y precios oficiales congelados.
                </p>

                <div class="hero-badges-lista">
                    <span class="hero-tag-item">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Sin registro previo
                    </span>
                    <span class="hero-tag-item">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        Turnos de 30 a 90 min
                    </span>
                    <span class="hero-tag-item">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        Capacidad max. 3 vehiculos
                    </span>
                    <span class="hero-tag-item">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                        Precios congelados en BD
                    </span>
                </div>

                <div class="hero-acciones">
                    <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/reservar" class="btn btn-primario btn-lg" style="padding: 0.95rem 2.25rem;">
                        <span>Reservar Turno Online</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </a>
                    <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/guia-lavados" class="btn btn-secundario btn-lg">
                        Ver Guia de Servicios
                    </a>
                </div>

                <!-- 3 Tarjetas de Metricas Clave Flotantes -->
                <div class="hero-stats-grilla">
                    <div class="hero-stat-card">
                        <div class="hero-stat-icono">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"></rect><path d="M7 17v-4a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v4"></path></svg>
                        </div>
                        <div>
                            <div class="hero-stat-titulo">3 Bahias Simultaneas</div>
                            <div class="hero-stat-desc">Control estricto de aforo para eliminar esperas innecesarias.</div>
                        </div>
                    </div>
                    <div class="hero-stat-card">
                        <div class="hero-stat-icono">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
                        </div>
                        <div>
                            <div class="hero-stat-titulo">Regla de 2 Horas</div>
                            <div class="hero-stat-desc">Reprograma o cancela directamente con tu codigo y telefono.</div>
                        </div>
                    </div>
                    <div class="hero-stat-card">
                        <div class="hero-stat-icono">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                        </div>
                        <div>
                            <div class="hero-stat-titulo">Pago al Retirar</div>
                            <div class="hero-stat-desc">Sin señas online. Efectivo, QR de Mercado Pago o Transferencia.</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 2. Como funciona en 4 pasos -->
        <section class="seccion-contenedor">
            <div class="seccion-encabezado">
                <span class="seccion-rotulo">Metodologia</span>
                <h2 class="seccion-titulo">Como Funciona WashFlow</h2>
                <p class="seccion-descripcion">
                    Un sistema pensado para resolver en pocos minutos desde tu celular, sin burocracia ni contrasenas obligatorias.
                </p>
            </div>

            <div class="grilla-pasos">
                <div class="tarjeta-paso">
                    <div class="tarjeta-paso-header">
                        <div class="tarjeta-paso-numero">01</div>
                        <div class="tarjeta-paso-icono">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"></path><circle cx="7" cy="17" r="2"></circle><path d="M9 17h6"></path><circle cx="17" cy="17" r="2"></circle></svg>
                        </div>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--color-texto);">Elegi tu Vehiculo</h3>
                    <p style="font-size: 0.92rem; color: var(--color-texto-secundario); line-height: 1.6;">
                        Selecciona tu categoria (Moto, Auto, Camioneta o 4x4) o busca tu modelo exacto en nuestro clasificador inteligente para saber la duracion y tarifa.
                    </p>
                </div>

                <div class="tarjeta-paso">
                    <div class="tarjeta-paso-header">
                        <div class="tarjeta-paso-numero">02</div>
                        <div class="tarjeta-paso-icono">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        </div>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--color-texto);">Selecciona Dia y Hora</h3>
                    <p style="font-size: 0.92rem; color: var(--color-texto-secundario); line-height: 1.6;">
                        El sistema calcula en tiempo real las franjas horarias libres respetando de forma estricta el limite de 3 vehiculos simultaneos en taller.
                    </p>
                </div>

                <div class="tarjeta-paso">
                    <div class="tarjeta-paso-header">
                        <div class="tarjeta-paso-numero">03</div>
                        <div class="tarjeta-paso-icono">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>
                        </div>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--color-texto);">Confirma Sin Cuenta</h3>
                    <p style="font-size: 0.92rem; color: var(--color-texto-secundario); line-height: 1.6;">
                        Solo necesitas tu nombre y telefono de contacto. Recibiras un codigo alfanumerico legible y un enlace seguro para consultar o reprogramar.
                    </p>
                </div>

                <div class="tarjeta-paso">
                    <div class="tarjeta-paso-header">
                        <div class="tarjeta-paso-numero">04</div>
                        <div class="tarjeta-paso-icono">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                        </div>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--color-texto);">Paga al Retirar</h3>
                    <p style="font-size: 0.92rem; color: var(--color-texto-secundario); line-height: 1.6;">
                        Presentate en el horario asignado. El pago se efectua al retirar tu vehiculo impecable (Efectivo, Mercado Pago QR o Transferencia bancaria).
                    </p>
                </div>
            </div>
        </section>

        <!-- 3 y 4. Servicios y Tarifas Vigentes de la BD -->
        <section class="seccion-contenedor" id="tarifas">
            <div class="seccion-encabezado">
                <span class="seccion-rotulo">Tarifas Oficiales</span>
                <h2 class="seccion-titulo">Servicios y Precios Congelados</h2>
                <p class="seccion-descripcion">
                    Valores transparentes leidos directamente de nuestra base de datos operativa. El precio que ves al reservar es el precio final que pagas.
                </p>
            </div>

            <div class="grilla-servicios">
                <?php foreach ($servicios as $s): ?>
                    <?php $esDestacado = ((int)$s['id'] === 2); ?>
                    <div class="tarjeta-servicio <?= $esDestacado ? 'tarjeta-servicio-destacada' : '' ?>">
                        <?php if ($esDestacado): ?>
                            <span class="badge-destacado-flotante">Recomendado</span>
                        <?php endif; ?>

                        <div>
                            <h3 style="font-size: 1.65rem; font-weight: 800; margin-bottom: 0.75rem; color: var(--color-texto);">
                                <?= htmlspecialchars($s['nombre'], ENT_QUOTES, 'UTF-8') ?>
                            </h3>
                            <p style="color: var(--color-texto-secundario); font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.75rem;">
                                <?= htmlspecialchars($s['descripcion'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>

                        <div>
                            <div style="font-family: var(--fuente-titulo); font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--color-texto-apagado); margin-bottom: 0.85rem;">
                                Valores segun categoria:
                            </div>
                            <div style="margin-bottom: 2rem;">
                                <?php foreach ($tiposVehiculo as $tv): ?>
                                    <?php
                                    $precioInfo = $precios[$s['id']][$tv['id']] ?? null;
                                    $precioStr = $precioInfo ? '$' . number_format((float)$precioInfo['precio'], 0, ',', '.') : 'A consultar';
                                    $duracion = $tv['duracion_minutos'] ?? 60;
                                    ?>
                                    <div class="fila-precio-tabla">
                                        <span><strong><?= htmlspecialchars($tv['nombre'], ENT_QUOTES, 'UTF-8') ?></strong> <span style="font-size: 0.8rem; color: var(--color-texto-apagado);">(<?= $duracion ?> min)</span></span>
                                        <span style="font-family: var(--fuente-titulo); font-weight: 800; color: var(--color-acento); font-size: 1.1rem;"><?= $precioStr ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/reservar" class="btn btn-primario" style="width: 100%; justify-content: center; padding: 0.85rem;">
                                Reservar este servicio
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- 5. Que tipo de vehiculo tengo? Buscador interactivo incrustado -->
        <section class="seccion-contenedor">
            <div class="caja-buscador-incrustado">
                <span class="seccion-rotulo">Clasificador Inteligente</span>
                <h2 style="font-family: var(--fuente-titulo); font-size: 2rem; font-weight: 800; margin-top: 0.35rem; color: var(--color-texto);">
                    Que tipo de vehiculo tengo?
                </h2>
                <p style="color: var(--color-texto-secundario); font-size: 0.95rem; margin: 0.75rem auto 1.75rem; max-width: 580px; line-height: 1.6;">
                    Escribi la marca o modelo de tu vehiculo (ej: Ka, Hilux, Cronos, Tracker, Ranger...) y el sistema te indicara su categoria y tiempo de atencion.
                </p>

                <div style="max-width: 520px; margin: 0 auto; position: relative;">
                    <input type="text" id="buscador-landing-input" class="input-buscador-landing" placeholder="Buscar modelo (ej: Ford Ka)..." autocomplete="off">
                    <div id="dropdown-landing-resultados" class="dropdown-landing-resultados"></div>

                    <div id="aviso-landing-resultado" style="display: none; margin-top: 1rem; padding: 1rem; border-radius: var(--radio-md); background: rgba(14, 165, 233, 0.15); border: 1px solid var(--color-acento);">
                        <div id="texto-landing-resultado" style="font-weight: 700; color: #7dd3fc; margin-bottom: 0.5rem;"></div>
                        <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/reservar" class="btn btn-primario btn-sm" style="display: inline-flex;">
                            Reservar Turno con esta Categoria
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- 6. Como es el lavado, paso a paso -->
        <section class="seccion-contenedor">
            <div class="seccion-encabezado">
                <span class="seccion-rotulo">Proceso Profesional</span>
                <h2 class="seccion-titulo">El Lavado Paso a Paso</h2>
                <p class="seccion-descripcion">
                    Protocolo estandarizado de higiene y cuidado vehicular ejecutado por operarios capacitados.
                </p>
            </div>

            <div class="grilla-etapas-lavado">
                <div class="tarjeta-etapa-lavado">
                    <div class="etapa-icono-numero">01</div>
                    <div>
                        <h4 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 0.35rem; color: var(--color-texto);">Recepcion e Inspeccion</h4>
                        <p style="font-size: 0.875rem; color: var(--color-texto-secundario); line-height: 1.5;">
                            Ingreso al box asignado, verificacion de la categoria pactada y revision visual del estado de pintura.
                        </p>
                    </div>
                </div>

                <div class="tarjeta-etapa-lavado">
                    <div class="etapa-icono-numero">02</div>
                    <div>
                        <h4 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 0.35rem; color: var(--color-texto);">Desbarre a Alta Presion</h4>
                        <p style="font-size: 0.875rem; color: var(--color-texto-secundario); line-height: 1.5;">
                            Remocion de barro acumulado en pasarruedas, zocalos y llantas mediante agua a presion controlada.
                        </p>
                    </div>
                </div>

                <div class="tarjeta-etapa-lavado">
                    <div class="etapa-icono-numero">03</div>
                    <div>
                        <h4 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 0.35rem; color: var(--color-texto);">Espuma Activa pH Neutro</h4>
                        <p style="font-size: 0.875rem; color: var(--color-texto-secundario); line-height: 1.5;">
                            Bano de nieve quimica que disuelve el hollin sin agredir ceras ni tratamientos preexistentes.
                        </p>
                    </div>
                </div>

                <div class="tarjeta-etapa-lavado">
                    <div class="etapa-icono-numero">04</div>
                    <div>
                        <h4 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 0.35rem; color: var(--color-texto);">Aspirado Profundo</h4>
                        <p style="font-size: 0.875rem; color: var(--color-texto-secundario); line-height: 1.5;">
                            Limpieza exhaustiva de alfombras, butacas, paneles laterales y baul con turbinas de alta potencia.
                        </p>
                    </div>
                </div>

                <div class="tarjeta-etapa-lavado">
                    <div class="etapa-icono-numero">05</div>
                    <div>
                        <h4 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 0.35rem; color: var(--color-texto);">Secado y Acondicionado</h4>
                        <p style="font-size: 0.875rem; color: var(--color-texto-secundario); line-height: 1.5;">
                            Secado con microfibras waffle anti-marcas, limpieza de cristales y silicona en cubiertas.
                        </p>
                    </div>
                </div>

                <div class="tarjeta-etapa-lavado">
                    <div class="etapa-icono-numero">06</div>
                    <div>
                        <h4 style="font-size: 1.1rem; font-weight: 800; margin-bottom: 0.35rem; color: var(--color-texto);">Control Final y Entrega</h4>
                        <p style="font-size: 0.875rem; color: var(--color-texto-secundario); line-height: 1.5;">
                            Inspeccion por supervisor de turno y entrega puntual del rodado listo para circular.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 7. Preguntas Frecuentes (FAQ) en formato acordeon administrable -->
        <section class="seccion-contenedor" id="faq">
            <div class="seccion-encabezado">
                <span class="seccion-rotulo">Dudas Resueltas</span>
                <h2 class="seccion-titulo">Preguntas Frecuentes</h2>
                <p class="seccion-descripcion">
                    Todo lo que necesitas saber antes de acercarte a nuestras instalaciones.
                </p>
            </div>

            <div class="acordeon-faq-contenedor">
                <?php if (!empty($preguntas)): ?>
                    <?php foreach ($preguntas as $idx => $p): ?>
                        <div class="acordeon-faq-item <?= $idx === 0 ? 'abierto' : '' ?>">
                            <button class="acordeon-faq-boton" aria-expanded="<?= $idx === 0 ? 'true' : 'false' ?>">
                                <span><?= htmlspecialchars($p['pregunta'], ENT_QUOTES, 'UTF-8') ?></span>
                                <span class="acordeon-faq-icono"><?= $idx === 0 ? '-' : '+' ?></span>
                            </button>
                            <div class="acordeon-faq-respuesta">
                                <p><?= nl2br(htmlspecialchars($p['respuesta'], ENT_QUOTES, 'UTF-8')) ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="acordeon-faq-item abierto">
                        <button class="acordeon-faq-boton" aria-expanded="true">
                            <span>Cuanto tiempo dura el lavado?</span>
                            <span class="acordeon-faq-icono">-</span>
                        </button>
                        <div class="acordeon-faq-respuesta">
                            <p>El turno dura entre 40 y 75 minutos segun la categoria del vehiculo (Motos 40 min, Autos 50 min, Camionetas 60 min, 4x4 75 min). Tanto el Lavado Normal como el Premium se realizan dentro de esa misma franja.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- 8. Galeria de Trabajos Administrable -->
        <section class="seccion-contenedor">
            <div class="seccion-encabezado">
                <span class="seccion-rotulo">Resultados Reales</span>
                <h2 class="seccion-titulo">Galeria de Trabajos Realizados</h2>
                <p class="seccion-descripcion">
                    Vehiculos terminados por nuestro equipo. Brillo, proteccion y dedicacion en cada detalle.
                </p>
            </div>

            <div class="grilla-galeria-fotos">
                <?php if (!empty($galeria)): ?>
                    <?php foreach ($galeria as $g): ?>
                        <div class="tarjeta-galeria-item">
                            <div class="galeria-imagen-caja">
                                <img src="<?= htmlspecialchars($g['imagen_url'], ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($g['titulo'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                            </div>
                            <div class="galeria-info-caja">
                                <h4 style="font-size: 1.05rem; font-weight: 800; color: var(--color-texto); margin-bottom: 0.35rem;">
                                    <?= htmlspecialchars($g['titulo'], ENT_QUOTES, 'UTF-8') ?>
                                </h4>
                                <?php if (!empty($g['descripcion'])): ?>
                                    <p style="font-size: 0.85rem; color: var(--color-texto-secundario); line-height: 1.4;">
                                        <?= htmlspecialchars($g['descripcion'], ENT_QUOTES, 'UTF-8') ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align: center; color: var(--color-texto-apagado); grid-column: 1 / -1;">No hay imagenes en la galeria por el momento.</p>
                <?php endif; ?>
            </div>
        </section>

        <!-- 9. Ubicacion, Horario y Contacto -->
        <section class="seccion-contenedor">
            <div class="seccion-encabezado">
                <span class="seccion-rotulo">Encontranos</span>
                <h2 class="seccion-titulo">Ubicacion, Horario y Contacto</h2>
                <p class="seccion-descripcion">
                    Instalaciones de facil acceso en zona norte, preparadas con 3 boxes de atencion simultanea.
                </p>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                <div style="background-color: var(--color-superficie); border: 1px solid var(--color-borde-suave); border-radius: var(--radio-xl); padding: 2rem;">
                    <h3 style="font-size: 1.35rem; font-weight: 800; margin-bottom: 1.25rem; color: var(--color-texto);">Puntos de Contacto</h3>
                    
                    <div style="margin-bottom: 1.25rem;">
                        <span style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: var(--color-texto-apagado); display: block;">Direccion:</span>
                        <div style="font-size: 1.05rem; font-weight: 700; color: var(--color-texto); margin-top: 0.2rem;">Av. Santa Fe 2480, Martinez, Buenos Aires</div>
                    </div>

                    <div style="margin-bottom: 1.25rem;">
                        <span style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: var(--color-texto-apagado); display: block;">Horario de Atencion:</span>
                        <div style="font-size: 1.05rem; font-weight: 700; color: var(--color-texto); margin-top: 0.2rem;">Lunes a Sabados de 10:00 a 18:00 hs</div>
                        <div style="font-size: 0.85rem; color: var(--color-texto-apagado); margin-top: 0.15rem;">Domingos cerrado por mantenimiento integral</div>
                    </div>

                    <div style="margin-bottom: 1.25rem;">
                        <span style="font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: var(--color-texto-apagado); display: block;">Telefono y WhatsApp:</span>
                        <div style="font-size: 1.05rem; font-weight: 700; color: var(--color-texto); margin-top: 0.2rem;">(011) 4792-8811 &bull; 11 3822-9900</div>
                    </div>

                    <div style="margin-top: 1.5rem;">
                        <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/reservar" class="btn btn-primario" style="width: 100%; justify-content: center;">
                            Reservar Turno Ahora
                        </a>
                    </div>
                </div>

                <div style="background-color: var(--color-superficie); border: 1px solid var(--color-borde-suave); border-radius: var(--radio-xl); padding: 2rem; display: flex; flex-direction: column; justify-content: center;">
                    <div style="text-align: center;">
                        <div style="width: 56px; height: 56px; border-radius: 16px; background: var(--color-acento-claro); color: var(--color-acento); margin: 0 auto 1.25rem; display: flex; align-items: center; justify-content: center;">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                        </div>
                        <h4 style="font-size: 1.25rem; font-weight: 800; color: var(--color-texto); margin-bottom: 0.5rem;">Facil Acceso en Zona Norte</h4>
                        <p style="font-size: 0.925rem; color: var(--color-texto-secundario); line-height: 1.6; max-width: 400px; margin: 0 auto;">
                            A metros de las principales avenidas y vias rapidas. Playa de espera asfaltada, sala climatizada con WiFi para clientes mientras esperan su rodado.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 10. Politicas Claras: Cancelacion, Ausencias y Medios de Pago -->
        <section class="seccion-contenedor">
            <div class="seccion-encabezado">
                <span class="seccion-rotulo">Reglas del Lavadero</span>
                <h2 class="seccion-titulo">Politicas de Servicio</h2>
                <p class="seccion-descripcion">
                    Transparencia absoluta en cada aspecto para una convivencia fluida entre clientes y lavadero.
                </p>
            </div>

            <div class="grilla-politicas">
                <div class="tarjeta-politica">
                    <h4 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--color-texto);">Regla de las 2 Horas</h4>
                    <p style="font-size: 0.9rem; color: var(--color-texto-secundario); line-height: 1.6;">
                        Tenes cancelacion o reprogramacion directa e inmediata hasta 2 horas antes de tu turno. Con menos de 2 horas se genera una solicitud que evalua el personal para cubrir el box.
                    </p>
                </div>

                <div class="tarjeta-politica">
                    <h4 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--color-texto);">Tolerancia de Llegada</h4>
                    <p style="font-size: 0.9rem; color: var(--color-texto-secundario); line-height: 1.6;">
                        Se otorga una tolerancia maxima de 10 minutos a partir de la hora de inicio. Pasado ese tiempo, el turno podra ser considerado ausente para no demorar al cliente posterior.
                    </p>
                </div>

                <div class="tarjeta-politica">
                    <h4 style="font-size: 1.15rem; font-weight: 800; margin-bottom: 0.5rem; color: var(--color-texto);">Pagos Presenciales</h4>
                    <p style="font-size: 0.9rem; color: var(--color-texto-secundario); line-height: 1.6;">
                        No cobramos señas por internet ni pedimos tarjetas de credito online. El abono se efectua en la recepcion al retirar el auto (Efectivo, Mercado Pago QR o Transferencia).
                    </p>
                </div>
            </div>
        </section>
    </main>

    <?php include dirname(__DIR__) . '/layouts/public_footer.php'; ?>

    <script src="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/assets/js/app.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Buscador interactivo en la landing page
            const inputLanding = document.getElementById('buscador-landing-input');
            const dropLanding = document.getElementById('dropdown-landing-resultados');
            const avisoLanding = document.getElementById('aviso-landing-resultado');
            const textoLanding = document.getElementById('texto-landing-resultado');
            let debounceLanding = null;

            if (inputLanding) {
                inputLanding.addEventListener('input', function() {
                    const q = this.value.trim();
                    clearTimeout(debounceLanding);

                    if (q.length < 2) {
                        dropLanding.style.display = 'none';
                        dropLanding.innerHTML = '';
                        return;
                    }

                    debounceLanding = setTimeout(() => {
                        fetch('<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/api/modelos?q=' + encodeURIComponent(q))
                            .then(r => r.json())
                            .then(data => {
                                dropLanding.innerHTML = '';
                                if (!data.modelos || data.modelos.length === 0) {
                                    dropLanding.innerHTML = '<div style="padding: 0.85rem 1rem; color: var(--color-texto-apagado); font-size: 0.9rem;">No se encontro ese modelo exacto. Consulta nuestra guia de lavados o el selector al reservar.</div>';
                                    dropLanding.style.display = 'block';
                                    return;
                                }

                                data.modelos.forEach(m => {
                                    const item = document.createElement('div');
                                    item.className = 'item-landing-modelo';
                                    item.innerHTML = '<span><strong>' + m.marca + ' ' + m.modelo + '</strong></span><span class="badge badge-activo">' + m.tipo_nombre + '</span>';
                                    item.addEventListener('click', function() {
                                        inputLanding.value = m.marca + ' ' + m.modelo;
                                        dropLanding.style.display = 'none';
                                        avisoLanding.style.display = 'block';
                                        textoLanding.textContent = 'Tu ' + m.marca + ' ' + m.modelo + ' se considera categoria ' + m.tipo_nombre + ' (' + m.duracion_minutos + ' min).';
                                    });
                                    dropLanding.appendChild(item);
                                });
                                dropLanding.style.display = 'block';
                            })
                            .catch(() => {
                                dropLanding.style.display = 'none';
                            });
                    }, 250);
                });
            }

            // Acordeon FAQ interactivo accesible con teclado
            const botonesFaq = document.querySelectorAll('.acordeon-faq-boton');
            botonesFaq.forEach(btn => {
                btn.addEventListener('click', function() {
                    const item = this.closest('.acordeon-faq-item');
                    const estaAbierto = item.classList.contains('abierto');
                    const icono = this.querySelector('.acordeon-faq-icono');

                    // Cerrar los demas
                    document.querySelectorAll('.acordeon-faq-item').forEach(it => {
                        it.classList.remove('abierto');
                        const b = it.querySelector('.acordeon-faq-boton');
                        const ic = it.querySelector('.acordeon-faq-icono');
                        if (b) b.setAttribute('aria-expanded', 'false');
                        if (ic) ic.textContent = '+';
                    });

                    if (!estaAbierto) {
                        item.classList.add('abierto');
                        this.setAttribute('aria-expanded', 'true');
                        if (icono) icono.textContent = '-';
                    }
                });
            });
        });
    </script>
</body>
</html>
