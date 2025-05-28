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
  responce_list(form_data,form_id,language,engine);
}
var writesonic_search_xhttp = new XMLHttpRequest();
function responce_list(data,type,language,engine) {
  writesonic_search_xhttp.abort();
  writesonic_search_xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        var list = JSON.parse(this.responseText);
        if(typeof list.detail !=="undefined"){
          html = "<table>";
          list.detail.forEach(function(objects){
            html += "<tr><th><span class='error'>"+objects.loc[1].replace("_", " ")+"</span></th><td><span class='error'>"+objects.msg+"</span></td></tr>";
              
          });
          html += "</table>";
          jQuery("#result_list").html(html);
          jQuery(".result_div").show();
        }else{
          if(typeof list.data !=="undefined"){
            var html = "";
            for(var i=0; i < list.data.length;i++){
              for(const key in  list.data[i]){
                html+="<li class='searcch-item'>";
                html +="<b>"+key.replace("_", " ")+":</b><br/>"+list.data[i][key]+"<br/>";
                html+="</li>";
              }
            }
            jQuery("#result_list").html(html);
            jQuery(".result_div").show();
          }else{

            var html = "";
            for(var i=0; i < list.length;i++){
              for(const key in  list[i]){
                html+="<li class='searcch-item'>";
                html +="<b>"+key.replace("_", " ")+":</b><br/>"+list[i][key]+"<br/>";
                html+="</li>";
              }
            }
            jQuery("#result_list").html(html);
            jQuery(".result_div").show();
          }
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
  writesonic_search_xhttp.send("action=Writesonic_Business_search&"+data+"&type="+type+"&lang="+language+"&engine="+engine);
}
function add_field(ele){
  jQuery(ele).parent().parent().parent().append('<tr><th></th><td id="article_section"><input type="text" id="article_sections[]" name="article_sections[]"  class="google-ads field" style="width: 97%;float: left;" required/><span style="float: left;padding: 5px;cursor: pointer;" onClick="remove_field(this);">X</span></td></tr>');
}
function remove_field(ele){
  jQuery(ele).parent().parent().remove();
}