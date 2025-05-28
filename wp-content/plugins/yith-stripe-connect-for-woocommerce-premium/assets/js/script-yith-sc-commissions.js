jQuery(function ($) {

    main_init();

    function main_init() {

        //view-info
        $('#yith_wcsc_filter').on('click', '.clear_filter', function (event) {
            event.preventDefault();
            $('#yith_wcsc_filter').find('.select2-selection__clear').trigger('mousedown');
            $('#yith_wcsc_date_from, #yith_wcsc_date_to').val('');
            $(this).trigger('mousedown');
        });

        $('#yith_wcsc_commissions_panel').on('click', '.view-info', function (event) {
            event.preventDefault();
            var id_commission = $(this).data('commission');
            var $view_button = $(this);

            $view_button.block({message: null, overlayCSS: {background: "#fff", opacity: .6}});

            var action = 'load_json_commission';

            var post_data =
                {
                    action       : action,
                    id_commission: id_commission,
                };


            $.post(yith_wcsc_commissions.ajaxurl, post_data).success(function (commission) {
                $view_button.unblock();
                $('body').WCBackboneModal({
                    template: 'yith-wcsc-modal-view-commission',
                    variable: commission
                });

            })

        });

        $('body').on('click', '.pay_commission_button', function (event) {
            event.preventDefault();

            var status_receiver = $(this).data('receciver_status'),
                order_status = $(this).data('order_status'),
                day_delay = $(this).data('day_delay'),
                proceed_payment = false;

            if (status_receiver == 'disconnect') { // Check Status receiver...
                alert(yith_wcsc_commissions.message.disconnected_stripe_account);
            } else if (order_status == 'completed' | order_status == 'processing') { // Check the order status...
                if (0 < day_delay) {
                    proceed_payment = confirm(yith_wcsc_commissions.message.delay_time_confirm);
                } else {
                    proceed_payment = true;
                }
            } else {
                alert(yith_wcsc_commissions.message.cant_process_payment_order_status + ' ' + order_status);
            }

            if (proceed_payment) {
                manual_transfer(this);
            }
        });

        $('#yith_wcsc_date_from').datepicker();
        $('#yith_wcsc_date_to').datepicker();
    }

    function manual_transfer(element) {
        var $yith_wcsc_commission_view = $(element).closest('.yith-wcsc-commission-view'),
            id_commission = $yith_wcsc_commission_view.data('commission_id'),
            action = 'manual_transfer';

        $yith_wcsc_commission_view.find('.commission_status_mark, .commission_item_status, .pay_commission_button').block({
            message   : null,
            overlayCSS: {
                background: "#fff",
                opacity   : .6
            }
        });

        var post_data =
            {
                action       : action,
                id_commission: id_commission
            };
        $.post(yith_wcsc_commissions.ajaxurl, post_data).success(function (result) {
            $yith_wcsc_commission_view.find('.commission_status_mark, .commission_item_status, .pay_commission_button').unblock();

            $('.modal-close').trigger('click');

            $('#wpcontent, .site-content-contain').WCBackboneModal({
                template: 'yith-wcsc-modal-view-commission',
                variable: result
            });

        });

    }
});