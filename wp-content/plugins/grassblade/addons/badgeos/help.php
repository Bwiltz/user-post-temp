
<h2 name="xapi_badgeos"><?php _e('BadgeOS Integration','grassblade'); ?></h2>
<a href="#xapi_badgeos" onclick="return showHideOptional('grassblade_xapi_badgeos');"><h3><img src="<?php echo get_bloginfo('wpurl')."/wp-content/plugins/grassblade/img/button.png"; ?>"/><span style="margin-left:10px;"><?php _e('What does the BadgeOS Integration do?','grassblade'); ?></span></h3></a>
<div id="grassblade_xapi_badgeos"  class="infoblocks"  style="display:none;">
<p>
<?php 
_e("BadgeOS Integration allows BadgeOS triggers to work even when the xAPI Content is added on a LearnDash page. A bug in BadgeOS LearnDash Integration causes the BadgeOS triggers to not work when the Mark Complete button is removed by GrassBlade. 

	Hence, if you have BadgeOS Installed, the Mark Complete button is added back. And users will have to click the Mark Complete button after completing the xAPI Content. Clicking the LearnDash Mark Complete button will award any Badges and BadgeOS Points if configured for that LearnDash Lesson, Topic or Quiz. 
",'grassblade'); ?>

</p>
</div>

<a href="#xapi_badgeos" onclick="return showHideOptional('grassblade_xapi_badgeos_earned');"><h3><img src="<?php echo get_bloginfo('wpurl')."/wp-content/plugins/grassblade/img/button.png"; ?>"/><span style="margin-left:10px;"><?php _e('What is Badge Earned Statement?','grassblade'); ?></span></h3></a>

<div id="grassblade_xapi_badgeos_earned"  class="infoblocks"  style="display:none;">
<p>
<?php 
_e("A statement is sent to the LRS with verb 'earned', every time a new badge is earned by the user, or a badge step is completed. This way badge earned is tracked in the LRS as well.
",'grassblade'); ?>
</p>
</div>