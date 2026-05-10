<?php

namespace ThomasVantuycom\StatamicMollie\Tests\Payments;

use Illuminate\Http\Request;
use Mockery;
use Mollie\Api\Contracts\Connector;
use Mollie\Api\Http\Data\Money;
use Mollie\Api\Http\Requests\CreatePaymentRequest;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Payment as MolliePayment;
use PHPUnit\Framework\Attributes\Test;
use Statamic\Facades\Form;
use ThomasVantuycom\StatamicMollie\Exceptions\PaymentConfigurationException;
use ThomasVantuycom\StatamicMollie\Payments\Payments;
use ThomasVantuycom\StatamicMollie\Tests\TestCase;

class PaymentsTest extends TestCase
{
    #[Test]
    public function it_creates_a_payment_from_a_submission(): void
    {
        $form = Form::make('contact')->set('payment', [
            'description' => 'Order #1001',
            'amount' => [
                'currency' => 'EUR',
                'value' => '10.00',
            ],
        ]);
        $submission = $form->makeSubmission();
        $submissionId = $submission->id();

        $this->bindCurrentRequest([
            '_redirect' => '/thanks',
            '_error_redirect' => '/try-again',
        ], 'http://localhost/form');

        $expectedPayment = $this->fakePayment();
        $mollie = Mockery::mock(MollieApiClient::class);
        $mollie->shouldReceive('send')
            ->once()
            ->with(Mockery::on(function (CreatePaymentRequest $request) use ($submissionId) {
                $payload = $request->payload();
                $amount = $payload->get('amount');

                $this->assertSame('payments', $request->resolveResourcePath());
                $this->assertSame('Order #1001', $payload->get('description'));
                $this->assertInstanceOf(Money::class, $amount);
                $this->assertSame('EUR', $amount->currency);
                $this->assertSame('10.00', $amount->value);
                $this->assertSame(url('/thanks'), $payload->get('redirectUrl'));
                $this->assertSame(url('/try-again'), $payload->get('cancelUrl'));
                $this->assertSame(route('statamic.mollie.webhook'), $payload->get('webhookUrl'));
                $this->assertSame($submissionId, $payload->get('metadata.submission_id'));

                return true;
            }))
            ->andReturn($expectedPayment);

        $payment = (new Payments($mollie))->createFromSubmission($submission);

        $this->assertSame($expectedPayment, $payment);
    }

    #[Test]
    public function it_evaluates_antlers_amounts_from_the_submission_data(): void
    {
        $form = Form::make('contact')->set('payment', [
            'description' => 'Order #1001',
            'amount' => [
                'currency' => 'EUR',
                'value' => '{{ amount }}',
            ],
        ]);
        $submission = $form->makeSubmission();
        $submission->data([
            'amount' => '12.34',
        ]);

        $this->bindCurrentRequest([
            '_redirect' => '/thanks',
            '_error_redirect' => '/try-again',
        ], 'http://localhost/form');

        $mollie = Mockery::mock(MollieApiClient::class);
        $mollie->shouldReceive('send')
            ->once()
            ->with(Mockery::on(function (CreatePaymentRequest $request) {
                /** @var Money $amount */
                $amount = $request->payload()->get('amount');

                $this->assertSame('12.34', $amount->value);

                return true;
            }))
            ->andReturn($this->fakePayment());

        (new Payments($mollie))->createFromSubmission($submission);
    }

    #[Test]
    public function it_rejects_payments_for_forms_that_do_not_store_submissions(): void
    {
        $form = Form::make('contact')
            ->store(false)
            ->set('payment', [
                'description' => 'Order #1001',
                'amount' => [
                    'currency' => 'EUR',
                    'value' => '10.00',
                ],
            ]);

        $this->expectException(PaymentConfigurationException::class);
        $this->expectExceptionMessage('Payments require form submissions to be stored.');

        (new Payments(Mockery::mock(MollieApiClient::class)))
            ->createFromSubmission($form->makeSubmission());
    }

    #[Test]
    public function it_rejects_antlers_amounts_that_do_not_resolve_to_valid_numbers(): void
    {
        $form = Form::make('contact')->set('payment', [
            'description' => 'Order #1001',
            'amount' => [
                'currency' => 'EUR',
                'value' => '{{ amount }}',
            ],
        ]);
        $submission = $form->makeSubmission();
        $submission->data([
            'amount' => 'free',
        ]);

        $this->expectException(PaymentConfigurationException::class);
        $this->expectExceptionMessage('Payments require a valid configured amount.');

        (new Payments(Mockery::mock(MollieApiClient::class)))
            ->createFromSubmission($submission);
    }

    #[Test]
    public function it_rejects_payments_without_a_configured_description(): void
    {
        $form = Form::make('contact')->set('payment', [
            'amount' => [
                'currency' => 'EUR',
                'value' => '10.00',
            ],
        ]);

        $this->expectException(PaymentConfigurationException::class);
        $this->expectExceptionMessage('Payments require a configured description.');

        (new Payments(Mockery::mock(MollieApiClient::class)))
            ->createFromSubmission($form->makeSubmission());
    }

    #[Test]
    public function it_rejects_payments_without_a_valid_configured_amount(): void
    {
        $form = Form::make('contact')->set('payment', [
            'description' => 'Order #1001',
            'amount' => [
                'currency' => 'EUR',
                'value' => '0.00',
            ],
        ]);

        $this->expectException(PaymentConfigurationException::class);
        $this->expectExceptionMessage('Payments require a valid configured amount.');

        (new Payments(Mockery::mock(MollieApiClient::class)))
            ->createFromSubmission($form->makeSubmission());
    }

    #[Test]
    public function it_uses_the_previous_url_when_redirects_are_external(): void
    {
        $form = Form::make('contact')->set('payment', [
            'description' => 'Order #1001',
            'amount' => [
                'currency' => 'EUR',
                'value' => '10.00',
            ],
        ]);
        $submission = $form->makeSubmission();

        $this->bindCurrentRequest([
            '_redirect' => 'https://example.org/thanks',
            '_error_redirect' => 'https://example.org/try-again',
        ], 'http://localhost/form');

        $mollie = Mockery::mock(MollieApiClient::class);
        $mollie->shouldReceive('send')
            ->once()
            ->with(Mockery::on(function (CreatePaymentRequest $request) {
                $payload = $request->payload();

                $this->assertSame('http://localhost/form', $payload->get('redirectUrl'));
                $this->assertSame('http://localhost/form', $payload->get('cancelUrl'));

                return true;
            }))
            ->andReturn($this->fakePayment());

        (new Payments($mollie))->createFromSubmission($submission);
    }

    private function bindCurrentRequest(array $input, string $referer): void
    {
        $request = Request::create('/!/forms/contact', 'POST', $input, [], [], [
            'HTTP_REFERER' => $referer,
        ]);

        $this->app->instance('request', $request);
        $this->app['url']->setRequest($request);
    }

    private function fakePayment(): MolliePayment
    {
        return new MolliePayment(Mockery::mock(Connector::class));
    }
}
