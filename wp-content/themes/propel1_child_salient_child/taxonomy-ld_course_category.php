<?php
/* template for LD Category */

get_header();
// nectar_page_header($post->ID);
$options = get_nectar_theme_options();
global $post;
$post_slug     = $post->post_name;
$term          = get_queried_object();
$category_slug = $term->slug;
$category_name = $term->name;
$user_id       = get_current_user_id();

function can_user_access_content( $user_id, $post_id ) {
	//check if there's a force public on this content
	if ( get_post_meta( $post_id, '_wc_memberships_force_public', true ) === 'yes' ) {
		return true;
	}
	$args       = array( 'status' => array( 'active' ) );
	$plans      = wc_memberships_get_user_memberships( $user_id, $args );
	$user_plans = array();
	foreach ( $plans as $plan ) {
			array_push( $user_plans, $plan->plan_id );
	}
	$rules = wc_memberships()->get_rules_instance()->get_post_content_restriction_rules( $post_id );
	foreach ( $rules as $rule ) {
		if ( in_array( $rule->get_membership_plan_id(), $user_plans, true ) ) {
				return true;
		}
	}
	return false;
}

if ( current_user_can( 'administrator' ) ) { ?>
	<style>
	.woocommerce.woocommerce-info{
			color: #fff;
	}
	@media screen and (min-width: 1000px){
		.woocommerce.woocommerce-info{
			top: 183px;
		}
	}
	</style>
	<?php
}
?>
<div class="container-wrap" id="propel-course-overview-page-wrapper">
	<div id="page-header-bg">
		<div class="container">
			<h1><?php echo esc_html( $category_name ); ?></h1>
		</div>
	</div>
	<div class="container vc_row-fluid">
		<div class="sidebar vc_col-sm-3">
			<?php
			if ( can_user_access_content( get_current_user_id(), $post->ID ) ) {
				echo do_shortcode( '[my-courses-filters]' );
			} else {
				echo do_shortcode( '[public-membership-courses-filters]' );
			}
			echo do_shortcode( '[membership-filter-by-category]' );
			?>
		</div> <!--/sidebar vc_col-sm-3-->
		<div class="main-content vc_col-sm-9">
			<?php
			if ( can_user_access_content( get_current_user_id(), $post->ID ) ) {
				echo do_shortcode( "[my-membership-courses category='$category_slug']" );
			} else {
				echo ("The taxonomy-ld_course_category.php file needs a membership plan slug on line 71. Add that and remove this warning!!");
				echo do_shortcode( "[public-membership-courses plan='' category='$category_slug']" );
			}
			?>
		</div><!--/main-content vc_col-sm-9-->cs
	</div> <!-- /container vc_row-fluid-->
</div><!--/container-wrap-->

<?php get_footer(); ?>
