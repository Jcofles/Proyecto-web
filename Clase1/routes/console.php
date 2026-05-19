<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('import:coordenadas {--truncate : Truncate nodo_tipos, nodos and conexiones before import}', function () {
    $sqlPath = realpath(__DIR__ . '/../../coordenadas/mapeo_itfip-1.sql');

    if (!$sqlPath || ! file_exists($sqlPath)) {
        $this->error('No se encontró el archivo de coordenadas en: ' . __DIR__ . '/../../coordenadas/mapeo_itfip-1.sql');
        return 1;
    }

    $content = file_get_contents($sqlPath);
    $tables = ['nodo_tipos', 'nodos', 'conexiones'];

    if ($this->option('truncate')) {
        $this->info('Truncando tablas antes de importar...');
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    $statements = [];
    foreach ($tables as $table) {
        $pattern = '/INSERT INTO `'.preg_quote($table, '/').'`.*?;/si';
        preg_match_all($pattern, $content, $matches);

        if (empty($matches[0])) {
            $this->warn("No se encontró INSERT INTO para la tabla {$table}.");
            continue;
        }

        foreach ($matches[0] as $statement) {
            $statements[] = $statement;
        }
    }

    if (empty($statements)) {
        $this->error('No se detectaron sentencias INSERT válidas para importar.');
        return 1;
    }

    DB::beginTransaction();

    try {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        foreach ($statements as $statement) {
            DB::statement($statement);
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
        DB::commit();

        $this->info('Importación de coordenadas completada correctamente.');
        return 0;
    } catch (\Throwable $exception) {
        DB::rollBack();
        $this->error('Error al importar coordenadas: ' . $exception->getMessage());
        return 1;
    }
})->purpose('Importar coordenadas mapeadas desde coordenadas/mapeo_itfip-1.sql en la base de datos actual');
