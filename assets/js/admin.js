jQuery(document).ready(function ($) {
    $(document.body).on('change', '.wcec_item_weight', function () {
        let wcec_item_id = $(this).data('item_id');
        let weight = $(this).val();
        console.log("saving weight...", wcec_item_id, weight);

        recalculatePricePerLb();

        function recalculatePricePerLb() {
            let price = parseFloat($(`input[name="item_price_per_lb[${wcec_item_id}]"]`).val());
            let qty = $(".quantity input").val();
            let price_per_lb = price * weight * qty;
            console.log("recalculating price per lb...", price, qty, price_per_lb);
            console.log($(`input[name="line_total[${wcec_item_id}]"]`))
            $(`input[name="line_total[${wcec_item_id}]"]`).val(price_per_lb.toFixed(2));
        }

        $.ajax({
            url: wcec_ajax.ajax_url,
            data: {
                action: 'wcec_update_order_item',
                item_id: wcec_item_id,
                weight: weight
            },
            type: 'POST'
        });
    });

    $(document.body).on('change', '.wcec_item_price_per_lb', function () {
        let item_id = $(this).data('item_id');
        let price_per_lb = $(this).val();
        console.log("saving price per lb...", item_id, price_per_lb);

        recalculatePricePerLb();

        function recalculatePricePerLb() {
            let weight = parseFloat($('.wcec_item_weight').val());
            let qty = $(".quantity input").val();
            let price_per_lb = price * weight * qty;
            console.log("recalculating price per lb...", price, qty, price_per_lb);
            console.log($('.line_total.wc_input_price'))
            $('.line_total.wc_input_price').val(price_per_lb.toFixed(2));
        }

        $.ajax({
            url: wcec_ajax.ajax_url,
            data: {
                action: 'wcec_update_order_item',
                item_id: item_id,
                price_per_lb: price_per_lb
            },
            type: 'POST'
        });
    });
});
