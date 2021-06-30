<?php

namespace App\Command;

use App\Config;
use App\Service\IngestService;
use App\Service\StorageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class IngestCommand extends Command
{
    private const CURRENT_WORKING_DIRECTORY_SYMBOL = '.';
    
    private Config $config;
    private StorageService $storageService;
    private IngestService $ingestService;

    protected static $defaultName = 'ingest';
    protected static $defaultDescription = 'Get the files from a source folder to a remote folder according to the configuration.';

    public function __construct(
        Config $config, 
        StorageService $storageService,
        IngestService $ingestService
    ) {
        parent::__construct();
        
        $this->config = $config;
        $this->storageService = $storageService;
        $this->ingestService = $ingestService;
    }

    protected function configure(): void
    {
        $this->addArgument(
            'source', 
            InputArgument::REQUIRED,
            'Path to origin folder'
        );

        $this->addArgument(
            'remote',
            InputArgument::REQUIRED,
            'Path to target folder'
        );

        $this->addOption(
            'copySource',
            'cs',
            InputOption::VALUE_OPTIONAL,
            'If set to true it will copy the files from origin, else it will move them from origin',
            true
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $source = realpath($input->getArgument('source'));
        $remote = realpath($input->getArgument('remote'));

        if ($source === self::CURRENT_WORKING_DIRECTORY_SYMBOL) {
            $source = getcwd();
        }

        if (!$remote) {
            $remote = $this->storageService->makePath($input->getArgument('remote'));
        }

        $files = $this->storageService->readDirectory($source);

        $progressBar = new ProgressBar($output, count($files));
        $progressBar->start();

        foreach ($files as $key => $file) {
            if (!$this->storageService->isImage($file)) {
                continue;
            }

            $ingest = $this->ingestService->ingestFile($file, $this->config);
            if (!$input->getOption('copySource')) {
                $this->storageService->moveIngestToRemote($ingest, $remote);
            } else {
                $this->storageService->copyIngestToRemote($ingest, $remote);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $io->success("Ingestion finished successfully.");

        return Command::SUCCESS;
    }
}
