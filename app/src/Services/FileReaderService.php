<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ApplicationInvalidDataSctructure;
use ArrayIterator;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class FileReaderService
{
    public function getDatabaseTablesOffsets($fileName): ArrayIterator
    {
        # Get total lines
        $processTotalLines = new Process(['sed', '-n', '$=', $fileName]);
        $processTotalLines->run();
        $linesTotal = (int)$processTotalLines->getOutput();

        $processReadLines = new Process(['grep', '-n', "Table structure", $fileName]);
        $processReadLines->run();

        if (!$processReadLines->isSuccessful()) {
            throw new ProcessFailedException($processReadLines); #@todo -check
        }

        foreach (explode(PHP_EOL, $processReadLines->getOutput()) as $key => $line) {
            preg_match('/^(\d+).+`(.+)`$/', $line, $matches);

            if (array_key_exists(1, $matches) && array_key_exists(2, $matches)) {
                $meta[$key] = [
                    'table' => $matches[2],
                    'start' => (int)$matches[1],
                ];

                if (array_key_exists($key - 1, $meta) && array_key_exists('start', $meta[$key - 1])) {
                    $meta[$key - 1]['end'] = (int)$matches[1] - 2;
                }

                if (array_key_exists($key, $meta) && !array_key_exists('end', $meta[$key])) {
                    $meta[$key]['end'] = $linesTotal;
                }
            }
        }

        foreach ($meta ?? [] as $line) {
            $metaLines[$line['table']] = ['start' => $line['start'], 'end' => $line['end']];
        }

        $iterator = new ArrayIterator($metaLines ?? []);

        if ($iterator->count() === 0) {
            throw new ApplicationInvalidDataSctructure('Can not find any tables definitions.');
        }

        return $iterator;
    }
}