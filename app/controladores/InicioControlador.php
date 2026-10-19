<?php
/**
 * WashFlow - Controlador de Inicio Publico
 */

declare(strict_types=1);

namespace WashFlow\Controladores;

use WashFlow\Core\Controlador;
use WashFlow\Modelos\Galeria;
use WashFlow\Modelos\PrecioServicio;
use WashFlow\Modelos\PreguntaFrecuente;
use WashFlow\Modelos\Servicio;
use WashFlow\Modelos\TipoVehiculo;

class InicioControlador extends Controlador {
    public function index(): void {
        $servicios = Servicio::listarActivos();
        $tiposVehiculo = TipoVehiculo::listarTodos();
        $tarifas = PrecioServicio::listarPreciosVigentes();
        $preguntas = PreguntaFrecuente::listarActivas();
        $galeria = Galeria::listarActivas();

        $precios = [];
        foreach ($tarifas as $t) {
            $precios[$t['servicio_id']][$t['tipo_vehiculo_id']] = [
                'precio' => $t['precio_vigente'],
                'duracion' => $t['duracion_minutos']
            ];
        }

        // Estado en vivo: Lun-Sab de 10:00 a 18:00
        $diaSemana = (int)date('N'); // 1 = Lunes, 7 = Domingo
        $horaActual = (int)date('G');
        $abierto = ($diaSemana >= 1 && $diaSemana <= 6 && $horaActual >= 10 && $horaActual < 18);

        $this->render('inicio/index', [
            'titulo' => 'WashFlow — Lavadero Profesional de Vehiculos',
            'servicios' => $servicios,
            'tiposVehiculo' => $tiposVehiculo,
            'tarifas' => $tarifas,
            'precios' => $precios,
            'preguntas' => $preguntas,
            'galeria' => $galeria,
            'abierto' => $abierto
        ], null);
    }

    public function guiaLavados(): void {
        $servicios = Servicio::listarActivos();
        $tiposVehiculo = TipoVehiculo::listarTodos();
        $tarifas = PrecioServicio::listarPreciosVigentes();

        $precios = [];
        foreach ($tarifas as $t) {
            $precios[$t['servicio_id']][$t['tipo_vehiculo_id']] = [
                'precio' => $t['precio_vigente'],
                'duracion' => $t['duracion_minutos']
            ];
        }

        $diaSemana = (int)date('N');
        $horaActual = (int)date('G');
        $abierto = ($diaSemana >= 1 && $diaSemana <= 6 && $horaActual >= 10 && $horaActual < 18);

        $this->render('guia/index', [
            'titulo' => 'Guia Completa de Servicios y Lavados — WashFlow',
            'servicios' => $servicios,
            'tiposVehiculo' => $tiposVehiculo,
            'tarifas' => $tarifas,
            'precios' => $precios,
            'abierto' => $abierto
        ], null);
    }
}
