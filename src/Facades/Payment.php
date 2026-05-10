<?php

namespace ThomasVantuycom\StatamicMollie\Facades;

use Illuminate\Support\Facades\Facade;
use ThomasVantuycom\StatamicMollie\Payments\Payments;

class Payment extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Payments::class;
    }
}
