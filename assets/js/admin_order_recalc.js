jQuery(document).ready(function ($) {

    // trigger callback on edit order item save button click
    // $(document.body).on('click', '.wcec_edit_order_item_save', function () {
    //     let wcec_item_id = $(this).data('item_id');
    //     let is_split_mode = $(`#wcec_action_split_weight_${wcec_item_id}`).is(':checked');
    //     let weight = $(`.wcec_item_weight_${wcec_item_id}`).val();
    //     let price = $(`.wcec_item_price_per_lb_${wcec_item_id}`).val();
    //     let split_cost_input = $(`.wcec_item_split_cost_${wcec_item_id}`);
    // });

    // $(document.body).on('click', '.wc-order-add-item .save-action' , function () {
    //     e.preventDefault();
    //     console.log("Trigered");
    // });

    $(document.body).on('change', '.wcec_item_weight', function () {
        let wcec_item_id = $(this).data('item_id');
        let is_split_mode = $(`#wcec_action_split_weight_${wcec_item_id}`).is(':checked');
        let weight = $(this).val();

        // console.log(is_split_mode.is(':checked'));

        // item_data = {
        //     action: 'wcec_update_order_item',
        //     item_id: wcec_item_id,
        //     weight: weight
        // };

        // console.log(item_data)

        if (is_split_mode) {
            let qty_no = $(this).data('qty_no');
            let split_id = `${wcec_item_id}_${qty_no}`;
            let price = $(`.wcec_item_price_per_lb_${split_id}`).val();
            let split_cost_input = $(`.wcec_item_split_cost_${split_id}`);

            // Object.assign(item_data, {'qty_split_no': qty_no});

            wcec_calculate_split_cost(price, weight, split_cost_input);
            wcec_calculate_split_totals(wcec_item_id);
        } else {
            let price = $(`.wcec_main_item_price_per_lb_${wcec_item_id}`).val();
            // console.log(wcec_item_id, price, weight);
            wcec_calculate_cost(wcec_item_id, price, weight);
        }

        // console.log(item_data)

        // $.ajax({
        //     url: wcec_ajax.ajax_url,
        //     data: item_data,
        //     type: 'POST',
        //     success: function (response) {
        //         console.log(response)
        //     }
        // });

    });

    $(document.body).on('change', '.wcec_item_price_per_lb', function () {
        let wcec_item_id = $(this).data('item_id');
        let is_split_mode = $(`#wcec_action_split_weight_${wcec_item_id}`).is(':checked');
        let price = $(this).val();
        
        if (is_split_mode) {
            let qty_no = $(this).data('qty_no');
            let split_id = `${wcec_item_id}_${qty_no}`;
            let weight = parseFloat($(`.wcec_item_weight_${split_id}`).val());
            let split_cost_input = $(`.wcec_item_split_cost_${split_id}`);
            wcec_calculate_split_cost(price, weight, split_cost_input);
            wcec_calculate_split_totals(wcec_item_id);
        } else {
            let weight = parseFloat($(`.wcec_item_merged_weight_${wcec_item_id}`).val());
            wcec_calculate_cost(wcec_item_id, price, weight);
        }
        // $.ajax({
        //     url: wcec_ajax.ajax_url,
        //     data: {
        //         action: 'wcec_update_order_item',
        //         item_id: wcec_item_id,
        //         price_per_lb: price,
        //         qty_split_no: qty_no
        //     },
        //     type: 'POST'
        // });
    });

    // $(document.body).on('change', 'input[name*="order_item_qty"]', function (e) {
    // //     let parent = $(this).closest('tr');
    // //     let wcec_item_id = parent.data('order_item_id');

    // //     let price_per_lb_elem = $(`input[name="item_price_per_lb[${wcec_item_id}]"]`);
    // //     let price_per_lb = parseFloat(price_per_lb_elem.val());

    // //     let weight_elem = $(`input[name="item_weight[${wcec_item_id}]"]`);
    // //     let weight = parseFloat(weight_elem.val());
    // //     let qty = $(this).val();

    //     // if (price_per_lb_elem.length && weight_elem.length) {
    //     //     recalculatePricePerLb(wcec_item_id, price_per_lb, weight, qty);
    //     // }

    //     e.preventDefault();
    //     console.log($(this).val())
    // });

    $(document.body).on('change', '.wcec_action_split_weight', function () {
        let is_split = $(this).is(':checked');
        let wcec_item_id = $(this).data('item_id');
        // save is split to db

        if (is_split) {
            console.log('split')
            $('.wcec_td_merged_weight_' + wcec_item_id).addClass('wcec_hidden');
            $('.wcec_td_split_weight_' + wcec_item_id).removeClass('wcec_hidden');

            wcec_calculate_split_totals(wcec_item_id);
        } else {
            console.log('merge')
            $('.wcec_td_merged_weight_' + wcec_item_id).removeClass('wcec_hidden');
            $('.wcec_td_split_weight_' + wcec_item_id).addClass('wcec_hidden');

            let price = parseFloat($(`.wcec_main_item_price_per_lb_${wcec_item_id}`).val());
            let weight = parseFloat($(`.wcec_item_merged_weight_${wcec_item_id}`).val());

            console.log(price, weight)
            wcec_calculate_cost(wcec_item_id, price, weight);
        }

        // $.ajax({
        //     url: wcec_ajax.ajax_url,
        //     data: {
        //         action: 'wcec_update_item_action',
        //         item_id: wcec_item_id,
        //         key: 'is_split',
        //         value: is_split ? 1 : 0
        //     },
        //     type: 'POST'
        // });

    });

    $(document.body).on('change', '.wcec_action_update_price', function () {
        let is_update_price = $(this).is(':checked');
        let wcec_item_id = $(this).data('item_id');

        if (is_update_price) {
            $('.wcec_item_price_per_lb_' + wcec_item_id).prop('disabled', false);
            $('.wcec_main_item_price_per_lb_' + wcec_item_id).prop('disabled', false);
        } else {
            $('.wcec_item_price_per_lb_' + wcec_item_id).prop('disabled', true);
            $('.wcec_main_item_price_per_lb_' + wcec_item_id).prop('disabled', true);
        }

        // $.ajax({
        //     url: wcec_ajax.ajax_url,
        //     data: {
        //         action: 'wcec_update_item_action',
        //         item_id: wcec_item_id,
        //         key: 'is_update_price',
        //         value: is_update_price ? 1 : 0
        //     },
        //     type: 'POST'
        // });

    });

    function wcec_calculate_cost(item_id, price, weight) {
        let total_cost = price * weight;
        $(`input[name="line_total[${item_id}]"]`).val(total_cost.toFixed(2));
    }

    function wcec_calculate_split_cost(price, weight, split_cost_input) {
        let split_cost = price * weight;
        split_cost_input.val(split_cost.toFixed(2));
    }

    function wcec_calculate_split_totals(item_id) {
        let total_cost = 0;
        $(`.wcec_item_split_cost_${item_id}`).each(function () {
            let split_cost = parseFloat($(this).val());
            total_cost += split_cost;
        });
        $(`input[name="line_total[${item_id}]"]`).val(total_cost.toFixed(2));
    }

    // function recalculatePricePerLb(item_id, price, weight, qty) {
    //     let total_price = price * weight * qty;
    //     $(`input[name="line_total[${item_id}]"]`).val(total_price.toFixed(2));
    // }

});
