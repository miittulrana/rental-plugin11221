<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Scripts
wp_enqueue_script('jquery');
wp_enqueue_script('jquery-ui-core'); // NOTE: We need it for datatables & datepicker in search params
wp_enqueue_script('datatables-jquery-datatables');
wp_enqueue_script('jquery-ui-datepicker', array('jquery','jquery-ui-core'));
wp_enqueue_script('jquery-ui-datepicker-locale');
wp_enqueue_script('jquery-validate');
wp_enqueue_script('fleet-management-admin');

// Styles
wp_enqueue_style('jquery-ui-theme');
wp_enqueue_style('jquery-validate');
wp_enqueue_style('fleet-management-admin');
?>
<p>&nbsp;</p>
<div class="fleet-management-tabbed-admin">
<div id="container-inside">
    <span class="title">Add/Edit <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Class</span>
    <input type="button" value="Back To <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Classes List" onclick="window.location.href='<?=esc_js($backToListURL);?>'" class="button-back"/>
    <hr/>
    <form action="<?=esc_url($formAction);?>" method="POST" id="form1">
        <table cellpadding="5" cellspacing="2" border="0">
            <input type="hidden" name="class_id" value="<?=esc_attr($classId);?>"/>
            <tr>
            <td><strong>Class Name:</strong></td>
                <td>
                    <input type="text" name="class_name" value="<?=esc_attr($className);?>" id="class_name" class="required" />
                </td>
            </tr>
            <tr>
                <td><strong>Class Order:</strong></td>
                <td>
                    <input type="text" name="class_order" value="<?=esc_attr($classOrder);?>" id="class_order" />
                    <em><?=($classId > 0 ? '' : '(optional, leave blank to add to the end)');?></em>
                </td>
            </tr>
            <tr>
                <td></td>
                <td><input type="submit" value="Save class" name="save_class" class="save-button"/></td>
            </tr>
        </table>
    </form>
</div>
</div>
<script type="text/javascript">
jQuery().ready(function() {
    'use strict';
		jQuery("#form1").validate();
});
</script>