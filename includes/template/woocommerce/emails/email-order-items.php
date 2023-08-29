<?php

/**
 * Email Order Items
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-order-items.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 3.7.0
 */

defined('ABSPATH') || exit;

$text_align  = is_rtl() ? 'right' : 'left';
$margin_side = is_rtl() ? 'left' : 'right';

foreach ($items as $item_id => $item) :
    $product       = $item->get_product();
    $sku           = '';
    $purchase_note = '';
    $image         = '';

    if (!apply_filters('woocommerce_order_item_visible', true, $item)) {
        continue;
    }

    if (is_object($product)) {
        $sku           = $product->get_sku();
        $purchase_note = $product->get_purchase_note();
        $image         = $product->get_image($image_size);
    }

    error_log(print_r($item->get_meta_data(), true));

?>
    <tr class="<?php echo esc_attr(apply_filters('woocommerce_order_item_class', 'order_item', $item, $order)); ?>">
        <td class="td" style="text-align:<?php echo esc_attr($text_align); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
            <?php

            // Show title/image etc.
            if ($show_image) {
                echo wp_kses_post(apply_filters('woocommerce_order_item_thumbnail', $image, $item));
            }

            // Product name.
            echo wp_kses_post(apply_filters('woocommerce_order_item_name', $item->get_name(), $item, false));

            // SKU.
            if ($show_sku && $sku) {
                echo wp_kses_post(' (#' . $sku . ')');
            }

            // allow other plugins to add additional product information here.
            do_action('woocommerce_order_item_meta_start', $item_id, $item, $order, $plain_text);

            wc_display_item_meta(
                $item,
                array(
                    'label_before' => '<strong class="wc-item-meta-label" style="float: ' . esc_attr($text_align) . '; margin-' . esc_attr($margin_side) . ': .25em; clear: both">',
                )
            );

            // allow other plugins to add additional product information here.
            do_action('woocommerce_order_item_meta_end', $item_id, $item, $order, $plain_text);

            ?>
        </td>
        <!-- WCEC START -->
        <td class="td" style="text-align:<?php echo esc_attr($text_align); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
            <?php if ($item->get_meta("_wcec_action_is_split_mode")) {
                $wcec_qty = $item->get_quantity();
                $wcec_split_weight_html = '';
                for ($wcec_i = 0; $wcec_i < $wcec_qty; $wcec_i++) {
                    $wcec_split_weight_html .= '<div class="">' . $item->get_meta('_wcec_weight_' . $wcec_i) . 'lbs</div>';
                }
                echo $wcec_split_weight_html;
            } elseif ($item->get_meta('_wcec_weight')) {
                echo $item->get_meta('_wcec_weight') . 'lbs';
            } else {
                // echo "1 lbs";
            }?>
        </td>

        <td class="td" style="text-align:<?php echo esc_attr($text_align); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
            <?php
            if ($item->get_meta("_wcec_action_is_split_mode")) {
                $wcec_qty = $item->get_quantity();
                $wcec_split_price_html = '';
                for ($wcec_i = 0; $wcec_i < $wcec_qty; $wcec_i++) {
                    $wcec_split_price_html .= '<div class="">$' . number_format((float)$item->get_meta('_wcec_price_per_lb_' . $wcec_i), 2, '.', '') . '</div>';
                }
                echo $wcec_split_price_html;
            } elseif ($item->get_meta('_wcec_price_per_lb')) {
                echo '$' . number_format((float)$item->get_meta('_wcec_price_per_lb'), 2, '.', '');
            } else {
                // $product_id = $item->get_parent_id();
                $variation_id = $product->get_id();
                $variation = wc_get_product($variation_id);
                $parent_product_id = $variation->get_parent_id();
                $unit_price = get_post_meta($parent_product_id, 'unit_price', true);

                if (!empty($unit_price)) {
                    echo '$' . $unit_price;
                }
            }

            ?>
        </td>
        <!-- WCEC END -->

        <td class="td" style="text-align:<?php echo esc_attr($text_align); ?>; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
            <?php
            $qty          = $item->get_quantity();
            $refunded_qty = $order->get_qty_refunded_for_item($item_id);

            if ($refunded_qty) {
                $qty_display = '<del>' . esc_html($qty) . '</del> <ins>' . esc_html($qty - ($refunded_qty * -1)) . '</ins>';
            } else {
                $qty_display = esc_html($qty);
            }
            echo wp_kses_post(apply_filters('woocommerce_email_order_item_quantity', $qty_display, $item));
            ?>
        </td>
        <td class="td" style="text-align:<?php echo esc_attr($text_align); ?>; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
            <?php echo wp_kses_post($order->get_formatted_line_subtotal($item)); ?>
        </td>
    </tr>
    <?php

    if ($show_purchase_note && $purchase_note) {
    ?>
        <tr>
            <td colspan="3" style="text-align:<?php echo esc_attr($text_align); ?>; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
                <?php
                echo wp_kses_post(wpautop(do_shortcode($purchase_note)));
                ?>
            </td>
        </tr>
    <?php
    }
    ?>

<?php endforeach; ?>