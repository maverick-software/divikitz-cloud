<?php get_header(); ?>
<style>
.et_right_sidebar #main-content .container:before {
    right: 0 !important;
}

#main{
    display: table;
    width: 100%;
    height: 100vh;
    text-align: center;
}

.fzf{
	  display: table-cell;
	  vertical-align: middle;
	  color: #a631f4;
}

.fzf h1{
	  font-size: 50px;
	  display: inline-block;
	  padding-right: 12px;
	  animation: type .5s alternate infinite;
}

[data-wipe] {
    display: inline-block;
    padding: 12px 18px;
    text-decoration: none;
    position: relative;
    border-radius: 3px;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    text-align: left;
    color: #fff;
    overflow: hidden;
    margin-top: 15px;
    background: #a631f4;
}

[data-wipe]:before, [data-wipe]:after {
  content: attr(data-wipe);
  padding-top: inherit;
  padding-bottom: inherit;
  white-space: nowrap;
  position: absolute;
  top: 0;
  overflow: hidden;
  color: #FFF;
  background: #8f60fc;
}
[data-wipe]:before {
  left: 0;
  text-indent: 18px;
  width: 0;
}
[data-wipe]:after {
  padding-left: inherit;
  padding-right: inherit;
  left: 100%;
  text-indent: calc(-100% - 36px);
  transition: 0.2s ease-in-out;
}
[data-wipe]:hover:before {
  width: 100%;
  transition: width 0.2s ease-in-out;
}
[data-wipe]:hover:after {
  left: 0;
  text-indent: 0;
  transition: 0s 0.2s ease-in-out;
}


@keyframes type{
	  from{box-shadow: inset -3px 0px 0px #231942;}
	  to{box-shadow: inset -3px 0px 0px transparent;}
}

</style>
<div id="main-content">
	<div class="container">
		<div id="content-area" class="clearfix">
			<article id="post-0" <?php post_class( 'et_pb_post not_found' ); ?>>
				<div id="main">
				    	<div class="fzf">
				        		<h1>Oops. 404!</h1>
				            <p>The page you are looking for does not exist</p>
				        <a href="<?php echo site_url(); ?>/dashboard/" class="btn" data-wipe="Take Me Home">Take Me Home</a>
				    	</div>
				</div>
			</article>
			
		</div>
	</div>
</div>

<?php

get_footer();