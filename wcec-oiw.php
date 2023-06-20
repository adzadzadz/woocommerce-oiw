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

function wcec_oiw_enqueue_style()
{
    wp_enqueue_style('wcec-oiw-style', WCECOIW_PLUGIN_URL . 'assets/css/style.css', [], '1.0.0', 'all');
}

add_action('admin_enqueue_scripts', 'wcec_oiw_enqueue_style');