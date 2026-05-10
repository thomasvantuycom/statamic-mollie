<?php

namespace ThomasVantuycom\StatamicMollie;

use Mollie\Api\MollieApiClient;
use Statamic\Facades\Addon;
use Statamic\Facades\Form;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;

class ServiceProvider extends AddonServiceProvider
{
    protected $vite = [
        'input' => [
            'resources/js/addon.js',
            'resources/css/addon.css',
        ],
        'publicDirectory' => 'resources/dist',
    ];

    public function register()
    {
        $this->app->singleton(MollieApiClient::class, function () {
            return (new MollieApiClient)
                ->setApiKey(config('statamic.mollie.key'))
                ->addVersionString('Statamic/'.Statamic::version())
                ->addVersionString('StatamicMollie/'.Addon::get('thomasvantuycom/statamic-mollie')->version());
        });
    }

    public function bootAddon()
    {
        $this
            ->bootConfig()
            ->bootFormConfigFields();
    }

    protected function bootConfig()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/mollie.php', 'statamic.mollie');

        $this->publishes([
            __DIR__.'/../config/mollie.php' => config_path('statamic/mollie.php'),
        ], 'mollie-config');

        return $this;
    }

    protected function bootFormConfigFields()
    {
        Form::appendConfigFields('*', __('Payment'), [
            'payment' => [
                'type' => 'payment_config',
                'full_width_setting' => true,
                'hide_display' => true,
                'fields' => [
                    [
                        'handle' => 'enabled',
                        'field' => [
                            'type' => 'toggle',
                            'display' => __('Process Payment'),
                            'instructions' => __('mollie::messages.form_configure_payment_enabled_instructions'),
                        ],
                    ],
                    [
                        'handle' => 'description',
                        'field' => [
                            'type' => 'text',
                            'display' => __('Description'),
                            'instructions' => __('mollie::messages.form_configure_payment_description_instructions'),
                            'if' => [
                                'enabled' => true,
                            ],
                            'validate' => ['sometimes', 'required'],
                        ],
                    ],
                    [
                        'handle' => 'amount',
                        'field' => [
                            'type' => 'payment_amount',
                            'display' => __('Amount'),
                            'instructions' => __('mollie::messages.form_configure_payment_amount_instructions'),
                            'required' => true,
                            'if' => [
                                'enabled' => true,
                            ],
                            'validate' => ['sometimes', 'required'],
                        ],
                    ],
                ],
            ],
        ]);
    }
}
