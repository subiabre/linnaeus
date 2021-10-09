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

class CountCommand extends Command
{
    private const CURRENT_WORKING_DIRECTORY_SYMBOL = '.';
    
    private Config $config;
    private StorageService $storageService;
    private IngestService $ingestService;

    protected static $defaultName = 'count';
    protected static $defaultDescription = 'Count the image files in a folder.';

    public function __construct(
        Config $config, 
        StorageService $storageService
    ) {
        parent::__construct();
        
        $this->config = $config;
        $this->storageService = $storageService;
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

        return Command::SUCCESS;
    }
}
