<?php
/**
 * WashFlow - Controlador Base de la Arquitectura MVC
 */

declare(strict_types=1);

namespace WashFlow\Core;

abstract class Controlador {
    protected function render(string $vista, array $datos = [], ?string $layout = 'layouts/main'): void {
        Vista::render($vista, $datos, $layout);
    }

    protected function json(mixed $datos, int $codigoHttp = 200): void {
        Vista::renderJson($datos, $codigoHttp);
    }

    protected function redirect(string $ruta): void {
        $config = require __DIR__ . '/../config/config.php';
        $urlBase = rtrim($config['url_base'], '/');

        if (str_starts_with($ruta, 'http://') || str_starts_with($ruta, 'https://')) {
            header('Location: ' . $ruta);
        } else {
            $rutaLimpia = '/' . ltrim($ruta, '/');
            header('Location: ' . $urlBase . $rutaLimpia);
        }
        exit;
    }

    /**
     * Valida la autorizacion por rol en el servidor (Diagrama 12 y requerimientos de seguridad)
     * @param string|array $rolesPermitidos 'cliente', 'empleado', 'administrador'
     */
    protected function requerirRol(string|array $rolesPermitidos): void {
        $roles = is_array($rolesPermitidos) ? $rolesPermitidos : [$rolesPermitidos];

        if (!Sesion::estaAutenticado()) {
            Sesion::setFlash('error', 'Debe iniciar sesion para acceder a esta seccion.');
            $this->redirect('/login');
        }

        $rolActual = Sesion::getRol();

        // El Administrador hereda los permisos de Empleado (Diagramas 01, 03 y 04a)
        $rolesAceptados = $roles;
        if (in_array('empleado', $roles, true) && !in_array('administrador', $roles, true)) {
            $rolesAceptados[] = 'administrador';
        }

        if (!in_array($rolActual, $rolesAceptados, true)) {
            $this->mostrarError403();
        }
    }

    protected function requerirAutenticado(): void {
        if (!Sesion::estaAutenticado()) {
            Sesion::setFlash('error', 'Debe iniciar sesion para continuar.');
            $this->redirect('/login');
        }
    }

    protected function requerirInvitado(): void {
        if (Sesion::estaAutenticado()) {
            $rol = Sesion::getRol();
            if ($rol === 'administrador') {
                $this->redirect('/admin/dashboard');
            } elseif ($rol === 'empleado') {
                $this->redirect('/empleado/dashboard');
            } else {
                $this->redirect('/cliente/dashboard');
            }
        }
    }

    protected function validarCsrf(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['_csrf_token'] ?? null;
            if (!Csrf::validar($token)) {
                http_response_code(403);
                $this->render('errores/403', ['mensaje' => 'Solicitud invalida o sesion expirada (CSRF). Por favor recargue la pagina.'], null);
                exit;
            }
        }
    }

    protected function getUsuarioId(): ?int {
        return Sesion::getUsuarioId();
    }

    protected function getRol(): ?string {
        return Sesion::getRol();
    }

    protected function mostrarError403(string $mensaje = 'No tiene permisos para acceder a este recurso.'): void {
        http_response_code(403);
        $this->render('errores/403', ['mensaje' => $mensaje], null);
        exit;
    }

    protected function mostrarError404(string $mensaje = 'La pagina solicitada no existe o fue movida.'): void {
        http_response_code(404);
        $this->render('errores/404', ['mensaje' => $mensaje], null);
        exit;
    }
}
