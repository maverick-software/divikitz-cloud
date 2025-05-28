
var url = window.location.href.split('?');
jQuery('#back_button').attr('href',url[0]);
var isTraversable = o => Array.isArray(o)
     || o !== null && ['function', 'object'].includes(typeof o);
function show_form(data,cat){
	// var data = jQuery("#select_type").val();
	var url = window.location.href.split('?');
	window.location = url[0]+"?type="+data+'&category='+cat;
}
function searchData(){
	jQuery(".error").remove();
	var form_id = jQuery('#select_type').val();
	var category = jQuery('#select_cat').val();
	var language = jQuery('#language').val();
  	var engine = jQuery('#engine').val();
	var form_field = jQuery("."+form_id);
	var error = 0;
	form_field.each(function(index,ele){

	if(jQuery(ele).val()=="" && jQuery(ele).parent().parent().find(".require").length>0){
	  jQuery(ele).parent().append("<span class='error'>This is required field</span>")
	  error++;
	}

	});
	if(error>0){
		return false;
	}
	var form_data = jQuery("#"+form_id).serialize();

	responce_list(form_data,form_id,category,language,engine);
}
var writesonic_search_xhttp = new XMLHttpRequest();
function responce_list(data,type,category,language,engine) {
	var oldbtn_html = jQuery("#submit").val();
	writesonic_search_xhttp.abort();
	writesonic_search_xhttp.onreadystatechange = function() {
	  	if (this.readyState == 4 && this.status == 200) {
		    var list = JSON.parse(this.responseText);


		    if(list.success===false){
		    	window.location=url[0];
		    	return;
		    }
		    if(typeof list.detail !=="undefined"){
	          html = "<table>";
		          if(isTraversable(list.detail) !== false){
			          list.detail.forEach(function(objects){
			            html += "<tr><th><span class='error'>"+objects.loc[1].replace("_", " ")+"</span></th><td><span class='error'>"+objects.msg+"</span></td></tr>";
			              
			          });
			      }else{
			      	html += "<tr><td colspan='2'><span class='error'>"+list.detail+"</span></td></tr>";
			      }
	          html += "</table>";
	          jQuery("#result_list").html(html);
	          jQuery(".result_div").show();jQuery(".ai--content--column").hide();
	        }else{
	          if(typeof list.data !=="undefined"){
	            var html = "";
	            for(var i=0; i < list.data.length;i++){
	              for(const key in  list.data[i]){
	                html+="<li class='searcch-item'><i class='copy-item fas fa-copy'></i>";
	                html +="<b>"+key.replace("_", " ")+":</b><br/>"+list.data[i][key]+"<br/>";
	                html+="</li>";
	              }
	            }
	            jQuery("#result_list").html(html);
	            jQuery(".result_div").show();jQuery(".ai--content--column").hide();
	          }else{

	            var html = "";
	            for(var i=0; i < list.length;i++){
	              for(const key in  list[i]){
	                html+="<li class='searcch-item'><i class='copy-item fas fa-copy'></i>";
	                html +="<b>"+key.replace("_", " ")+":</b><br/>"+list[i][key]+"<br/>";
	                html+="</li>";
	              }
	            }
	            jQuery("#result_list").html(html);
	            jQuery(".result_div").show();jQuery(".ai--content--column").hide();
	          }
	        }  

	  		jQuery("#submit").prop("disabled",false);
	  		jQuery("#submit").val(oldbtn_html);

		}
	};

	jQuery("#result_list").html("");
	jQuery(".result_div").hide();
	jQuery("#submit").prop("disabled",true);
	jQuery("#submit").val("Searching...");
	writesonic_search_xhttp.open("POST", ajax_url, true);
	writesonic_search_xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	writesonic_search_xhttp.setRequestHeader("Accept", "application/json");
	writesonic_search_xhttp.send("action=Writesonic_Business_search_front&"+data+"&type="+type+'&category='+category+"&lang="+language+"&engine="+engine);
}

// jQuery(document).on('click','li.searcch-item',function(){
// 	jQuery('li.searcch-item').removeClass('text_copied');
// 	//alert('test');
//     var $temp = jQuery("<input>");
//   jQuery("body").append($temp);
//   $temp.val(jQuery(this).parent().text()).select();
//   document.execCommand("copy");
//   $temp.remove();
//   jQuery(this).addClass('text_copied');
//  // alert('Text Copied');
// });

jQuery(document).on('click','i.copy-item',function(){
	if(jQuery(".active.fa-check-circle").length > 0){
		jQuery(".active.fas.fa-check-circle").each(function(){
			jQuery(this).removeClass('active fas fa-check-circle').addClass('fas fa-copy');
		});
	}
  var $temp = jQuery("<input>");
  jQuery("body").append($temp);
  $temp.val(jQuery(this).parent().text()).select();
  document.execCommand("copy");
  $temp.remove();
  jQuery(this).removeClass('fas fa-copy').addClass('active fas fa-check-circle');
});

function gotobuycredit(){
   Swal.fire({
        title: "Error!",
        text: "You don't have enough credits. Please buy credits to start",
        confirmButtonColor: '#000',
        icon: "error"
    });
}
jQuery(document).on('click','.buy-credit',function(){
	var link = jQuery(this).attr('data-href');
	var crystal_image= jQuery(this).attr('data_image_url');
	var crystal_title= jQuery(this).attr('data_title');
	var crystal_subtitle= jQuery(this).attr('data_subtitle');

	Swal.fire({
        title: crystal_title,
        text: crystal_subtitle,
        imageUrl: crystal_image,
        input: 'number',
        inputValue: 1,
        showCancelButton: true,
      	customClass: {
      		input: 'quantity-input',
      	}      
    }).then((result) => {
    	if(result.isConfirmed){
	        if (result.value != "" && result.value > 0) {
	            window.location.href = link+'&quantity='+result.value;
	        }else{
	        	Swal.fire({
			        title: "Error!",
			        text: "You need to enter the quantity!",
			        confirmButtonColor: '#000',
			        icon: "error"
			    });
	        }
	    }
    });
});