<?php

namespace ThomasVantuycom\StatamicMollie\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Mollie\Api\Exceptions\NotFoundException;
use Statamic\Facades\FormSubmission;
use ThomasVantuycom\StatamicMollie\Facades\Payment;

class WebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        if (! $request->filled('id')) {
            return response()->noContent(status: 200);
        }

        try {
            $payment = Payment::get($request->input('id'));
        } catch (NotFoundException) {
            return response()->noContent(status: 200);
        }

        $submissionId = $payment->metadata->submission_id ?? null;

        if (! $submissionId) {
            return response()->noContent(status: 200);
        }

        $submission = FormSubmission::find($submissionId);

        if (! $submission) {
            return response()->noContent(status: 200);
        }

        $paymentData = $submission->get('payment') ?? [];
        $paymentData['status'] = $payment->status;

        $submission->set('payment', $paymentData);
        $submission->save();

        return response()->noContent(status: 200);
    }
}
