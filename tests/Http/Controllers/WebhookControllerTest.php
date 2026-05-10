<?php

namespace ThomasVantuycom\StatamicMollie\Tests\Http\Controllers;

use Mockery;
use Mollie\Api\Contracts\Connector;
use Mollie\Api\Exceptions\NotFoundException;
use Mollie\Api\Resources\Payment as MolliePayment;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use Statamic\Facades\FormSubmission;
use Statamic\Testing\Concerns\PreventsSavingStacheItemsToDisk;
use ThomasVantuycom\StatamicMollie\Facades\Payment;
use ThomasVantuycom\StatamicMollie\Tests\TestCase;

class WebhookControllerTest extends TestCase
{
    use PreventsSavingStacheItemsToDisk;

    #[Test]
    public function it_updates_the_submission_payment_status_from_the_webhook(): void
    {
        $form = Form::make('contact')->title('Contact');
        $form->save();

        $submission = $form->makeSubmission();
        $submission->data([
            'payment' => [
                'id' => 'tr_123',
                'description' => 'Order #1001',
                'amount' => [
                    'currency' => 'EUR',
                    'value' => '10.00',
                ],
                'status' => 'open',
            ],
        ]);
        $submission->save();

        Payment::shouldReceive('get')
            ->once()
            ->with('tr_123')
            ->andReturn($this->fakePayment($submission->id(), 'paid'));

        $response = $this->post(route('statamic.mollie.webhook'), [
            'id' => 'tr_123',
        ]);

        $response->assertStatus(200);

        $updatedSubmission = FormSubmission::find($submission->id());

        $this->assertSame([
            'id' => 'tr_123',
            'description' => 'Order #1001',
            'amount' => [
                'currency' => 'EUR',
                'value' => '10.00',
            ],
            'status' => 'paid',
        ], $updatedSubmission->get('payment'));
    }

    #[Test]
    public function it_returns_no_content_when_the_webhook_has_no_payment_id(): void
    {
        Payment::shouldReceive('get')->never();

        $this->post(route('statamic.mollie.webhook'))
            ->assertStatus(200);
    }

    #[Test]
    public function it_returns_no_content_when_mollie_cannot_find_the_payment(): void
    {
        Payment::shouldReceive('get')
            ->once()
            ->with('tr_missing')
            ->andThrow(Mockery::mock(NotFoundException::class));

        $this->post(route('statamic.mollie.webhook'), [
            'id' => 'tr_missing',
        ])->assertStatus(200);
    }

    private function fakePayment(string $submissionId, string $status): MolliePayment
    {
        $payment = new MolliePayment(Mockery::mock(Connector::class));
        $payment->status = $status;
        $payment->metadata = (object) [
            'submission_id' => $submissionId,
        ];

        return $payment;
    }
}
