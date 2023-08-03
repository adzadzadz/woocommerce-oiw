<?php 

class WCEC_OIW_Cart 
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
        add_filter('woocommerce_add_cart_item_data', [$this, 'add_custom_field_to_cart_item'], 10, 3);
        add_filter('woocommerce_cart_item_name', [$this, 'display_custom_field_in_cart'], 10, 3);
    }
    
    // add custom variation option data to the cart item
    public function add_custom_field_to_cart_item($cart_item_data, $product_id, $variation_id)
    {
        if (isset($_POST['wcec_sold_by_weight_option'][$variation_id])) {
            $cart_item_data['wcec_sold_by_weight_option'] = wc_clean($_POST['wcec_sold_by_weight_option'][$variation_id]);
        }
        return $cart_item_data;
    }

    

    // display custom variation option data in the cart
    public function display_custom_field_in_cart($product_name, $cart_item, $cart_item_key)
    {
        if (isset($cart_item['wcec_sold_by_weight_option'])) {
            $product_name .= '<br /><div class="mcs-custom-checkbox">' . __('Sold by weight:', 'wc-editable-calculated-order-item-weights') . ' ' . $cart_item['wcec_sold_by_weight_option'] . '</div>';
        }
        return $product_name;
    }
}