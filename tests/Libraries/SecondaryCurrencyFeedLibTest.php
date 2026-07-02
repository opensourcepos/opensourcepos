<?php

namespace Tests\Libraries;

use App\Libraries\SecondaryCurrencyFeedLib;
use CodeIgniter\Test\CIUnitTestCase;

class SecondaryCurrencyFeedLibTest extends CIUnitTestCase
{
    public function testBuildFeedUrlReplacesBaseAndQuoteTokens(): void
    {
        $service = new SecondaryCurrencyFeedLib();

        $url = $service->buildFeedUrl([
            'currency_code' => 'USD',
            'secondary_currency_code' => 'LBP',
            'secondary_currency_feed_url' => 'https://example.test/latest/{base}?quote={quote}',
        ]);

        $this->assertSame('https://example.test/latest/USD?quote=LBP', $url);
    }

    public function testExtractRateSupportsCommonPayloadShapes(): void
    {
        $service = new SecondaryCurrencyFeedLib();

        $this->assertSame(1500.5, $service->extractRate([
            'rates' => [
                'LBP' => 1500.5,
            ],
        ], 'LBP'));

        $this->assertSame(1501.25, $service->extractRate([
            'conversion_rates' => [
                'LBP' => 1501.25,
            ],
        ], 'LBP'));

        $this->assertSame(1502.75, $service->extractRate([
            'data' => [
                'LBP' => 1502.75,
            ],
        ], 'LBP'));
    }
}
