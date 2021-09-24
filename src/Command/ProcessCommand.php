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

class ProcessCommand extends Command
{
    private const CURRENT_WORKING_DIRECTORY_SYMBOL = '.';
    
    private Config $config;
    private StorageService $storageService;
    private IngestService $ingestService;

    protected static $defaultName = 'process';
    protected static $defaultDescription = 'Process the files from a folder according to the configuration.';

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
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $source = realpath($input->getArgument('source'));

        if ($source === self::CURRENT_WORKING_DIRECTORY_SYMBOL) {
            $source = getcwd();
        }

        $io->write("Getting all the images in the source directory... ");

        $images = $this->storageService->readDirectoryImages($source);
        $imagesCount = count($images);

        $io->write(sprintf("Got %d images.\n", $imagesCount));
        $io->writeln("Ingesting the images in the source directory...\n");

        $progressBar = new ProgressBar($output, $imagesCount);
        $progressBar->start();

        foreach ($images as $key => $image) {
            $ingest = $this->ingestService->ingestFile($image, $this->config);
            
            $this->storageService->moveIngestToRemote($ingest, $source);

            $progressBar->advance();
        }

        $progressBar->finish();

        $io->writeln("");
        $io->success("Ingestion finished successfully.");

        return Command::SUCCESS;
    }
}
