<?php

/**
 * ChatGPT (Adding header checkbox option to variations): 
 * Please note that this code will add the checkbox to each variation, but it will not display in the header of the variations. 
 * To add a field to the variations header, you would need to modify the WooCommerce template files, 
 * which is not generally recommended as it can lead to issues with future WooCommerce updates.
 */
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
        // add_action('woocommerce_product_after_variable_attributes', [$this, 'wcec_add_custom_field_to_variations'], 10, 3);
        // add_action('woocommerce_save_product_variation', [$this, 'wcec_save_custom_field_variations'], 10, 2);
        // add_filter('woocommerce_available_variation', [$this, 'wcec_add_custom_field_variation_data'], 10, 3);
        add_action('woocommerce_variation_options', [$this, 'add_variation_options'], 10, 3);
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
                'description' => __('Enable this to add the weight of the product.', 'woocommerce'),
                'value' => get_post_meta($variation->ID, 'wcec_sold_by_weight_option', true)
            )
        );
    }





    // Display Checkbox
    // public function wcec_add_custom_field_to_variations($loop, $variation_data, $variation)
    // {
    //     woocommerce_wp_checkbox(
    //         array(
    //             'id' => 'custom_checkbox[' . $variation->ID . ']',
    //             'label' => __('Custom Checkbox', 'woocommerce'),
    //             'description' => __('Check me!', 'woocommerce'),
    //             'value' => get_post_meta($variation->ID, 'custom_checkbox', true),
    //         )
    //     );
    // }


    // // Save Checkbox Value
    public function wcec_save_custom_field_variations($variation_id, $i)
    {
        $checkbox = $_POST['custom_checkbox'][$variation_id];
        if (isset($checkbox)) {
            update_post_meta($variation_id, 'custom_checkbox', 'yes');
        } else {
            update_post_meta($variation_id, 'custom_checkbox', 'no');
        }
    }


    // Make Checkbox Value available in frontend
    // public function wcec_add_custom_field_variation_data($data, $product, $variation)
    // {
    //     $custom_checkbox = get_post_meta($variation->get_id(), 'custom_checkbox', true);
    //     if ($custom_checkbox == 'yes') {
    //         $data['custom_checkbox'] = $custom_checkbox;
    //     }
    //     return $data;
    // }



}