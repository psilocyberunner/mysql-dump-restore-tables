<?php

declare(strict_types=1);

namespace App\Command;

use App\Services\FileLocatorService;
use App\Services\FileReaderService;
use App\Services\FileSystemService;
use App\Services\MysqlForeignKeyService;
use App\Services\MysqlSqlModeService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Process\Process;

class MysqlDumpRestore extends Command
{
    protected static $defaultName        = 'mysql:restore';
    protected static $defaultDescription = 'Restores mysqldump generated file (*.sql)';

    private readonly string $sysTmpDir;

    public function __construct(
        private readonly FileLocatorService     $fileLocatorService,
        private readonly FileReaderService      $fileReaderService,
        private readonly FileSystemService      $fileSystemService,
        private readonly MysqlForeignKeyService $mysqlForeignKeyService,
        private readonly MysqlSqlModeService    $mysqlSqlModeService
    )
    {
        parent::__construct();

        $this->sysTmpDir = sys_get_temp_dir();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        # Do we want immediate restore (assume foreign key checks set to 0 and sql mode to '' for old database dumps)?
        # $this->askForImmediateRestore();

        # If not restoring immediately - may be output to file?
        $targetFName = $this->askForOutputToFile($input, $output);

        //dd($targetFName);
        if ($targetFName) {
            # Disable foreign key checks?
            $enableFKChecks = $this->askForForeignKeyChecks($input, $output);

            # Set SQL mode?
            $sqlMode = $this->askForSQLMode($input, $output);

            # Create target data file for restoration data
            if ($this->fileSystemService->exists($targetFName)) {
                $this->fileSystemService->remove($targetFName);
            }
            $this->fileSystemService->touch($targetFName);

            # Set FK checks
            $this->fileSystemService->appendToFile(
                $targetFName,
                $this->mysqlForeignKeyService->disableForeignKeyChecks($enableFKChecks)
            );

            # Set SQL Mode
            $this->fileSystemService->appendToFile(
                $targetFName,
                $this->mysqlSqlModeService->setSessionSQLMode($sqlMode)
            );
        }

        $selectedSQLFileName = $this->askForSQLFileName($input, $output);

        $tableOffsetIterator = $this->fileReaderService->getDatabaseTablesOffsets($selectedSQLFileName);

        $selectedDatabaseTables = $this->askForTableName($input, $output, $tableOffsetIterator);


        foreach ($selectedDatabaseTables as $tableName) {
            $offsetDefinitions = $tableOffsetIterator->offsetGet($tableName);

            $processTotalLines = new Process(['sed', '-n', $offsetDefinitions['start'] . ', ' . $offsetDefinitions['end'] . ' p', $selectedSQLFileName]);
            $processTotalLines->run();

            if ($targetFName){
                $this->fileSystemService->appendToFile($targetFName, $processTotalLines->getOutput());
            } else {
                echo $processTotalLines->getOutput();
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Do you want output to file?
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return false|string
     */
    private function askForOutputToFile(InputInterface $input, OutputInterface $output): false|string
    {
        $fileHelper   = $this->getHelper('question');
        $fileQuestion = new ChoiceQuestion(
            'Do you want results to be written to file? ',
            // choices can also be PHP objects that implement __toString() method
            ['Y', 'N'],
            0
        );

        $fOutput = $fileHelper->ask($input, $output, $fileQuestion);

        if ($fOutput === 'Y') {
            $fileHelper   = $this->getHelper('question');
            $fileQuestion = new Question(
                'Please provide desired filename: ',
            // choices can also be PHP objects that implement __toString() method
            # ['Y', 'N'],
            #0
            );

            return $fileHelper->ask($input, $output, $fileQuestion);
        }

        return false;
    }

    private function askForTableName(InputInterface $input, OutputInterface $output, \ArrayIterator $tableOffsetIterator)
    {
        # Choose table
        $fileHelper   = $this->getHelper('question');
        $fileQuestion = new ChoiceQuestion(
            'Please select table(s) you want to work with: ',
            // choices can also be PHP objects that implement __toString() method
            array_keys($tableOffsetIterator->getArrayCopy()),
        );
        $fileQuestion->setMultiselect(true);

        return $fileHelper->ask($input, $output, $fileQuestion);
    }

    private function askForSQLMode(InputInterface $input, OutputInterface $output): string
    {
        $fileHelper   = $this->getHelper('question');
        $fileQuestion = new ChoiceQuestion(
            'Reset sql mode to empty type? ',
            // choices can also be PHP objects that implement __toString() method
            ['Y', 'N'],
            0
        );

        $answer = $fileHelper->ask($input, $output, $fileQuestion);

        if ($answer === 'Y') {
            return '';
        }

        $fileHelper   = $this->getHelper('question');
        $fileQuestion = new Question(
            'What SQL mode(s) you need? Data format from (SELECT @@SESSION.sql_mode): ',
        // choices can also be PHP objects that implement __toString() method
        # ['Y', 'N'],
        #0
        );

        return $fileHelper->ask($input, $output, $fileQuestion);
    }

    private function askForForeignKeyChecks(InputInterface $input, OutputInterface $output): string
    {
        $fileHelper   = $this->getHelper('question');
        $fileQuestion = new ChoiceQuestion(
            'Disable foreign key checks? ',
            // choices can also be PHP objects that implement __toString() method
            ['Y', 'N'],
            0
        );

        return (string)($fileHelper->ask($input, $output, $fileQuestion) === 'Y' ? 0 : 1);
    }

    private function askForSQLFileName(InputInterface $input, OutputInterface $output): string
    {
        $files = $this->fileLocatorService->getFilesList();

        # Choose file
        $fileHelper   = $this->getHelper('question');
        $fileQuestion = new ChoiceQuestion(
            'Please select *.sql file you want to work with: ',
            // choices can also be PHP objects that implement __toString() method
            $files,
            0
        );

        return $fileHelper->ask($input, $output, $fileQuestion);
    }

    /**
     * @return void
     * @todo implement
     */
    private function askForImmediateRestore()
    {
    }
}