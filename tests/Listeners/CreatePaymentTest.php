<?php

namespace ThomasVantuycom\StatamicMollie\Tests\Listeners;

use Mockery;
use Mollie\Api\Contracts\Connector;
use Mollie\Api\Resources\Payment as MolliePayment;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Events\FormSubmitted;
use Statamic\Facades\Form;
use ThomasVantuycom\StatamicMollie\Facades\Payment;
use ThomasVantuycom\StatamicMollie\Listeners\CreatePayment;
use ThomasVantuycom\StatamicMollie\Tests\TestCase;

class CreatePaymentTest extends TestCase
{
    #[Test]
    public function it_sets_payment_data_and_submission_redirect(): void
    {
        $form = Form::make('contact')->set('payment', [
            'description' => 'Order #1001',
            'amount' => [
                'currency' => 'EUR',
                'value' => '10.00',
            ],
        ]);

        $submission = $form->makeSubmission();

        Payment::shouldReceive('createFromSubmission')
            ->once()
            ->with($submission)
            ->andReturn($this->fakePayment());

        (new CreatePayment)->handle(new FormSubmitted($submission));

        $this->assertSame([
            'id' => 'tr_123',
            'description' => 'Order #1001',
            'amount' => [
                'currency' => 'EUR',
                'value' => '10.00',
            ],
            'status' => 'open',
        ], $submission->get('payment'));
        $this->assertSame('https://example.com/checkout/tr_123', $submission->getSupplement('checkout_url'));
        $this->assertSame('https://example.com/checkout/tr_123', Form::getSubmissionRedirect($submission));
    }

    #[Test]
    public function it_does_not_create_a_payment_when_the_config_is_missing(): void
    {
        $form = Form::make('contact');
        $submission = $form->makeSubmission();

        Payment::shouldReceive('createFromSubmission')->never();

        (new CreatePayment)->handle(new FormSubmitted($submission));

        $this->assertNull($submission->get('payment'));
        $this->assertNull($submission->getSupplement('checkout_url'));
    }

    #[Test]
    public function it_does_not_create_a_payment_when_the_config_is_not_an_array(): void
    {
        $form = Form::make('contact')->set('payment', null);
        $submission = $form->makeSubmission();

        Payment::shouldReceive('createFromSubmission')->never();

        (new CreatePayment)->handle(new FormSubmitted($submission));

        $this->assertNull($submission->get('payment'));
        $this->assertNull($submission->getSupplement('checkout_url'));
    }

    private function fakePayment(): MolliePayment
    {
        $payment = new MolliePayment(Mockery::mock(Connector::class));
        $payment->id = 'tr_123';
        $payment->description = 'Order #1001';
        $payment->amount = (object) [
            'currency' => 'EUR',
            'value' => '10.00',
        ];
        $payment->status = 'open';
        $payment->_links = (object) [
            'checkout' => (object) [
                'href' => 'https://example.com/checkout/tr_123',
            ],
        ];

        return $payment;
    }
}
