jQuery(document).ready(function(){
	jQuery('.admin-userole-wrapper select').on('change',function(){
		
		jQuery.ajax({
			url: site_info.admin_ajax,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'display_user_role_limit',
				role: jQuery(this).val()
			},
			beforeSend:function(){
				jQuery('.display-user-field-wrapper').html('<b>Please Wait......</b>');
			},
			success: function(response){
				//jQuery('.loadingMessage').html('');
				jQuery('.display-user-field-wrapper').html(response.result);
			}
		})
	})

	jQuery(document).on('click','#submit_user_role',function(){
		jQuery.ajax({
			url: site_info.admin_ajax,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'update_user_role_limit',
				role: jQuery('.wrapper-user-form-listing .user_role_limit').val(),
				pageLimit: jQuery('.wrapper-user-form-listing .page_limit').val(),
				projectLimit: jQuery('.wrapper-user-form-listing .project_limit').val(),
				sectionLimit: jQuery('.wrapper-user-form-listing .section_limit').val(),
			},
			beforeSend:function(){
				//jQuery('.display-user-field-wrapper').html('<b>Please Wait......</b>');
			},
			success: function(response){
				//jQuery('.loadingMessage').html('');
				jQuery('.update_status').html(response.result);
				setTimeout(function(){ jQuery('.update_status').html('') }, 2000);

			}
		})
	})
})