<?php
/**
 * Template Name: submission Frontend Form
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package WordPress
 * @subpackage propel1_child_salient_child
 * @since 1.0.0
 */

acf_form_head();
get_header();
?>

    <section id="primary" class="content-area">
		<main id="main" class="site-main">
			<h1 class="mkt-page-header blue-txt">This is the title of this form</h1>
			<p class="mkt-header-txt">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin tempor magna vel neque porttitor, eu imperdiet dui pulvinar. Duis condimentum ipsum erat, ac euismod leo tristique vitae.</p>
			<p class="mkt-header-txt blue-txt">Fields with '*' are required.</p>

			<?php

			/* Start the Loop */
			while ( have_posts() ) :
				the_post();

				get_template_part( 'template-parts/content/content', 'page' );

				// If comments are open or we have at least one comment, load up the comment template.
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}

			endwhile; // End of the loop.
			?>

			<?php				$fields = array(
					'field_5e664fce8d818',	// description
					'field_5e644904db60d',	// list apps
					'field_5e644904db653',	// attach documents
					'field_5e6446ac21021',	// email
					'field_5e6446d021022',	// name
					'field_5e6446df21023',	// organization name
					'field_5e6446f021024',	// organization type
					'field_5e64473a21025',	// other specify
					'field_5e66437ed3739',	// evidence message
					'field_5e6641cfa9f1b',	// mhealth stage
					'field_5e645950006dc',	// mhealth support
					'field_5e666998b95b3',	// mhealth links
					'field_5e6649137d83a',	// use message
					'field_5e6459940655a',	// intended use
					'field_5e645a19cee8f',	// if education
					'field_5e645a5fcee90',	// education other
					'field_5e6642baa79ee',	// conditions message
					'field_5e645aea67df8',	// diabetes
					'field_5e645aea67e9a',	// diabetes other
					'field_5e645b2fb2322',	// cardiovascular
					'field_5e645b4db2323',	// cardiovascular other
					'field_5e645b71b2324',	// if neither
					'field_5e645bc4b2326',	// primary outcomes
					'field_5e645bebb2327',	// primary outcomes other
					'field_5e664965930fc',	// demographic message
					'field_5e645c9a40d25',	// gender
					'field_5e645c9a40dce',	// gender other
					'field_5e645c9a40e15',	// age
					'field_5e645c9a40f04',	// race
					'field_5e645c9a40ebd',	// race other
					'field_5e645c9a40f5a',	// setting
					'field_5e645c9a41003',	// setting other
					'field_5e645d4c07c3c',	// payer
					'field_5e645d6507c3d',	// payer other
					'field_5e645d8007c3e',	// number participates
					'field_5e66498bfe937',	// clinically message
					'field_5e645de8be75b',	// if medical device
					'field_5e645e03be75c',	// if in process
					'field_5e645e31be75d',	// 3rd party payers
					'field_5e645e5cbe75e',	// billing process
					'field_5e645e7cbe75f'	// number participates
				);
				acf_register_form(array(
					'id'		    	=> 'new-submission',
					'post_id'	    	=> 'new_post',
					'new_post'			=> array(
						'post_type'		=> 'submission',
						'post_status'	=> 'draft'
					),
					'post_title'		=> true,
					'post_content'  	=> true,
					'uploader'      	=> 'basic',
					'return'			=> home_url('thank-your-for-submitting-your-submission'),
					'fields'				=> $fields,
					'submit_value'		=> 'Submit this form'
		    	));
				// Load the form
				acf_form('new-submission');
			?>


		</main><!-- #main -->
	</section><!-- #primary -->

<?php
get_footer();