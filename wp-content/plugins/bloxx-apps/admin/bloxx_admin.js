var bloxx = {"ajax_url":window.location.origin+"/wp-admin/admin-ajax.php"};
function markread(el){
    jQuery(el).prop('disabled',true);
    var ajax_url=bloxx.ajax_url;
    jQuery.ajax({
        type : "POST",
        url : ajax_url,
        dataType : "json",
        data : {id:jQuery(el).data('id'),'action':'mark_as_read'},            
        success: function(resp) {
            swal.fire(resp.message);
            jQuery(el).prop('disabled',false);
            if(resp.code ==200){
                
                if(resp.code ==200){
                    setTimeout(function(){
                        window.location.href = window.location.origin + '/bloxx-applications/';
                    },2000);
                    
                }
            }
        }
    });
}

jQuery(document).on("click",".notice-dismiss-bloxx",function(){
    jQuery(".notice-warning").fadeOut();
});

jQuery(document).on('change','.select-group',function(){
    jQuery(this).parent('form').submit();
});

jQuery(document).ready(function(){
    if(jQuery("#servers_table").length > 0){
        jQuery('#servers_table').DataTable();
    }

    if(jQuery("#clusters_table").length > 0){
        jQuery('#clusters_table').DataTable();
    }





    jQuery('.toplevel_page_bloxx-app .wp-submenu li.wp-first-item a.wp-first-item').text('API');






});



