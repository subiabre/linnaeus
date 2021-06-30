<?php

namespace App\Service;

class StorageService
{
    /**
     * Builds an absolute path with the given arguments
     * @return string
     */
    public function buildPath(string... $args): string
    {
        $path = '';
        foreach ($args as $key => $value) {
            $path .= sprintf('%s%s', DIRECTORY_SEPARATOR, ltrim($value, '\\/'));
        }

        return rtrim($path, DIRECTORY_SEPARATOR);
    }

    /**
     * Recursively scan a directory and load into a one dimension array all the items inside
     * @param string $path
     * @return array
     */
    public function readDirectory(string $path): array
    {
        $directory = scandir($path);
        $files = [];

        foreach ($directory as $key => $value) {
            $item = $this->buildPath($path, $value);

            if (!is_dir($item)) {
                $files[] = $item;
            } elseif ($value != "." && $value != "..") {
                $files = array_merge($files, $this->readDirectory($item));
            }
        }

        return $files;
    }

    /**
     * Read the mime type of a file
     * @param string $file
     * @return string
     */
    public function getMimeType(string $file): string
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $type = finfo_file($finfo, $file);

        finfo_close($finfo);

        return $type;
    }

    /**
     * Tell if a file is of type image
     * @param string $file
     * @return bool
     */
    public function isImage(string $file): bool
    {
        return str_starts_with($this->getMimeType($file), 'image/');
    }
}
