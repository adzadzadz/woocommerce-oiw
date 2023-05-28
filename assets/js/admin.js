jQuery(document).ready(function ($) {
    $(document.body).on('change', '.wcec_item_weight', function () {
        let wcec_item_id = $(this).data('item_id');
        let weight = $(this).val();

        console.log("saving weight...", wcec_item_id, weight);

        let price = parseFloat($(`input[name="item_price_per_lb[${wcec_item_id}]"]`).val());
        let qty = parseFloat($(`input[name="order_item_qty[${wcec_item_id}]"]`).val());
        console.log("Price", price)
        recalculatePricePerLb(wcec_item_id, price, weight, qty);

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
        let wcec_item_id = $(this).data('item_id');
        let price_per_lb = $(this).val();

        console.log("saving price per lb...", wcec_item_id, price_per_lb);

        let weight = parseFloat($(`input[name="item_weight[${wcec_item_id}]"]`).val());
        let qty = parseFloat($(`input[name="order_item_qty[${wcec_item_id}]"]`).val());

        recalculatePricePerLb(wcec_item_id, price_per_lb, weight, qty);

        $.ajax({
            url: wcec_ajax.ajax_url,
            data: {
                action: 'wcec_update_order_item',
                item_id: wcec_item_id,
                price_per_lb: price_per_lb
            },
            type: 'POST'
        });
    });

    function recalculatePricePerLb(item_id, price, weight, qty) {
        let total_price = price * weight * qty;

        console.log("recalculating price per lb...", price, qty, weight, total_price);
        console.log($(`input[name="line_total[${item_id}]"]`))

        $(`input[name="line_total[${item_id}]"]`).val(total_price.toFixed(2));
    }

    // function getPriceElement(item_id) {
    //     return $(`input[name="item_price_per_lb[${item_id}]"]`);
    // }

    // function getWeightElement(item_id) {
    //     return $(`input[name="item_weight[${item_id}]"]`);
    // }

    // function getQtyElement(item_id) {
    //     return $(`input[name="order_item_qty[${item_id}]"]`);
    // }

});
