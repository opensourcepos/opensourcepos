<?php

namespace Tests\Helpers;

use PHPUnit\Framework\TestCase;

class UrlHelperTest extends TestCase
{
    protected function setUp(): void
    {
        require_once __DIR__ . '/../../app/Helpers/url_helper.php';
    }

    public function testBase64urlEncode(): void
    {
        $data = 'Test data';
        $encoded = base64url_encode($data);

        $this->assertMatchesRegularExpression('/^[A-Za-z0-9\-_]+$/', $encoded);

        $decoded = base64url_decode($encoded);
        $this->assertEquals($data, $decoded);
    }
}