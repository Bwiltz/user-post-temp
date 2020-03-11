<h2 name="xapi_content"><?php _e('xAPI Content Manager','grassblade'); ?></h2>

<a href="#xapi_content" onclick="return showHideOptional('grassblade_cu_howto');"><h3><img src="<?php echo get_bloginfo('wpurl')."/wp-content/plugins/grassblade/img/button.png"; ?>"/><span style="margin-left:10px;"><?php _e('How to upload an xAPI (Tin Can) content package from Articulate or other provider?','grassblade'); ?></span></h3></a>
<div id="grassblade_cu_howto"  class="infoblocks"  style="display:none;">
<p>
<?php 
_e('xAPI Content menu option can be used to upload xAPI Content zip Package. You have to click on \'Add New\' under \'xAPI Content\' menu option. Write a title, select the zip package using uploader, select the version and hit publish. Simple!<br><br>
You can test the upload using the Preview button.<br><br>
Use metabox or xAPI Content block on any page or post to add the content on that page. Or, use the generated shortcode.<br><br>
Make sure you are using the right version. e.g. For a 0.90 Articulate Content you need to select 0.90 in both content uploader, and the shortcode.','grassblade'); ?>

</p>
<p>
<?php echo sprintf(__("Learn More: %s", "grassblade"), '<a href="https://www.nextsoftwaresolutions.com/kb/upload-xapi-content-on-wordpress-with-grassblade-xapi-plugin/" target="_blank">'.__("Upload xAPI Content", "grassblade").'</a>');  ?>
</p>
</div>
<a href="#xapi_nonxapicontent" onclick="return showHideOptional('grassblade_cu_nonxapi');"><h3><img src="<?php echo get_bloginfo('wpurl')."/wp-content/plugins/grassblade/img/button.png"; ?>"/><span style="margin-left:10px;"><?php _e('Can I upload Non xAPI (Tin Can) content package from Articulate or other provider?','grassblade'); ?></span></h3></a>
<div id="grassblade_cu_nonxapi"  class="infoblocks"  style="display:none;">
<p>
<?php _e('Currently you can upload non TinCan version of Articulate Studio, Articulate Storyline and Captivate packages using xAPI Content Upload tool. PageViews can be tracked for such packages if you have PageViews feature enabled on your GrassBlade version. Please contact us if you need support for more packages.','grassblade'); ?>

</p>
</div>
<a href="#xapi_completiontracking" onclick="return showHideOptional('grassblade_xapi_completiontracking');"><h3><img src="<?php echo get_bloginfo('wpurl')."/wp-content/plugins/grassblade/img/button.png"; ?>"/><span style="margin-left:10px;"><?php _e('How does completion tracking work?','grassblade'); ?></span></h3></a>
<div id="grassblade_xapi_completiontracking"  class="infoblocks"  style="display:none;">
<p>
<?php echo sprintf(__('Completion Tracking helps in getting content completion information back from the LRS and integrating with other actions. Currently its most relavent to xAPI Content posted on LearnDash lessons, topics, or quizzes. It works only with %s installed. If there are more ideas or requirements for other integrations please contact us.','grassblade'), '<a href="https://www.nextsoftwaresolutions.com/grassblade-lrs-experience-api/" target="_blank">GrassBlade LRS</a>'); ?>

</p>
</div>
<a href="#xapi_uploadlimit" onclick="return showHideOptional('grassblade_xapi_uploadlimit');"><h3><img src="<?php echo get_bloginfo('wpurl')."/wp-content/plugins/grassblade/img/button.png"; ?>"/><span style="margin-left:10px;"><?php _e('What to do if my filesize is larger than server upload limit?','grassblade'); ?></span></h3></a>
<div id="grassblade_xapi_uploadlimit"  class="infoblocks"  style="display:none;">
<p>
<?php _e("You have two options:", "grassblade"); ?><br>
<p>
<?php echo sprintf(__("1:  %s", "grassblade"), '<a href="http://www.nextsoftwaresolutions.com/direct-upload-of-tin-can-api-content-from-dropbox-to-wordpress-using-grassblade-xapi-companion/" target="_blank">'.__("Use dropbox upload method", "grassblade").'</a>');  ?>
</p>
<p><?php echo sprintf(__("2:  %s", "grassblade"), '<a href="http://www.nextsoftwaresolutions.com/increasing-file-upload-limit/" target="_blank">'.__("Increase the file upload limit", "grassblade").'</a>');  ?>

</div>

<h2 name="xapi_user_scores"><?php _e('User Score Shortcodes','grassblade'); ?></h2>
<a href="#xapi_user_scores" onclick="return showHideOptional('grassblade_xapi_user_scores');"><h3><img src="<?php echo get_bloginfo('wpurl')."/wp-content/plugins/grassblade/img/button.png"; ?>"/><span style="margin-left:10px;"><?php _e('Which user score shortcode is available and how to use it?','grassblade'); ?></span></h3></a>
<div id="grassblade_xapi_user_scores"  class="infoblocks"  style="display:none;">
<p>
<?php 
_e("Here are the shortcodes you can use for user scores: <br>
1. [grassblade_user_score show='total_score'] : Total User Score on all xAPI Content combined. <br>
2. [grassblade_user_score show='average_percentage'] : Average Percentage value scored on all xAPI Content by the user. <br>
3. [grassblade_user_score show='badgeos_points'] : Total BadgeOS Points scored by the user. <br>
4. [grassblade_user_score show='total_score' add='badgeos_points'] : Total User Score on xAPI Contents + BadgeOS Points scored by the user. <br>
<br>
Instead of total scores, score of specific xAPI Content can also be shown using the above shortcode by adding a content_id parameter. e.g.<Br>
[grassblade_user_score show='total_score' content_id=1234] : Score in xAPI Content with ID 1234 by the current user.
<br>
<br>
You can also add from <b>User Score</b> gutenberg block.
",'grassblade'); ?>

</p>
</div>