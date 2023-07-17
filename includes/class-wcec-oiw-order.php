<?php

class WCEC_OIW_Order
{
    protected static $_instance = null;

    private $_item_cost;

    private $_is_split_mode;

    private $_is_update_price;

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

        add_action('woocommerce_before_save_order_items', [$this, 'save_custom_item_meta'], 10, 2);

        // add_action('wp_ajax_wcec_update_order_item', [$this, 'ajax_wcec_update_order_item']);
        // add_action('wp_ajax_nopriv_wcec_update_order_item', [$this, 'ajax_wcec_update_order_item']);

        // add_action('wp_ajax_wcec_update_item_action', [$this, 'ajax_wcec_update_item_action']);
        // add_action('wp_ajax_nopriv_wcec_update_item_action', [$this, 'ajax_wcec_update_item_action']);
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
        $arr[] = '_wcec_weight';
        $arr[] = '_wcec_price_per_lb';
        $arr[] = '_wcec_action_is_split_mode';
        $arr[] = '_wcec_action_update_price';

        for ($i = 0; $i < 15; $i++) {
            $arr[] = '_wcec_weight_' . $i;
            $arr[] = '_wcec_price_per_lb_' . $i;
        }

        return $arr;
    }

    public function action_woocommerce_admin_order_item_headers()
    {
        $title_weight = __('Weight (lb)', 'woocommerce');
        $title_price_per_lb = __('Price per lb ($)', 'woocommerce');
        $split_cost = __('Split Cost ($)', 'woocommerce');
        $actions = __('Actions', 'woocommerce');

        $header = <<<HTML
            <th class="sortable" data-sort="float" style="width: 10%; max-width: 200px;">$title_price_per_lb</th>
            <th class="sortable" data-sort="float" style="width: 10%; max-width: 200px;">$title_weight</th>
            <th class="sortable" data-sort="float" style="width: 10%; max-width: 200px;">$split_cost</th>
            <th class="sortable" data-sort="string-ins" style="width: 10%; max-width: 200px;">$actions</th>
        HTML;
        echo $header;
    }

    private function build_split_mode_html($qty, $_product, $item_id)
    {
        $price_per_lb = [];
        $weight = [];
        $split_cost = [];

        $edit_input_price_per_lb_html = '';
        $edit_input_weight_html = '';
        $edit_input_split_cost_html = '';
        $edit_actions_html = '';

        $view_price_per_lb_html = '';
        $view_weight_html = '';
        $view_split_cost_html = '';
        $view_actions_html = '';

        $output = '';

        $wcec_sold_by_weight_option = get_post_meta($_product->get_id(), 'wcec_sold_by_weight_option', true);

        $is_update_price_enabled = $this->_is_update_price ? '' : 'disabled';

        if ($wcec_sold_by_weight_option == true) {
            for ($i = 0; $i < $qty; $i++) {
                $price_per_lb[$i] = wc_get_order_item_meta($item_id, '_wcec_price_per_lb_' . $i, true);

                if (empty($price_per_lb[$i])) {
                    $price_per_lb[$i] = $this->_item_cost;
                }

                $edit_input_price_per_lb_html .= <<<HTML
                    <div>
                        <input 
                            type="number" 
                            class="wcec_item_price_per_lb price_per_lb-field wcec_item_price_per_lb_{$item_id} wcec_item_price_per_lb_{$item_id}_{$i}" 
                            name="_wcec_price_per_lb_{$i}[$item_id]" 
                            data-item_id="$item_id" 
                            data-qty_no="$i" 
                            value="$price_per_lb[$i]" 
                            step="any" 
                            min="0" 
                            placeholder="0" 
                            $is_update_price_enabled
                            />
                    </div>
                HTML;

                $weight[$i] = wc_get_order_item_meta($item_id, '_wcec_weight_' . $i, true);

                if (empty($weight[$i])) {
                    $weight[$i] = floatval(1);
                }

                $edit_input_weight_html .= <<<HTML
                    <div>
                        <input 
                            type="number" 
                            class="wcec_item_weight weight-field wcec_item_weight_{$item_id} wcec_item_weight_{$item_id}_{$i}" 
                            name="_wcec_weight_{$i}[{$item_id}]" 
                            data-item_id="$item_id" 
                            data-qty_no="$i" 
                            value="$weight[$i]" 
                            step="any" 
                            min="0" 
                            placeholder="0" />
                    </div>
                HTML;

                $split_cost[$i] = floatval($price_per_lb[$i]) * floatval($weight[$i]);

                $edit_input_split_cost_html .= <<<HTML
                    <div>
                        <input 
                            type="number" 
                            class="wcec_item_split_cost split_cost-field wcec_item_split_cost_{$item_id} wcec_item_split_cost_{$item_id}_{$i}" 
                            name="_wcec_item_split_cost[$item_id]" 
                            data-item_id="$item_id" 
                            data-qty_no="$i" 
                            value="$split_cost[$i]" 
                            step="any" 
                            min="0" 
                            placeholder="0" 
                            disabled />
                    </div>
                HTML;

                $view_price_per_lb_html .= <<<HTML
                    <div><span class="currency">$</span>$price_per_lb[$i]</div>
                HTML;

                $view_weight_html .= <<<HTML
                    <div>$weight[$i]</div>
                HTML;

                $view_split_cost_html .= <<<HTML
                    <div><span class="currency">$</span><span class="split_cost_value">$split_cost[$i]</span></div>
                HTML;
            }

            $view_actions_html = $this->build_item_actions_col_html($item_id)['view'];
            $edit_actions_html = $this->build_item_actions_col_html($item_id)['edit'];
        }

        $is_split_mode = wc_get_order_item_meta($item_id, '_wcec_action_is_split_mode', true);

        $is_split_mode_class = $is_split_mode ? '' : 'wcec_hidden';

        $output .= <<<HTML
            <td class="td_item_price_per_lb wcec_td_split_weight_$item_id $is_split_mode_class" width="1%">
                <div class="view">
                    $view_price_per_lb_html
                </div>  
                <div class="edit" style="display: none;">
                    $edit_input_price_per_lb_html
                </div>
            </td>
            <td class="td_item_weight wcec_td_split_weight_$item_id $is_split_mode_class" width="1%">
                <div class="view">
                    $view_weight_html
                </div>  
                <div class="edit" style="display: none;">
                    $edit_input_weight_html
                </div>
            </td>
            <td class="td_item_split_cost wcec_td_split_weight_$item_id $is_split_mode_class" width="1%">
                <div class="view">
                    $view_split_cost_html
                </div>  
                <div class="edit" style="display: none;">
                    $edit_input_split_cost_html
                </div>
            </td>
            <td class="td_item_actions" width="1%">
                <div class="view">
                    $view_actions_html
                </div>  
                <div class="edit" style="display: none;">
                    $edit_actions_html
                </div>
            </td>
        HTML;

        return $output;
    }


    private function build_item_actions_col_html($item_id)
    {
        $is_split_mode_checked = $this->_is_split_mode ? 'checked' : '';
        $is_update_price_checked = $this->_is_update_price ? 'checked' : '';

        $view_actions_html = <<<HTML
            <div>
                <label for="wcec_view_action_split_weight_{$item_id}">Split Mode</label>
                <input id="wcec_view_action_split_weight_{$item_id}" type="checkbox" $is_split_mode_checked disabled/>
            </div>
            <div>
                <label for="wcec_view_action_update_price{$item_id}">Update Price</label>
                <input id="wcec_view_action_update_price{$item_id}" type="checkbox" $is_update_price_checked disabled/>
            </div>
        HTML;

        $edit_actions_html = <<<HTML
            <div>
                <label for="wcec_action_split_weight_{$item_id}">Split Mode</label>
                <input id="wcec_action_split_weight_{$item_id}-hidden" name="_wcec_action_is_split_mode[{$item_id}]" class="wcec_action_split_weight" value="0" data-item_id="$item_id" type="hidden" $is_split_mode_checked/>
                <input id="wcec_action_split_weight_{$item_id}" name="_wcec_action_is_split_mode[{$item_id}]" class="wcec_action_split_weight" value="1" data-item_id="$item_id" type="checkbox" $is_split_mode_checked/>
            </div>
            <div>
                <label for="wcec_action_update_price_{$item_id}">Update Price</label>
                <input id="wcec_action_update_price_{$item_id}-hidden" name="_wcec_action_update_price[{$item_id}]" class="wcec_action_update_price" value="0" data-item_id="$item_id" type="hidden" $is_update_price_checked />
                <input id="wcec_action_update_price_{$item_id}" name="_wcec_action_update_price[{$item_id}]" class="wcec_action_update_price" value="1" data-item_id="$item_id" type="checkbox" $is_update_price_checked />
            </div>
        HTML;

        return [
            'view' => $view_actions_html,
            'edit' => $edit_actions_html
        ];
    }

    private function build_merged_weight_html($_product, $item_id)
    {
        $edit_input_price_per_lb_html = '';
        $edit_input_weight_html = '';
        $edit_input_split_cost_html = '';

        $view_price_per_lb_html = '';
        $view_weight_html = '';
        $view_split_cost_html = '';

        $output = '';

        $wcec_sold_by_weight_option = get_post_meta($_product->get_id(), 'wcec_sold_by_weight_option', true);

        $is_update_price_enabled = $this->_is_update_price ? '' : 'disabled';

        if ($wcec_sold_by_weight_option == true) {

            $price_per_lb = wc_get_order_item_meta($item_id, '_wcec_price_per_lb', true);

            if (empty($price_per_lb)) {
                $price_per_lb = $this->_item_cost;
            }

            $edit_input_price_per_lb_html .= <<<HTML
                <div>
                    <input 
                        type="number" 
                        class="wcec_item_price_per_lb wcec_main_item_price_per_lb price_per_lb-field wcec_main_item_price_per_lb_{$item_id}" 
                        name="_wcec_price_per_lb[$item_id]" 
                        data-item_id="$item_id" 
                        value="$price_per_lb" 
                        step="any" 
                        min="0" 
                        placeholder="0" 
                        $is_update_price_enabled
                        />
                </div>
            HTML;

            $weight = wc_get_order_item_meta($item_id, '_wcec_weight', true);

            if (empty($weight)) {
                $weight = floatval(1);
            }

            $edit_input_weight_html .= <<<HTML
                <div>
                    <input 
                        type="number" 
                        class="wcec_item_weight wcec_item_merged_weight weight-field wcec_item_merged_weight_{$item_id}" 
                        name="_wcec_weight[$item_id]"
                        data-item_id="$item_id" 
                        value="$weight" 
                        step="any" 
                        min="0" 
                        placeholder="0" />
                </div>
            HTML;

            $view_price_per_lb_html .= <<<HTML
                <div><span class="currency">$</span>$price_per_lb</div>
            HTML;

            $view_weight_html .= <<<HTML
                <div>$weight</div>
            HTML;
        }

        $is_merge_mode_class = !$this->_is_split_mode ? '' : 'wcec_hidden';

        $output .= <<<HTML
            <td class="td_item_price_per_lb wcec_td_merged_weight_$item_id $is_merge_mode_class" width="1%">
                <div class="view">
                    $view_price_per_lb_html
                </div>  
                <div class="edit" style="display: none;">
                    $edit_input_price_per_lb_html
                </div>
            </td>
            <td class="td_item_weight wcec_td_merged_weight_$item_id $is_merge_mode_class" width="1%">
                <div class="view">
                    $view_weight_html
                </div>  
                <div class="edit" style="display: none;">
                    $edit_input_weight_html
                </div>
            </td>
            <td class="td_item_split_cost wcec_td_merged_weight_$item_id $is_merge_mode_class" width="1%">
                <div class="view">
                    $view_split_cost_html
                </div>  
                <div class="edit" style="display: none;">
                    $edit_input_split_cost_html
                </div>
            </td>
        HTML;

        return $output;
    }

    // define the woocommerce_admin_order_item_values callback
    public function action_woocommerce_admin_order_item_values($_product, $item, $item_id)
    {
        $output = '';
        if (!empty($_product)) {
            $this->_is_update_price = wc_get_order_item_meta($item_id, '_wcec_action_update_price', true);
            $this->_is_split_mode = wc_get_order_item_meta($item_id, '_wcec_action_is_split_mode', true);
            $this->_item_cost = $_product->get_price();
            $qty = $item->get_quantity();

            $output .= $this->build_merged_weight_html($_product, $item_id);
            $output .= $this->build_split_mode_html($qty, $_product, $item_id);
        }

        echo $output;
    }


    // public function ajax_wcec_update_order_item()
    // {
    //     $item_id = $_POST['item_id'];

    //     if (array_key_exists('qty_split_no', $_POST)) {
    //         $qty_split_no = $_POST['qty_split_no'];
    //         $weight_meta_key = '_weight_' . $qty_split_no;
    //         $price_meta_key  = '_price_per_lb_' . $qty_split_no;
    //     } else {
    //         $weight_meta_key = '_weight';
    //         $price_meta_key  = '_price_per_lb';
    //     }

    //     if (array_key_exists('weight', $_POST)) {
    //         $weight = floatval($_POST['weight']);
    //         wc_update_order_item_meta($item_id, $weight_meta_key, $weight);
    //     }

    //     if (array_key_exists('price_per_lb', $_POST)) {
    //         $price_per_lb = floatval($_POST['price_per_lb']);
    //         wc_update_order_item_meta($item_id, $price_meta_key, $price_per_lb);
    //     }

    //     // wp_send_json_success("${weight_meta_key}");
    //     wp_send_json_success('Updated successfully.');
    //     wp_die(); // always end ajax requests with wp_die() to prevent further output
    // }

    // public function ajax_wcec_update_item_action()
    // {
    //     $item_id = $_POST['item_id'];

    //     if (array_key_exists('key', $_POST) && 'is_split' == $_POST['key']) {
    //         wc_update_order_item_meta($item_id, "_wcec_action_is_split_mode", $_POST['value']);
    //     }

    //     if (array_key_exists('key', $_POST) && 'is_update_price' == $_POST['key']) {
    //         wc_update_order_item_meta($item_id, "_wcec_action_update_price", $_POST['value']);
    //     }

    //     wp_send_json_success('Updated successfully.');
    //     wp_die();
    // }

    public function save_custom_item_meta($order_id, $items)
    {
        foreach ($items['order_item_id'] as $item_id) {
            if (isset($items['_wcec_action_is_split_mode'][$item_id]))
                error_log(print_r($items['_wcec_action_is_split_mode'][$item_id], true));
            foreach ($items as $key => $each) {
                if (strpos($key, '_wcec_') !== false && isset($each[$item_id])) {
                    $new_value = $each[$item_id];
                    wc_update_order_item_meta($item_id, $key, $new_value);
                }
            }
        }
    }
}
