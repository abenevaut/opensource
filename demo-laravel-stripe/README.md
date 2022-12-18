# laravel-stripe

## Valet
```
valet link stripe.opensource
valet secure stripe.opensource
```

## Setup `.env`
```
STRIPE_PUBLIC_KEY=
STRIPE_SECRET_KEY=
```


## Setup Stripe webhooks

- https://stripe.com/docs/stripe-cli
- https://stripe.com/docs/webhooks/test

```
stripe listen --forward-to https://stripe.opensource.test/webhook/stripe
```
