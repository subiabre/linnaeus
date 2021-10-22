<?php

namespace App;

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

    public function getFilename(array $data): string
    {
        return strtr($this->config['output']['naming']['files'], $data);
    }

    public function getFolder(array $data): string
    {
        return strtr($this->config['output']['naming']['folders'], $data);
    }

    public function hasRemove(): bool
    {
        return $this->config['input']['removeFiles'];
    }
}
