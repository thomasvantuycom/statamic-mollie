<?php

namespace ThomasVantuycom\StatamicMollie\Listeners;

use Statamic\Contracts\Forms\Submission;
use Statamic\Events\FormSubmitted;
use Statamic\Facades\Form;
use ThomasVantuycom\StatamicMollie\Facades\Payment;

class CreatePayment
{
    public function handle(FormSubmitted $event): void
    {
        $submission = $event->submission;
        $form = $submission->form();

        if ($form->has('payment') && is_array($form->get('payment'))) {
            $payment = Payment::createFromSubmission($submission);

            $event->submission->set('payment', [
                'id' => $payment->id,
                'description' => $payment->description,
                'amount' => [
                    'currency' => $payment->amount->currency,
                    'value' => $payment->amount->value,
                ],
                'status' => $payment->status,
            ]);
            $event->submission->setSupplement('checkout_url', $payment->getCheckoutUrl());

            Form::redirect($submission->form()->handle(), function (Submission $submission) {
                if ($submission->hasSupplement('checkout_url')) {
                    return $submission->getSupplement('checkout_url');
                }
            });
        }
    }
}
