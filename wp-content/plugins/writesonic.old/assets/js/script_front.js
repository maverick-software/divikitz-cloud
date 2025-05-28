jQuery('#back_button').attr('href',window.location.href);
function show_form(data,cat){
	// var data = jQuery("#select_type").val();
	var url = window.location.href.split('?');
	window.location = url[0]+"?type="+data+'&category='+cat;
}
function searchData(){
	jQuery(".error").remove();
	var form_id = jQuery('#select_type').val();
	var category = jQuery('#select_cat').val();
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
	responce_list(form_data,form_id,category);
}
var writesonic_search_xhttp = new XMLHttpRequest();
function responce_list(data,type,category) {
	writesonic_search_xhttp.abort();
	writesonic_search_xhttp.onreadystatechange = function() {
	  	if (this.readyState == 4 && this.status == 200) {
		    var list = JSON.parse(this.responseText);
		    if(list.success===false){
		    	window.location.reload();
		    	return;
		    }
		    if(typeof list.copies !=="undefined"){
		      	if(typeof list.copies[0] !=="undefined"){
		        	var html = "";
		        	list.copies.forEach(function(objects){
		          		html+="<li class='searcch-item'><a href='javascript:void(0)' class='copy-item'><i class='fa fa-copy'></i></a>";
		          		for(const key in  objects){
		            
		            		html +="<b>"+key.replace("_", " ")+":</b><br/>"+objects[key]+"<br/>";
		          		}
		          		html+="</li>";
		          
		        	});
		        	jQuery("#result_list").html(html);
		        
		      	}else{
		        	var html = "";
		        	html+="<li class='searcch-item'><a href='javascript:void(0)' class='copy-item'><i class='fa fa-copy'></i></a>";
		            for(const key in  list.copies){							                
		                html +="<b>"+key.replace("_", " ")+":</b><br/>"+list.copies[key]+"<br/>";
		            }
		        	html+="</li>";
		        	jQuery("#result_list").html(html);
		      	}
		      	jQuery(".result_div").show();
		      	jQuery(".form_div").remove();
		    }
	    	if(typeof list.detail !=="undefined"){
	     
	      		list.detail.forEach(function(objects){
	          		jQuery("#"+objects.loc[1]).parent().append("<span class='error'>"+objects.msg+"</span>")
	      		});
	      
	    	} 

	  		jQuery("#submit").prop("disabled",false);
	  		jQuery("#submit").val("Search");

		}
	};

	jQuery("#result_list").html("");
	jQuery(".result_div").hide();
	jQuery("#submit").prop("disabled",true);
	jQuery("#submit").val("Searching...");
	writesonic_search_xhttp.open("POST", ajax_url, true);
	writesonic_search_xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	writesonic_search_xhttp.setRequestHeader("Accept", "application/json");
	writesonic_search_xhttp.send("action=writesonic_search_front&"+data+"&type="+type+'&category='+category);
}

jQuery(document).on('click','li.searcch-item',function(){
    var $temp = jQuery("<input>");
  jQuery("body").append($temp);
  $temp.val(jQuery(this).parent().text()).select();
  document.execCommand("copy");
  $temp.remove();
  alert('Text Copied');
});

function gotobuycredit(){
   Swal.fire({
        title: "Error!",
        text: "You don't have enought credits. Please buy credits to start",
        confirmButtonColor: '#000',
        icon: "error"
    });
}
jQuery(document).on('click','.buy-credit',function(){
	var link = jQuery(this).attr('data-href');
	Swal.fire({
        title: "Enter Quantity",
        text: "Enter how many credits you want to buy",
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