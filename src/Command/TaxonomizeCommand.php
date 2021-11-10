<?php

namespace App\Command;

use App\Config;
use App\Service\StorageService;
use App\Service\TaxonomyService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Stopwatch\Stopwatch;

class TaxonomizeCommand extends Command
{
    private Config $config;
    private StorageService $storageService;
    private TaxonomyService $taxonomyService;

    protected static $defaultName = 'taxonomize';
    protected static $defaultDescription = 'Process the files in a folder and taxonomize them';

    public function __construct(
        Config $config, 
        StorageService $storageService,
        TaxonomyService $taxonomyService
    ) {
        parent::__construct();
        
        $this->config = $config;
        $this->storageService = $storageService;
        $this->taxonomyService = $taxonomyService;
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
            'target',
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
        $target = realpath($input->getArgument('target'));
        $config = realpath($input->getOption('config'));

        if (!$source) {
            $io->error(sprintf("The source path `%s` does not exist.", $input->getArgument('source')));
            return self::FAILURE;
        }

        if (!$target) {
            $io->note(sprintf("The target path `%s` does not exist.", $input->getArgument('target')));
            $create = $io->confirm("Do you want to create it now?", true);

            if (!$create) {
                return self::FAILURE;
            }

            $target = realpath($this->storageService->makePath($input->getArgument('target')));
        }

        $config = $config ? $this->config->setConfig($config) : $this->config;

        $stopwatch = new Stopwatch(true);
        $stopwatch->start('execution');

        $io->info(sprintf("Using configuration at `%s`", $config->getPath()));
        $io->text("Getting all the images in the source directory... ");

        $images = $this->storageService->readDirectoryImages($source);
        $imagesCount = count($images);

        $io->text(sprintf("Got %d images.\n", $imagesCount));
        $io->text("Ingesting the images in the source directory...");

        $progressBar = new ProgressBar($output, $imagesCount);
        $progressBar->start();

        foreach ($images as $image) {
            $taxonomy = $this->taxonomyService->getFileTaxonomy($image, $target, $config);
            
            if ($config->isCopyFiles()) {
                $this->storageService->copyTaxonomy($taxonomy);
            } else {
                $this->storageService->moveTaxonomy($taxonomy);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $execution = $stopwatch->stop('execution');

        $io->newLine(2);
        $io->success([
            sprintf("Time: %f seconds", $execution->getDuration() / 1000),
            sprintf("Input: %d images.", $imagesCount),
            sprintf("Source: `%s`", $source),
            sprintf("Target: `%s`", $target)
        ]);

        return Command::SUCCESS;
    }
}
