<?php

declare(strict_types=1);

namespace App\Services;

class MysqlForeignKeyService
{
    # SET FOREIGN_KEY_CHECKS=0;
    public function disableForeignKeyChecks(string $foreignKeyChecks): string
    {
        return 'SET FOREIGN_KEY_CHECKS=' . $foreignKeyChecks . ';' . PHP_EOL;
    }
}