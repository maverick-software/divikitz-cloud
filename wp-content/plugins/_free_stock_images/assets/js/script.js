var free_stock_query="";
  var free_stock_page = 1;
  var is_append = false;
  var free_stock_xhttp = new XMLHttpRequest();
  function free_stock_search(ele){
    if(ele.value.length>2){
      free_stock_page=1;
      is_append = false;
      free_stock_query = ele.value;
      free_stock_list();
    }
  }
  function next_page(ele){
    free_stock_page++;
    is_append = true;
    ele.innerHTML = 'Loading..';
    document.getElementById("search-free-stock").style.display = "none"; 
    if(free_stock_page==26){
      document.getElementById("free-stock-load-more").style.display = "none"; 
    }
    free_stock_list();

  }
  function free_stock_list() {

    free_stock_xhttp.abort();
    
   free_stock_xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        var list = JSON.parse(this.responseText);      

        document.getElementById("free-stock-load-more").innerHTML = "Load More"; 
        document.getElementById("search-free-stock").style.display = "Block"; 
        if(is_append){
          document.getElementById("free-stock-image-list").innerHTML = document.getElementById("free-stock-image-list").innerHTML+list.list; 
        }else{
          document.getElementById("free-stock-image-list").innerHTML = list.list; 
        }
       }
     };

    free_stock_xhttp.open("POST", ajax_url, true);
    free_stock_xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    free_stock_xhttp.setRequestHeader("Accept", "application/json");
    free_stock_xhttp.send("action=free_stock_list&q="+free_stock_query+"&p="+free_stock_page);
  }
  function free_stock_image_list_click(ele){
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
    var html = "<a href='"+plugin_url+"/download.php?file="+image_data['largeImageURL']+"' download> Download Image</a><hr>";
   
    for (var key in image_data) {
       var obj = image_data[key];
        
      html +="<b>"+key+":</b><br/> "+obj+"<hr>";
    }
    jQuery(".list-pixbay").find(".detail").html(html);
    
  }