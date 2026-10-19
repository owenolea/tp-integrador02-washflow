/**
 * WashFlow - JavaScript Principal (Vanilla JS sin dependencias)
 * Accesibilidad, navegacion, tema oscuro y asistente interactivo
 */

document.addEventListener('DOMContentLoaded', () => {
    // 1. Inicializacion y gestion del Tema Claro / Oscuro (Oscuro por defecto)
    const temaGuardado = localStorage.getItem('washflow_tema') || 'oscuro';
    document.documentElement.setAttribute('data-tema', temaGuardado);

    const btnTema = document.getElementById('btn-cambiar-tema');
    if (btnTema) {
        btnTema.addEventListener('click', () => {
            const temaActual = document.documentElement.getAttribute('data-tema') || 'oscuro';
            const nuevoTema = (temaActual === 'oscuro') ? 'claro' : 'oscuro';
            document.documentElement.setAttribute('data-tema', nuevoTema);
            localStorage.setItem('washflow_tema', nuevoTema);
        });
    }

    // 2. Control de barra lateral en dispositivos moviles
    const btnToggleSidebar = document.getElementById('btn-toggle-sidebar');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    if (btnToggleSidebar && sidebar) {
        btnToggleSidebar.addEventListener('click', () => {
            sidebar.classList.toggle('abierto');
            if (overlay) overlay.classList.toggle('activo');
        });
    }

    if (overlay && sidebar) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('abierto');
            overlay.classList.remove('activo');
        });
    }

    // 3. Cierre automatico o manual de alertas flash
    const alertas = document.querySelectorAll('.alerta');
    alertas.forEach(alerta => {
        setTimeout(() => {
            alerta.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
            alerta.style.opacity = '0';
            alerta.style.transform = 'translateY(-5px)';
            setTimeout(() => alerta.remove(), 400);
        }, 7000);
    });

    // 4. Asistente de Reserva de Turnos por Pasos
    const wizardForm = document.getElementById('asistente-reserva-form');
    if (wizardForm) {
        inicializarAsistenteReserva();
    }
});

/**
 * Logica del Asistente Stepper para Reserva de Turnos
 */
function inicializarAsistenteReserva() {
    let pasoActual = 1;
    const totalPasos = 4;

    const steps = document.querySelectorAll('.step-item');
    const panels = document.querySelectorAll('.panel-paso');

    const btnAnterior = document.getElementById('btn-paso-anterior');
    const btnSiguiente = document.getElementById('btn-paso-siguiente');
    const btnConfirmar = document.getElementById('btn-confirmar-reserva');

    const inputVehiculo = document.getElementById('input-vehiculo-id');
    const inputServicio = document.getElementById('input-servicio-id');
    const inputFecha = document.getElementById('input-reserva-fecha');
    const inputHora = document.getElementById('input-reserva-hora');

    // Selector de tarjetas de vehiculo
    const vehiculoCards = document.querySelectorAll('.card-seleccion-vehiculo');
    vehiculoCards.forEach(card => {
        card.addEventListener('click', () => {
            vehiculoCards.forEach(c => c.classList.remove('seleccionada'));
            card.classList.add('seleccionada');
            inputVehiculo.value = card.dataset.id;
            inputVehiculo.dataset.duracion = card.dataset.duracion;
            inputVehiculo.dataset.tipoNombre = card.dataset.tipoNombre;
            inputVehiculo.dataset.info = card.dataset.info;
            actualizarEstadoBotones();
        });
    });

    // Selector de tarjetas de servicio
    const servicioCards = document.querySelectorAll('.card-seleccion-servicio');
    servicioCards.forEach(card => {
        card.addEventListener('click', () => {
            servicioCards.forEach(c => c.classList.remove('seleccionada'));
            card.classList.add('seleccionada');
            inputServicio.value = card.dataset.id;
            inputServicio.dataset.nombre = card.dataset.nombre;
            actualizarEstadoBotones();
        });
    });

    // Cambio de fecha
    if (inputFecha) {
        inputFecha.addEventListener('change', () => {
            inputHora.value = '';
            cargarFranjasDisponibles();
        });
    }

    function cargarFranjasDisponibles() {
        const fecha = inputFecha.value;
        const duracion = inputVehiculo.dataset.duracion || 60;
        const contenedorFranjas = document.getElementById('contenedor-franjas-horarias');

        if (!fecha || !contenedorFranjas) return;

        contenedorFranjas.innerHTML = '<p class="texto-ayuda">Consultando disponibilidad en tiempo real...</p>';

        const url = `${window.WASHFLOW_URL_BASE || ''}/api/disponibilidad?fecha=${encodeURIComponent(fecha)}&duracion=${encodeURIComponent(duracion)}`;

        fetch(url)
            .then(res => res.json())
            .then(data => {
                if (!data.es_dia_atencion) {
                    contenedorFranjas.innerHTML = `<div class="alerta alerta-aviso">${data.mensaje || 'Dia no disponible.'}</div>`;
                    actualizarEstadoBotones();
                    return;
                }

                if (!data.franjas || data.franjas.length === 0) {
                    contenedorFranjas.innerHTML = '<div class="alerta alerta-aviso">No hay franjas disponibles para la fecha seleccionada.</div>';
                    actualizarEstadoBotones();
                    return;
                }

                let html = '<div class="grilla-franjas">';
                data.franjas.forEach(f => {
                    const libre = f.disponible;
                    const claseCard = libre ? 'franja-card' : 'franja-card deshabilitada';
                    const cupoClase = `cupo-${f.ocupacion}`;

                    let badgeTexto = 'Disponible';
                    let badgeClase = 'badge-exito';

                    if (!libre) {
                        if (f.ocupacion >= f.capacidad_maxima) {
                            badgeTexto = 'Lleno';
                            badgeClase = 'badge-peligro';
                        } else if (f.motivo && (f.motivo.toLowerCase().includes('anterior') || f.motivo.toLowerCase().includes('pasad'))) {
                            badgeTexto = 'Pasado';
                            badgeClase = 'badge-alerta';
                        } else {
                            badgeTexto = 'No disponible';
                            badgeClase = 'badge-peligro';
                        }
                    }

                    const libres = f.lugares_libres !== undefined ? f.lugares_libres : (f.capacidad_maxima - f.ocupacion);
                    const textoOcupacion = libre 
                        ? `${libres} de ${f.capacidad_maxima} libres` 
                        : (f.ocupacion >= f.capacidad_maxima ? 'Capacidad agotada' : `${f.ocupacion}/${f.capacidad_maxima} ocupados`);

                    html += `
                        <div class="${claseCard}" data-hora="${f.hora_inicio}" data-disponible="${libre}">
                            <div class="franja-hora">${f.hora_inicio} a ${f.hora_fin}</div>
                            <div class="franja-ocupacion">
                                <span>${textoOcupacion}</span>
                                <span class="badge ${badgeClase}">${badgeTexto}</span>
                            </div>
                            <div class="barra-cupo">
                                <div class="barra-cupo-relleno ${cupoClase}" style="width: ${(f.ocupacion / f.capacidad_maxima) * 100}%;"></div>
                            </div>
                            ${f.motivo ? `<span class="texto-ayuda" style="font-size: 0.72rem;">${f.motivo}</span>` : ''}
                        </div>
                    `;
                });
                html += '</div>';

                contenedorFranjas.innerHTML = html;

                // Agregar evento click a cada franja disponible
                const cards = contenedorFranjas.querySelectorAll('.franja-card[data-disponible="true"]');
                cards.forEach(c => {
                    c.addEventListener('click', () => {
                        cards.forEach(x => x.classList.remove('seleccionada'));
                        c.classList.add('seleccionada');
                        inputHora.value = c.dataset.hora;
                        actualizarEstadoBotones();
                    });
                });
            })
            .catch(err => {
                console.error(err);
                contenedorFranjas.innerHTML = '<div class="alerta alerta-error">No se pudo cargar la disponibilidad. Intente nuevamente.</div>';
            });
    }

    function actualizarResumenConfirmacion() {
        const resumenVehiculo = document.getElementById('resumen-vehiculo');
        const resumenServicio = document.getElementById('resumen-servicio');
        const resumenFecha = document.getElementById('resumen-fecha');
        const resumenHora = document.getElementById('resumen-hora');
        const resumenDuracion = document.getElementById('resumen-duracion');
        const resumenPrecio = document.getElementById('resumen-precio');

        if (resumenVehiculo) resumenVehiculo.textContent = inputVehiculo.dataset.info || 'No seleccionado';
        if (resumenServicio) resumenServicio.textContent = inputServicio.dataset.nombre || 'No seleccionado';
        if (resumenFecha) resumenFecha.textContent = inputFecha.value || 'No seleccionada';
        if (resumenHora) resumenHora.textContent = inputHora.value ? `${inputHora.value} hs` : 'No seleccionado';
        if (resumenDuracion) resumenDuracion.textContent = `${inputVehiculo.dataset.duracion || 60} minutos`;

        // Consultar precio exacto
        if (resumenPrecio && inputVehiculo.value && inputServicio.value) {
            resumenPrecio.textContent = 'Calculando...';
            const url = `${window.WASHFLOW_URL_BASE || ''}/api/precio?servicio_id=${inputServicio.value}&vehiculo_id=${inputVehiculo.value}`;
            fetch(url)
                .then(r => r.json())
                .then(d => {
                    if (d.precio) {
                        resumenPrecio.textContent = `$${parseFloat(d.precio).toLocaleString('es-AR', { minimumFractionDigits: 2 })}`;
                    } else {
                        resumenPrecio.textContent = 'A convenir';
                    }
                })
                .catch(() => {
                    resumenPrecio.textContent = 'No disponible';
                });
        }
    }

    function actualizarEstadoBotones() {
        if (btnAnterior) btnAnterior.style.display = (pasoActual > 1) ? 'inline-flex' : 'none';

        let puedeAvanzar = false;
        if (pasoActual === 1) {
            puedeAvanzar = !!inputVehiculo.value;
        } else if (pasoActual === 2) {
            puedeAvanzar = !!inputServicio.value;
        } else if (pasoActual === 3) {
            puedeAvanzar = !!inputFecha.value && !!inputHora.value;
        }

        if (pasoActual < totalPasos) {
            if (btnSiguiente) {
                btnSiguiente.style.display = 'inline-flex';
                btnSiguiente.disabled = !puedeAvanzar;
            }
            if (btnConfirmar) btnConfirmar.style.display = 'none';
        } else {
            if (btnSiguiente) btnSiguiente.style.display = 'none';
            if (btnConfirmar) btnConfirmar.style.display = 'inline-flex';
            actualizarResumenConfirmacion();
        }
    }

    function mostrarPaso(num) {
        pasoActual = num;
        steps.forEach((st, idx) => {
            const pasoIdx = idx + 1;
            st.classList.remove('activo', 'completado');
            if (pasoIdx < pasoActual) {
                st.classList.add('completado');
            } else if (pasoIdx === pasoActual) {
                st.classList.add('activo');
            }
        });

        panels.forEach((p, idx) => {
            p.style.display = (idx + 1 === pasoActual) ? 'block' : 'none';
        });

        if (pasoActual === 3 && inputFecha.value) {
            cargarFranjasDisponibles();
        }

        if (pasoActual === 4) {
            actualizarResumenConfirmacion();
        }

        actualizarEstadoBotones();
    }

    if (btnSiguiente) {
        btnSiguiente.addEventListener('click', () => {
            if (pasoActual < totalPasos) {
                mostrarPaso(pasoActual + 1);
            }
        });
    }

    if (btnAnterior) {
        btnAnterior.addEventListener('click', () => {
            if (pasoActual > 1) {
                mostrarPaso(pasoActual - 1);
            }
        });
    }

    // Inicializar primer paso
    mostrarPaso(1);
}

// Restriccion y formateo automatico de patentes argentinas (Max 7 caracteres alfanumericos)
document.addEventListener('input', (e) => {
    if (e.target && (e.target.classList.contains('input-patente') || e.target.name === 'patente' || e.target.id.includes('patente'))) {
        let val = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        if (val.length > 7) {
            val = val.substring(0, 7);
        }
        e.target.value = val;
    }
});

