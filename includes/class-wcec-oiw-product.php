<?php

class WCEC_OIW_Product
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
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('woocommerce_variation_options', [$this, 'add_variation_options'], 10, 3);

        add_action('woocommerce_save_product_variation', [$this, 'save_custom_field_variations'], 10, 2);
        add_action('woocommerce_checkout_create_order_line_item', [$this, 'add_custom_field_to_order_item'], 10, 4);
        add_action('woocommerce_order_item_meta_start', [$this, 'display_custom_field_in_order'], 10, 4);

        add_filter('woocommerce_add_cart_item_data', [$this, 'add_custom_field_to_cart_item'], 10, 3);
        add_filter('woocommerce_cart_item_name', [$this, 'display_custom_field_in_cart'], 10, 3);
    }

    public function enqueue_admin_scripts($hook)
    {
        if ('post.php' == $hook) {
            global $post;
            if ($post->post_type == 'shop_order') {
                wp_enqueue_script('wcec-oiw-admin-product-variations-js', WCECOIW_PLUGIN_URL . 'assets/js/admin_product_variations.js', array('jquery'), '1.2', true);
                wp_localize_script('wcec-oiw-admin-product-variations-js', 'wcec_ajax', array('ajax_url' => admin_url('admin-ajax.php')));
            }
        }
    }

    // add a checkbox after "Enabled" checkbox inside the variations tab
    public function add_variation_options($loop, $variation_data, $variation)
    {
        woocommerce_wp_checkbox(
            array(
                'id' => 'wcec_sold_by_weight_option[' . $variation->ID . ']',
                'wrapper_class' => 'show_if_simple',
                'label' => __('Sold by Weight', 'woocommerce'),
                'desc_tip' => 'true',
                'description' => __('Enable this to activate the sold by weight feature.', 'woocommerce'),
                'value' => get_post_meta($variation->ID, 'wcec_sold_by_weight_option', true)
            )
        );
    }

    // save custom variation option data
    public function save_custom_field_variations($variation_id, $i)
    {
        $checkbox = $_POST['wcec_sold_by_weight_option'][$variation_id];
        if (isset($checkbox)) {
            update_post_meta($variation_id, 'wcec_sold_by_weight_option', 'yes');
        } else {
            update_post_meta($variation_id, 'wcec_sold_by_weight_option', 'no');
        }
    }

    // add custom variation option data to the cart item
    public function add_custom_field_to_cart_item($cart_item_data, $product_id, $variation_id)
    {
        if (isset($_POST['wcec_sold_by_weight_option'][$variation_id])) {
            $cart_item_data['wcec_sold_by_weight_option'] = wc_clean($_POST['wcec_sold_by_weight_option'][$variation_id]);
        }
        return $cart_item_data;
    }

    // add custom variation option data to the order item
    public function add_custom_field_to_order_item($item, $cart_item_key, $values, $order)
    {
        if (isset($values['wcec_sold_by_weight_option'])) {
            $item->update_meta_data('wcec_sold_by_weight_option', $values['wcec_sold_by_weight_option']);
        }
    }

    // display custom variation option data in the cart
    public function display_custom_field_in_cart($product_name, $cart_item, $cart_item_key)
    {
        if (isset($cart_item['wcec_sold_by_weight_option'])) {
            $product_name .= '<br /><div class="mcs-custom-checkbox">' . __('Sold by weight:', 'wc-editable-calculated-order-item-weights') . ' ' . $cart_item['wcec_sold_by_weight_option'] . '</div>';
        }
        return $product_name;
    }

    // display custom variation option data in the order
    public function display_custom_field_in_order($item_id, $item, $order, $plain_text)
    {
        if (isset($item['wcec_sold_by_weight_option'])) {
            echo '<br /><div class="mcs-custom-checkbox"><strong>' . __('Sold by weight:', 'wc-editable-calculated-order-item-weights') . '</strong> ' . $item['wcec_sold_by_weight_option'] . '</div>';

        }
    }

}