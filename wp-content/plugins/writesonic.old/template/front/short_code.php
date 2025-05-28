<?php 
	$user_credit = get_user_meta(get_current_user_id(),'writesonic_credit',true);//print_r($user_credit);
	if(empty($user_credit)){
		$user_credit = 0;
	}
	
?>

<div class="form_div">
	<?php if(!isset($_GET['type'])){?>
	<div class="writesonic_form field">
		<!-- <label for="select_type">Select type of AI</label> -->
		<div class="sonicGrid">
			<p class="credit-score">Total credits: <?= $user_credit;?></p>
			<h2 class="text-center">CREATE A COPY</h2>
			<p class="text-center">What kind of copy you would you like Writesonic to generate? Please select from one of the copy types below.</p>
			<p class="text-center"><a href="javascript:void(0)" data-href="<?php echo site_url();?>?add-to-cart=75790" class="buy-credit">Buy Credits</a></p>
			<div class="bgWhite">
				<div class="rowWrap">
					<!-- <select name="select_type" id="select_type" onchange="show_form();"> -->
					<?php 
						$integration = $this->integration;
						$writesonic_credit = json_decode(get_option( 'writesonic_credit' ),true);
						
						foreach ($integration as $key => $api) { ?>
							<div class="flex-12 cat-title">
								<h3><?= ucfirst(str_replace('-',' ',$key)) ;?></h3>
							</div>
							<?php foreach ($api as $key1 => $value) {
								
							
					?>
			
					<div class="flex-3 mBottom" <?php if($user_credit >= $writesonic_credit[$value['slug']]):?>onclick="show_form('<?php echo $value['slug']; ?>','<?php echo $key;?>');" <?php else :?> onclick="gotobuycredit()" <?php endif;?>>
						<div class="sonicColumn">
							<div class="sonicIcon">
								<img src="<?php echo $value['thumb']; ?>">
								<span class="credits"><i class="fas fa-dollar-sign"></i> Credits: <?= $writesonic_credit[$value['slug']];?></span></div>
							<h3><?php echo $value['name']; ?></h3>
							<p>Quality ads that rank in the search results and drive more traffic.</p>
						</div>
					</div>
					<?php
							}?>
						
						<?php
						}
					?>
				</div>
			</div>
		</div>
	
	</div>
	<?php } ?>
			

<?php 
	if(isset($_GET['type']) && !empty($_GET['type']) && isset($_GET['category']) && !empty($_GET['category'])){
				
		$writesonic_credit = json_decode(get_option( 'writesonic_credit' ),true); //echo "<pre>";print_r($writesonic_credit);
		$credit_required = $writesonic_credit[$_GET['type']];
		 ?>

		
			<input type="hidden" id="select_type" name="select_type" value="<?= $_GET['type'];?>">
			<input type="hidden" id="select_cat" name="select_cat" value="<?= $_GET['category'];?>">
			<?php
				// if($user_credit<$credit_required){
				// 	echo '<div class="sonicTable">';
				// 	echo "<p class='creditTable'><b>Total Credits Required:</b> <span>".$credit_required."</span></p>";
				// 	echo "<p class='creditTable'><b>Total Credits in Account:</b> <span>".$user_credit."</span></p>";
					
				// 	$credit_extra = $credit_required-$user_credit;
				// 	/*echo "<a href=''>Buy Now</a>";do_shortcode("[getpaid item=".get_option( 'writesonic_credit_product' )."|".$credit_extra." button='Buy Now']");*/
				// 	echo "<a href='".get_option( 'writesonic_credit_product' )."'>Buy Now</a>";
				// 	echo '</div>';
				// }else{ ?>
			
			<div class="sonicTitle">	
				<h2><?= str_replace('-',' ',$_GET['type']);?> Descriptions</h2>	
				<p><span class="credits"><i class="fas fa-dollar-sign"></i> Credits: <?= $writesonic_credit[$_GET['type']];?></span></p>
				<p>Describe your product/service below and get personalized <?= ucwords(str_replace('-',' ',$_GET['type']));?> Descriptions in a click.</p>
				<?php
				require dirname(dirname(__FILE__))."/forms/".$_GET['type'].".php";
				?>
				<p class="submit"><input type="button" name="submit" id="submit" class="button button-primary" value="Generate <?= ucwords(str_replace('-',' ',$_GET['type']));?>" onclick="searchData();"></p>
			</div>
				
				<?php
			//}?>
		<?php 
	}
?>

</div>
<div class="result_div" style="display:none;">
	<h2 class="wp-heading-inline">Result <a href="#" id="back_button">Back</a></h2>	
	<ol id="result_list">
		
	</ol>
</div>