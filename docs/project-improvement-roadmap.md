# Roadmap De Mejora Del Proyecto

## Objetivo
Mejorar estabilidad, usabilidad y mantenibilidad del panel Filament despues de la migracion.

## Prioridad Alta (Semana 1)

1. Cobertura de humo de rutas criticas del panel
- Estado: iniciado (se agrego `AdminPanelSmokeTest`).
- Exito: detectar rapido regresiones de login/admin/reportes.

2. Estandarizar fabrica de usuarios para pruebas
- Estado: implementado en `UserFactory`.
- Exito: crear usuarios validos sin datos faltantes.

3. Auditoria de permisos y acceso por rol
- Accion: revisar `canView`, politicas y Shield en recursos/paginas.
- Exito: no exponer paginas a roles no autorizados.

4. Checklist de verificacion post-deploy
- Accion: documentar comandos de cache, migrate y health checks.
- Exito: despliegues repetibles sin errores manuales.

## Prioridad Media (Semana 2)

5. Mejorar navegacion del panel
- Accion: revisar orden y nombres de grupos para reducir ambiguedad.
- Exito: menos clics para llegar a las acciones frecuentes.

6. Estandarizar mensajes de exito/error
- Accion: homologar notificaciones en CRUD y acciones de negocio.
- Exito: feedback consistente y accionable al usuario final.

7. Optimizar consultas pesadas en tablas
- Accion: agregar `with()`, columnas necesarias e indices faltantes.
- Exito: carga de listados en menos de 1 segundo en entorno local.

## Prioridad Baja (Semana 3)

8. Estados vacios y ayudas contextuales
- Accion: agregar textos claros y CTA cuando no hay datos.
- Exito: menor friccion para usuarios nuevos.

9. Pruebas de flujo de negocio completo
- Accion: escenarios end-to-end de reservas y prestamos.
- Exito: cubrir ciclo de vida principal de la app.

10. Pipeline CI basico (lint + test + build)
- Accion: ejecutar `pint`, `php artisan test`, `npm run build`.
- Exito: bloquear merges con errores de calidad.

## KPIs sugeridos
- Errores 500 en panel: 0 por release.
- Tiempo promedio de carga en recursos criticos: < 1.0s.
- Cobertura de pruebas de flujo principal: >= 70%.
- Regresiones detectadas en QA manual: reduccion >= 50%.
