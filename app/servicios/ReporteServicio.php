<?php
/**
 * WashFlow - Servicio de Informes, Estadisticas y Exportacion CSV (Diagramas 03, 12)
 */

declare(strict_types=1);

namespace WashFlow\Servicios;

use PDO;
use WashFlow\Core\BaseDatos;

class ReporteServicio {

    public static function serviciosMasSolicitados(string $desde, string $hasta): array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('
            SELECT s.id, s.nombre,
                   COUNT(r.id) AS total_reservas,
                   COALESCE(SUM(r.precio_acordado), 0) AS monto_total
            FROM servicios s
            LEFT JOIN reservas r ON r.servicio_id = s.id AND r.fecha >= ? AND r.fecha <= ? AND r.estado = "reservada"
            GROUP BY s.id, s.nombre
            ORDER BY total_reservas DESC
        ');
        $stmt->execute([$desde, $hasta]);
        return $stmt->fetchAll();
    }

    public static function ocupacionPorDia(string $desde, string $hasta): array {
        $db = BaseDatos::getInstancia()->getConexion();
        $stmt = $db->prepare('
            SELECT fecha,
                   COUNT(CASE WHEN estado = "reservada" THEN 1 END) AS reservadas,
                   COUNT(CASE WHEN estado = "cancelada" THEN 1 END) AS canceladas,
                   COUNT(CASE WHEN estado = "ausente" THEN 1 END) AS ausentes,
                   COUNT(*) AS total_turnos
            FROM reservas
            WHERE fecha >= ? AND fecha <= ?
            GROUP BY fecha
            ORDER BY fecha ASC
        ');
        $stmt->execute([$desde, $hasta]);
        return $stmt->fetchAll();
    }

    public static function exportarCsv(string $nombreArchivo, array $encabezados, array $filas): void {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $nombreArchivo . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $salida = fopen('php://output', 'w');
        // Agregar BOM para compatibilidad con Excel
        fputs($salida, "\xEF\xBB\xBF");

        fputcsv($salida, $encabezados, ';');

        foreach ($filas as $fila) {
            fputcsv($salida, $fila, ';');
        }

        fclose($salida);
        exit;
    }
}
