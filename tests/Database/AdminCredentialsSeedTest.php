<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Guard test:
 * Ensure the seeded admin credentials documented in README/INSTALL remain valid.
 *
 * This does not require a DB; it validates the hash embedded in app/Database/tables.sql.
 */
final class AdminCredentialsSeedTest extends TestCase
{
    public function testAdminPasswordSeedMatchesPointOfSale(): void
    {
        $tablesSqlPath = __DIR__ . '/../../app/Database/tables.sql';
        $this->assertFileExists($tablesSqlPath);

        $sql = file_get_contents($tablesSqlPath);
        $this->assertNotFalse($sql);

        // Example line:
        // ('admin', '$2y$10$...', 1, 0, 2);
        $matched = preg_match(
            "/\\(\\s*'admin'\\s*,\\s*'([^']+)'\\s*,\\s*1\\s*,\\s*0\\s*,\\s*2\\s*\\)\\s*;/",
            $sql,
            $m
        );

        $this->assertSame(
            1,
            $matched,
            'Could not locate seeded admin credentials in app/Database/tables.sql'
        );

        $hash = $m[1];

        $this->assertNotSame('', $hash);
        $this->assertTrue(password_verify('pointofsale', $hash));
    }
}

