<?php

namespace Tests;

use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class LanguageFilesTest extends CIUnitTestCase
{
    /**
     * @return array<string, array{0: string}>
     */
    public static function languageFileProvider(): array
    {
        $cases = [];
        $languageRoot = realpath(__DIR__ . '/../app/Language');

        foreach (glob($languageRoot . '/*', GLOB_ONLYDIR) as $localeDir) {
            foreach (glob($localeDir . '/*.php') as $file) {
                $cases[substr($file, strlen($languageRoot) + 1)] = [$file];
            }
        }

        return $cases;
    }

    #[DataProvider('languageFileProvider')]
    public function testLanguageFile_ParsesToArray(string $file): void
    {
        try {
            $result = include $file;
        } catch (\ParseError $parseError) {
            $this->fail("Failed to parse {$file}: {$parseError->getMessage()}");
        }

        $this->assertIsArray($result, "{$file} must return an array");
    }
}
