<?php
/**
 * Plugin Name: WooCommerce Editable Calculated Order Item Weights
 * Plugin URI: http://mycustomsoftware.com/
 * Description: An extension to WooCommerce products and orders for managing products sold by weight.
 * Version: 1.0.0
 * Author: MCS
 * Author URI: http://mycustomsoftware.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * WC requires at least: 3.0.0
 * WC tested up to: 5.9.0
 * Text Domain: wc-editable-calculated-order-item-weights
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

// Define constants for plugin paths and URLs
define('WCECOIW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WCECOIW_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once WCECOIW_PLUGIN_DIR . 'includes/class-wcec-oiw-product.php';
require_once WCECOIW_PLUGIN_DIR . 'includes/class-wcec-oiw-order.php';
require_once WCECOIW_PLUGIN_DIR . 'includes/class-wcec-oiw-email.php';

// Initialize the plugin classes
WCEC_OIW_Product::init();
WCEC_OIW_Order::init();
WCEC_OIW_Email::init();

// // Save product variation data
// add_action('woocommerce_save_product_variation', 'save_variation_weight', 10, 2);
// function save_variation_weight($variation_id, $i)
// {
//     if (isset($_POST['_wcec_sold_by_weight'][$variation_id])) {
//         update_post_meta($variation_id, '_wcec_sold_by_weight', wc_clean($_POST['_wcec_sold_by_weight'][$variation_id]));
//     }
// }

// // Add variation data to the cart item
// add_filter('woocommerce_add_cart_item_data', 'add_variation_weight_to_cart_item', 10, 3);
// function add_variation_weight_to_cart_item($cart_item_data, $product_id, $variation_id)
// {
//     if (isset($_POST['_wcec_sold_by_weight'][$variation_id])) {
//         $cart_item_data['wcec_sold_by_weight'] = wc_clean($_POST['_wcec_sold_by_weight'][$variation_id]);
//     }
//     return $cart_item_data;
// }

// // Add variation data to the order item
// add_action('woocommerce_checkout_create_order_line_item', 'add_variation_weight_to_order_item', 10, 4);
// function add_variation_weight_to_order_item($item, $cart_item_key, $values, $order)
// {
//     if (isset($values['wcec_sold_by_weight'])) {
//         $item->update_meta_data('wcec_sold_by_weight', $values['wcec_sold_by_weight']);
//     }
// }

// // Display variation data in the cart
// add_filter('woocommerce_cart_item_name', 'display_variation_weight_in_cart', 10, 3);
// function display_variation_weight_in_cart($product_name, $cart_item, $cart_item_key)
// {
//     if (isset($cart_item['wcec_sold_by_weight'])) {
//         $product_name .= '<br /><div class="mcs-sold-by-weight">' . __('Sold by Weight:', 'wc-editable-calculated-order-item-weights') . ' ' . $cart_item['wcec_sold_by_weight'] . '</div>';
//     }
//     return $product_name;
// }

// // Display variation data in the order
// add_action('woocommerce_order_item_meta_start', 'display_variation_weight_in_order', 10, 4);
// function display_variation_weight_in_order($item_id, $item, $order, $plain_text)
// {
//     if (isset($item['wcec_sold_by_weight'])) {
//         echo '<br /><div class="mcs-sold-by-weight"><strong>' . __('Sold by Weight:', 'wc-editable-calculated-order-item-weights') . '</strong> ' . $item['wcec_sold_by_weight'] . '</div>';

//     }
// }

/* Solution 2 */

// save custom variation option data
add_action('woocommerce_save_product_variation', 'save_custom_field_variations', 10, 2);
function save_custom_field_variations($variation_id, $i)
{
    $checkbox = $_POST['wcec_sold_by_weight_option'][$variation_id];
    if (isset($checkbox)) {
        update_post_meta($variation_id, 'wcec_sold_by_weight_option', 'yes');
    } else {
        update_post_meta($variation_id, 'wcec_sold_by_weight_option', 'no');
    }
}

// add custom variation option data to the cart item
add_filter('woocommerce_add_cart_item_data', 'add_custom_field_to_cart_item', 10, 3);
function add_custom_field_to_cart_item($cart_item_data, $product_id, $variation_id)
{
    if (isset($_POST['wcec_sold_by_weight_option'][$variation_id])) {
        $cart_item_data['wcec_sold_by_weight_option'] = wc_clean($_POST['wcec_sold_by_weight_option'][$variation_id]);
    }
    return $cart_item_data;
}

// add custom variation option data to the order item
add_action('woocommerce_checkout_create_order_line_item', 'add_custom_field_to_order_item', 10, 4);
function add_custom_field_to_order_item($item, $cart_item_key, $values, $order)
{
    if (isset($values['wcec_sold_by_weight_option'])) {
        $item->update_meta_data('wcec_sold_by_weight_option', $values['wcec_sold_by_weight_option']);
    }
}

// display custom variation option data in the cart
add_filter('woocommerce_cart_item_name', 'display_custom_field_in_cart', 10, 3);
function display_custom_field_in_cart($product_name, $cart_item, $cart_item_key)
{
    if (isset($cart_item['wcec_sold_by_weight_option'])) {
        $product_name .= '<br /><div class="mcs-custom-checkbox">' . __('Custom Checkbox:', 'wc-editable-calculated-order-item-weights') . ' ' . $cart_item['wcec_sold_by_weight_option'] . '</div>';
    }
    return $product_name;
}

// display custom variation option data in the order
add_action('woocommerce_order_item_meta_start', 'display_custom_field_in_order', 10, 4);
function display_custom_field_in_order($item_id, $item, $order, $plain_text)
{
    if (isset($item['wcec_sold_by_weight_option'])) {
        echo '<br /><div class="mcs-custom-checkbox"><strong>' . __('Custom Checkbox:', 'wc-editable-calculated-order-item-weights') . '</strong> ' . $item['wcec_sold_by_weight_option'] . '</div>';

    }
}