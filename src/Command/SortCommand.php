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
use Symfony\Component\Stopwatch\Stopwatch;

class SortCommand extends Command
{
    private Config $config;
    private StorageService $storageService;
    private IngestService $ingestService;

    protected static $defaultName = 'sort';
    protected static $defaultDescription = 'Process the files in a folder and sort them';

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
            InputArgument::OPTIONAL,
            'Path to origin folder',
            getcwd()
        );

        $this->addArgument(
            'remote',
            InputArgument::OPTIONAL,
            'Path to target folder',
            getcwd()
        );

        $this->addOption(
            'config',
            'c',
            InputOption::VALUE_OPTIONAL,
            'Specify the filename to look for linnaeus configuration',
            'linnaeus.yaml'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $source = realpath($input->getArgument('source'));
        $remote = realpath($input->getArgument('remote'));
        $config = realpath($input->getOption('config'));

        if (!$source) {
            $io->error(sprintf("The source path `%s` does not exist.", $input->getArgument('source')));
            return self::FAILURE;
        }

        if (!$remote) {
            $io->note(sprintf("The target path `%s` does not exist.", $input->getArgument('remote')));
            $create = $io->confirm("Do you want to create it now?", true);

            if (!$create) {
                return self::FAILURE;
            }

            $remote = realpath($this->storageService->makePath($input->getArgument('remote')));
        }

        $config = $config ? $this->config->setConfig($config) : $this->config;

        $stopwatch = new Stopwatch(true);
        $stopwatch->start('sort');

        $io->info(sprintf("Using configuration at `%s`", $config->getPath()));
        $io->text("Getting all the images in the source directory... ");

        $images = $this->storageService->readDirectoryImages($source);
        $imagesCount = count($images);

        $io->text(sprintf("Got %d images.\n", $imagesCount));
        $io->text("Ingesting the images in the source directory...\n");

        $progressBar = new ProgressBar($output, $imagesCount);
        $progressBar->start();

        foreach ($images as $image) {
            $ingest = $this->ingestService->ingestFile($image, $config);
            
            if ($this->config->hasRemove()) {
                $this->storageService->moveIngestToRemote($ingest, $remote);
            } else {
                $this->storageService->copyIngestToRemote($ingest, $remote);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $time = $stopwatch->stop('sort');

        $io->newLine(2);
        $io->success([
            "Finished sorting.",
            sprintf("Time: %f seconds", $time->getDuration() / 1000),
            sprintf("Input: %d images at `%s`", $imagesCount, $source),
            sprintf("Output: %d images at `%s`", count($this->storageService->readDirectoryImages($remote)), $remote)
        ]);

        return Command::SUCCESS;
    }
}