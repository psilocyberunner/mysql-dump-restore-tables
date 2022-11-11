<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ApplicationFileNotFoundException;
use Symfony\Component\Finder\Finder;

class FileLocatorService
{
    public function __construct(private readonly string $filesLocation, private readonly string $filePatterns, private readonly Finder $finder)
    {
    }

    public function getFilesList(): array
    {
        $this->finder
            ->files()
            ->name($this->filePatterns)
            ->in($this->filesLocation)
            ->sortByName();

        return !$this->finder->hasResults()
            ?
            throw new ApplicationFileNotFoundException("Can not find any " . $this->filePatterns . " files in: " . $this->filesLocation)
            :
            (array)$this->finder->getIterator();
    }

}