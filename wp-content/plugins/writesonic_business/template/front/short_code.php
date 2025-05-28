<?php 
	$user_credit = get_user_meta(get_current_user_id(),'writesonic_credit',true);//print_r($user_credit);
	if(empty($user_credit)){
		$user_credit = 0;
	}
	
?>



<!-- tabs start  -->

<style type="text/css">
.tab {
  overflow: hidden;
  border: 1px solid #ccc;
  background-color: #f1f1f1;
}
.tab button {
  background-color: inherit;
  float: left;
  border: none;
  outline: none;
  cursor: pointer;
  padding: 14px 16px;
  transition: 0.3s;
}
.tab button:hover {
  background-color: #ddd;
}
.tab button.active {
  background-color: #ccc;
}
.tabcontent {
  display: none;
  padding: 6px 12px;
  border: 1px solid #ccc;
  border-top: none;
}

.tabcontent {
  animation: fadeEffect 1s; 
}


.wrapContent{
	background: #fff;
}

.wrapContent .topWrapmenu {
    margin: 0 0 30px;
}

#table-page .wrapContent {
    padding: 0 0 3rem;
}

.wrapContainer{
		background: url("https://wordpress-782565-2667748.cloudwaysapps.com/wp-content/plugins/writesonic_business/assets/images/WritrAI.png") no-repeat center / cover;
    padding: 2rem 2rem 0;
}

#table-page .wrapContent .wrapContainer.user_actions .form_div,
#table-page .wrapContent .wrapContainer.user_actions {
	max-width: 100%;
}



@keyframes fadeEffect {
  from {opacity: 0;}
  to {opacity: 1;}
}
</style>
<script type="text/javascript">
jQuery(document).ready(function(){
document.getElementById("defaultOpen").click();
});
	
	function openCity(evt, tabName) {
  // Declare all variables
  var i, tabcontent, tablinks;

  // Get all elements with class="tabcontent" and hide them
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  // Get all elements with class="tablinks" and remove the class "active"
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  // Show the current tab, and add an "active" class to the button that opened the tab
  document.getElementById(tabName).style.display = "block";
  evt.currentTarget.className += " active";
}
</script>

<!-- Tab links -->

<!-- tabs end -->


<div class="WritrAI form_div">
	<?php if(!isset($_GET['type'])){?>
	<div class="writesonic_form field">
		<!-- <label for="select_type">Select type of AI</label> -->
		<div class="sonicGrid">
			<!-- <p class="credit-score"> <span class="credits">Crystal Fragments: </p> -->
			
			<div class="rowWrap_top">
					<div class="flex-8">
							<div class="WritrAI_top_bix">
								<div class="top_img">
									<img style="vertical-align: middle; max-width: 150px;" src="<?= get_field('crystal_image', 'options'); ?>">
								</div>
								<div class="top_content">
									<h2><?= get_field('writr_title', 'options'); ?></h2>
									<?= get_field('writr_content', 'options'); ?>
								</div>
							</div>
					</div>
					<div class="flex-4">
						<div class="WritrAI_top_right">
									<div class="top-icon">
										<img style="vertical-align: middle; max-width: 40px;" src="<?php echo writesonic_business_url."/assets/images/";?>/crystal.png"> 
									</div>
									<div class="top-crystals">
										<span><?= $user_credit;?></span>
										CRYSTALS
									</div>
								<div class="top-btn">
									<p class="text-center">
										<a href="javascript:void(0)" data-href="<?php echo site_url();?>?add-to-cart=<?php echo get_option( 'writesonic_bussiness_credit_product' ); ?>" class="buy-credit" data_image_url="<?php echo get_field( 'write_popup_image', 'option'); ?>" data_title="<?php echo get_field( 'write_popup_title', 'option'); ?>" data_subtitle="<?php echo get_field( 'write_popup_subtitle', 'option'); ?>"><?= get_field('buy_button_text', 'options'); ?></a>
									</p>
								</div>
						</div>			
					</div>
			</div>

			<div class="bgWhite">
				<div class="rowWrap">
					<!-- <select name="select_type" id="select_type" onchange="show_form();"> -->
					<?php 
						$integration = $this->integration;
						$writesonic_credit = json_decode(get_option( 'writesonic_credit' ),true);
						$i = 0;
						echo '<div class="tab">';

						?>
						
						<?php
						//pre($integration);
						foreach ($integration as $key => $api) { 
							//pre($api);
							if(!empty($api)){?>
							<?php /*<div class="flex-12 cat-title">
								  <h3><?= ucfirst(str_replace('-',' ',$key)) ;?></h3> 
							</div>
							*/ ?>

							<?php if($i==0){ ?>
								<button class="tablinks"  id="defaultOpen" onclick="openCity(event, 'tab_all')">
							  	   All
							  </button>
							<?php } else { ?>
							  <button class="tablinks" onclick="openCity(event, 'tab_<?php echo $i; ?>')">
							  	<?= ucfirst(str_replace('-',' ',$key)) ;?>
							  </button>
							 <?php } ?>

							<?php }  $i++;
						} // end foreach
						echo '</div>';

						


						echo '<div id="tab_all" class="tabcontent"><div class="rowWrap">';
						foreach ($integration as $key => $api1) { 

							
								
								foreach ($api1 as $key1 => $value1) {
								
								

								if($value1['status']):?>
									<div class="flex-3 mBottom"  <?php if($user_credit >= $writesonic_credit[$value1['slug']]):?>onclick="show_form('<?php echo $value1['slug']; ?>','<?php echo $key;?>');" <?php else :?> onclick="gotobuycredit()" <?php endif;?>>
									<!-- <div class="flex-3 mBottom" onclick="show_form('<?php // echo $value['slug']; ?>','<?php //echo $key;?>');" > -->
										<div class="box-card">
											<div class="sonicColumn">
												<div class="sonicIcon">
													<img src="<?php echo $value1['thumb']; ?>">
													<span class="credits"><img style="vertical-align: middle; max-width: 20px;" src="<?php echo writesonic_business_url."/assets/images/";?>/crystal.png"> Crystals: <?= $writesonic_credit[$value1['slug']];?></span></div>
												<h3><?php echo $value1['name']; ?></h3>
												<p><?php echo $value1['desc']; ?></p>
												<div class="go-corner"></div>
											</div>
										</div>
									</div>
									<?php
									endif;
								} //end foreach tab content
								
							
							?>
						
						<?php
						 }
						echo '</div></div>';
					

						$j =0;
						foreach ($integration as $key => $api2) { 

								if($j==0){

								}else{


								echo '<div id="tab_'.$j.'" class="tabcontent"><div class="rowWrap">';
							
								


							// if(empty($api)){
							// 	echo 'No Data found';
							// }
								foreach ($api2 as $key2 => $value2) {
									
									

								if($value2['status']):?>
									<div class="flex-3 mBottom"  <?php if($user_credit >= $writesonic_credit[$value2['slug']]):?>onclick="show_form('<?php echo $value2['slug']; ?>','<?php echo $key;?>');" <?php else :?> onclick="gotobuycredit()" <?php endif;?>>
									<!-- <div class="flex-3 mBottom" onclick="show_form('<?php echo $value['slug']; ?>','<?php echo $key;?>');" > -->
										<div class="sonicColumn">
											<div class="sonicIcon">
												<img src="<?php echo $value2['thumb']; ?>">
												<span class="credits"><img style="vertical-align: middle; max-width: 20px;" src="<?php echo writesonic_business_url."/assets/images/";?>/BloxxCoins.png"> Crystals: <?= $writesonic_credit[$value2['slug']];?></span></div>
											<h3><?php echo $value2['name']; ?></h3>
											<p><?php echo $value2['desc']; ?></p>
											<div class="go-corner"></div>
										</div>
									</div>
									<?php
									endif;
								} //end foreach tab content
								echo '</div></div>';
								}
							?>
						
						<?php
						$j++; }
					?>
				</div>
			</div>
		</div>
	
	</div>
	<?php } ?>
			

<?php 
	if(isset($_GET['type']) && !empty($_GET['type']) && isset($_GET['category']) && !empty($_GET['category'])){
		?>
		<div class="rowWrap">
            <div class="flex-5">
                <div class="writr_detail_sidebar">
                    <?php
                        $integration2 = $this->integration;	
                        $writesonic_credit = json_decode(get_option( 'writesonic_credit' ),true); 
                        //echo "<pre>";print_r($writesonic_credit);
                        $credit_required = $writesonic_credit[$_GET['type']];
                        /* sidebar */
                        foreach ($integration2 as $key => $api1) { 
                            foreach ($api1 as $key1 => $value1) {
                                if($value1['status']):?>
                                <div class="flex-12 mBottom"  <?php if($user_credit >= $writesonic_credit[$value1['slug']]):?>onclick="show_form('<?php echo $value1['slug']; ?>','<?php echo $key;?>');" <?php else :?> onclick="gotobuycredit()" <?php endif;?>>
                                <!-- <div class="flex-3 mBottom" onclick="show_form('<?php // echo $value['slug']; ?>','<?php //echo $key;?>');" > -->
                                    <div class="sonicColumn">
                                        <div class="sonicIcon">
                                            <!-- <img src="<?php// echo $value['thumb']; ?>"> -->
                                            <div class="heading_icon">
                                                    <img style="vertical-align: middle; max-width: 20px;" src="<?php echo writesonic_business_url."/assets/images/";?>/icon1.png">
                                            </div>
                                            <h3><?php echo $value1['name']; ?></h3>
                                            <span class="credits"><img style="vertical-align: middle; max-width: 20px;" src="<?php echo writesonic_business_url."/assets/images/";?>/BloxxCoins.png"> <?= $writesonic_credit[$value1['slug']];?></span></div>	
                                            <div class="go-corner"></div>
                                    </div>
                                </div>
                                <?php
                                endif;
                            }	
                        }
                            /**/
                    ?>		
                    <?php 
                    /*
                    $writesonic_credit = json_decode(get_option( 'writesonic_credit' ),true);
                    
                    //pre($integration2);
                    foreach ($integration2 as $key => $api2) {
                        if($key!=$_GET['category']){
                            continue;
                        }

                        foreach ($api2 as $key2 => $value2) {
                            if($value2['status']):?>

                                <div class="writr_title_full flex-12 mBottom"  <?php if($user_credit >= $writesonic_credit[$value2['slug']]):?>onclick="show_form('<?php echo $value2['slug']; ?>','<?php echo $key;?>');" <?php else :?> onclick="gotobuycredit()" <?php endif;?>>
                                <!-- <div class="flex-3 mBottom" onclick="show_form('<?php echo $value['slug']; ?>','<?php echo $key;?>');" > -->
                                    <div class="sonicColumn">
                                        <div class="sonicIcon">
                                            <img src="<?php echo $value2['thumb']; ?>">
                                            <span class="credits"><img style="vertical-align: middle; max-width: 20px;" src="<?php echo writesonic_business_url."/assets/images/";?>/BloxxCoins.png"> Crystals: <?= $writesonic_credit[$value2['slug']];?></span></div>
                                        <h3><?php echo $value2['name']; ?></h3>
                                        
                                    </div>
                                </div>
                                <?php
                                endif;
                        }
                    }
                    */
                    ?>
                </div>
                <div class="writr_title_right">
                    <input type="hidden" id="select_type" name="select_type" value="<?= $_GET['type'];?>">
                    <input type="hidden" id="select_cat" name="select_cat" value="<?= $_GET['category'];?>">
					<div class="sonicTitle">	
                        <p>Every time you click the 'Generate' button, we will compose upto 5 unique product descriptions for you. Check out some examples here.</p>
                        <form>
                        <table class="form-table" role="presentation">
                            <tbody>
                                <tr class="select_type-wrap">
                                    
                                    <th><label for="engine">Engine</label></th>
                                    <td>
                                        <select id="engine" class="field">
                                            
                                                <option value="economy">Economy</option>
                                                <option value="business">Business</option>
                                            
                                        </select>
                                    </td>
                                    <th>Language</th>
                                    <td>
                                        <select id="language" class="field">
                                            <option value="en">en</option>
                                            <option value="nl">nl</option>
                                            <option value="fr">fr</option>
                                            <option value="de">de</option>
                                            <option value="it">it</option>
                                            <option value="pl">pl</option>
                                            <option value="es">es</option>
                                            <option value="pt-pt">pt-pt</option>
                                            <option value="pt-br">pt-br</option>
                                            <option value="ru">ru</option>
                                            <option value="ja">ja</option>
                                            <option value="zh">zh</option>
                                            <option value="bg">bg</option>
                                            <option value="cs">cs</option>
                                            <option value="da">da</option>
                                            <option value="el">el</option>
                                            <option value="hu">hu</option>
                                            <option value="lt">lt</option>
                                            <option value="lv">lv</option>
                                            <option value="ro">ro</option>
                                            <option value="sk">sk</option>
                                            <option value="sl">sl</option>
                                            <option value="sv">sv</option>
                                            <option value="fi">fi</option>
                                            <option value="et">et</option>
                                            
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        </form>
                        <?php
                        require dirname(dirname(__FILE__))."/forms/".$_GET['type'].".php";
                        ?>
                        <p class="submit"><input type="button" name="submit" id="submit" class="button button-primary" value="Generate <?= ucwords(str_replace('-',' ',$_GET['type']));?>" onclick="searchData();"></p>
                    </div>
                </div>
            </div>
            <div class="flex-7">
                <div class="ai--content--column">
                    <img src="<?php echo writesonic_business_url."assets/images/";?>ai-content.png" alt="..." />
                    <p>Your AI-Generated copy will appear here.</p>
                </div>
                <div class="result_div" style="display:none;">
                    <h2 class="wp-heading-inline">Select the generated content you prefer</h2>	
                    <ol id="result_list">
                            
                    </ol>
                </div>
            </div>
		</div>			
	<?php 
    }
?>

</div>