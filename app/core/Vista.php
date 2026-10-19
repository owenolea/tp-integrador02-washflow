<?php
/**
 * WashFlow - Motor de Vistas y Plantillas
 */

declare(strict_types=1);

namespace WashFlow\Core;

class Vista {
    /**
     * Renderiza una vista dentro de un layout o directamente.
     */
    public static function render(string $vista, array $datos = [], ?string $layout = 'layouts/main'): void {
        $config = require __DIR__ . '/../config/config.php';

        // Variables globales para todas las vistas
        $urlBase = rtrim($config['url_base'], '/');
        $usuarioActual = Sesion::getUsuario();
        $rolActual = Sesion::getRol();
        $flashes = Sesion::getFlashes();
        $csrfToken = Csrf::obtenerToken();
        $csrfCampo = Csrf::campoHtml();

        // Extraer datos pasados a la vista
        extract($datos, EXTR_SKIP);

        $archivoVista = __DIR__ . '/../vistas/' . ltrim($vista, '/') . '.php';
        if (!file_exists($archivoVista)) {
            http_response_code(500);
            echo "Error 500: La vista [{$vista}] no fue encontrada.";
            exit;
        }

        // Capturar contenido de la vista
        ob_start();
        include $archivoVista;
        $contenido = ob_get_clean();

        // Si se especifico un layout, envolver la vista en el layout
        if ($layout !== null) {
            $archivoLayout = __DIR__ . '/../vistas/' . ltrim($layout, '/') . '.php';
            if (!file_exists($archivoLayout)) {
                http_response_code(500);
                echo "Error 500: El layout [{$layout}] no fue encontrado.";
                exit;
            }
            include $archivoLayout;
        } else {
            echo $contenido;
        }
    }

    /**
     * Respuesta JSON tipada para llamadas asincronas / AJAX
     */
    public static function renderJson(mixed $datos, int $codigoHttp = 200): void {
        http_response_code($codigoHttp);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Escape seguro contra XSS
     */
    public static function escape(?string $cadena): string {
        return htmlspecialchars($cadena ?? '', ENT_QUOTES, 'UTF-8');
    }
}
