<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';

use App\Command\MysqlDumpRestore;
use App\Services\FileLocatorService;
use App\Services\FileReaderService;
use App\Services\FileSystemService;
use App\Services\MysqlForeignKeyService;
use App\Services\MysqlSqlModeService;
use Symfony\Component\Console\Application;
use Symfony\Component\Finder\Finder;

$application = new Application();

$application->add(new MysqlDumpRestore(
        new FileLocatorService(
            __DIR__,
            '*.sql',
            new Finder()
        ),
        new FileReaderService(),
        new FileSystemService(),
        new MysqlForeignKeyService(),
        new MysqlSqlModeService()
    )
);

try {
    $application->run();
} catch (Throwable $exception) {
    exit($exception->getMessage());
}
