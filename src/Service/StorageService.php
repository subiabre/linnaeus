<?php

namespace App\Service;

class StorageService
{
    /**
     * Builds a valid path with the given arguments
     * @return string
     */
    public function buildPath(string... $args): string
    {
        $path = '';
        foreach ($args as $key => $value) {
            $path .= sprintf('%s%s', DIRECTORY_SEPARATOR, ltrim($value, '\\/'));
        }

        return $path;
    }

    /**
     * Creates a given path if it does not exist
     * @param string $path
     * @return string $path
     */
    public function makePath(string $path): string
    {
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        return $path;
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
     * Returns the array from `readDirectory` but it filters the files for those that match `isImage`
     * @param string $path
     * @return array
     */
    public function readDirectoryImages(string $path): array
    {
        $images = [];
        
        $files = $this->readDirectory($path);
        foreach ($files as $key => $file) {
            if ($this->isImage($file)) {
                array_push($images, $file);
            }
        }

        return $images;
    }

    /**
     * Makes the ingest end path
     * @param array $ingest
     * @param string $remote
     * @return string Output path
     */
    private function buildIngestOutput(array $ingest, string $remote): string
    {
        $output = $this->buildPath($remote, $ingest['output']);
        
        $this->makePath($this->buildPath(dirname($output), DIRECTORY_SEPARATOR));
        return $output;
    }

    /**
     * Copy the ingest file from the source to remote
     * @param array $ingest
     * @param string $remote
     */
    public function copyIngestToRemote(array $ingest, string $remote)
    {
        copy($ingest['input'], $this->buildIngestOutput($ingest, $remote));
    }

    /**
     * Move the ingest file from the source to remote
     * @param array $ingest
     * @param string $remote
     */
    public function moveIngestToRemote(array $ingest, string $remote)
    {
        rename($ingest['input'], $this->buildIngestOutput($ingest, $remote));
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
