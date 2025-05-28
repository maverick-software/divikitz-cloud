<div class="list-pixbay">
	<div class="row-free-stock">
	  <input type="text" id="search-free-stock" class="field-free-stock" placeholder="Search"/>
	</div>
	<div class="image-list">
		<div class="row-free-stock" id="free-stock-image-list">
		  <?php 
		    foreach ($images->hits as $key => $image) { 

		      require 'image_element_search_api.php';

		    } 
		  ?>
		</div>
		<div class="row-free-stock">
		  <button class="load-button" id="free-stock-load-more">Load More</button>
		</div>
	</div>
	<div class="detail">
		
	</div>
</div>
