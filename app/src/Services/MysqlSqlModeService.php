<?php

declare(strict_types=1);

namespace App\Services;

class MysqlSqlModeService
{
    public function setSessionSQLMode(string $modes, bool $isSessionVariable = true): string
    {
        return 'SET ' . ($isSessionVariable ? 'SESSION' : '') . ' sql_mode = \'' . $modes . '\';' . PHP_EOL;
    }
}