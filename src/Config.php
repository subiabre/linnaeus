<?php

namespace App;

use Symfony\Component\Yaml\Yaml;

class Config
{
    public const DATE_YEAR = '{date.year}';
    public const DATE_MONTH = '{date.month}';
    public const DATE_DAY = '{date.day}';
    public const DATE_HOUR = '{date.hour}';
    public const DATE_MINUTES = '{date.minutes}';
    public const DATE_SECONDS = '{date.seconds}';
    public const FILE_NAME = '{file.name}';
    public const FILE_EXT = '{file.extension}';
    public const FILE_HASH = '{file.hash}';
    public const IMAGE_TYPE = '{image.type}';
    public const IMAGE_WIDTH = '{image.width}';
    public const IMAGE_HEIGHT = '{image.height}';
    public const IMAGE_AUTHOR = '{image.author}';
    public const IMAGE_CAMERA = '{image.camera}';

    private string $path;
    private array $config;

    public function __construct($path)
    {
        $this->setConfig($path);
    }

    /**
     * @param string $path Location of a yaml file containing linnaeus configuration
     * @return self
     */
    public function setConfig(string $path): self
    {
        $this->path = $path;
        $this->config = Yaml::parseFile($path);

        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    private function normalizeNaming($key)
    {
        $naming = $this->config['output']['naming'][$key];

        if (is_array($naming)) {
            return implode('', $naming);
        }

        return $naming;
    }

    public function getNamingFiles(): string
    {
        return $this->normalizeNaming('files');
    }

    public function getNamingFolders(): string
    {
        return $this->normalizeNaming('folders');
    }

    public function isCopyFiles(): bool
    {
        return $this->config['input']['copyFiles'];
    }

    public function getFileHashLength(): int
    {
        return $this->config['output']['fileHashLength'];
    }
}
