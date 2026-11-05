# ReleaseCart

ReleaseCart is a free WordPress plugin for selling digital downloads and software license keys from your own site. It takes card payments through Stripe, issues a license key with each sale and runs an update server, so the plugins you sell get their updates on your customers' Plugins screen.

This repository holds the source of the free plugin. To install it, use [ReleaseCart on WordPress.org](https://wordpress.org/plugins/releasecart/). Setup and usage are in the [documentation](https://drplugin.com/docs/releasecart).

## Features

- Products with pricing tiers, categories, tags and bundles
- Card checkout with Stripe Elements, so card details never reach your server
- Guest checkout
- A license key for each licensed product in an order, with an activation limit and a license length set per pricing tier
- Local and staging sites do not count toward the activation limit
- An update server: upload a release, and sites with a valid license see it on their Plugins screen
- An update client to add to the plugins you sell, and a public REST API to activate, validate and deactivate a key
- Discount codes, and automatic sales with the old price struck through
- Full and partial refunds
- A receipt email with the license keys and download links
- A customer account page with purchases, downloads and licenses
- Sales reports by day, with CSV export
- Signed webhooks for orders, refunds, licenses and new customers
- 20 blocks with matching shortcodes, five block patterns and ten Elementor widgets
- Personal data export and erasure through the WordPress privacy tools

[ReleaseCart Pro](https://drplugin.com/plugins/releasecart) is a separate paid plugin. It adds subscriptions, PayPal, EU and UK VAT on one-time sales, PDF invoices, an affiliate program and imports from other stores.

## Requirements

- WordPress 6.9 or newer
- PHP 8.1 or newer
- A Stripe account for card payments
- HTTPS for live card payments

## Build

You need Node.js 18.12 or newer.

```
npm install
npm run build
```

The build writes `assets/admin/build` and `assets/blocks/build`, which the plugin loads. Use `npm run start` to rebuild on every change.

The PHP needs no build step. Composer installs Action Scheduler into `vendor/woocommerce/action-scheduler`, and that copy is committed, so you do not need to run Composer.

## What is where

- `releasecart.php`: the plugin entry file and its autoloader
- `src/`: PHP classes, namespace `DrPlugin\WP\ReleaseCart`
- `templates/`: front-end templates, which a theme can override
- `blocks/`: the `block.json` file for each block
- `patterns/`: the starter block patterns
- `assets/admin/src`: the wp-admin app
- `assets/blocks/src`: the block editor script
- `assets/frontend`: storefront CSS and scripts
- `client/`: the update client and license verifier that you add to the plugins you sell
- `vendor/woocommerce/action-scheduler`: Action Scheduler 4.2.0, unchanged, pinned in `composer.json`

## License

ReleaseCart is licensed under the GPL, version 2 or later. See `LICENSE`. The bundled Action Scheduler is licensed under the GPL, version 3 or later.
