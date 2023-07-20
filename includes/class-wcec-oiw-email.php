<?php

class WCEC_OIW_Email
{
    protected static $_instance = null;

    public static function init()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function __construct()
    {
        // Hook into WooCommerce email templates
        // add_filter('woocommerce_email_order_items_args', [$this, 'email_order_items_args']);
        // add_action('woocommerce_email_after_order_table', [$this, 'add_weight_info_to_email'], 10, 4);
        // add_action('woocommerce_email_order_details', [$this, 'add_weight_info_to_email'], 20, 4);

        add_filter('wc_get_template', [$this, 'wcec_get_template'], 10, 5);
    }
    public function wcec_get_template($located, $template_name, $args, $template_path, $default_path) {
        $plugin_template_path = plugin_dir_path(__FILE__) . 'template/woocommerce/' . $template_name;
        error_log($plugin_template_path);
        if(file_exists($plugin_template_path)) {
            return $plugin_template_path;
        }
        return $located;
    }

    // Modify email order items args here
    public function email_order_items_args($args)
    {
        return $args;
    }

    // Add weight info to email template here
    public function add_weight_info_to_email($order, $sent_to_admin, $plain_text, $email)
    {
        if ($email->id == 'customer_processing_order') {
            $order_id = $order->get_id();
            $order = wc_get_order($order_id);
            $items = $order->get_items();
            $weight = 0;
            foreach ($items as $item) {
                $product = $item->get_product();
                $weight += $product->get_weight() * $item->get_quantity();
            }
            echo '<p><strong>Total Weight: </strong>' . $weight . ' lbs</p>';
        }
    }
}
