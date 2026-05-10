<?php

namespace ThomasVantuycom\StatamicMollie\Fieldtypes;

use Statamic\Fieldtypes\Group;

class PaymentSummary extends Group
{
    protected $selectable = false;
    protected $selectableInForms = false;
}
