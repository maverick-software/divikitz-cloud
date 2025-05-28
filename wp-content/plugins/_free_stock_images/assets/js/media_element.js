
var selection ;
var free_stock_query="";
var free_stock_page = 1;
var is_append = false;
var ajax_url =  pixabay_media_tab_ajax_object.ajax_url;
var free_stock_xhttp = new XMLHttpRequest();
jQuery(document).ready(function($){
    if ( wp.media ) {
      var l10n = wp.media.view.l10n;
      if(wp.media.view.MediaFrame.Select){
        var select = wp.media.view.MediaFrame.Select;
      }else{
        var select = wp.media.view.MediaFrame.ETSelect;
      }
      select.prototype.browseRouter = function( routerView ) {
          routerView.set({
              upload: {
                  text:     l10n.uploadFilesTitle,
                  priority: 20
              },
              browse: {
                  text:     l10n.mediaLibraryTitle,
                  priority: 40
              },
              Pixabay: {
                  text:     "Pixabay",
                  priority: 60
              }
          });
      };
        wp.media.view.Modal.prototype.on( "open", function() {
          if(wp.media.frame){
            selection = wp.media.frame.state().get( "selection" );
          }else{
            selection = wp.media.frames.file_frame.state().get( "selection" );

          }
                        
            if(jQuery('body').find('#menu-item-Pixabay').hasClass('active')){                
                doMyTabContent();
            }
        });
        jQuery(document).on('click', '.media-menu-item', function(e){
          
          if(jQuery(this).attr('id')=="menu-item-Pixabay"){
               doMyTabContent();
          }
        });
    }
});
function doMyTabContent() {
    var html = '<div class="list-pixbay"><div class="row-free-stock"><input type="text" id="search-free-stock" class="field-free-stock" placeholder="Search" onkeyup="free_stock_search_add_media(this);" /></div><div class="image-list"><div class="row-free-stock" id="free-stock-image-list">Loading</div><div class="row-free-stock"><button class="load-button" onClick="next_page_add_media(this);" id="free-stock-load-more">Load More</button></div></div><div class="detail"></div></div>';

    html += '<style>.column-free-stock{float:left;width:23%;margin:5px;height:150px}.column-free-stock img{opacity:.8;cursor:pointer;margin-left:auto;margin-right:auto;vertical-align:middle;max-height:150px;max-width:100%}.column-free-stock img:hover{opacity:1}.row-free-stock:after{content:"";display:table;clear:both}.row-free-stock{margin-top:20px;padding-left:10px;padding-right:10px}.field-free-stock{width:100%}.load-button{margin-left:40%}.list-pixbay{width:100%}.list-pixbay .image-list{width:75%;float:left}.list-pixbay .detail{width:23%;float:right;padding-top:20px;padding-left:10px}.active img{opacity:1;border:3px solid #4f94d4}#free-stock-image-list{max-height:500px;overflow:auto}</style>';
    
    jQuery('body .media-modal-content .media-frame-content:visible')[0].innerHTML = html;
    free_stock_search_add_media(jQuery("#search-free-stock")[0]);
}

  function free_stock_search_add_media(ele){
      free_stock_page=1;
      is_append = false;
      free_stock_query = ele.value;
      free_stock_list_add_media();
  }
  function next_page_add_media(ele){
    free_stock_page++;
    is_append = true;
    ele.innerHTML = 'Loading..';
    document.getElementById("search-free-stock").style.display = "none"; 
    if(free_stock_page==26){
      document.getElementById("free-stock-load-more").style.display = "none"; 
    }
    free_stock_list_add_media();

  }
  function free_stock_list_add_media() {

    free_stock_xhttp.abort();
    
   free_stock_xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        var list = JSON.parse(this.responseText);      

        document.getElementById("free-stock-load-more").innerHTML = "Load More"; 
        document.getElementById("search-free-stock").style.display = "Block"; 
        if(is_append){
          jQuery('body .media-modal-content .media-frame-content:visible').find("#free-stock-image-list")[0].innerHTML = jQuery('body .media-modal-content .media-frame-content:visible').find("#free-stock-image-list")[0].innerHTML+list.list; 
        }else{
          jQuery('body .media-modal-content .media-frame-content:visible').find("#free-stock-image-list")[0].innerHTML = list.list; 
        }
       }
     };

    free_stock_xhttp.open("POST", ajax_url, true);
    free_stock_xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    free_stock_xhttp.setRequestHeader("Accept", "application/json");
    free_stock_xhttp.send("action=free_stock_list_media_element&q="+free_stock_query+"&p="+free_stock_page);
  }
   function free_stock_image_click(ele){
    var image_data = jQuery(ele).data('image');
    jQuery(".column-free-stock").removeClass('active');
    jQuery(ele).parent().addClass('active');
    delete image_data.id;
    delete image_data.user_id;
    delete image_data.user;
    delete image_data.userImageURL;
    delete image_data.comments;
    delete image_data.collections;
    delete image_data.downloads;
    delete image_data.views;
    delete image_data.imageSize;
    var html = "<button class='button button-primary button-large free_stock_import_button' onClick='add_url(\""+image_data['largeImageURL']+"\")' > Import Image</button><hr>";
   
    for (var key in image_data) {
       var obj = image_data[key];
        
      html +="<b>"+key+":</b><br/> "+obj+"<hr>";
    }
    jQuery(".list-pixbay").find(".detail").html(html);
    
  }
  function add_url(url){
     free_stock_xhttp.abort();
      jQuery(".free_stock_import_button").prop('disable',true);
      jQuery(".free_stock_import_button").html('Please wait');

    free_stock_xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        var list = JSON.parse(this.responseText);      
        jQuery('.media-router:visible').find("#menu-item-browse").click();
        if(wp.media.frame){
          if(wp.media.frame.content.get()){
            wp.media.frame.content.get().collection._requery( true );
          }
        }else{
          if(wp.media.frames.file_frame.content.get()){

            wp.media.frames.file_frame.content.get().collection._requery( true );
          }
        }
        attachment = wp.media.attachment(list.post_id);
        attachment.fetch();
        selection.add(attachment ? [attachment] : []);
      }
     };

    free_stock_xhttp.open("POST", ajax_url, true);
    free_stock_xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    free_stock_xhttp.setRequestHeader("Accept", "application/json");
    free_stock_xhttp.send("action=free_stock_add_media&url="+url);
  }