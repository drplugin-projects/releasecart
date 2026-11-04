# ReleaseCart

ReleaseCart is a WordPress plugin for selling plugins, themes and other digital products from your own site. It takes card payments through Stripe, issues license keys and runs an update server for the plugins you sell.

This repository holds the source of the free plugin. To install it, use [ReleaseCart on WordPress.org](https://wordpress.org/plugins/releasecart/). Setup and usage are in the [documentation](https://drplugin.com/docs/releasecart).

## Build

You need Node.js 18.12 or newer.

```
npm install
npm run build
```

The build writes `assets/admin/build` and `assets/blocks/build`, which the plugin loads. Use `npm run start` to rebuild on every change.

The PHP needs no build step. Composer installs Action Scheduler into `vendor/woocommerce/action-scheduler`, and that copy is committed, so you do not need to run Composer. The PHP runs on WordPress 6.9 or newer and PHP 8.1 or newer.

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
