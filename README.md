> [!WARNING]  
> I haven't tagged a release yet, but the application should work just fine. If you encounter a bug, fetch the latest changes and see if this fixes the issue. if it doesn't, please [create an issue](https://github.com/tombroucke/privatewpackagist/issues/new)\
> As the application is still in an early stage, I am open to suggestions and improvements. [start a discussing](https://github.com/tombroucke/privatewpackagist/discussions/new/choose)

This application allows you to maintain a composer repository with all your premium WordPress plugins. It is highly inspired by https://github.com/generoi/github-action-update-plugins, You can use the same recipes mentioned in their README file.

# Installation

1. **Clone this repo**

```
git clone git@github.com:tombroucke/privatewpackagist.git
```

2. **Install dependencies**

```
composer install --optimize-autoloader --no-dev
```

3. **Create .env**\
   Duplicate .env.example to .env & set `APP_NAME`, `APP_URL`, `PACKAGES_VENDOR_NAME` and database credentials

4. **Generate encryption key**

```
php artisan key:generate
```

5. **Run migrations**

```
php artisan migrate
```

6.  **Add a filament user**

```
php artisan make:filament-user
```

# Setup

-   2FA is required, you will be prompted to set this up after the first login

# Schedule package updates

To schedule package updates (every 6 hours), you need to add this cron job:

```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

# Manually triggering package updates

```bash
php artisan app:update-package package-slug
php artisan app:update-packages
```

# Packages

## WPML packages

-   Needs a `WPML_LICENSE_KEY` and a `WPML_USER_ID` env variable
-   Required field is WPML **Slug**: sitepress-multilingual-cms, acfml, woocommerce-multilingual etc.

## ACF package

-   Needs a `ACF_LICENSE_KEY` env variable

## Woocommerce packages

-   Needs a `WOOCOMMERCE_ACCESS_TOKEN` and a `WOOCOMMERCE_ACCESS_TOKEN_SECRET` env variable
-   Required field is Woocommerce **Slug**: woocommerce-eu-vat-number, woocommerce-product-filters etc.

## EDD (Easy Digital Downloads) packages

-   Needs a `{{PACKAGE_SLUG}}_LICENSE_KEY`env variable E.g. `POLYLANG_PRO_LICENSE_KEY`
-   Required fields are
    -   **Slug**: You need to find this in the plugin / theme source code. E.g. 'Polylang Pro'
    -   **Source url**: The url attached to your license
    -   **Endpoint url**: You need to find this in the plugin source code (search for `edd_action`). E.g. 'https://polylang.pro'
    -   **Method**: GET or POST, currently only GET is supported. I have no idea if POST should be supported
    -   **Changelog extract**: Regex to extract latest release changelog, leave empty to use fallback (Isn't used anywhere right now)

## Gravity Forms packages

-   Needs a `GRAVITYFORMS_LICENSE_KEY` env variable
-   Required field is Gravity Forms **Slug**: gravityformsmailchimp, gravityformszapier etc.

## PuC (YahnisElsts Plugin Update Checker) packages

-   Needs a `{{PACKAGE_SLUG}}_LICENSE_KEY`env variable E.g. `WOO_DISCOUNT_RULES_PRO_LICENSE_KEY`
-   Required fields are
    -   **Slug**: You need to find this in the plugin / theme source code. E.g. 'discount-rules-v2-pro'
    -   **Source url**: The url attached to your license
    -   **Metadata url**: This is a little tricky to find (search for `Puc_v4_Factory::buildUpdateChecker`). E.g. 'https://my.flycart.org?wpaction=updatecheck&wpslug=discount-rules-v2-pro&dlid=${{ WOO_DISCOUNT_RULES_PRO_LICENSE_KEY }}'

## WP Rocket packages

-   Needs a `WP_ROCKET_EMAIL`, `WP_ROCKET_KEY`, `WP_ROCKET_URL` environment variable.

## Direct packages

Packages with direct links E.g. Revolution Slider, WPBakery Visual Composer

-   You can add environment variable in the name. Note that the variable needs to be prefixed with the plugin name

## Manual packages

For plugins that are not (yet) supported, you can add a package and choose a the Manual updater. You can create a release manually and upload your plugin file.

# Usage

https://example.com/repo/packages.json and all the packages are protected with basic authentication. You can create credentials in the admin/tokens screen: https://example.com/tokens\
You should generate a different token for each application, so you can revoke the tokens later on.

Add the repository to your `composer.json` file (replace `privatewpackagist` with your chosen package vendor name):

```json
{
    "type": "composer",
    "url": "https://example.com/repo",
    "only": ["privatewpackagist-plugin/*", "privatewpackagist-theme/*"]
}
```

```
composer require privatewpackagist-plugin/advanced-custom-fields-pro
```

# Package configurations

## ACF

<details>
<summary>Advanced Custom Fields Pro</summary>

**type:** acf\
**slug:** advanced-custom-fields-pro

</details>

## Admin Columns Pro

<details>
<summary>Admin Columns Pro</summary>

**type:** admin_columns_pro\
**slug:** admin-columns-pro

```
{
	slug: admin-columns-pro
}
```

</details>
<details>
<summary>Addon - Advanced Custom Fields</summary>

**type:** admin_columns_pro\
**slug:** ac-addon-acf

```
{
	slug: ac-addon-acf
}
```

</details>
<details>
<summary>Addon - BuddyPress</summary>

**type:** admin_columns_pro\
**slug:** ac-addon-buddypress

```
{
	slug: ac-addon-buddypress
}
```

</details>
<details>
<summary>Addon - Events Calendar</summary>

**type:** admin_columns_pro\
**slug:** ac-addon-events-calendar

```
{
	slug: ac-addon-events-calendar
}
```

</details>
<details>
<summary>Addon - Gravity Forms</summary>

**type:** admin_columns_pro\
**slug:** ac-addon-gravityforms

```
{
	slug: ac-addon-gravityforms
}
```

</details>
<details>
<summary>Addon - JetEngine</summary>

**type:** admin_columns_pro\
**slug:** ac-addon-jetengine

```
{
	slug: ac-addon-jetengine
}
```

</details>
<details>
<summary>Addon - Meta Box</summary>

**type:** admin_columns_pro\
**slug:** ac-addon-metabox

```
{
	slug: ac-addon-metabox
}
```

</details>
<details>
<summary>Addon - Ninja Forms</summary>

**type:** admin_columns_pro\
**slug:** ac-addon-ninjaforms

```
{
	slug: ac-addon-ninjaforms
}
```

</details>
<details>
<summary>Addon - Pods</summary>

**type:** admin_columns_pro\
**slug:** ac-addon-pods

```
{
	slug: ac-addon-pods
}
```

</details>
<details>
<summary>Addon - Toolset Types</summary>

**type:** admin_columns_pro\
**slug:** ac-addon-types

```
{
	slug: ac-addon-types
}
```

</details>
<details>
<summary>Addon - Woocommerce</summary>

**type:** admin_columns_pro\
**slug:** ac-addon-woocommerce

```
{
	slug: ac-addon-woocommerce
}
```

</details>
<details>
<summary>Addon - Yoast SEO</summary>

**type:** admin_columns_pro\
**slug:** ac-addon-yoast-seo

```
{
	slug: ac-addon-yoast-seo
}
```

</details>

## Direct

<details>
<summary>Revolution slider</summary>

**type:** direct\
**slug:** revslider

```
{
	url: https://updates.themepunch-ext-a.tools/revslider/download.php?code=${{ REVSLIDER_LICENSE_KEY }}
}
```

</details>

<details>
<summary>WPBakery Page Builder</summary>

**type:** direct\
**slug:** js-composer

```
{
	url: https://support.wpbakery.com/updates/download-link?product=vc&url=https://example.com&key=${{ JS_COMPOSER_LICENSE_KEY }}
}
```

</details>

## EDD

<details>
<summary>Advanced Order Export For WooCommerce (Pro)</summary>

**type:** edd\
**slug:** woocommerce-order-export

```
{
	slug: Advanced Order Export For WooCommerce (Pro),
	source_url: https://example.com,
	endpoint_url: https://algolplus.com/plugins/,
	method: GET
}
```

</details>

<details>
<summary>Product Sales Report Pro for WooCommerce</summary>

**type:** edd\
**slug:** hm-product-sales-report-pro

```
{
	slug: Product Sales Report Pro for WooCommerce,
	source_url: https://example.com,
	endpoint_url: https://wpzone.co/,
	method: GET
}
```

</details>

<details>
<summary>PDF Invoices & Packing Slips for WooCommerce - Professional</summary>

**type:** edd\
**slug:** woocommerce-pdf-ips-pro

```
{
	slug: PDF Invoices & Packing Slips for WooCommerce - Professional,
	source_url: https://example.com,
	endpoint_url: https://wpovernight.com/license-api,
	method: GET
}
```

</details>

<details>
<summary>Gravity Forms Image Choices</summary>

**type:** edd\
**slug:** gf-image-choices

```
{
	slug: Gravity Forms Image Choices,
	source_url: https://example.com,
	endpoint_url: https://jetsloth.com,
	method: GET
}
```

</details>

<details>
<summary>Ultimate WooCommerce Auction Pro</summary>

**type:** edd\
**slug:** ultimate-woocommerce-auction-pro

```
{
	slug: Ultimate WooCommerce Auction Pro,
	source_url: https://example.com,
	endpoint_url: https://auctionplugin.net/,
	method: GET
}
```

</details>

<details>
<summary>WP All Import</summary>

**type:** edd\
**slug:** wp-all-import-pro

```
{
	slug: WP All Import,
	source_url: https://example.com,
	endpoint_url: https://update.wpallimport.com/check_version,
	method: GET,
	skip_license_check: true
}
```

</details>

<details>
<summary>WP All Export</summary>

**type:** edd\
**slug:** wp-all-export-pro

```
{
	slug: WP All Export,
	source_url: https://example.com,
	endpoint_url: https://update.wpallimport.com/check_version,
	method: GET,
	skip_license_check: true
}
```

</details>

<details>
<summary>WP All Export - ACF Export Add-On Pro</summary>

**type:** edd\
**slug:** wpae-acf-add-on

```
{
	slug: ACF Export Add-On Pro,
	source_url: https://example.com,
	endpoint_url: https://update.wpallimport.com/check_version,
	method: GET,
	skip_license_check: true
}
```

</details>

<details>
<summary>WP All Import - ACF Add-On</summary>

**type:** edd\
**slug:** wpai-acf-add-on

```
{
	slug: ACF Add-On,
	source_url: https://example.com,
	endpoint_url: https://update.wpallimport.com/check_version,
	method: GET,
	skip_license_check: true
}
```

</details>

<details>
<summary>WP All Import - Link Cloaking Add-On</summary>

**type:** edd\
**slug:** wpai-linkcloak-add-on

```
{
	slug: Link Cloaking Add-On,
	source_url: https://example.com,
	endpoint_url: https://update.wpallimport.com/check_version,
	method: GET,
	skip_license_check: true
}
```

</details>

<details>
<summary>WP All Import - User Import Add-On</summary>

**type:** edd\
**slug:** wpai-user-add-on

```
{
	slug: User Import Add-On,
	source_url: https://example.com,
	endpoint_url: https://update.wpallimport.com/check_version,
	method: GET,
	skip_license_check: true
}
```

</details>

<details>
<summary>WP All Import - WooCommerce Add-On</summary>

**type:** edd\
**slug:** wpai-woocommerce-add-on

```
{
	slug: WooCommerce Add-On,
	source_url: https://example.com,
	endpoint_url: https://update.wpallimport.com/check_version,
	method: GET,
	skip_license_check: true
}
```

</details>

<details>
<summary>WP All Export - WooCommerce Export Add-On Pro</summary>

**type:** edd\
**slug:** wpae-woocommerce-add-on

```
{
	slug: WooCommerce Export Add-On Pro,
	source_url: https://example.com,
	endpoint_url: https://update.wpallimport.com/check_version,
	method: GET,
	skip_license_check: true
}
```

</details>

<details>
<summary>Ninja Forms - Campaign Monitor</summary>

**type:** edd\
**slug:** ninja-forms-campaign-monitor

```
{
	slug: Campaign Monitor,
	source_url: https://example.com,
	endpoint_url: https://ninjaforms.com/update-check/,
	method: GET
}
```

</details>

<details>
<summary>Ninja Forms - Conditional Logic</summary>

**type:** edd\
**slug:** ninja-forms-conditionals

```
{
	slug: Conditional Logic,
	source_url: https://example.com,
	endpoint_url: https://ninjaforms.com/update-check/,
	method: GET
}
```

</details>

<details>
<summary>Ninja Forms - Constant Contact</summary>

**type:** edd\
**slug:** ninja-forms-constant-contact

```
{
	slug: Constant Contact,
	source_url: https://example.com,
	endpoint_url: https://ninjaforms.com/update-check/,
	method: GET
}
```

</details>

<details>
<summary>Ninja Forms - Convertkit</summary>

**type:** edd\
**slug:** ninja-forms-convertkit

```
{
	slug: Convertkit,
	source_url: https://example.com,
	endpoint_url: https://ninjaforms.com/update-check/,
	method: GET
}
```

</details>

<details>
<summary>Ninja Forms - EmailOctopus</summary>

**type:** edd\
**slug:** ninja-forms-emailoctopus

```
{
	slug: EmailOctopus,
	source_url: https://example.com,
	endpoint_url: https://ninjaforms.com/update-check/,
	method: GET
}
```

</details>

<details>
<summary>Ninja Forms - Emma</summary>

**type:** edd\
**slug:** ninja-forms-emma

```
{
	slug: Emma,
	source_url: https://example.com,
	endpoint_url: https://ninjaforms.com/update-check/,
	method: GET
}
```

</details>

<details>
<summary>Ninja Forms - File Uploads</summary>

**type:** edd\
**slug:** ninja-forms-uploads

```
{
	slug: File Uploads,
	source_url: https://example.com,
	endpoint_url: https://ninjaforms.com/update-check/,
	method: GET
}
```

</details>

<details>
<summary>Ninja Forms - Layout and Styles</summary>

**type:** edd\
**slug:** ninja-forms-style

```
{
	slug: Layout and Styles,
	source_url: https://example.com,
	endpoint_url: https://ninjaforms.com/update-check/,
	method: GET
}
```

</details>

<details>
<summary>Forgravity Advanced Permissions</summary>

**type:** edd\
**slug:** forgravity-advancedpermissions

```
{
	slug: Advanced Permissions,
	source_url: https://example.com,
	endpoint_url: https://cosmicgiant.com,
	method: GET
}
```

</details>

<details>
<summary>AffiliateWP</summary>

**type:** edd\
**slug:** affiliate-wp

```
{
	slug: AffiliateWP,
	source_url: https://example.com/,
	endpoint_url: https://affiliatewp.com,
	method: GET
}
```

</details>

<details>
<summary>WooCommerce Next Order Coupon</summary>

**type:** edd\
**slug:** woocommerce-next-order-coupon

```
{
	slug: WooCommerce Next Order Coupon,
	source_url: https://example.com,
	endpoint_url: https://wpovernight.com/license-api/,
	method: GET
}
```

</details>

<details>
<summary>Wp Rocket</summary>

**type:** wp_rocket\
**slug:** wp-rocket

</details>

## Gravity Forms

<details>
<summary>Gravityforms</summary>

**type:** gravity_forms\
**slug:** gravityforms

```
{
	slug: gravityforms
}
```

</details>

<details>
<summary>Gravityformsmailchimp</summary>

**type:** gravity_forms\
**slug:** gravityformsmailchimp

```
{
	slug: gravityformsmailchimp
}
```

</details>

<details>
<summary>Gravityformszapier</summary>

**type:** gravity_forms\
**slug:** gravityformszapier

```
{
	slug: gravityformszapier
}
```

</details>

<details>
<summary>Gravityformsrecaptcha</summary>

**type:** gravity_forms\
**slug:** gravityformsrecaptcha

```
{
	slug: gravityformsrecaptcha
}
```

</details>

## PuC

These are a little tricky, not all implementations are the same. This will need a little research to get the right metadata_url, and might need some code changes. (See `app/Listeners/FilebirdProPackageInformationListener`)

<details>
<summary>Woo Discount Rules Pro</summary>

**type:** puc\
**slug:** woo-discount-rules-pro

```
{
	slug: discount-rules-v2-pro,
	source_url: https://example.com,
	metadata_url: https://my.flycart.org?wpaction=updatecheck&wpslug=discount-rules-v2-pro&dlid=${{ WOO_DISCOUNT_RULES_PRO_LICENSE_KEY }}
}
```

</details>

<details>
<summary>Filebird Pro</summary>

**type:** puc\
**slug:** filebird-pro

```
{
	slug: filebird_pro,
	source_url: https://example.com,
	metadata_url: https://active.ninjateam.org/json/filebird.json
}
```

Filebird needs an emailaddres (the e-mailadres you purchased filebird-pro with), you need to add it in the `FILEBIRD_PRO_EMAIL` environment variable.

</details>

## Woocommerce

<details>
<summary>Woocommerce Product Filters</summary>

**type:** woocommerce\
**slug:** woocommerce-product-filters

```
{
	slug: woocommerce-product-filters
}
```

</details>

<details>
<summary>Woocommerce Eu Vat Number</summary>

**type:** woocommerce\
**slug:** woocommerce-eu-vat-number

```
{
	slug: woocommerce-eu-vat-number
}
```

</details>

<details>
<summary>Woocommerce Product Feeds</summary>

**type:** woocommerce\
**slug:** woocommerce-product-feeds

```
{
	slug: woocommerce-product-feeds
}
```

</details>

<details>
<summary>Woocommerce Subscriptions</summary>

**type:** woocommerce\
**slug:** woocommerce-subscriptions

```
{
	slug: woocommerce-subscriptions
}
```

</details>

<details>
<summary>Woocommerce Gateway Ogone</summary>

**type:** woocommerce\
**slug:** woocommerce-gateway-ogone

```
{
	slug: woocommerce-gateway-ogone
}
```

</details>

<details>
<summary>Woocommerce Product Bundles</summary>

**type:** woocommerce\
**slug:** woocommerce-product-bundles

```
{
	slug: woocommerce-product-bundles
}
```

</details>

<details>
<summary>Woocommerce Product Addons</summary>

**type:** woocommerce\
**slug:** woocommerce-product-addons

```
{
	slug: woocommerce-product-addons
}
```

</details>

<details>
<summary>Woocommerce Sequential Order Numbers Pro</summary>

**type:** woocommerce\
**slug:** woocommerce-sequential-order-numbers-pro

```
{
	slug: woocommerce-sequential-order-numbers-pro
}
```

</details>

<details>
<summary>Woocommerce Cost Of Goods</summary>

**type:** woocommerce\
**slug:** woocommerce-cost-of-goods

```
{
	slug: woocommerce-cost-of-goods
}
```

</details>

<details>
<summary>reCaptcha for WooCommerce</summary>

**type:** woocommerce\
**slug:** recaptcha-for-woocommerce

```
{
	slug: recaptcha-for-woocommerce
}
```

</details>

## WPML

<details>
<summary>WPML Multilingual CMS</summary>

**type:** wpml\
**slug:** sitepress-multilingual-cms

```
{
	slug: sitepress-multilingual-cms
}
```

</details>

<details>
<summary>Advanced Custom Fields Multilingual</summary>

**type:** wpml\
**slug:** acfml

```
{
	slug: acfml
}
```

</details>

<details>
<summary>WooCommerce Multilingual & Multicurrency</summary>

**type:** wpml\
**slug:** woocommerce-multilingual

```
{
	slug: woocommerce-multilingual
}
```

</details>

<details>
<summary>Gravity Forms Multilingual</summary>

**type:** wpml\
**slug:** gravityforms-multilingual

```
{
	slug: gravityforms-multilingual
}
```

</details>

<details>
<summary>WPML SEO</summary>

**type:** wpml\
**slug:** wp-seo-multilingual

```
{
	slug: wp-seo-multilingual
}
```

</details>

<details>
<summary>Media Translation</summary>

**type:** wpml\
**slug:** wpml-media-translation

```
{
	slug: wpml-media-translation
}
```

</details>

<details>
<summary>String Translation</summary>

**type:** wpml\
**slug:** wpml-string-translation

```
{
	slug: wpml-string-translation
}
```

</details>

# TODO

-   Calculate checksum after download instead of when packages.json is generated, and store it in the DB.
-   Add failed releases in notification email
-   Check mime-type application/zip for downloaded packages before creating release
-   Exhaustive testing
