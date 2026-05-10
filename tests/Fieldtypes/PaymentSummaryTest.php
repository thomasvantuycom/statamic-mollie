<?php

namespace ThomasVantuycom\StatamicMollie\Tests\Fieldtypes;

use PHPUnit\Framework\Attributes\Test;
use Statamic\Fieldtypes\Group;
use ThomasVantuycom\StatamicMollie\Fieldtypes\PaymentSummary;
use ThomasVantuycom\StatamicMollie\Tests\TestCase;

class PaymentSummaryTest extends TestCase
{
    #[Test]
    public function it_extends_the_group_fieldtype()
    {
        $this->assertInstanceOf(Group::class, new PaymentSummary);
    }

    #[Test]
    public function it_is_not_selectable(): void
    {
        $fieldtype = new PaymentSummary;

        $this->assertFalse($fieldtype->selectable());
        $this->assertFalse($fieldtype->selectableInForms());
    }
}
