function show_form(){
  var form_id = jQuery('#select_type').val();
  jQuery('form').hide();
  jQuery("#"+form_id).show();
  jQuery("#result_list").html("");
  jQuery(".result_div").hide();
  jQuery("#submit").prop("disabled",false);
  jQuery("#submit").val("Search");
}
show_form();
function searchData(){
  jQuery(".error").remove();
  var form_id = jQuery('#select_type').val();
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
  responce_list(form_data,form_id);
}
var writesonic_search_xhttp = new XMLHttpRequest();
function responce_list(data,type) {
  writesonic_search_xhttp.abort();
  writesonic_search_xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        var list = JSON.parse(this.responseText);
        if(typeof list.copies !=="undefined"){
         
          if(typeof list.copies[0] !=="undefined"){
            var html = "";
            list.copies.forEach(function(objects){
              html+="<li class='searcch-item'>";
              for(const key in  objects){
                
                html +="<b>"+key.replace("_", " ")+":</b><br/>"+objects[key]+"<br/>";
              }
              html+="</li>";
              
            });
            jQuery("#result_list").html(html);
            
          }else{
            var html = "";
              html+="<li class='searcch-item'>";
              for(const key in  list.copies){
                
                html +="<b>"+key.replace("_", " ")+":</b><br/>"+list.copies[key]+"<br/>";
              }
              html+="</li>";
            jQuery("#result_list").html(html);
          }
          jQuery(".result_div").show();
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
  writesonic_search_xhttp.send("action=writesonic_search&"+data+"&type="+type);
}
  