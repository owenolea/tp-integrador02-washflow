<?php
/**
 * WashFlow - Enrutador HTTP Ligero con soporte para parametros dinamicos
 */

declare(strict_types=1);

namespace WashFlow\Core;

class Router {
    private array $rutas = [];

    public function get(string $ruta, array|callable $manejador): void {
        $this->registrar('GET', $ruta, $manejador);
    }

    public function post(string $ruta, array|callable $manejador): void {
        $this->registrar('POST', $ruta, $manejador);
    }

    private function registrar(string $metodo, string $ruta, array|callable $manejador): void {
        // Normalizar ruta: siempre comenzar con / y sin / final
        $rutaNormalizada = '/' . trim($ruta, '/');
        if ($rutaNormalizada !== '/') {
            $rutaNormalizada = rtrim($rutaNormalizada, '/');
        }

        $this->rutas[] = [
            'metodo' => $metodo,
            'ruta' => $rutaNormalizada,
            'manejador' => $manejador
        ];
    }

    public function despachar(): void {
        $metodoPeticion = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uriPeticion = $this->obtenerUriLimpia();

        foreach ($this->rutas as $definicion) {
            if ($definicion['metodo'] !== $metodoPeticion) {
                continue;
            }

            // Convertir /ruta/{param} a regex
            $patron = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[^/]+)', $definicion['ruta']);
            $patron = '#^' . $patron . '$#';

            if (preg_match($patron, $uriPeticion, $coincidencias)) {
                // Filtrar solo las claves con nombre
                $parametros = array_filter(
                    $coincidencias,
                    fn($clave) => !is_numeric($clave),
                    ARRAY_FILTER_USE_KEY
                );

                $manejador = $definicion['manejador'];

                if (is_callable($manejador)) {
                    call_user_func_array($manejador, array_values($parametros));
                    return;
                }

                if (is_array($manejador) && count($manejador) === 2) {
                    [$clase, $metodo] = $manejador;

                    if (!class_exists($clase)) {
                        http_response_code(500);
                        echo "Error 500: Controlador [{$clase}] no encontrado.";
                        exit;
                    }

                    $instancia = new $clase();
                    if (!method_exists($instancia, $metodo)) {
                        http_response_code(500);
                        echo "Error 500: Metodo [{$metodo}] en [{$clase}] no encontrado.";
                        exit;
                    }

                    call_user_func_array([$instancia, $metodo], array_values($parametros));
                    return;
                }
            }
        }

        // Si no hubo coincidencia, mostrar 404
        http_response_code(404);
        Vista::render('errores/404', ['mensaje' => 'La ruta solicitada no existe.'], null);
    }

    private function obtenerUriLimpia(): string {
        // Soporte tanto para query string ?r=/ruta como para PATH_INFO / REQUEST_URI amigable
        if (isset($_GET['r']) && is_string($_GET['r'])) {
            $uri = '/' . trim($_GET['r'], '/');
            return $uri === '/' ? '/' : rtrim($uri, '/');
        }

        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

        // Si corre dentro de una subcarpeta (soporta tanto /washflow/public/ como /washflow/)
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        if ($scriptDir !== '/' && $scriptDir !== '' && str_starts_with($uri, $scriptDir)) {
            $uri = substr($uri, strlen($scriptDir));
        } else {
            // Caso donde Apache reescribe desde la raiz del proyecto sin el segmento 'public'
            $parentDir = str_replace('\\', '/', dirname($scriptDir));
            if ($parentDir !== '/' && $parentDir !== '' && str_starts_with($uri, $parentDir)) {
                $uri = substr($uri, strlen($parentDir));
            }
        }

        $uri = '/' . trim($uri, '/');
        return $uri === '/' ? '/' : rtrim($uri, '/');
    }
}
