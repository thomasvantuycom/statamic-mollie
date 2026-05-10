<?php

namespace ThomasVantuycom\StatamicMollie\Tests;

use Statamic\Testing\AddonTestCase;
use ThomasVantuycom\StatamicMollie\ServiceProvider;

abstract class TestCase extends AddonTestCase
{
    protected string $addonServiceProvider = ServiceProvider::class;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('statamic.mollie.key', 'test_123456789012345678901234567890');
    }
}
