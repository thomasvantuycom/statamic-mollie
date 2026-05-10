<?php

namespace ThomasVantuycom\StatamicMollie\Tests\Support;

use PHPUnit\Framework\Attributes\Test;
use ThomasVantuycom\StatamicMollie\Support\Currencies;
use ThomasVantuycom\StatamicMollie\Tests\TestCase;

class CurrenciesTest extends TestCase
{
    #[Test]
    public function it_returns_enabled_currencies(): void
    {
        config([
            'statamic.mollie.currencies' => ['EUR', 'USD'],
        ]);

        $this->assertSame(['EUR', 'USD'], Currencies::enabled());
    }

    #[Test]
    public function it_only_returns_enabled_currencies_when_supported(): void
    {
        config([
            'statamic.mollie.currencies' => ['EUR', 'INVALID', 'USD'],
        ]);

        $this->assertSame(['EUR', 'USD'], Currencies::enabled());
    }

    #[Test]
    public function it_returns_the_default_currency(): void
    {
        config([
            'statamic.mollie.currencies' => ['EUR', 'USD'],
            'statamic.mollie.default_currency' => 'USD',
        ]);

        $this->assertSame('USD', Currencies::default());
    }

    #[Test]
    public function it_only_returns_the_default_currency_when_enabled(): void
    {
        config([
            'statamic.mollie.currencies' => ['EUR'],
            'statamic.mollie.default_currency' => 'USD',
        ]);

        $this->assertNull(Currencies::default());
    }

    #[Test]
    public function it_only_returns_the_default_currency_when_supported(): void
    {
        config([
            'statamic.mollie.currencies' => ['EUR'],
            'statamic.mollie.default_currency' => 'INVALID',
        ]);

        $this->assertNull(Currencies::default());
    }
}
