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
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);

        // add_action('woocommerce_new_order_item', [$this, 'add_order_item_defaults'], 10, 3);
        add_filter('woocommerce_hidden_order_itemmeta', [$this, 'wcec_hide_order_itemmeta']);

        add_action('woocommerce_admin_order_item_headers', [$this, 'action_woocommerce_admin_order_item_headers'], 10, 0);
        add_action('woocommerce_admin_order_item_values', [$this, 'action_woocommerce_admin_order_item_values'], 10, 3);

        // add_action('woocommerce_before_save_order_items', [$this, 'save_custom_field_in_order_item_meta'], 10, 1);
        add_action('wp_ajax_wcec_update_order_item', [$this, 'ajax_wcec_update_order_item']);
        add_action('wp_ajax_nopriv_wcec_update_order_item', [$this, 'ajax_wcec_update_order_item']);


    }

    public function enqueue_admin_scripts($hook)
    {
        if ('post.php' == $hook) {
            global $post;
            if ($post->post_type == 'shop_order') {
                wp_enqueue_script('wcec-oiw-admin-js', WCECOIW_PLUGIN_URL . 'assets/js/admin_order_recalc.js', array('jquery'), '1.2', true);
                wp_localize_script('wcec-oiw-admin-js', 'wcec_ajax', array('ajax_url' => admin_url('admin-ajax.php')));
            }
        }
    }

    public function wcec_hide_order_itemmeta($arr)
    {
        $arr[] = '_weight';
        $arr[] = '_price_per_lb';

        for ($i = 0; $i < 15; $i++) {
            // Add your order item meta keys to this array
            $arr[] = '_weight_' . $i;
            $arr[] = '_price_per_lb_' . $i;
        }

        return $arr;
    }



    // public function add_order_item_defaults($item_id, $item, $order_id)
    // {
    //     $product = $item->get_product();
    //     $weight = $product->get_weight();
    //     $price_per_lb = $product->get_price();
    //     wc_add_order_item_meta($item_id, 'weight', floatval(1)); // Default weight: 1lb
    //     wc_add_order_item_meta($item_id, 'price_per_lb', floatval($price_per_lb));
    // }


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

    public function build_input_boxes($qty, $_product, $item_id)
    {
        $wcec_sold_by_weight_option = get_post_meta($_product->get_id(), 'wcec_sold_by_weight_option', true);
        
        $price_per_lb = [];
        $weight = [];

        $edit_input_price_per_lb_html = '';
        $edit_input_weight_html = '';

        $view_price_per_lb_html = "";
        $view_weight_html = "";

        $output = "";

        if ($wcec_sold_by_weight_option == true) {
            for ($i = 0; $i < $qty; $i++) {
                $price_per_lb[$i] = wc_get_order_item_meta($item_id, '_price_per_lb_' . $i, true);
                $weight[$i] = wc_get_order_item_meta($item_id, '_weight_' . $i, true);
            
                if (empty($price_per_lb[$i])) {
                    $price_per_lb[$i] = $_product->get_price();
                }

                $price_per_lb_temp = $price_per_lb[$i];
                $edit_input_price_per_lb_html .= <<<HTML
                    <div>
                        <input 
                            type="number" 
                            class="wcec_item_price_per_lb price_per_lb-field" 
                            name="item_price_per_lb[$item_id]" 
                            data-item_id="$item_id" 
                            data-qty_no="$i" 
                            value="$price_per_lb_temp" 
                            step="any" 
                            min="0" 
                            placeholder="0" />
                    </div>
                HTML;
                
                if (empty($weight[$i])) {
                    $weight[$i] = floatval(1);
                }

                $weight_temp = $weight[$i];
                $edit_input_weight_html .= <<<HTML
                    <div>
                        <input 
                            type="number" 
                            class="wcec_item_weight weight-field" 
                            name="item_weight[$item_id]" 
                            data-item_id="$item_id" 
                            data-qty_no="$i" 
                            value="$weight_temp" 
                            step="any" 
                            min="0" 
                            placeholder="0" />
                    </div>
                HTML;

                $view_price_per_lb_html .= <<<HTML
                    <div>$price_per_lb_temp</div>
                HTML;
                $view_weight_html .= <<<HTML
                    <div>$weight_temp</div>
                HTML;
            }
        }

        $output .= <<<HTML
            <td class="td_item_price_per_lb" width="1%" data-sort-value="">
                <div class="view">
                    $view_price_per_lb_html
                </div>  
                <div class="edit" style="display: none;">
                    $edit_input_price_per_lb_html
                </div>
            </td>

            <td class="td_item_weight" width="1%" data-sort-value="">
                <div class="view">
                    $view_weight_html
                </div>  
                <div class="edit" style="display: none;">
                    $edit_input_weight_html
                </div>
            </td>
        HTML;

        return $output;
    }

    // define the woocommerce_admin_order_item_values callback
    public function action_woocommerce_admin_order_item_values($_product, $item, $item_id)
    {
        $qty = $item->get_quantity();
        
        $output = $this->build_input_boxes($qty, $_product, $item_id);
        
        echo $output;
    }


    public function ajax_wcec_update_order_item()
    {
        $item_id = $_POST['item_id'];
        $qty_split_no = $_POST['qty_split_no'];

        if (array_key_exists('weight', $_POST)) {
            $weight = floatval($_POST['weight']);
            wc_update_order_item_meta($item_id, '_weight_' . $qty_split_no, $weight);
        }

        if (array_key_exists('price_per_lb', $_POST)) {
            $price_per_lb = floatval($_POST['price_per_lb']);
            wc_update_order_item_meta($item_id, '_price_per_lb_' . $qty_split_no, $price_per_lb);
        }

        wp_send_json_success('Updated successfully.');
        wp_die(); // always end ajax requests with wp_die() to prevent further output
    }




}