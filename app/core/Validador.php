<?php
/**
 * WashFlow - Validador y Saneador de Entradas
 */

declare(strict_types=1);

namespace WashFlow\Core;

class Validador {
    private array $datos;
    private array $errores = [];

    public function __construct(array $datos) {
        $this->datos = $this->sanear($datos);
    }

    private function sanear(array $entradas): array {
        $saneadas = [];
        foreach ($entradas as $clave => $valor) {
            if (is_string($valor)) {
                // Eliminar bytes nulos y espacios en blanco extremos
                $saneadas[$clave] = trim(str_replace(chr(0), '', $valor));
            } elseif (is_array($valor)) {
                $saneadas[$clave] = $this->sanear($valor);
            } else {
                $saneadas[$clave] = $valor;
            }
        }
        return $saneadas;
    }

    public function getDatos(): array {
        return $this->datos;
    }

    public function getDato(string $campo, mixed $defecto = null): mixed {
        return $this->datos[$campo] ?? $defecto;
    }

    public function validar(array $reglas): bool {
        $this->errores = [];

        foreach ($reglas as $campo => $listaReglas) {
            $valor = $this->datos[$campo] ?? null;
            $reglasArr = is_array($listaReglas) ? $listaReglas : explode('|', $listaReglas);

            foreach ($reglasArr as $regla) {
                $parametro = null;
                if (str_contains($regla, ':')) {
                    [$regla, $parametro] = explode(':', $regla, 2);
                }

                $regla = trim($regla);

                // Requerido
                if ($regla === 'requerido') {
                    if ($valor === null || $valor === '' || (is_array($valor) && empty($valor))) {
                        $this->agregarError($campo, 'Este campo es obligatorio.');
                        break;
                    }
                }

                // Si no es requerido y viene vacio, omitir las demas reglas
                if (($valor === null || $valor === '') && $regla !== 'requerido') {
                    continue;
                }

                switch ($regla) {
                    case 'correo':
                        if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                            $this->agregarError($campo, 'Debe ingresar un correo electronico valido.');
                        }
                        break;

                    case 'min':
                        $min = (int)$parametro;
                        if (mb_strlen((string)$valor) < $min) {
                            $this->agregarError($campo, "Debe contener al menos {$min} caracteres.");
                        }
                        break;

                    case 'max':
                        $max = (int)$parametro;
                        if (mb_strlen((string)$valor) > $max) {
                            $this->agregarError($campo, "No puede exceder los {$max} caracteres.");
                        }
                        break;

                    case 'entero':
                        if (!filter_var($valor, FILTER_VALIDATE_INT) && $valor !== 0 && $valor !== '0') {
                            $this->agregarError($campo, 'Debe ser un numero entero.');
                        }
                        break;

                    case 'numero':
                        if (!is_numeric($valor)) {
                            $this->agregarError($campo, 'Debe ser un valor numerico.');
                        }
                        break;

                    case 'positivo':
                        if (!is_numeric($valor) || (float)$valor <= 0) {
                            $this->agregarError($campo, 'Debe ser un valor mayor a cero.');
                        }
                        break;

                    case 'no_negativo':
                        if (!is_numeric($valor) || (float)$valor < 0) {
                            $this->agregarError($campo, 'El valor no puede ser negativo.');
                        }
                        break;

                    case 'fecha':
                        $d = \DateTime::createFromFormat('Y-m-d', (string)$valor);
                        if (!$d || $d->format('Y-m-d') !== (string)$valor) {
                            $this->agregarError($campo, 'La fecha debe tener el formato AAAA-MM-DD.');
                        }
                        break;

                    case 'hora':
                        $formatoValido = preg_match('/^(?:2[0-3]|[01][0-9]):[0-5][0-9](?::[0-5][0-9])?$/', (string)$valor);
                        if (!$formatoValido) {
                            $this->agregarError($campo, 'La hora debe tener formato HH:MM.');
                        }
                        break;

                    case 'in':
                        $opciones = explode(',', (string)$parametro);
                        if (!in_array((string)$valor, $opciones, true)) {
                            $this->agregarError($campo, 'El valor seleccionado no es valido.');
                        }
                        break;

                    case 'patente':
                        $limpia = strtoupper(str_replace([' ', '-'], '', (string)$valor));
                        $esValida = (bool)preg_match('/^([A-Z]{2}[0-9]{3}[A-Z]{2}|[A-Z]{3}[0-9]{3}|[A-Z]{1}[0-9]{3}[A-Z]{3}|[0-9]{3}[A-Z]{3})$/', $limpia);
                        if (!$esValida) {
                            $this->agregarError($campo, 'Patente invalida. Formato esperado: AB123CD (Mercosur) o ABC123 (anterior).');
                        }
                        break;
                }
            }
        }

        return empty($this->errores);
    }

    private function agregarError(string $campo, string $mensaje): void {
        if (!isset($this->errores[$campo])) {
            $this->errores[$campo] = $mensaje;
        }
    }

    public function tieneErrores(): bool {
        return !empty($this->errores);
    }

    public function getErrores(): array {
        return $this->errores;
    }

    public function getPrimerError(string $campo): ?string {
        return $this->errores[$campo] ?? null;
    }
}
