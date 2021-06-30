<?php

namespace App\Command;

use App\Config;
use App\Service\IngestService;
use App\Service\StorageService;
use Symfony\Component\Console\Command\Command;
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
    protected static $defaultDescription = 'Ingest and process the photo files in a folder';

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
            'path', 
            InputArgument::OPTIONAL, 
            'Path to folder, defaults to the current one', 
            self::CURRENT_WORKING_DIRECTORY_SYMBOL
        );

        $this->addOption(
            'copySources',
            'cs',
            InputOption::VALUE_OPTIONAL,
            'If set to true it will copy the files from origin, else it will move them from origin',
            true
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = realpath($input->getArgument('path'));

        if ($path === self::CURRENT_WORKING_DIRECTORY_SYMBOL) {
            $path = getcwd();
        }

        $this->ingestService->ingestFiles(
            $this->storageService->readDirectory($path),
            $this->config,
            filter_var($input->getOption('copySources'), FILTER_VALIDATE_BOOLEAN)
        );

        return Command::SUCCESS;
    }
}
