<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Storage;

$filePath = $argv[1] ?? null;
if (!$filePath || !file_exists($filePath)) {
    echo "Archivo de backup no encontrado: $filePath\n";
    exit(1);
}

$fileName = basename($filePath);
echo "Sincronizando $fileName a Cloudflare R2...\n";

try {
    $stream = fopen($filePath, 'r');
    Storage::disk('r2')->put('backups/' . $fileName, $stream);
    if (is_resource($stream)) {
        fclose($stream);
    }
    echo "OK: Backup subido exitosamente a Cloudflare R2 (backups/$fileName)\n";
} catch (\Throwable $e) {
    echo "ERROR R2: " . $e->getMessage() . "\n";
    exit(1);
}
