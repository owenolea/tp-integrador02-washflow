<?php
/**
 * WashFlow - Guia Completa de Servicios y Lavados (Requisito 3.3)
 * Formato articulo profesional, responsive, estetica oscura refinada. Sin emojis.
 */
?>
<!DOCTYPE html>
<html lang="es" data-tema="oscuro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guia de Lavados y Servicios — WashFlow</title>
    <link rel="stylesheet" href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/assets/css/app.css">
    <style>
        .guia-header {
            background: linear-gradient(135deg, #091a24 0%, #0e2938 50%, #163e54 100%);
            padding: 5rem 1.5rem 4rem;
            text-align: center;
            border-bottom: 1px solid var(--color-borde-suave);
        }
        .guia-header-contenedor {
            max-width: 800px;
            margin: 0 auto;
        }
        .guia-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(14, 165, 233, 0.12);
            border: 1px solid rgba(14, 165, 233, 0.3);
            color: #7dd3fc;
            padding: 0.35rem 1rem;
            border-radius: var(--radio-full);
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 1.25rem;
        }
        .guia-titulo {
            font-family: var(--fuente-titulo);
            font-size: 2.75rem;
            font-weight: 800;
            line-height: 1.2;
            color: #ffffff;
            margin-bottom: 1rem;
        }
        .guia-subtitulo {
            font-size: 1.15rem;
            color: #94a3b8;
            line-height: 1.6;
        }
        .guia-cuerpo {
            max-width: 900px;
            margin: 3.5rem auto;
            padding: 0 1.5rem;
        }
        .articulo-seccion {
            background-color: var(--color-superficie);
            border: 1px solid var(--color-borde-suave);
            border-radius: var(--radio-xl);
            padding: 2.5rem;
            margin-bottom: 2.5rem;
            box-shadow: var(--sombra-sm);
        }
        .articulo-subtitulo {
            font-family: var(--fuente-titulo);
            font-size: 1.6rem;
            font-weight: 800;
            margin-bottom: 1.25rem;
            color: var(--color-texto);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .articulo-subtitulo::before {
            content: '';
            display: inline-block;
            width: 4px;
            height: 1.4rem;
            background: var(--color-acento);
            border-radius: 2px;
        }
        .guia-parrafo {
            color: var(--color-texto-secundario);
            font-size: 1.025rem;
            line-height: 1.7;
            margin-bottom: 1.25rem;
        }
        .comparativa-grilla {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin: 1.75rem 0;
        }
        .tarjeta-comparativa {
            background-color: var(--color-superficie-alt);
            border: 1px solid var(--color-borde-suave);
            border-radius: var(--radio-lg);
            padding: 1.75rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .tarjeta-comparativa.destacada {
            border-color: var(--color-acento);
            background: linear-gradient(180deg, rgba(14, 165, 233, 0.05) 0%, var(--color-superficie-alt) 100%);
        }
        .lista-items-lavado {
            list-style: none;
            padding: 0;
            margin: 1rem 0;
        }
        .lista-items-lavado li {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            font-size: 0.95rem;
            color: var(--color-texto);
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--color-borde-suave);
        }
        .lista-items-lavado li:last-child {
            border-bottom: none;
        }
        .check-svg {
            color: var(--color-acento);
            flex-shrink: 0;
            margin-top: 0.15rem;
        }
        .tabla-guia {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.25rem;
            font-size: 0.95rem;
        }
        .tabla-guia th {
            text-align: left;
            padding: 0.85rem 1rem;
            background-color: var(--color-superficie-alt);
            color: var(--color-texto-apagado);
            font-family: var(--fuente-titulo);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            border-bottom: 1px solid var(--color-borde);
        }
        .tabla-guia td {
            padding: 1rem;
            border-bottom: 1px solid var(--color-borde-suave);
            color: var(--color-texto);
        }
        .tabla-guia tr:last-child td {
            border-bottom: none;
        }
        .caja-consejo {
            background-color: var(--color-acento-claro);
            border-left: 4px solid var(--color-acento);
            padding: 1.25rem 1.5rem;
            border-radius: var(--radio-md);
            margin: 1.5rem 0;
            color: var(--color-texto);
            font-size: 0.95rem;
            line-height: 1.6;
        }
        .buscador-flotante-caja {
            background-color: var(--color-superficie-alt);
            border: 1px solid var(--color-borde);
            border-radius: var(--radio-lg);
            padding: 1.75rem;
            margin-top: 1.25rem;
        }
        .input-busqueda-guia {
            width: 100%;
            background-color: var(--color-fondo);
            border: 1px solid var(--color-borde);
            border-radius: var(--radio-md);
            padding: 0.85rem 1rem;
            color: var(--color-texto);
            font-size: 1rem;
            font-family: inherit;
            transition: all 0.2s ease;
        }
        .input-busqueda-guia:focus {
            outline: none;
            border-color: var(--color-acento);
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.25);
        }
        .resultados-guia-contenedor {
            max-height: 240px;
            overflow-y: auto;
            margin-top: 0.75rem;
            border-radius: var(--radio-md);
            border: 1px solid var(--color-borde-suave);
            background-color: var(--color-fondo);
            display: none;
        }
        .resultado-item-guia {
            padding: 0.75rem 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--color-borde-suave);
            cursor: pointer;
            transition: background-color 0.15s ease;
        }
        .resultado-item-guia:hover {
            background-color: var(--color-borde-suave);
        }
        .resultado-item-guia:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <?php $paginaActual = 'guia'; include dirname(__DIR__) . '/layouts/public_navbar.php'; ?>

    <main>
        <section class="guia-header">
            <div class="guia-header-contenedor">
                <span class="guia-badge">Guia de Servicios</span>
                <h1 class="guia-titulo">Que incluye cada lavado y como elegir el ideal para tu vehiculo</h1>
                <p class="guia-subtitulo">
                    En WashFlow cuidamos cada detalle de tu rodado. Conoce las diferencias exactas entre nuestro Lavado Normal y el Tratamiento Premium, tiempos de proceso y clasificacion de categorias.
                </p>
            </div>
        </section>

        <div class="guia-cuerpo">
            <!-- Seccion 1: Resumen rapido -->
            <article class="articulo-seccion">
                <h2 class="articulo-subtitulo">Resumen rapido: Que servicio te conviene</h2>
                <p class="guia-parrafo">
                    La eleccion entre un lavado y otro depende principalmente del estado de la pintura, el tiempo desde el ultimo tratamiento y la proteccion que buscas contra los factores ambientales.
                </p>

                <div class="comparativa-grilla">
                    <div class="tarjeta-comparativa">
                        <div>
                            <span style="font-family: var(--fuente-titulo); font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--color-texto-apagado);">Mantenimiento Frecuente</span>
                            <h3 style="font-size: 1.4rem; font-weight: 800; margin: 0.35rem 0 0.75rem; color: var(--color-texto);">Lavado Normal</h3>
                            <p style="font-size: 0.925rem; color: var(--color-texto-secundario); line-height: 1.5; margin-bottom: 1rem;">
                                Recomendado para vehiculos que se lavan con frecuencia (cada 7 a 15 dias). Elimina polvo diario, suciedad urbana y renueva el interior.
                            </p>
                            <ul class="lista-items-lavado">
                                <li>
                                    <svg class="check-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <span>Lavado exterior a mano con espuma activa pH neutro.</span>
                                </li>
                                <li>
                                    <svg class="check-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <span>Aspirado interior minucioso de butacas, alfombras y baul.</span>
                                </li>
                                <li>
                                    <svg class="check-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <span>Silicona protectora y abrillantador en cubiertas y llantas.</span>
                                </li>
                                <li>
                                    <svg class="check-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <span>Secado con microfibras de alto gramaje sin micro-rayas.</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="tarjeta-comparativa destacada">
                        <div>
                            <span style="font-family: var(--fuente-titulo); font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em; color: var(--color-acento);">Proteccion y Brillo Prolongado</span>
                            <h3 style="font-size: 1.4rem; font-weight: 800; margin: 0.35rem 0 0.75rem; color: var(--color-texto);">Lavado Premium con Cera Permanente</h3>
                            <p style="font-size: 0.925rem; color: var(--color-texto-secundario); line-height: 1.5; margin-bottom: 1rem;">
                                Recomendado cada 30 a 60 dias o para vehiculos que duermen en la calle. Brinda una capa protectora hidrofobica de larga duracion.
                            </p>
                            <ul class="lista-items-lavado">
                                <li>
                                    <svg class="check-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <span>Todo lo incluido en el Lavado Normal.</span>
                                </li>
                                <li>
                                    <svg class="check-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <span>Aplicacion de cera permanente / sellado acrilico de alto brillo.</span>
                                </li>
                                <li>
                                    <svg class="check-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <span>Efecto hidrofobico: el agua y la suciedad resbalan sin adherirse.</span>
                                </li>
                                <li>
                                    <svg class="check-svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    <span>Proteccion de la pintura contra rayos UV, savia de arboles y lluvia acida.</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </article>

            <!-- Seccion 2: Tiempos y Duracion por Categoria -->
            <article class="articulo-seccion">
                <h2 class="articulo-subtitulo">Cuanto tarda cada lavado segun la categoria</h2>
                <p class="guia-parrafo">
                    En WashFlow ambos servicios (Normal y Premium) tienen la misma duracion de turno para cada categoria de vehiculo. Nuestro equipo trabaja con asignacion de boxes y operarios dedicados para aplicar la cera sin demorar tu tiempo.
                </p>

                <table class="tabla-guia">
                    <thead>
                        <tr>
                            <th>Categoria</th>
                            <th>Ejemplos de Rodados</th>
                            <th>Tiempo Estimado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tiposVehiculo as $tv): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($tv['nombre'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td style="color: var(--color-texto-secundario);">
                                    <?php
                                    echo match ($tv['nombre']) {
                                        'Moto' => 'Honda Wave, Rouser 200, Yamaha FZ, Vespa',
                                        'Auto' => 'Ford Ka, VW Gol Trend, Peugeot 208, Fiat Cronos, Toyota Corolla',
                                        'Camioneta' => 'Toyota Hilux, Ford Ranger, VW Amarok, Chevrolet S10',
                                        '4x4' => 'Jeep Wrangler, Toyota SW4, Land Rover Defender, RAM 1500',
                                        default => 'Modelos estandar de la categoria'
                                    };
                                    ?>
                                </td>
                                <td>
                                    <span class="badge badge-activo"><?= (int)$tv['duracion_minutos'] ?> minutos</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </article>

            <!-- Seccion 3: Tarifas Transparentes leidas de la base de datos -->
            <article class="articulo-seccion">
                <h2 class="articulo-subtitulo">Tarifas oficiales segun la base de datos</h2>
                <p class="guia-parrafo">
                    Los valores indicados son los vigentes en el sistema administrativo del lavadero. Al reservar un turno, el precio queda congelado y no sufre modificaciones posteriores.
                </p>

                <table class="tabla-guia">
                    <thead>
                        <tr>
                            <th>Categoria</th>
                            <th>Lavado Normal</th>
                            <th>Lavado Premium con Cera</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tiposVehiculo as $tv): ?>
                            <?php
                            $precioNormal = $precios[1][$tv['id']]['precio'] ?? null;
                            $precioPremium = $precios[2][$tv['id']]['precio'] ?? null;
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($tv['nombre'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td style="font-family: var(--fuente-titulo); font-weight: 700; color: var(--color-texto);">
                                    <?= $precioNormal ? '$' . number_format((float)$precioNormal, 0, ',', '.') : 'A consultar' ?>
                                </td>
                                <td style="font-family: var(--fuente-titulo); font-weight: 800; color: var(--color-acento);">
                                    <?= $precioPremium ? '$' . number_format((float)$precioPremium, 0, ',', '.') : 'A consultar' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </article>

            <!-- Seccion 4: Buscador de modelos incrustado -->
            <article class="articulo-seccion">
                <h2 class="articulo-subtitulo">No sabes que categoria es tu vehiculo? Averigualo aqui</h2>
                <p class="guia-parrafo">
                    Escribi la marca o modelo de tu auto y nuestro buscador te dira exactamente si corresponde a Moto, Auto, Camioneta o 4x4.
                </p>

                <div class="buscador-flotante-caja">
                    <label for="buscador-guia-input" style="font-size: 0.9rem; font-weight: 600; display: block; margin-bottom: 0.5rem; color: var(--color-texto);">
                        Buscar modelo (ej: Ford Ka, Hilux, Cronos, Amarok, Tracker...):
                    </label>
                    <input type="text" id="buscador-guia-input" class="input-busqueda-guia" placeholder="Escribi al menos 2 letras...">
                    
                    <div id="resultados-guia" class="resultados-guia-contenedor"></div>

                    <div id="aviso-categoria-guia" style="display: none; margin-top: 1rem; padding: 1rem; border-radius: var(--radio-md); background: rgba(14, 165, 233, 0.15); border: 1px solid var(--color-acento);">
                        <span id="texto-categoria-guia" style="font-weight: 700; color: #7dd3fc;"></span>
                    </div>
                </div>
            </article>

            <!-- Seccion 5: Consejos y Politicas -->
            <article class="articulo-seccion">
                <h2 class="articulo-subtitulo">Consejos antes de venir y politicas de atencion</h2>
                <div class="caja-consejo">
                    <strong>Recomendacion importante:</strong> Por favor retira pertenencias personales de valor, monedas y documentos de la gaveta antes de ingresar el vehiculo al box de lavado.
                </div>

                <p class="guia-parrafo">
                    <strong>Puntualidad y tolerancia:</strong> Solicitamos presentarse 5 minutos antes del horario acordado. Existe una tolerancia maxima de 10 minutos para mantener la fluidez de los turnos programados.
                </p>
                <p class="guia-parrafo">
                    <strong>Regla de las 2 horas:</strong> Podes reprogramar o cancelar tu turno de forma directa desde tu enlace de reserva siempre que falten 2 horas o mas. Si faltan menos de 2 horas, el sistema generara una solicitud que sera evaluada por el equipo para coordinar la mejor solucion.
                </p>
                <p class="guia-parrafo">
                    <strong>Pagos presenciales:</strong> El cobro se efectua al momento de retirar el vehiculo finalizado. Aceptamos efectivo, Mercado Pago mediante codigo QR y transferencia bancaria inmediata.
                </p>

                <div style="text-align: center; margin-top: 2.5rem;">
                    <a href="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/reservar" class="btn btn-primario btn-lg" style="padding: 1rem 2.5rem; font-size: 1.1rem;">
                        <span>Reservar Turno Online Ahora</span>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                    </a>
                </div>
            </article>
        </div>
    </main>

    <?php include dirname(__DIR__) . '/layouts/public_footer.php'; ?>

    <script src="<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/assets/js/app.js"></script>
    <script>
        // Buscador interactivo en la guia
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('buscador-guia-input');
            const lista = document.getElementById('resultados-guia');
            const aviso = document.getElementById('aviso-categoria-guia');
            const textoAviso = document.getElementById('texto-categoria-guia');
            let debounceTimer = null;

            if (!input) return;

            input.addEventListener('input', function() {
                const query = this.value.trim();
                clearTimeout(debounceTimer);

                if (query.length < 2) {
                    lista.style.display = 'none';
                    lista.innerHTML = '';
                    return;
                }

                debounceTimer = setTimeout(() => {
                    fetch('<?= htmlspecialchars($urlBase, ENT_QUOTES, 'UTF-8') ?>/api/modelos?q=' + encodeURIComponent(query))
                        .then(r => r.json())
                        .then(data => {
                            lista.innerHTML = '';
                            if (!data.modelos || data.modelos.length === 0) {
                                lista.innerHTML = '<div style="padding: 0.85rem 1rem; color: var(--color-texto-apagado); font-size: 0.9rem;">No se encontro ese modelo exacto. Podes elegir la categoria mas parecida o consultar la tabla superior.</div>';
                                lista.style.display = 'block';
                                return;
                            }

                            data.modelos.forEach(m => {
                                const item = document.createElement('div');
                                item.className = 'resultado-item-guia';
                                item.innerHTML = '<span><strong>' + m.marca + ' ' + m.modelo + '</strong></span><span class="badge badge-activo">' + m.tipo_nombre + '</span>';
                                item.addEventListener('click', function() {
                                    input.value = m.marca + ' ' + m.modelo;
                                    lista.style.display = 'none';
                                    aviso.style.display = 'block';
                                    textoAviso.textContent = 'Tu ' + m.marca + ' ' + m.modelo + ' se clasifica como: ' + m.tipo_nombre + ' (' + m.duracion_minutos + ' min).';
                                });
                                lista.appendChild(item);
                            });
                            lista.style.display = 'block';
                        })
                        .catch(() => {
                            lista.style.display = 'none';
                        });
                }, 250);
            });
        });
    </script>
</body>
</html>
