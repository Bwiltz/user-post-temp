<?php get_header(); ?>

<?php nectar_page_header( $post->ID ); ?>

<div class="container-wrap">
	<div class="container main-content">
		<div class="row">
			<?php
			//breadcrumbs
			if ( function_exists( 'yoast_breadcrumb' ) && ! is_home() && ! is_front_page() ) {
				yoast_breadcrumb( '<p id="breadcrumbs">', '</p>' );
			}
			//buddypress
			global $bp;
			if ( $bp && ! bp_is_blog_page() ) {
				echo '<h1>' . get_the_title() . '</h1>';
			}
			//wp post loop
			if ( have_posts() ) {
				while ( have_posts() ) {
					the_post();
					the_content();
					if ( get_field( 'force_lesson_order', $post->ID ) ) {
						?>
						<script>
							jQuery( document ).ready(function() {
								let disabledLessonLink = jQuery("#lessons_list a.notcompleted").not(":eq(0)");
								disabledLessonLink.addClass( "disabled" );
								disabledLessonLink.click(function(e) {
									e.preventDefault();
								});
							});
						</script>
						<?php
					}
				}
			}
			?>
		</div><!-- /row -->
	</div><!-- /container main-content -->
</div> <!-- /container-wrap -->
<script type="text/javascript">
var blurred = false;
window.onblur = function() { blurred = true; };
window.onfocus = function() { blurred && (location.reload()); };
</script>
<?php get_footer(); ?>
