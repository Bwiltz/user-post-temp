<h2 name="leaderboard"><?php _e("Leaderboard", "grassblade"); ?></h2>

<a href="#leaderboard" onclick="return showHideOptional('grassblade_leaderboard');"><h3><img src="<?php echo get_bloginfo('wpurl')."/wp-content/plugins/grassblade/img/button.png"; ?>"/><span style="margin-left:10px;"><?php _e("How can I show a Leaderboard?", "grassblade"); ?></span></h3></a>
<div id="grassblade_leaderboard"  class="infoblocks"  style="display:none;">
<p>
	<?php _e('Leaderboard can be added to any page by using the [gb_leaderboard] shortcode or <b> Leaderboard </b> gutenberg block. Following parameters can be used to customize:', 'grassblade'); ?> <br><br>
</p>
<p>
<?php _e('1. <b>id</b>: Optional. Defaults to same page. It can be xAPI Content ID or Page ID, or Post ID, or LearnDash Course/Lesson/Quiz ID. Shows Leaderboard for the specified page. If Course ID is given, it shows leaderboard based on sum of all xAPI Content on the specified course.', 'grassblade'); ?>
<br>
<?php _e('2. <b>allow</b>: Optional. Defaults to "all". Role ID or Capabilities can be provded in comma separated form. e.g. [gb_leaderboard id="1234" allow="administrator,edit_posts"]', 'grassblade'); ?>
<br>
<?php _e('2. <b>score</b>: Optional. Defaults to "score". User "percentage" if you want to show and make calculations based on percentage values. A course that sends only percentage value will use percentage values in both cases.', 'grassblade'); ?>
<br>
<?php _e('2. <b>limit</b>: Optional. Defaults to "20". This is the number or records to show in the Leaderboard. e.g. [gb_leaderboard id="1234" limit="10"] to show top 10 users for course with ID 1234.', 'grassblade'); ?>
</p>


</div>
