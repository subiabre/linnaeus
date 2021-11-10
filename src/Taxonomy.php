<?php

namespace App;

/**
 * This class represents the transition object between an input file path and the ouput path the file must take\
 * It is built by Service\TaxonomyService and fed to Service\StorageService
 */
class Taxonomy
{
    private string $input;
    private string $output;

    public function __construct(string $input, string $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    public function getInput(): string
    {
        return $this->input;
    }

    public function getOutput(): string
    {
        return $this->output;
    }
}
