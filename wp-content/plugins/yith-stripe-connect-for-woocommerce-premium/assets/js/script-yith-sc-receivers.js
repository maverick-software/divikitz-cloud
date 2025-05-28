/**
 * Created by fmateo on 19/10/2017.
 */

jQuery(function ($) {

    main_init();


    function main_init() {
        $('.add_receiver_button').click(add_receiver);

        $('#yith_wcsc_receivers_panel').on('click', '.remove_receiver', function (event) {
            event.preventDefault();
            var $_data_to_remove = $('#_data_to_remove'),
                $_receiver_row = $(this).closest('.yith_wcsc_receiver_row'),
                $_id = $_receiver_row.attr('id'),
                $_id_receiver = $('#_receivers_' + $_id + '_ID').val();

            if ('new' != $_id_receiver) {
                if ($_data_to_remove.val().split(',').indexOf($_id_receiver) < 0) {
                    if (0 == $_data_to_remove.val().length) {
                        $_data_to_remove.val($_id_receiver);
                    } else {
                        $_data_to_remove.val($_data_to_remove.val() + ',' + $_id_receiver);
                    }
                }
                $_data_to_remove.val($_data_to_remove.val().replace(',,', ','));
            }
            var regular = new RegExp($_id + ',?');
            $('#_data_to_save').val($('#_data_to_save').val().replace(regular, ''));

            $_receiver_row.remove();
            refresh_position();
        });

        $('.yith_wcsc_table_receivers').sortable({
            stop: function (event, ui) {
                refresh_position();
            },
        });

        $('#yith_wcsc_receivers_panel').on('click', '._receiver_all_products_button', function (e) {
            e.preventDefault();
            var $check_input = $(this).next('._receiver_all_products');
            if (1 == $check_input.val()) {
                $check_input.attr('value', '0');
                $(this).removeClass('yith_wcsc_enabled');
            } else {
                $check_input.attr('value', '1');
                $(this).addClass('yith_wcsc_enabled');
            }

            $check_input.trigger('change');
        });

        $('.save_receivers').click(function (event) {
            var empty_finder = false;
            /*$('.finder').each(function (e) {
             empty_finder = ($(this).val().length == 0) ? true : empty_finder;
             });*/

            if (!empty_finder) {
                event.preventDefault();
                var $receivers_to_save = $('#_data_to_save').val().split(','),
                    $receivers_to_remove = $('#_data_to_remove').val().split(','),
                    $receivers = $('._receiver').serialize();
                var post_data = 'action=save_receivers_action&_receivers_to_save=' + $receivers_to_save + '&_receivers_to_remove=' + $receivers_to_remove + '&' + $receivers;

                $('#yith_wcsc_receivers_panel').block({message: null, overlayCSS: {background: "#fff", opacity: .6}});

                $.post(ajaxurl, post_data).success(function (data) {
                    $('#_data_to_save').val('');
                    $('#_data_to_remove').val('');

                    for (var i = 0; i < data.length; i++) {
                        var stripe_id_field = $('#_receivers_' + data[i].index + '_stripe_id');
                        $('#_receivers_' + data[i].index + '_receiver_ID').val(data[i].id_receiver);
                        stripe_id_field.val(data[i].stripe_id);
                        if ( data[i].status_receiver === 'connect' ) {
                            stripe_id_field.css('border', '1px solid green');
                            stripe_id_field.css('color', 'black');
                            stripe_id_field.parent().next().find('._commission').attr("src", yith_wcsc_receivers.assets_url + 'images/sc-icon-' + data[i].status_receiver  + '.svg');
                            stripe_id_field.parent().next().find('._commission').attr('title', 'Connected');
                        }
                    }
                    $('#yith_wcsc_receivers_panel').unblock();
                });
            }
        });

        $('#yith_wcsc_receivers_panel').on('change', '._receiver_all_products', function () {
            var $option_product = $(this).closest('.option-product');
            if (1 == $(this).val()) {
                $option_product.find('.select2-selection__clear').trigger('mousedown');
                $option_product.find('.select2-selection__placeholder').text('All Products');
                $option_product.find('select').prop('disabled', true);
            } else {
                $option_product.find('select').prop('disabled', false);
                $option_product.find('.select2-selection__placeholder').text('Search products');

            }
        });

        $('._receiver_all_products').trigger('change');


        $('#yith_wcsc_receivers_panel').on('change', '._receiver', function () {
            var $_data_to_save = $('#_data_to_save'),
                $_id = $(this).closest('.yith_wcsc_receiver_row').attr('id');

            if ($_data_to_save.val().split(',').indexOf($_id) < 0) {
                if (0 == $_data_to_save.val().length) {
                    $_data_to_save.val($_id);
                } else {
                    $_data_to_save.val($_data_to_save.val() + ',' + $_id);
                }
            }
            $('#_data_to_save').val($('#_data_to_save').val().replace(',,', ','));
        });

    }

    function add_receiver(event) {
        event.preventDefault();

        $('#yith_wcsc_receivers_panel').block({message: null, overlayCSS: {background: "#fff", opacity: .6}});

        var post_data =
            {
                action: 'print_receiver_row_action',
                index : $('.yith_wcsc_receiver_row').length
            };

        if (yith_wcsc_receivers.context !== undefined & yith_wcsc_receivers.product_id !== undefined) {
            post_data['context'] = yith_wcsc_receivers.context;
            post_data['product_id'] = yith_wcsc_receivers.product_id;
        }

        $.post(ajaxurl, post_data).success(function (data) {
            $('#yith_wcsc_receivers_panel').find('table').find('tbody').append(data);
            $('.wc-customer-search, .wc-product-search').trigger('wc-enhanced-select-init');


            $('#yith_wcsc_receivers_panel').unblock();
        });
    }

    function refresh_position() {
        $('._receiver_order').each(function (i) {
            $(this).val(i);
            $(this).trigger('change');
        });
    }


})
;