jQuery(document).ready(function ($) {

    $(document.body).on('change', '.wcec_variation_sold_by_weight_option', function () {
        let variation_loop = $(this).data('variation_loop');
        let is_sold_by_weight = $(this).is(':checked');

        console.log(variation_loop, is_sold_by_weight);
        wcec_update_product_variation_view(variation_loop, is_sold_by_weight);
    });

    function wcec_update_product_variation_view(variation_loop, is_sold_by_weight) {
        let fields = {
            'price_input': $(`#variable_regular_price_${variation_loop}`),
            'sale_price_input': $(`#variable_sale_price${variation_loop}`)
        }
        let labels = {
            'price_label': $(`label[for='${fields.price_input.attr('id')}']`),
            'sale_price_label': $(`label[for='${fields.sale_price_input.attr('id')}']`)
        }

        if (is_sold_by_weight) {
            labels.price_label.text('Price per lb ($)');
            labels.sale_price_label.text('Sale price per lb ($)');
        } else {
            labels.price_label.text('Regular price ($)');
            labels.sale_price_label.text('Sale price ($)');
        }
    }

});