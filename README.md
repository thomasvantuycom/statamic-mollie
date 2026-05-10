# Mollie addon for Statamic

This addon connects Statamic forms to Mollie so you can create payments from form submissions, send visitors to checkout, and track payment status on the stored submission.

## Requirements

- Statamic 6.0.0 or above
- A Mollie account and API key
- A publicly reachable URL for Mollie webhooks

## Installation

Install the addon with Composer:

```bash
composer require thomasvantuycom/statamic-mollie
```

Add your Mollie API key to your environment:

```dotenv
MOLLIE_API_KEY=test_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
```

## Configuration

Publish the config file if you want:

```bash
php artisan vendor:publish --tag=mollie-config
```

You can configure:

- `key`: the Mollie API key
- `currencies`: the currencies editors may choose from in the control panel
- `default_currency`: the default selected currency

## Form Setup

After installation, forms get a `Payment` config section in the control panel.

To enable payments for a form:

1. Open the form in Statamic.
2. Enable `Process Payment`.
3. Set a payment description.
4. Set an amount and currency.

When a form has payments enabled:

- the visitor submits the form
- the addon creates a Mollie payment
- the visitor is redirected to Mollie checkout
- the submission stores payment metadata
- Mollie calls the webhook
- the addon updates the stored payment status

## Amount Values

The payment amount can be either:

- a fixed amount, like `10.00`
- an Antlers expression, like `{{ amount }}`

Antlers expressions are evaluated against the submitted form data at runtime.

Examples:

```yaml
amount:
  currency: EUR
  value: "10.00"
```

```yaml
amount:
  currency: EUR
  value: "{{ amount }}"
```

The resolved value must still be a valid positive Mollie amount for the selected currency.

Examples:

- `EUR` expects values like `12.34`
- `JPY` expects values like `1200`

If the expression resolves to an invalid value like `free`, payment creation will fail.

## Frontend Example

Example Statamic form:

```antlers
{{ form:contact }}
    <label for="name">Name</label>
    <input id="name" type="text" name="name" value="{{ old:name }}" />

    <label for="amount">Amount</label>
    <input id="amount" type="text" name="amount" value="{{ old:amount }}" />

    <input type="hidden" name="_redirect" value="/thanks" />
    <input type="hidden" name="_error_redirect" value="/payment-error" />

    <button type="submit">Pay</button>
{{ /form:contact }}
```

If the form's payment config uses `{{ amount }}`, the submitted `amount` field will be used to create the Mollie payment.

## Stored Submission Data

The addon stores payment metadata on the submission in a `payment` field similar to:

```yaml
payment:
  description: Donation
  amount:
    currency: EUR
    value: "10.00"
  status: open
```

The webhook updates the `status` field as Mollie sends payment updates.

## Webhook

The addon registers a webhook endpoint at:

```text
/!/mollie/webhook
```

Mollie must be able to reach this endpoint from the public internet. During local development you will usually need a tunnel such as ngrok or Expose.

## Important Behavior

- Paid forms must store submissions. Forms with `store: false` are not supported.
