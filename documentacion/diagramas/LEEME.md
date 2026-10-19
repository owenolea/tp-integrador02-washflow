# Diagramas UML de WashFlow (versión sin cobros ni inventario)

Estilo: fondo negro, líneas y textos blancos. Cada diagrama tiene su `.drawio` (editable) y su `.png` (para el informe).

| Archivo | Contenido |
|---|---|
| 01_casos_uso_general | Casos de uso generales (Cliente, Empleado, Administrador) |
| 02_casos_uso_reservas | Reservas y lavados (include / extend) |
| 03_casos_uso_administracion | Funciones administrativas y permisos |
| 04a_diagrama_clases_reservas | Clases: usuarios, vehículos, modelos, servicios, precios, reservas, solicitudes, bloqueos |
| 04b_diagrama_clases_lavados_contenido | Clases: lavado, notificaciones, registro de operaciones, preguntas frecuentes, galería |
| 05_secuencia_reservar_turno | Reservar sin cuenta |
| 06_secuencia_registrar_lavado | Lavado con reserva |
| 07_secuencia_lavado_sin_reserva | Lavado sin reserva |
| 08_estados_reserva | Estados de reserva, lavado y categoría del vehículo (separados) |
| 09_actividades_reservar_turno | Actividades con carriles Cliente / Sistema |
| 10_despliegue | Navegador, Apache/PHP/MySQL (XAMPP), correo opcional |
| 11_componentes | Módulos y dependencias |
| 12_secuencia_gestionar_reserva_con_enlace | Ver, cambiar o cancelar una reserva sin cuenta |

## Qué cambió respecto de la versión anterior
- Se eliminaron los cobros (pagos, gastos y caja) y el inventario (productos y movimientos).
- Se eliminó la secuencia "registrar pago" y se renumeró desde el diagrama 08.
- Los informes quedan como estadísticas operativas (ocupación por día, servicios más solicitados, exportación a CSV), sin ganancias.
- 04b pasó de "caja e inventario" a "lavados y contenido".

## Cómo abrirlos
1. https://app.diagrams.net > Archivo > Abrir desde > Dispositivo > elegir el `.drawio`.
2. Si el fondo se ve blanco: panel Diseño > "Fondo" > #000000.
3. Exportar: Archivo > Exportar como > PNG.
