<div class="components-panel__row">
    <?php
        $value = get_post_meta($post_id, 'wdm_check_answer', true); //Write a code to   set a default value.
    ?>
    <div class="components-base-control__field">
        <input type="checkbox" class="components-text-control__input" id="wdm_check_answer" name="wdm_check_answer" value="1" <?php echo checked(1, $value, false); ?>
        ?>
        <label class="components-base-control__label" for="wdm_check_answer">
            <?php _e('Enable Question Retake', 'wdm_cae');?>
        </label>
    </div>
</div>
<div class="components-panel__row">
    <div class="components-base-control__field">
        <p class="description" id="wdm_check_answer-description"><?php _e('It will not display next question untill user chooses the correct answer', 'wdm_cae');?></p>
    </div>
</div>
<?php
wp_nonce_field('compulsory_check_answer_action', 'compulsory_check_answer_nonce');
/********************
 * Wordpress side template :
 * components-panel__row : class for each new row
 * components-base-control__field : for each settings, this will enable the settings classes/UI
 * components-text-control__input : for "input field"
 *  components-base-control__label : for "input labels"
 */