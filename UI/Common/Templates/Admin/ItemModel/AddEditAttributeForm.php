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
   <span class="title">Attribute Add/Edit</span>
   <input type="button" value="Back To Attributes List" onclick="window.location.href='<?=esc_js($backToListURL);?>'" class="button-back"/>
   <hr/>
   <form action="<?=esc_url($formAction);?>" method="POST" id="form1">
        <table cellpadding="5" cellspacing="2" border="0">
            <input type="hidden" name="attribute_id" value="<?=esc_attr($attributeId);?>"/>
            <input type="hidden" name="attribute_group_id" value="<?=esc_attr($attributeGroupId);?>"/>
            <tr>
              <td><strong>Attribute Title:</strong></td>
              <td><input type="text" name="attribute_title" value="<?=esc_attr($attributeTitle);?>" id="attribute_title" class="required" /></td>
            </tr>
            <tr>
              <td><strong>Attribute Group:</strong></td>
              <td><?=esc_html($attributeGroupName);?></td>
            </tr>
            <tr>
                <td></td>
                <td><input type="submit" value="Save attribute" name="save_attribute" class="save-button"/></td>
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