<?php

namespace App\Service;

use App\Config;
use App\Taxonomy;
use DateTime;
use SplFileInfo;

class TaxonomyService
{
    private StorageService $storageService;

    public function __construct(
        StorageService $storageService
    ) {
        $this->storageService = $storageService;
    }

    /**
     * Generate the Taxonomy for an image file
     * @param string $file
     * @param string $target
     * @param Config $config
     * @return Taxonomy
     */
    public function getFileTaxonomy(string $file, string $target, Config $config): Taxonomy
    {
        $data = $this->processExifData($file, $config);
        $output = $this->storageService->buildPath(
            $target,
            strtr($config->getNamingFolders(), $data),
            strtr($config->getNamingFiles(), $data)
        );

        return new Taxonomy($file, $output);
    }

    /**
     * Generate the Taxonomy for each file in an array of image files
     * @param array $files
     * @param string $target
     * @param Config $config
     * @return Taxonomy[]
     */
    public function taxonomizeFiles(array $files, string $target, Config $config): array
    {
        foreach ($files as $key => $file) {
            if (!$this->storageService->isImage($file)) {
                continue;
            }

            $files[$key] = $this->getFileTaxonomy($file, $target, $config);
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
            Config::IMAGE_AUTHOR => $this->getData($raw, 'IFD0', 'Artist'),
            Config::IMAGE_CAMERA => $this->getData($raw, 'IFD0', 'Model'),
            Config::IMAGE_TYPE => $this->getImageType($raw, $file),
            Config::IMAGE_WIDTH => $this->getImageWidth($raw, $file),
            Config::IMAGE_HEIGHT => $this->getImageHeight($raw, $file),
            Config::FILE_NAME => $this->getFileName($raw, $file),
            Config::FILE_EXT => $this->getFileExtension($raw, $file),
            Config::FILE_HASH => $this->getFileHash($file, $config->getFileHashLength()),
            Config::DATE_YEAR => $date->format('Y'),
            Config::DATE_MONTH => $date->format('m'),
            Config::DATE_DAY => $date->format('d'),
            Config::DATE_HOUR => $date->format('H'),
            Config::DATE_MINUTES => $date->format('i'),
            Config::DATE_SECONDS => $date->format('s'),
        ];
    }

    /**
     * Get the value at the specified keys
     * @param array $raw
     * @param string $keys
     * @return null|mixed The value contained at the specified keys
     */
    private function getData(array $raw, string... $keys)
    {
        $data = $raw;
        foreach ($keys as $key) {
            if (!array_key_exists($key, $data)) {
                return null;
            }

            $data = $data[$key];
        }

        return $data;
    }

    private function getImageExifData(string $file): array
    {
        return @exif_read_data($file) ? @exif_read_data($file, null, true) : [];
    }

    private function getImageType(array $raw, string $file): string
    {
        $mime = $this->getData($raw, 'FILE', 'MimeType');

        $imageType = $mime
            ? $mime
            : $this->storageService->getMimeType($file)
            ;

        return ltrim($imageType, 'image/');
    }

    private function getFileHash(string $file, int $length): string
    {
        return substr(hash_file('sha256', $file), 0, $length);
    }

    private function getFileExtension(array $raw, string $file): string
    {
        $splExtension = (new SplFileInfo($file))->getExtension();

        if (strlen($splExtension) > 0) return $splExtension;

        switch ($this->getImageType($raw, $file)) {
            case 'jpeg':
                return image_type_to_extension(IMAGETYPE_JPEG, false);   
            case 'png':
                return image_type_to_extension(IMAGETYPE_PNG, false);
            case 'tiff':
                return image_type_to_extension(IMAGETYPE_TIFF_II, false);
            case 'gif':
                return image_type_to_extension(IMAGETYPE_GIF, false);
            case 'bmp':
                return image_type_to_extension(IMAGETYPE_BMP, false);
            default:
                return $splExtension;
        }
    }

    private function getFileName(array $raw, string $file): string
    {
        $extension = $this->getFileExtension($raw, $file);
        
        return basename($file, $extension);
    }

    private function getImageDate(array $raw, string $file): DateTime
    {
        $date = $this->getData($raw, 'EXIF', 'DateTimeOriginal');

        return $date
            ? new DateTime($date)
            : DateTime::createFromFormat('U', filectime($file))
            ;
    }

    private function getImageWidth(array $raw, string $file): int
    {
        $width = $this->getData($raw, 'COMPUTED', 'Width');

        return $width
            ? $width
            : getimagesize($file)[0]
            ;
    }

    private function getImageHeight(array $raw, string $file): int
    {
        $height = $this->getData($raw, 'COMPUTED', 'Height');

        return $height
            ? $height
            : getimagesize($file)[1]
            ;
    }
}
