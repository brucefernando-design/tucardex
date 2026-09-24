<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Facturación Electrónica (SUNAT) — tareas programadas
|--------------------------------------------------------------------------
| Requiere que el cron del servidor ejecute cada minuto:
|   * * * * * php /ruta/al/proyecto/artisan schedule:run >> /dev/null 2>&1
*/

// Envía el Resumen Diario de las boletas del día anterior (01:00).
Schedule::command('facturacion:resumen-diario')
    ->dailyAt('01:00')
    ->withoutOverlapping()
    ->runInBackground();

// Consulta los tickets de resúmenes pendientes cada 30 minutos.
Schedule::command('facturacion:consultar-tickets')
    ->everyThirtyMinutes()
    ->withoutOverlapping();
