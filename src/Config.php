<?php

namespace App;

use App\Service\StorageService;
use Symfony\Component\Yaml\Yaml;

class Config
{
    public const YEAR = '{year}';
    public const MONTH = '{month}';
    public const DAY = '{day}';
    public const HOUR = '{hour}';
    public const MINUTES = '{minutes}';
    public const SECONDS = '{seconds}';
    public const FILENAME = '{fileName}';
    public const EXTENSION = '{fileExtension}';
    public const IMAGE_TYPE = '{imageType}';

    private array $config;

    public function __construct(
        string $config,
        StorageService $storageService
    ) {
        $configPath = $storageService->buildPath(
            dirname(__DIR__),
            'config',
            $config
        );

        $this->config = Yaml::parseFile($configPath);
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function setFilename(array $data): string
    {
        return strtr($this->config['output']['fileNaming'], $data);
    }

    public function setFolder(array $data): string
    {
        $folderStructure = array_key_exists($data[self::IMAGE_TYPE], $this->config['output']['folderStructure'])
            ? $this->config['output']['folderStructure'][$data[self::IMAGE_TYPE]]
            : $this->config['output']['folderStructure']['default']
            ;
        
        return strtr(
            $folderStructure,
            $data
        );
    }
}
