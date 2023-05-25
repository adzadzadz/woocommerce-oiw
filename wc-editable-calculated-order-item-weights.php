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
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

// Define constants for plugin paths and URLs
define('WCECOIW_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WCECOIW_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
// require_once WCECOIW_PLUGIN_DIR . 'includes/class-wcec-oiw-product.php';
require_once WCECOIW_PLUGIN_DIR . 'includes/class-wcec-oiw-order.php';
// require_once WCECOIW_PLUGIN_DIR . 'includes/class-wcec-oiw-email.php';


// Initialize the plugin classes
// WCEC_OIW_Product::init();
WCEC_OIW_Order::init();
// WCEC_OIW_Email::init();


// error_log("ADZ TEST");


function enqueue_admin_scripts($hook) {
    if('post.php' == $hook){
        global $post;
        if($post->post_type == 'shop_order'){
            wp_enqueue_script('wcec-oiw-admin-js', WCECOIW_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), '1.2', true);
            wp_localize_script('wcec-oiw-admin-js', 'wcec_ajax', array('ajax_url' => admin_url('admin-ajax.php')));
        }
    }
}

add_action('admin_enqueue_scripts', 'enqueue_admin_scripts');


function wcec_update_order_item() {
    $item_id = $_POST['item_id'];
    // $weight = floatval($_POST['weight']);
    // wc_update_order_item_meta($item_id, '_weight', $weight);

    if ( array_key_exists('weight', $_POST) ) {
        $weight = floatval($_POST['weight']);
        wc_update_order_item_meta($item_id, '_weight', $weight);
    }
    
    if ( array_key_exists('price_per_lb', $_POST) ) {
        $price_per_lb = floatval($_POST['price_per_lb']);
        wc_update_order_item_meta($item_id, '_price_per_lb', $price_per_lb);
    }

    wp_send_json_success('Weight updated successfully.');
    wp_die(); // always end ajax requests with wp_die() to prevent further output
}

add_action('wp_ajax_wcec_update_order_item', 'wcec_update_order_item');
add_action('wp_ajax_nopriv_wcec_update_order_item', 'wcec_update_order_item');