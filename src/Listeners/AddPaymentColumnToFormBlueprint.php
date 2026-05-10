<?php

namespace ThomasVantuycom\StatamicMollie\Listeners;

use Statamic\Events\FormBlueprintFound;

class AddPaymentColumnToFormBlueprint
{
    public function handle(FormBlueprintFound $event): void
    {
        if ($event->form->has('payment') && $this->isOnAllowedRoute()) {
            $contents = $event->blueprint->contents();

            $contents['tabs']['sidebar'] = [
                'display' => 'Sidebar',
                'sections' => [
                    [
                        'display' => __('Payment'),
                        'fields' => [
                            [
                                'handle' => 'payment',
                                'field' => [
                                    'type' => 'payment_summary',
                                    'hide_display' => true,
                                    'fields' => [
                                        [
                                            'handle' => 'amount',
                                            'field' => [
                                                'display' => __('Amount'),
                                                'type' => 'payment_amount',
                                            ],
                                        ],
                                        [
                                            'handle' => 'status',
                                            'field' => [
                                                'display' => __('Status'),
                                                'type' => 'payment_status',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            $event->blueprint->setContents($contents);
        }
    }

    protected function isOnAllowedRoute()
    {
        if (! $route = optional(request()->route())->getName()) {
            return false;
        }

        return in_array($route, [
            'statamic.cp.forms.show',
            'statamic.cp.forms.submissions.index',
            'statamic.cp.forms.submissions.show',
            'statamic.cp.relationship.index',
        ]);
    }
}
