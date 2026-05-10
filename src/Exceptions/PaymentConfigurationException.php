<?php

namespace ThomasVantuycom\StatamicMollie\Exceptions;

use RuntimeException;

class PaymentConfigurationException extends RuntimeException
{
    public static function formMustStoreSubmissions(): self
    {
        return new self('Payments require form submissions to be stored.');
    }

    public static function missingDescription(): self
    {
        return new self('Payments require a configured description.');
    }

    public static function invalidAmount(): self
    {
        return new self('Payments require a valid configured amount.');
    }
}
