<?php

namespace ThomasVantuycom\StatamicMollie\Payments;

use Illuminate\Support\Facades\Validator;
use Mollie\Api\Http\Data\Money;
use Mollie\Api\Http\Requests\CreatePaymentRequest;
use Mollie\Api\Http\Requests\GetPaymentRequest;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Payment;
use Statamic\Contracts\Forms\Submission;
use Statamic\Facades\Antlers;
use Statamic\Facades\URL;
use ThomasVantuycom\StatamicMollie\Exceptions\PaymentConfigurationException;
use ThomasVantuycom\StatamicMollie\Rules\PaymentAmount as PaymentAmountRule;

class Payments
{
    protected $mollie;

    public function __construct(MollieApiClient $mollie)
    {
        $this->mollie = $mollie;
    }

    public function create(
        string $description,
        array $amount,
        string $redirectUrl,
        string $cancelUrl,
        array $metadata = [],
    ): Payment {
        return $this->mollie->send(new CreatePaymentRequest(
            description: $description,
            amount: new Money(
                currency: $amount['currency'],
                value: $amount['value'],
            ),
            redirectUrl: $redirectUrl,
            cancelUrl: $cancelUrl,
            webhookUrl: route('statamic.mollie.webhook'),
            metadata: $metadata,
        ));
    }

    public function createFromSubmission(Submission $submission): Payment
    {
        $form = $submission->form();
        $config = $form->get('payment');

        if (! $form->store()) {
            throw PaymentConfigurationException::formMustStoreSubmissions();
        }

        $this->assertValidConfiguration($config);
        $amount = $this->resolveAmount($config['amount'], $submission);

        return $this->create(
            description: $config['description'],
            amount: $amount,
            redirectUrl: $this->getRedirectUrl('_redirect'),
            cancelUrl: $this->getRedirectUrl('_error_redirect'),
            metadata: [
                'submission_id' => $submission->id(),
            ],
        );
    }

    public function get(string $id): Payment
    {
        return $this->mollie->send(new GetPaymentRequest(
            id: $id
        ));
    }

    protected function getRedirectUrl(string $key): string
    {
        $redirectUrl = request()->filled($key)
           ? url(request()->input($key))
           : url()->previous();

        return URL::isExternalToApplication($redirectUrl)
            ? url()->previous()
            : $redirectUrl;
    }

    protected function assertValidConfiguration(mixed $config): void
    {
        if (! is_array($config)) {
            throw PaymentConfigurationException::invalidAmount();
        }

        if (! isset($config['description']) || ! is_string($config['description']) || trim($config['description']) === '') {
            throw PaymentConfigurationException::missingDescription();
        }

        $validator = Validator::make(
            ['amount' => $config['amount'] ?? null],
            ['amount' => [new PaymentAmountRule]]
        );

        if ($validator->fails()) {
            throw PaymentConfigurationException::invalidAmount();
        }
    }

    protected function resolveAmount(array $amount, Submission $submission): array
    {
        $resolvedAmount = $amount;
        $value = trim($resolvedAmount['value']);

        if (PaymentAmountRule::containsAntlers($value)) {
            $value = trim((string) Antlers::parse($value, $submission->data()->all()));
        }

        $resolvedAmount['value'] = $value;

        $validator = Validator::make(
            ['amount' => $resolvedAmount],
            ['amount' => [new PaymentAmountRule]]
        );

        if ($validator->fails()) {
            throw PaymentConfigurationException::invalidAmount();
        }

        return $resolvedAmount;
    }
}
