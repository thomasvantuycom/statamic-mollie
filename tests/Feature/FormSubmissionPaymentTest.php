<?php

namespace ThomasVantuycom\StatamicMollie\Tests\Feature;

use Mockery;
use Mollie\Api\Contracts\Connector;
use Mollie\Api\Resources\Payment as MolliePayment;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\FormSubmission;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use ThomasVantuycom\StatamicMollie\Facades\Payment;
use ThomasVantuycom\StatamicMollie\Tests\TestCase;

class FormSubmissionPaymentTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_redirects_to_checkout_and_stores_payment_data_after_form_submission(): void
    {
        $form = Form::make('contact')
            ->title('Contact')
            ->set('payment', [
                'description' => 'Order #1001',
                'amount' => [
                    'currency' => 'EUR',
                    'value' => '10.00',
                ],
            ]);

        $form->save();

        Payment::shouldReceive('createFromSubmission')
            ->once()
            ->andReturn($this->fakePayment());

        $response = $this->from('/form')->post(route('statamic.forms.submit', $form->handle()), [
            '_redirect' => '/thanks',
        ]);

        $response->assertRedirect('https://example.com/checkout/tr_123');

        $submission = FormSubmission::all()->sole();

        $this->assertSame([
            'id' => 'tr_123',
            'description' => 'Order #1001',
            'amount' => [
                'currency' => 'EUR',
                'value' => '10.00',
            ],
            'status' => 'open',
        ], $submission->get('payment'));
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
