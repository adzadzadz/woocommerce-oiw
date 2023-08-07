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
        
        // add_action('woocommerce_variation_options_pricing', [$this, 'add_variation_fields'], 10, 3);
    }

    public function enqueue_admin_scripts($hook)
    {
        if ('post.php' == $hook) {
            global $post;
            if ($post->post_type == 'product') {
                wp_enqueue_script('wcec-oiw-admin-product-variations-js', WCECOIW_PLUGIN_URL . 'assets/js/admin_product_variations.js', array('jquery'), '1.2', true);
                wp_localize_script('wcec-oiw-admin-product-variations-js', 'wcec_ajax', array('ajax_url' => admin_url('admin-ajax.php')));
            }
        }
    }

    // add variation input field for "Weight per piece", "No. of pieces", "Total weight" inside the variations tab
    public function add_variation_fields($loop, $variation_data, $variation)
    {
        woocommerce_wp_text_input(
            [
                'id' => 'wcec_weight_per_piece[' . $loop . ']',
                'wrapper_class' => 'form-row form-row-first wcec-variations-fields-wrapper',
                'class' => 'wcec_variation_weight_per_piece',
                'custom_attributes' => ['data-variation_id' => $variation->ID, 'data-variation_loop' => $loop],
                'label' => __('Weight per piece (lb)', 'woocommerce'),
                'desc_tip' => 'true',
                'description' => __('Enter the weight per piece.', 'woocommerce'),
                'value' => get_post_meta($variation->ID, 'wcec_weight_per_piece', true)
            ]
        );

        woocommerce_wp_text_input(
            [
                'id' => 'wcec_no_of_pieces[' . $loop . ']',
                'wrapper_class' => 'form-row form-row-last wcec-variations-fields-wrapper',
                'class' => 'wcec_variation_no_of_pieces',
                'custom_attributes' => ['data-variation_id' => $variation->ID, 'data-variation_loop' => $loop],
                'label' => __('No. of pieces', 'woocommerce'),
                'desc_tip' => 'true',
                'description' => __('Enter the number of pieces.', 'woocommerce'),
                'value' => get_post_meta($variation->ID, 'wcec_no_of_pieces', true)
            ]
        );

        woocommerce_wp_text_input(
            [
                'id' => 'wcec_total_weight[' . $loop . ']',
                'wrapper_class' => 'form-row form-row-first wcec-variations-fields-wrapper',
                'class' => 'wcec_variation_total_weight',
                'custom_attributes' => ['data-variation_id' => $variation->ID, 'data-variation_loop' => $loop],
                'label' => __('Total weight (lb)', 'woocommerce'),
                'desc_tip' => 'true',
                'description' => __('Enter the total weight.', 'woocommerce'),
                'value' => get_post_meta($variation->ID, 'wcec_total_weight', true)
            ]
        );
    }


    // add a checkbox after "Enabled" checkbox inside the variations tab
    public function add_variation_options($loop, $variation_data, $variation)
    {
        woocommerce_wp_checkbox(
            [
                'id' => 'wcec_sold_by_weight_option[' . $loop . ']',
                'wrapper_class' => 'wcec-variations-options-wrapper',
                'class' => 'wcec_variation_sold_by_weight_option wcec-toggle wcec-toggle-large',
                'custom_attributes' => ['data-variation_id' => $variation->ID, 'data-variation_loop' => $loop],
                'label' => __('Sold by Weight', 'woocommerce'),
                'desc_tip' => 'true',
                'description' => __('Enable this to activate the sold by weight feature.', 'woocommerce'),
                'value' => get_post_meta($variation->ID, 'wcec_sold_by_weight_option', true)
            ]
        );
    }

    // save custom variation option data
    public function save_custom_field_variations($variation_id, $i)
    {
        $checkbox = $_POST['wcec_sold_by_weight_option'][$i];
        if (isset($checkbox)) {
            update_post_meta($variation_id, 'wcec_sold_by_weight_option', 'yes');
        } else {
            update_post_meta($variation_id, 'wcec_sold_by_weight_option', 'no');
        }
    }
}