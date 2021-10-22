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
     * Generate the ingest output for an image file
     * @param string $file
     * @param Config $config
     * @return array
     */
    public function ingestFile(string $file, Config $config): array
    {
        $data = $this->processExifData($file, $config);

        return [
            'output' => $this->storageService->buildPath(
                $config->getFolder($data),
                $config->getFilename($data)
            ),
            'input' => $file
        ];
    }

    /**
     * Generate the ingest output for each file in an array of image files
     * @param array $files
     * @param Config $config
     * @return array
     */
    public function ingestFiles(array $files, Config $config): array
    {
        foreach ($files as $key => $file) {
            if (!$this->storageService->isImage($file)) {
                continue;
            }

            $files[$key] = $this->ingestFile($file, $config);
        }

        return $files;
    }

    /**
     * Returns a normalized array from the exif data that can be processed by `App\Config`
     * @param string $file
     * @param Config $config
     * @return array
     */
    public function processExifData(string $file, Config $config): array
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
            Config::HOUR => $date->format('H'),
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
        return !empty($raw) && !empty($raw['EXIF']['DateTimeOriginal'])
            ? new DateTime($raw['EXIF']['DateTimeOriginal'])
            : DateTime::createFromFormat('U', filectime($file))
            ;
    }
}
