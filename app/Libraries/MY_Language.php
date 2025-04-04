<?php

namespace app\Libraries;

use CodeIgniter\Language\Language;

class MY_Language extends Language
{

    public function getLine(string $line, array $args = [])
    {
        // If no file is given, just parse the line
        if (! str_contains($line, '.')) {
            return $this->formatMessage($line, $args);
        }

        // Parse out the file name and the actual alias.
        // Will load the language file and strings.
        [$file, $parsedLine] = $this->parseLine($line, $this->locale);

        $output = $this->getTranslationOutput($this->locale, $file, $parsedLine);

        if ($output === NULL && strpos($this->locale, '-')) {
            [$locale] = explode('-', $this->locale, 2);

            [$file, $parsedLine] = $this->parseLine($line, $locale);

            $output = $this->getTranslationOutput($locale, $file, $parsedLine);
        }

        // If still not found, try English
        if ($output === NULL || $output === "") {
            [$file, $parsedLine] = $this->parseLine($line, 'en');

            $output = $this->getTranslationOutput('en', $file, $parsedLine);
        }

        $output ??= $line;

        return $this->formatMessage($output, $args);
    }
}
