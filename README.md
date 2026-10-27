# WooCommerce Editable Calculated Order Item Weights

A WordPress plugin for managing WooCommerce product variations sold by weight. Store staff can record actual weights on an order, adjust the price per pound, and recalculate line-item totals before saving.

The plugin calculates **order-item prices**. It does not implement shipping-rate calculations or update the product's shipping weight.

## Features

- **Sold by Weight** option on individual product variations.
- **Merged weight:** enter one total weight and price per pound for an order line.
- **Split Mode:** enter a separate weight and price per pound for each unit in an order line.
- **Update Price:** unlock price-per-pound fields for editing.
- Custom HTML order-email tables with weight and price-per-pound columns.
- Optional delivery-date output through the `wpo_wcpdf_after_order_data` hook when an order has `jckwds_timestamp` metadata.

## Requirements and compatibility

- A WordPress installation with WooCommerce installed and active.
- Variable products with **Sold by Weight** enabled on the relevant variations.
- The classic WooCommerce order editor. The order script loads on `post.php` for the `shop_order` post type; HPOS order-screen support is not implemented.

The plugin header declares version **1.0.0**, WooCommerce **3.0.0** as the minimum, and **5.9.0** as the last tested version. These are recorded metadata, not confirmation of compatibility with newer releases. No minimum WordPress or PHP version is declared.

## Installation

1. Copy this repository into `wp-content/plugins/woocommerce-oiw/`, keeping `wcec-oiw.php` at the root of that directory. Alternatively, upload a ZIP containing that plugin directory through **Plugins > Add New > Upload Plugin**.
2. Activate WooCommerce, then activate **WooCommerce Editable Calculated Order Item Weights** from **Plugins**.
3. Configure the product variations as described below.

There is no Composer or npm build step. The PHP, JavaScript, and CSS files are loaded directly.

## Configure a product

1. Open a variable product in the WordPress admin area.
2. Under **Product data > Variations**, expand a variation.
3. Enable **Sold by Weight**. The variation's price labels change to **Price per lb ($)** and **Sale price per lb ($)**.
4. Set the variation price and save the changes.

For order editing, the initial price per pound comes from the parent product's `unit_price` custom field when it has a non-empty value. Otherwise, it falls back to the variation's current price. This plugin does not provide an editor for `unit_price`.

## Edit order weights and prices

1. Open an editable order containing a variation marked **Sold by Weight** in the classic order editor.
2. Use the order item's edit control to show its weight, price, and action fields.
3. Leave **Split Mode** off to enter one total weight for the line, or enable it to enter a weight for each unit in the line's quantity.
4. Enable **Update Price** if you need to edit the price per pound.
5. Change a weight or price field to recalculate the line total, then save the order items using WooCommerce's save control.
6. Review the saved line total and use WooCommerce's order-total recalculation control as needed for taxes and totals.

Calculations run in the browser when fields change. Custom weights, prices, and mode flags are persisted when WooCommerce saves the order items.

| Mode | Calculation | Example |
| --- | --- | --- |
| Merged weight | Total line weight × price per pound | 3.5 lb × $12 = $42.00 |
| Split Mode | Sum of each unit's weight × its price per pound | (1.2 lb × $12) + (0.8 lb × $12) = $24.00 |

Merged mode does not multiply by quantity again: enter the weight for the whole line. Split Mode creates one set of fields per unit of quantity. Weight fields initially display `1` when no non-zero weight is stored.

## Implementation notes and limitations

- **Units and currency:** custom labels and email output use pounds (`lb`/`lbs`) and dollar signs (`$`) directly, independently of WooCommerce's configured units and currency.
- **Email overrides:** the plugin supplies `emails/email-order-details.php` and `emails/email-order-items.php` through `wc_get_template`. These overrides apply whenever those templates are requested, including orders without weight-based items, and take precedence over the previously resolved template. Both are based on WooCommerce template version `3.7.0`.
- **Cart metadata:** the cart handler only copies `wcec_sold_by_weight_option` when it is submitted in the add-to-cart request for the variation. The saved admin checkbox alone does not populate that cart value, and this repository does not add a storefront input for it.
- **Admin discount display:** the `woocommerce_admin_discount_totals` callback contains a fixed `-10.00` value labelled **Price Adjustments**. It is placeholder logic, not a calculated adjustment.
- **Debug logging:** the HTML email item template writes order-item metadata to the PHP error log when rendered.

## Repository structure

```text
wcec-oiw.php                         Plugin metadata, includes, and initialization
includes/
  class-wcec-oiw-product.php         Variation option and price labels
  class-wcec-oiw-order.php           Order fields, metadata, and integration hooks
  class-wcec-oiw-cart.php            Cart metadata handling and display
  class-wcec-oiw-email.php           Email template selection
  template/woocommerce/emails/       Custom HTML email templates
assets/
  js/admin_product_variations.js    Variation price-label changes
  js/admin_order_recalc.js           Weight and price calculations
  css/style.css                     Admin styles
```

## Manual verification

This repository does not include an automated test suite. On a test WordPress installation:

1. Enable **Sold by Weight** for a variation, save, and confirm the option persists after reloading.
2. Add that variation to an editable order and verify the merged-weight example above.
3. Set the line quantity to two, save and reload, then verify the Split Mode example.
4. Enable **Update Price**, change a rate, save, and reload to check that the rate, weight, mode, and line total persist.
5. Preview an HTML order email and check the weight, price-per-pound, quantity, and totals columns for both weight-based and ordinary items.

## License

The plugin header declares **GPL-2.0-or-later** (`GPL-2.0+`).
