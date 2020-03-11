<link rel="stylesheet" type="text/css" href="<?php echo plugins_url() ?>/grassblade/assets/DataTables/datatables.min.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo plugins_url() ?>/grassblade/assets/DataTables/media/css/jquery.dataTables.min.css"/>
<link rel="stylesheet" type="text/css" href="<?php echo plugins_url() ?>/grassblade/addons/user_report/css/style.css"/>
<style>
.gb-user-info{
	background-color: <?=$bg_color ?>;
}
.gb-course-info {
	border: 2px solid <?=$bg_color ?> !important;
}
.gb-expand-filter button{
	background: <?=$bg_color ?> !important;
}
.xapi-content-link:hover{
	color: <?=$bg_color ?> !important;
}
.paginate_button:hover {
	background: <?=$bg_color ?> !important;
}
</style>
<script type="text/javascript" src="<?php echo plugins_url() ?>/grassblade/assets/DataTables/datatables.min.js"></script>
<div id="gb_user_report" class="gb-profile">
	<div class='gb-user-profile'><?php echo $profile_pic; ?></div>
	<div class='gb-user-info'>
		<h3><?php echo $user->display_name ?></h3>
		<a class='gb-edit-profile' href='<?php echo $edit_profile?>'><?php echo __("Edit profile"); ?></a>
		<div class='gb-course-data'>
			<div><p class="gb-data-value"><?php echo $total_xapi_contents; ?></p><p><?php echo __("Courses"); ?></p></div>
			<div><p class="gb-data-value"><?php echo $total_completed; ?></p><p><?php echo __("Completed"); ?></p></div>
			<div><p class="gb-data-value"><?php echo $total_in_progress; ?></p><p><?php echo __("In Progress"); ?></p></div>
			<div><p class="gb-data-value"><?php echo $avg_score.'%'; ?></p><p><?php echo __("Avg Score"); ?></p></div>
		</div>
		<div style="clear: both;"></div> 
	</div>
	<div class="gb-course-info">
		<div class="gb-status-filter">
			<label><b>Result Filter: </b>  </label>
			<select id="gb_result_filter">
				<option>All</option>
				<option>Passed</option>
				<option>Completed</option>
				<option>Failed</option>
				<option>In Progress</option>
			</select>
		</div>
		<div class="gb-expand-filter">
			<button id="gb_expand_btn" class="gb-collapsed" onclick="gb_expand_attempts('<?php echo $user->ID; ?>');"><b><span id="expand_text"><?php echo __("Expand All"); ?></span></b></button>
		</div>
		<table id="gb_report_table">
			<thead>
				<tr>
					<th style="min-width:55px;"><?php echo __("SNo."); ?></th>
					<th><?php echo __("Courses"); ?></th>
					<th style="min-width:64px;"><?php echo __("Score"); ?></th>
					<th style="min-width:88px;"><?php echo __("Status"); ?></th>
					<th><?php echo __("Time Spent"); ?></th>
					<th style="min-width:100px;"><?php echo __("Attempts"); ?></th>
				</tr>
			</thead>
			<tbody>
			<?php $sno = 1;
			 $Attempts = array();
			 $content_quiz_enable = array();
			 foreach ($xapi_contents as $key => $xapi_content) {
				$attempts['attempts_'.$xapi_content['content']->ID.'_'.$user->ID] = json_encode($xapi_content['attempts']);
				$content_report_enable[$xapi_content['content']->ID] = $xapi_content['quiz_report_enable'];
				?>
				<tr id="gb_row_<?php echo $xapi_content['content']->ID; ?>">
					<td scope="row" data-label="<?php echo __("SNo."); ?>">
						<span><?php echo $sno; ?></span>
					</td>
					<td data-label="<?php echo __("Courses"); ?>"><a class='xapi-content-link' href='<?php echo get_post_permalink($xapi_content['content']->ID); ?>'><?php echo $xapi_content['content']->post_title; ?></a></td>
					<td data-label="<?php echo __("Score"); ?>"><?php echo $xapi_content['best_score']; ?> </td>
					<td data-label="<?php echo __("Status"); ?>"><?php echo $xapi_content['content_status']; ?> </td>
					<td data-label="<?php echo __("Time Spent"); ?>"><?php echo $xapi_content['total_time_spent']; ?> </td>
					<?php if (!empty($xapi_content['attempts'])) { ?>
						<td data-label="<?php echo __("Attempts"); ?>"><a onclick='gb_get_score("<?php echo $xapi_content['content']->ID; ?>","<?php echo $user->ID; ?>");'><img class="gb-icon-img" src='<?php echo plugins_url(); ?>/grassblade/img/down-arrow.png' width="20px"></a></td>
					<?php } else { ?>
						<td data-label="<?php echo __("Attempts"); ?>"> -- </td>
					<?php } ?>
				</tr>
			<?php $sno++; } ?>
			</tbody>
		</table>
	</div>
</div>
<script type="text/javascript">
//<![CDATA[
	var gb_content_attempts = <?php echo json_encode($attempts); ?>;
	var gb_content_quiz_enable = <?php echo json_encode($content_report_enable); ?>;
	var total_xapi_contents = '<?php echo $total_xapi_contents; ?>';
//]]>
</script>
