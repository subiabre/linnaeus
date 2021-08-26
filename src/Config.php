<?php

namespace App;

use App\Service\StorageService;
use DateTime;
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

    public const DEFAULT_DATE_FORMAT_YEAR = 'Y';
    public const DEFAULT_DATE_FORMAT_MONTH = 'm';
    public const DEFAULT_DATE_FORMAT_DAY = 'd';
    public const DEFAULT_DATE_FORMAT_HOUR = 'H';
    public const DEFAULT_DATE_FORMAT_MINUTES = 'i';
    public const DEFAULT_DATE_FORMAT_SECONDS = 's';

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

    public function getDateFormat(string $unit): string
    {
        return array_key_exists($unit, $this->config['output']['dateFormatting'])
            ? $this->config['output']['dateFormatting'][$unit]
            : $this->getDefaultDateFormat($unit)
            ;
    }

    public function getDefaultDateFormat(string $unit): string
    {
        switch ($unit) {
            case 'year':
                return self::DEFAULT_DATE_FORMAT_YEAR;
                break;
            case 'month':
                return self::DEFAULT_DATE_FORMAT_MONTH;
                break;
            case 'day':
                return self::DEFAULT_DATE_FORMAT_DAY;
                break;
            case 'hour':
                return self::DEFAULT_DATE_FORMAT_HOUR;
                break;
            case 'minutes':
                return self::DEFAULT_DATE_FORMAT_MINUTES;
                break;
            case 'seconds':
                return self::DEFAULT_DATE_FORMAT_SECONDS;
                break;
            default:
                return 'U';
                break;
        }
    }
}
