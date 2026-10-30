<?php

namespace Tests;

use CodeIgniter\Test\CIUnitTestCase;
use ParseError;
use PHPUnit\Framework\Attributes\DataProvider;

class LanguageFilesTest extends CIUnitTestCase
{
    /**
     * @var array<string, list<string>>
     */
    private static array $englishKeysCache = [];

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
        } catch (ParseError $parseError) {
            $this->fail("Failed to parse {$file}: {$parseError->getMessage()}");
        }

        $this->assertIsArray($result, "{$file} must return an array");
    }

    #[DataProvider('languageFileProvider')]
    public function testLanguageFile_MatchesEnglishKeys(string $file): void
    {
        $baseName = basename($file);
        if (! isset(self::$englishKeysCache[$baseName])) {
            $enFile = realpath(__DIR__ . '/../app/Language/en/' . $baseName);
            self::$englishKeysCache[$baseName] = array_keys(include $enFile);
        }

        $currentKeys = array_keys(include $file);
        $expectedKeys = self::$englishKeysCache[$baseName];

        $extra = array_diff($currentKeys, $expectedKeys);
        $this->assertEmpty($extra, "{$file} has extra keys: " . implode(', ', $extra));

        if (str_contains($file, '/en-GB/')) {
            return;
        }

        $missing = array_diff($expectedKeys, $currentKeys);
        $this->assertEmpty($missing, "{$file} is missing keys: " . implode(', ', $missing));
    }

    #[DataProvider('languageFileProvider')]
    public function testLanguageFile_RequiresAlphabeticalOrder(string $file): void
    {
        $baseName = basename($file);
        if ($baseName === 'Calendar.php') {
            $this->assertTrue(true);
            return;
        }

        $keys = array_keys(include $file);
        $sortedKeys = $keys;
        sort($sortedKeys, SORT_STRING);

        $this->assertSame($sortedKeys, $keys, "Keys in {$file} must be sorted alphabetically");
    }

    #[DataProvider('languageFileProvider')]
    public function testLanguageFile_RejectsDuplicateKeys(string $file): void
    {
        $content = file_get_contents($file);
        $lines = explode("\n", $content);
        $seen = [];
        $duplicates = [];

        foreach ($lines as $line) {
            if (preg_match('/^\s*\'([^\']+)\'\s*=>/', $line, $matches)) {
                $key = $matches[1];
                if (isset($seen[$key])) {
                    $duplicates[] = $key;
                }
                $seen[$key] = true;
            }
        }

        $this->assertEmpty($duplicates, "Duplicate keys in {$file}: " . implode(', ', $duplicates));
    }

    public function testSupportedLocales_ContainsAllLanguageDirectories(): void
    {
        $appConfig = config('Config\App');
        $languageRoot = realpath(__DIR__ . '/../app/Language');
        $dirs = array_map('basename', glob($languageRoot . '/*', GLOB_ONLYDIR));

        $missing = array_diff($dirs, $appConfig->supportedLocales);
        $this->assertEmpty($missing, 'Config\App::$supportedLocales missing: ' . implode(', ', $missing));
    }

    public function testGetLanguages_ContainsAllLanguageDirectories(): void
    {
        helper('locale');
        $languages = get_languages();
        $registeredLocales = array_map(static fn ($key) => explode(':', $key)[0], array_keys($languages));

        $languageRoot = realpath(__DIR__ . '/../app/Language');
        $dirs = array_map('basename', glob($languageRoot . '/*', GLOB_ONLYDIR));

        $missing = array_diff($dirs, $registeredLocales);
        $this->assertEmpty($missing, 'locale_helper::get_languages() missing: ' . implode(', ', $missing));
    }
}
