<div class="list-pixbay">
	<div class="row-free-stock">
	  <input type="text" id="search-free-stock" class="field-free-stock" placeholder="Search" onkeyup="free_stock_search(this);" />
	</div>
	<div class="image-list">
		<div class="row-free-stock" id="free-stock-image-list">
		  <?php 
		    foreach ($images->hits as $key => $image) { 

		      require 'image_element_search.php';

		    } 
		  ?>
		</div>
		<div class="row-free-stock">
		  <button class="load-button" onClick="next_page(this);" id="free-stock-load-more">Load More</button>
		</div>
	</div>
	<div class="detail">
		
	</div>
</div>
