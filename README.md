> [!WARNING]  
> This is an experimental project, proceed with caution

This application allows you to maintain a composer repository with all your premium WordPress plugins. It is highly inspired by https://github.com/generoi/github-action-update-plugins, You can use the same recipes mentioned in their README file.

# Setup

-   Set package vendor name in .env `PACKAGES_VENDOR_NAME`

# Triggering package updates

```bash
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

-   Needs a `{{PACKAGE_SLUG}}_LICENSE_KEY`env variable
-   Required fields are
    -   **Slug**: You need to find this in the plugin / theme source code. E.g. 'Polylang Pro'
    -   **Source url**: The url attached to your license
    -   **Endpoint url**: You need to find this in the plugin source code. E.g. 'https://polylang.pro'
    -   **Method**: GET or POST, currently only GET is supported. I have no idea if POST should be supported
    -   **Changelog extract**: Regex to extract latest release changelog, leave empty to use fallback (Isn't used anywhere right now)

# Usage

For each application, you can generate a different token.

Add the repository to your `composer.json` file (replace `privatewpackagist` with your chosen package vendor name):

```json
{
    "type": "composer",
    "url": "https://example.com/repo",
    "only": ["privatewpackagist/*"]
},
```

```
composer require privatewpackagist-plugin/polylang-pro
```

The repo is protected with basic authentication. You can create credentials in the admin/tokens screen: https://example.com/tokens

# TODO

-   Add more providers (PuC etc.)
-   Schedule the update command
-   Send notifications after new releases
-   Exhaustive testing
