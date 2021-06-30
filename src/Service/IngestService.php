<?php

namespace App\Service;

use App\Config;
use DateTime;

class IngestService
{
    private StorageService $storageService;

    public function __construct(
        StorageService $storageService
    ) {
        $this->storageService = $storageService;
    }

    /**
     * Ingest an array of input files and outputs them according to the configuration
     * @param array $files
     * @param Config $config
     * @param bool $copySources If set to true it will copy the file from sources, else it will move them from origin
     */
    public function ingestFiles(array $files, Config $config, bool $copySources = true)
    {
        foreach ($files as $key => $file) {
            if (!$this->storageService->isImage($file)) {
                continue;
            }

            $data = $this->processExifData($file);
            $outputPath = $this->storageService->buildPath(
                $config->getMediaFolder(),
                $config->setFolder($data),
                $config->setFilename($data)
            );

            if ($copySources) {
                copy($file, $outputPath);
            } else {
                rename($file, $outputPath);
            }
        }
    }

    /**
     * Returns a normalized array from the exif data that can be processed by App\Config
     * @param string $file
     * @return array
     */
    public function processExifData(string $file): array
    {
        $raw = $this->getImageExifData($file);
        $date = $this->getImageDate($raw, $file);

        return [
            Config::IMAGE_TYPE => $this->getImageType($raw, $file),
            Config::FILENAME => $this->getImageFilename($raw, $file),
            Config::EXTENSION => $this->getImageExtension($raw, $file),
            Config::YEAR => $date->format('Y'),
            Config::MONTH => $date->format('m'),
            Config::DAY => $date->format('d'),
            Config::HOUR => $date->format('h'),
            Config::MINUTES => $date->format('i'),
            Config::SECONDS => $date->format('s'),
        ];
    }

    private function getImageExifData(string $file): array
    {
        return @exif_read_data($file) ? @exif_read_data($file, null, true) : [];
    }

    private function getImageType(array $raw, string $file): string
    {
        $imageType = !empty($raw) && !empty($raw['FILE']['MimeType'])
            ? $raw['FILE']['MimeType']
            : $this->storageService->getMimeType($file)
            ;

        return ltrim($imageType, 'image/');
    }

    private function getImageExtension(array $raw, string $file): string
    {
        $fileExtensions = !empty($raw) && !empty($raw['FILE']['FileName'])
            ? explode('.', $raw['FILE']['FileName'])
            : explode('.', basename($file))
            ;

        return end($fileExtensions);
    }

    private function getImageFilename(array $raw, string $file): string
    {
        $extension = $this->getImageExtension($raw, $file);
        $filename = !empty($raw) && !empty($raw['FILE']['FileName'])
            ? $raw['FILE']['FileName']
            : basename($file)
            ;

        return rtrim($filename, ".$extension");
    }

    private function getImageDate(array $raw, string $file): DateTime
    {
        $date = !empty($raw) && !empty($raw['FILE']['FileDateTime'])
            ? $raw['FILE']['FileDateTime']
            : filectime($file)
            ;

        return DateTime::createFromFormat('U', $date);
    }
}
