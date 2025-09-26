<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return static function (RectorConfig $rectorConfig): void {
    // Carpetas donde aplicará Rector
    $rectorConfig->paths([
        __DIR__ . '/app',
        __DIR__ . '/database',
        __DIR__ . '/routes',
        __DIR__ . '/tests',
    ]);

    // Nivel de PHP al que quieres refactorizar
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_82,
    ]);

    // Opcional: importar nombres de clases automáticamente
    $rectorConfig->importNames();
};
