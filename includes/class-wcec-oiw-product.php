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
        // add_action('woocommerce_product_after_variable_attributes', [$this, 'wcec_add_custom_field_to_variations'], 10, 3);
        // add_action('woocommerce_save_product_variation', [$this, 'wcec_save_custom_field_variations'], 10, 2);
        // add_filter('woocommerce_available_variation', [$this, 'wcec_add_custom_field_variation_data'], 10, 3);
        add_action('woocommerce_variation_options', [$this, 'add_variation_options'], 10, 3);
    }
    // add a checkbox after "Enabled" checkbox inside the variations tab

    public function add_variation_options($loop, $variation_data, $variation)
    {
        woocommerce_wp_checkbox(
            array(
                'id' => '_mcs_sold_by_weight[' . $variation->ID . ']',
                'wrapper_class' => 'show_if_simple',
                'label' => __('Sold by Weight', 'woocommerce'),
                'desc_tip' => 'true',
                'description' => __('Enable this to add the weight of the product.', 'woocommerce'),
                'value' => get_post_meta($variation->ID, '_weight', true)
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
    // public function wcec_save_custom_field_variations($variation_id, $i)
    // {
    //     $checkbox = $_POST['custom_checkbox'][$variation_id];
    //     if (isset($checkbox)) {
    //         update_post_meta($variation_id, 'custom_checkbox', 'yes');
    //     } else {
    //         update_post_meta($variation_id, 'custom_checkbox', 'no');
    //     }
    // }


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