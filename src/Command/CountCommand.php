<?php

namespace App\Command;

use App\Service\StorageService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CountCommand extends Command
{
    private StorageService $storageService;

    protected static $defaultName = 'count';
    protected static $defaultDescription = 'Count the image files in a folder';

    public function __construct(StorageService $storageService)
    {
        parent::__construct();
        
        $this->storageService = $storageService;
    }

    protected function configure(): void
    {
        $this->addArgument(
            'source', 
            InputArgument::OPTIONAL,
            'Path to origin folder',
            getcwd()
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $source = realpath($input->getArgument('source'));

        if (!$source) {
            $io->error(sprintf("The source path `%s` does not exist.", $input->getArgument('source')));
            return self::FAILURE;
        }

        $io->text("Getting all the images in the source directory... ");

        $images = $this->storageService->readDirectoryImages($source);
        $imagesCount = count($images);

        $io->text(sprintf("Got %d images.\n", $imagesCount));

        return Command::SUCCESS;
    }
}
