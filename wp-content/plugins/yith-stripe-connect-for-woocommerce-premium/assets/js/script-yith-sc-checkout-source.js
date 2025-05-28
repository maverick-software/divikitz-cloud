/* global Stripe, yith_stripe_connect_info, woocommerce_params */


var stripe = Stripe(yith_stripe_connect_info.public_key),
    element = stripe.elements(),
    type = typeof  yith_stripe_connect_source_info != 'undefined' ? yith_stripe_connect_source_info.payment_type : 'token',
    stripe_card,
    stripe_exp,
    stripe_cvc;

(function ($) {

    $(function () {

        var elementStyles = {
            base: {
                color: '#32325d',
                lineHeight: '18px',
                fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                fontSmoothing: 'antialiased',
                fontSize: '16px',
                '::placeholder': {
                    color: '#aab7c4'
                }
            },
            invalid: {
                color: '#fa755a',
                iconColor: '#fa755a'
            }
        };

        var elementClasses = {
            focus: 'focused',
            empty: 'empty',
            invalid: 'invalid',
        };

        stripe_card = element.create('cardNumber', { style: elementStyles, classes: elementClasses });
        stripe_exp = element.create('cardExpiry', { style: elementStyles, classes: elementClasses });
        stripe_cvc = element.create('cardCvc', { style: elementStyles, classes: elementClasses });

        $(document.body).on('updated_checkout', function () {
            stripe_card.mount('#yith-card-number-field-wrapper');
            stripe_exp.mount('#yith-card-expiry-field-wrapper');
            stripe_cvc.mount('#yith-card-cvc-field-wrapper');
        });

        // Checkout handled
        $( 'form.checkout' ).on( 'checkout_place_order_yith-stripe-connect', function (e) {
            return stripeFormHandler(e);
        });

        //Add payment and Pay order pages
        if (  $( 'form#add_payment_method' ).length > 0 || $( 'form#order_review' ).length > 0 ){
            stripe_card.mount('#yith-card-number-field-wrapper');
            stripe_exp.mount('#yith-card-expiry-field-wrapper');
            stripe_cvc.mount('#yith-card-cvc-field-wrapper');

            $( 'form#add_payment_method, form#order_review' ).on( 'submit', function (e) {
                return stripeFormHandler(e);
            });
        }

        stripe_card.on( 'change', function( event ) {
        //    wc_stripe_form.updateCardBrand( event.brand );
            $('#yith-stripe-cc').attr('class', '').addClass( event.brand );
        } );
    });

    // Form handler
    function stripeFormHandler( event ) {
        var $form = $( 'form.checkout, form#order_review, form#add_payment_method' );

        if ( $form.is('.add-card') ||
            $( 'input#payment_method_yith-stripe-connect' ).is( ':checked' ) && ( ! $( 'input[name="wc-yith-stripe-connect-payment-token"]').length ||
                $( 'input[name="wc-yith-stripe-connect-payment-token"]:checked').val() == 'new' ) ) {

            if ( 0 === $( 'input.stripe-token' ).length &&  0 === $( 'input.stripe-source' ).size() ) {

                if ('source' == type) {
                    owner_info = getOwnerInfo();
                    if (owner_info !== false ) {
                        stripe.createSource(stripe_card, getOwnerInfo()).then(stripeResponseHandler);
                    } else {
                        stripe.createSource(stripe_card).then(stripeResponseHandler);
                    }
                    stripe.createSource(stripe_card, getOwnerInfo()).then(stripeResponseHandler);
                } else {
                    stripe.createToken(stripe_card).then(stripeResponseHandler);
                }

                return false;
            }
        }
        return event;
    }

    // Handle Stripe response
    function stripeResponseHandler( response ) {
        var $form  = $( 'form.checkout, form#order_review, form#add_payment_method' ),
            ccForm = $( '#wc-yith-stripe-connect-cc-form, #yith-stripe-connect-cc-form' );

        if ( response.error ) {

            // Show the errors on the form
            $( '.woocommerce-error, .stripe-token', ccForm ).remove();
            $form.unblock();

            if ( response.error.message ) {
                ccForm.prepend( '<ul class="woocommerce-error">' + response.error.message + '</ul>' );
            }

            // Show any validation errors
            else if ( 'validation' === response.error.code ) {
                var fieldErrors = response.error.fieldErrors,
                    fieldErrorsLength = fieldErrors.length,
                    errorList = '';

                for ( var i = 0; i < fieldErrorsLength; i++ ) {
                    errorList += '<li>' + yith_stripe_connect_info[ fieldErrors[i] ] + '</li>';
                }

                ccForm.prepend( '<ul class="woocommerce-error">' + errorList + '</ul>' );
            }

        } else {

            // Insert the token into the form so it gets submitted to the server
            if( 'source' == type ){
                ccForm.append( '<input type="hidden" class="stripe-source" name="stripe_connect_source" value="' +  response.source.id + '"/>' );
            }else{
                ccForm.append( '<input type="hidden" class="stripe-token" name="stripe_connect_token" value="' +  response.token.id + '"/>' );
            }


            $form.submit();
        }
    }

    function getOwnerInfo(){
        var owner_info = { owner: { address: {} } },
            name = $('#yith-stripe-connect-card-name').length ? $( '#yith-stripe-connect-card-name' ).val() : '',
            first_name = $( '#billing_first_name' ).length ? $( '#billing_first_name' ).val() : '',
            last_name  = $( '#billing_last_name' ).length ? $( '#billing_last_name' ).val() : '',
            billing_email  = $( '#billing_email' ).length ? $( '#billing_email' ).val() : '',
            billing_phone  = $( '#billing_phone' ).length ? $( '#billing_phone' ).val() : '',
            billing_country  = $( '#billing_country' ).length ? $( '#billing_country' ).val() : '',
            billing_city  = $( '#billing_city' ).length ? $( '#billing_city' ).val() : '',
            billing_address_1  = $( '#billing_address_1' ).length ? $( '#billing_address_1' ).val() : '',
            billing_address_2  = $( '#billing_address_2' ).length ? $( '#billing_address_2' ).val() : '',
            billing_state  = $( '#billing_state' ).length ? $( '#billing_state' ).val() : '',
            billing_postcode  = $( '#billing_postcode' ).length ? $( '#billing_postcode' ).val() : '';

        if (name !== '') {
            owner_info.owner.name = name;
        } else {
            if (first_name !== '') {
                owner_info.owner.name = first_name;
            }

            if (last_name !== '') {
                owner_info.owner.name += (first_name !== '') ? ' ' + last_name : last_name;
            }
        }

        if( owner_info.owner.name ===  ''){
            return false;
        }

        if (billing_email !== '') {
            owner_info.owner.email = billing_email;
        }

        if (billing_phone !== '') {
            owner_info.owner.phone = billing_phone;
        }

        if (billing_city !== '') {
            owner_info.owner.address.city = billing_city;
        }

        if (billing_country !== '') {
            owner_info.owner.address.country = billing_country;
        }

        if( billing_address_1 !== '' ){
            owner_info.owner.address.line1 = billing_address_1;
        }

        if( billing_address_2 !== '' ){
            owner_info.owner.address.line2 = billing_address_2;
        }

        if( billing_postcode !== '' ){
            owner_info.owner.address.postal_code = billing_postcode;
        }

        if( billing_state !== ''){
            owner_info.owner.address.state = billing_state;
        }

        return owner_info;
    }


}(jQuery) );
