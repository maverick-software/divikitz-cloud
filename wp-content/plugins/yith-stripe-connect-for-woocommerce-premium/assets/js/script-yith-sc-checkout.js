/* global Stripe, yith_stripe_connect_info, woocommerce_params */

(function ($) {

    var $body = $( 'body' ),
        style = {
            base: {
                // Add your base input styles here. For example:
                backgroundColor: yith_stripe_connect_info.background_color,
                fontSize: yith_stripe_connect_info.font_size,
                color: yith_stripe_connect_info.color,
                fontFamily: yith_stripe_connect_info.font_family,
                '::placeholder': {
                    color: yith_stripe_connect_info.placeholder_color
                }
            },
        },
        stripe_opts = yith_stripe_connect_info.account_id ? { stripeAccount: yith_stripe_connect_info.account_id } : {},
        stripe = Stripe( yith_stripe_connect_info.public_key, stripe_opts ),
        elements = stripe.elements(),
        card,
        cardExpiry,
        cardCvc;

    // Init Stripe Elements fields
    function init_elements() {
        var number = $( '#yith-stripe-connect-card-number' ),
            expiry = $( '#yith-stripe-connect-card-expiry' ),
            cvc    = $( '#yith-stripe-connect-card-cvc' );

        if( number.length ){
            var placeholder = number.attr('placeholder');

            if( typeof card != 'undefined' ){
                card.destroy();
            }

            card = elements.create( 'cardNumber', { style: style, placeholder: placeholder } );

            number.replaceWith( '<div id="yith-stripe-card-number" class="yith-stripe-elements-field">' );
            card.mount( '#yith-stripe-card-number' );
        }

        if( expiry.length ) {
            var placeholder = expiry.attr('placeholder');

            if( typeof cardExpiry != 'undefined' ){
                cardExpiry.destroy();
            }

            cardExpiry = elements.create( 'cardExpiry', { style: style, placeholder: placeholder } );

            expiry.replaceWith( '<div id="yith-stripe-card-expiry" class="yith-stripe-elements-field">' );
            cardExpiry.mount( '#yith-stripe-card-expiry' );
        }

        if( cvc.length ) {
            var placeholder = cvc.attr('placeholder');

            if( typeof cardCvc != 'undefined' ){
                cardCvc.destroy();
            }

            cardCvc = elements.create( 'cardCvc', { style: style, placeholder: placeholder } );

            cvc.replaceWith( '<div id="yith-stripe-card-cvc" class="yith-stripe-elements-field">' );
            cardCvc.mount( '#yith-stripe-card-cvc' );
        }
    }

    // Form handler
    function stripeFormHandler( event ) {
        if ( $( 'input#payment_method_yith-stripe-connect' ).is( ':checked' ) && 0 === $( 'input.stripe-connect-intent' ).length ) {
            var $form =  $( 'form.checkout, form#order_review, form#add_payment_method' ),
                ccForm = $( '#wc-yith-stripe-connect-cc-form, #yith-stripe-connect-cc-form' ),
                toBlockForms = $( '.woocommerce-checkout-payment, .woocommerce-checkout-review-order-table, #add_payment_method' ),
                name_input = $('.wc-credit-card-form-card-name'),
                name = name_input.length ? name_input.val() : $('#billing_first_name').val() + ' ' + $('#billing_last_name').val(),
                billing_email_input = $('#billing_email'),
                billing_email = billing_email_input.val(),
                billing_country_input = $('#billing_country'),
                billing_country = billing_country_input.val(),
                billing_city_input = $('#billing_city:visible'),
                billing_city = billing_city_input.val(),
                billing_address_1_input = $('#billing_address_1:visible'),
                billing_address_1 = billing_address_1_input.val(),
                billing_address_2_input = $('#billing_address_2:visible'),
                billing_address_2 = billing_address_2_input.val(),
                billing_state_input = $('select#billing_state, input#billing_state:visible'),
                billing_state = billing_state_input.val(),
                billing_postal_code_input = $('#billing_postcode:visible'),
                billing_postal_code = billing_postal_code_input.val(),

                selectedCard = $( 'input[name="wc-yith-stripe-connect-payment-token"]:checked'),
                cardData;

            selectedCard = selectedCard.length && 'new' !== selectedCard.val() ? selectedCard.val() : false;

            toBlockForms.block({
                message: null,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.6
                }
            });

            var error = false,
                fields = [];

            // validate extra fields
            if (
                billing_country_input.closest('p.form-row.validate-required' ).length            && billing_country_input.length         && billing_country === ''
                || billing_city_input.closest('p.form-row.validate-required' ).length            && billing_city_input.length            && billing_city === ''
                || billing_address_1_input.closest('p.form-row.validate-required' ).length       && billing_address_1_input.length       && billing_address_1 === ''
                || billing_state_input.closest('p.form-row.validate-required' ).length           && billing_state_input.length           && billing_state === ''
                || billing_postal_code_input.closest('p.form-row.validate-required' ).length     && billing_postal_code_input.length     && billing_postal_code === ''
            ) {
                error = true;
                fields.push( 'billing.fields' );
                billing_country === ''         && billing_country_input.parents( 'p.form-row' ).addClass( 'error' );
                billing_city === ''            && billing_city_input.parents( 'p.form-row' ).addClass( 'error' );
                billing_address_1 === ''       && billing_address_1_input.parents( 'p.form-row' ).addClass( 'error' );
                billing_state === ''           && billing_state_input.parents( 'p.form-row' ).addClass( 'error' );
                billing_postal_code === ''     && billing_postal_code_input.parents( 'p.form-row' ).addClass( 'error' );

            }

            if ( error ) {
                stripeResponseHandler( {
                    error: {
                        code: 'validation',
                        fieldErrors : fields
                    }
                });

                $('fieldset#wc-yith-stripe-connect-cc-form input, fieldset#wc-yith-stripe-connect-cc-form select, fieldset#yith-stripe-connect-cc-form input, fieldset#yith-stripe-connect-cc-form select').one( 'keydown', function() {
                    $(this).closest('p.form-row.error').removeClass('error');
                });

                $(document).trigger( 'yith-stripe-connect-card-error' );
            }

            // go to payment
            else {
                if( ! selectedCard ){
                    cardData = filter_empty_attributes( {
                        billing_details: {
                            name: name,
                            address: {
                                line1  : billing_address_1,
                                line2  : billing_address_2,
                                city   : billing_city,
                                state  : billing_state,
                                country: billing_country,
                                postal_code: billing_postal_code
                            },
                            email: billing_email
                        }
                    } );

                    stripe.createPaymentMethod( 'card', card, cardData ).then( function( result ){
                        if (result.error) {
                            stripeResponseHandler(result);
                        } else {
                            ccForm.append('<input type="hidden" class="stripe-connect-intent" name="stripe_connect_intent" value=""/>');
                            ccForm.append('<input type="hidden" class="stripe-connect-payment-method" name="stripe_connect_payment_method" value="' + result.paymentMethod.id + '"/>');

                            toBlockForms.unblock();
                            $form.submit();
                        }
                    } )
                }
                else{
                    ccForm.append('<input type="hidden" class="stripe-connect-intent" name="stripe_connect_intent" value=""/>');
                    toBlockForms.unblock();
                    $form.submit();
                }

            }

            // Prevent the form from submitting
            return false;
        }

        return event;
    }

    // init add payment method
    function addMethodHandler( event ){
        if ( $( 'input#payment_method_yith-stripe-connect' ).is( ':checked' ) && 0 === $( 'input.stripe-connect-intent' ).length ) {
            var ccForm = $('#wc-yith-stripe-connect-cc-form, #yith-stripe-connect-cc-form'),
                $form = $('form#add_payment_method'),
                toBlockForms = $('#add_payment_method'),
                nameInput = $('.wc-credit-card-form-card-name'),
                billing_email = $('#billing_email'),
                billing_country_input = $('#billing_country'),
                billing_city_input = $('#billing_city:visible'),
                billing_address_1_input = $('#billing_address_1:visible'),
                billing_address_2_input = $('#billing_address_2:visible'),
                billing_state_input = $('select#billing_state:visible, input#billing_state:visible'),
                billing_postal_code_input = $('#billing_postcode:visible'),
                cardData = filter_empty_attributes({
                    payment_method_data: {
                        billing_details: {
                            name   : nameInput.length ? nameInput.val() : $('#billing_first_name').val() + ' ' + $('#billing_last_name').val(),
                            address: {
                                line1  : billing_address_1_input.length ? billing_address_1_input.val() : '',
                                line2  : billing_address_2_input.length ? billing_address_2_input.val() : '',
                                city   : billing_city_input.length ? billing_city_input.val() : '',
                                state  : billing_state_input.length ? billing_state_input.val() : '',
                                country: billing_country_input.length ? billing_country_input.val() : '',
                                postal_code: billing_postal_code_input.length ? billing_postal_code_input.val() : ''

                            },
                            email  : billing_email.length ? billing_email.val() : ''
                        }
                    },
                    save_payment_method: true
                }),
                intent_id,
                intent_secret;

            toBlockForms.block({
                message   : null,
                overlayCSS: {
                    background: '#fff',
                    opacity   : 0.6
                }
            });

            update_intent().then(function (data) {
                if (typeof data.res != 'undefined') {
                    if (!data.res && typeof data.error != 'undefined') {
                        stripeResponseHandler(data);
                        return false;
                    }
                }

                intent_id = data.intent_id;
                intent_secret = data.intent_secret;

                stripe.handleCardSetup(intent_secret, card, cardData).then(function (result) {
                    if (result.error) {
                        stripeResponseHandler(result);
                    } else {
                        intent_id = typeof result.paymentIntent != 'undefined' ? result.paymentIntent.id : result.setupIntent.id;

                        ccForm.append('<input type="hidden" class="stripe-connect-intent" name="stripe_connect_intent" value="' + intent_id + '"/>');
                        toBlockForms.unblock();
                        $form.submit();
                    }
                });
            });

            return false;
        }

        return event;
    }

    // handle hash change
    function on_hash_change() {
        var partials = window.location.hash.match( /^#?yith-stripe-connect-confirm-pi-([^:]+):(.+)$/ );

        if ( ! partials || 3 > partials.length ) {
            return;
        }
        const [ , intentClientSecret, redirectURL ] = partials;
        // Cleanup the URL
        window.location.hash = '';
        open_intent_modal( intentClientSecret, redirectURL );
    }

    // manual confirmation for payment intent
    function open_intent_modal( secret, redirectURL ){
            var $form =  $( 'form.checkout, form#order_review' ),
                handler = secret.indexOf( 'seti' ) < 0 ? 'handleCardAction' : 'handleCardSetup';

            stripe[handler]( secret ).then( function( result ){
                if ( result.error ) {
                    stripeResponseHandler( result )
                }
                else {
                    window.location = redirectURL;
                }
            } ).catch( function( error ) {
                console.log(error);
            } );
        }

    // Handle Stripe response
    function stripeResponseHandler( response ) {
        var $form  = $( 'form.checkout, form#order_review, form#add_payment_method' ),
            ccForm = $( '#wc-yith-stripe-connect-cc-form, #yith-stripe-connect-cc-form' ),
            toBlockForms = $( '.woocommerce-checkout-payment, .woocommerce-checkout-review-order-table, #add_payment_method' ),
            intent_id;

        if ( response.error ) {
            // Remove previous errors
            $( '.woocommerce-error, .stripe-token', ccForm ).remove();

            // Show the errors on the form
            if ( response.error.message ) {
                ccForm.prepend( '<ul class="woocommerce-error">' + response.error.message + '</ul>' );
            }
            else if ( 'validation' === response.error.code ) {
                var fieldErrors = response.error.fieldErrors,
                    fieldErrorsLength = fieldErrors.length,
                    errorList = '';

                for ( var i = 0; i < fieldErrorsLength; i++ ) {
                    errorList += '<li>' + yith_stripe_connect_info[ fieldErrors[i] ] + '</li>';
                }

                ccForm.prepend( '<ul class="woocommerce-error">' + errorList + '</ul>' );
            }

            $('html, body').animate( { scrollTop: $( '.woocommerce-error', ccForm ).offset().top } );

        }

        $form.removeClass('processing').unblock();
        toBlockForms.unblock();
    }

    // Remove token from DOM
    function remove_token(){
        $( '.stripe-connect-intent' ).remove();
    }

    // Update paymentIntent
    function update_intent( token ){
        var data = [];

        if( yith_stripe_connect_info.is_checkout && ! yith_stripe_connect_info.order ){
            data = $( 'form.checkout' ).serializeArray();
        }

        return $.ajax( {
            data: pushRecursive( data, {
                action: 'yith_stripe_connect_refresh_intent',
                yith_stripe_connect_refresh_intent: yith_stripe_connect_info.refresh_intent,
                selected_token: token,
                is_checkout: yith_stripe_connect_info.is_checkout,
                order: yith_stripe_connect_info.order
            } ),
            method: 'POST',
            url: yith_stripe_connect_info.ajaxurl
        } );
    }

    // utility: removes empty attributes from objects
    function filter_empty_attributes( object ){
        var result = {},
            key,
            value;

        if( typeof object != 'object' ){
            return object;
        }

        for( key in object ){
            if ( ! object.hasOwnProperty( key ) ) {
                continue;
            }

            value = typeof object[ key ] == 'object' ? filter_empty_attributes( object[ key ] ) : object[ key ];

            if( value && ! $.isEmptyObject( value ) ){
                result[ key ] = value;
            }
        }

        return result
    }

    // utility: add data to array that comes from $.serializeArray()
    function pushRecursive( arr, data ){
            var key;

            for( key in data ){
                if ( ! data.hasOwnProperty( key ) ) {
                    continue;
                }

                arr.push( {
                    name: key,
                    value: data[ key ]
                } );
            }

            return arr;
        }

    $(function () {
        // Init elements form
        init_elements();
        on_hash_change();

        // Handles errors messages
        if( typeof card != 'undefined' ){
            card.addEventListener( 'change', stripeResponseHandler );
        }

        // Checkout handled
        $( 'form.checkout' ).on( 'checkout_place_order_yith-stripe-connect', stripeFormHandler);

        // Pay page handler
        $( 'form#order_review' ).on( 'submit', stripeFormHandler);

        // Add card form
        $( 'form#add_payment_method' ).on( 'submit', addMethodHandler );

        // handles hash change
        window.addEventListener( 'hashchange', on_hash_change );

        // Handle checkout error
        $body.on( 'checkout_error', remove_token );

        // Init elements and updates it when checkout is updated
        $body.on( 'updated_checkout', init_elements );

        // handle change of payment method
        $( 'form.checkout, form#order_review, form#add_payment_method' ).on( 'change', '#wc-yith-stripe-connect-cc-form input, #yith-stripe-connect-cc-form input', remove_token );
    });

}(jQuery) );
