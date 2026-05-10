<?php

namespace ThomasVantuycom\StatamicMollie\Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use ThomasVantuycom\StatamicMollie\Fieldtypes\PaymentStatus;
use ThomasVantuycom\StatamicMollie\Tests\TestCase;

class PaymentStatusTest extends TestCase
{
    #[Test]
    public function it_is_not_selectable(): void
    {
        $fieldtype = new PaymentStatus;

        $this->assertFalse($fieldtype->selectable());
        $this->assertFalse($fieldtype->selectableInForms());
    }
}
