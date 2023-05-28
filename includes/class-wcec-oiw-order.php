<?php

class WCEC_OIW_Order
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
        // add_action('woocommerce_new_order_item', [$this, 'add_order_item_defaults'], 10, 3);
        add_action('add_meta_boxes', [$this, 'remove_shop_order_meta_boxe'], 90);

        add_action('woocommerce_admin_order_item_headers', [$this, 'action_woocommerce_admin_order_item_headers'], 10, 0);
        add_action('woocommerce_admin_order_item_values', [$this, 'action_woocommerce_admin_order_item_values'], 10, 3);

        // add_action('woocommerce_before_save_order_items', [$this, 'save_custom_field_in_order_item_meta'], 10, 1);


    }


    public function remove_shop_order_meta_boxe()
    {
        remove_meta_box('postcustom', 'shop_order', 'normal');
    }


    public function add_order_item_defaults($item_id, $item, $order_id)
    {
        $product = $item->get_product();
        $weight = $product->get_weight();
        $price_per_lb = $product->get_price();
        wc_add_order_item_meta($item_id, 'weight', 1); // Default weight: 1lb
        wc_add_order_item_meta($item_id, 'price_per_lb', $price_per_lb);
    }


    public function action_woocommerce_admin_order_item_headers()
    {
        $title_weight = __('Weight (lb)', 'woocommerce');
        $title_price_per_lb = __('Price per lb ($)', 'woocommerce');
        $header = <<<HTML
            <th class="item sortable" data-sort="string-ins">$title_price_per_lb</th>
            <th class="item sortable" data-sort="string-ins">$title_weight</th>
        HTML;
        echo $header;
    }

    // define the woocommerce_admin_order_item_values callback
    function action_woocommerce_admin_order_item_values($_product, $item, $item_id)
    {
        $weight = wc_get_order_item_meta($item_id, '_weight', true);
        $price_per_lb = wc_get_order_item_meta($item_id, '_price_per_lb', true);

        if (empty($price_per_lb))
            $price_per_lb = $_product->get_price();

        $value = <<<HTML
            <td class="td_item_price_per_lb" width="1%" data-sort-value="$price_per_lb">
                <div class="view">
                    $price_per_lb
                </div>  
                <div class="edit" style="display: none;">
                    <input type="number" class="wcec_item_price_per_lb price_per_lb-field" name="item_price_per_lb[$item_id]" data-item_id="$item_id" value="$price_per_lb" step="any" min="0" placeholder="0" />
                </div>
            </td>

            <td class="td_item_weight" width="1%" data-sort-value="$weight">
                <div class="view">
                    $weight
                </div>  
                <div class="edit" style="display: none;">
                    <input type="number" class="wcec_item_weight weight-field" name="item_weight[$item_id]" data-item_id="$item_id" value="$weight" step="any" min="0" placeholder="0" />
                </div>
            </td>
        HTML;
        echo $value;
    }


}