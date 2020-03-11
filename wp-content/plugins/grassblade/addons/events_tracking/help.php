<h2 name="events_tracking"><?php _e('Events Tracking','grassblade'); ?></h2>

<a href="#events_tracking" onclick="return showHideOptional('grassblade_pv_whatis');"><h3><img src="<?php echo get_bloginfo('wpurl')."/wp-content/plugins/grassblade/img/button.png"; ?>"/><span style="margin-left:10px;"><?php _e('What is Events tracking?','grassblade'); ?></span></h3></a>
<div id="grassblade_pv_whatis"  class="infoblocks"  style="display:none;">
<p>
<?php _e('Events tracking feature sends details to the LRS for different events. Every time someone performs an activity that is being tracked, an xAPI statement is sent to the LRS.','grassblade') ?>
</p>
</div>

<a href="#events_tracking" onclick="return showHideOptional('grassblade_pv_use');"><h3><img src="<?php echo get_bloginfo('wpurl')."/wp-content/plugins/grassblade/img/button.png"; ?>"/><span style="margin-left:10px;"><?php _e('How does GrassBlade decide which Pages/Posts to track?','grassblade'); ?></span></h3></a>
<div id="grassblade_pv_use"  class="infoblocks"  style="display:none;">
<p>
<?php _e('Based on the settings on Events Tracking Settings page, you can choose to track all Pages and Posts. Or, choose to track posts in specific categories or specific tags.','grassblade'); ?>
</p>
</div>
