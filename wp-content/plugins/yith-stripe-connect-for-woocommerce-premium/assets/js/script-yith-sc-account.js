jQuery(function ($) {

	init_connect_button();

	function init_connect_button() {
		$('#yith-sc-connect-button').on('click', function (e) {
			if ($(this).hasClass('yith-sc-disconnect')) {
				e.preventDefault();
				$('.stripe-connect').block({message: null, overlayCSS: {background: "#fff", opacity: .6}});

				var options = {
					action: yith_wcsc_account_page_script.disconnect_stripe_connect_action
				};
				$.post(yith_wcsc_account_page_script.ajaxurl, options)
					.done(function (data) {
						var sc = $('.stripe-connect');

						sc.unblock();
						if (data['disconnected']) {
							sc.removeClass('yith-sc-disconnect');
							sc.attr('href', yith_wcsc_account_page_script.oauth_link);

							$('.message').text('');
							$('.stripe-connect>span').text(yith_wcsc_account_page_script.messages.connect_to);
						} else {
							$('.message').text(data['message']);
							$('.stripe-connect>span').text(yith_wcsc_account_page_script.messages.disconnect_to);
						}
					}).fail(function (jqXHR, textStatus, errorThrown) {
						console.error()
					}
				)
			}
		});


	}

});