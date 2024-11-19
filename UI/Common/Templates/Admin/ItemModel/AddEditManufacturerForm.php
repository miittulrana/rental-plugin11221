<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Scripts
wp_enqueue_script('jquery');
wp_enqueue_script('jquery-validate');
wp_enqueue_script('fleet-management-admin');

// Styles
wp_enqueue_style('jquery-validate');
wp_enqueue_style('fleet-management-admin');
?>
<p>&nbsp;</p>
<div class="fleet-management-tabbed-admin">
<div id="container-inside">
   <span class="title">Add/Edit Manufacturer</span>
   <input type="button" value="Back To Manufacturer List" onclick="window.location.href='<?=esc_js($backToListURL);?>'" class="button-back"/>
   <hr />
   <form action="<?=esc_url($formAction);?>" method="POST" id="form1" enctype="multipart/form-data">
        <table cellpadding="5" cellspacing="2" border="0">
            <input type="hidden" name="manufacturer_id" value="<?=esc_attr($manufacturerId);?>"/>

<tr>
    <td><strong>Manufacturer Name:</strong></td>
    <td><input type="text" name="manufacturer_name" maxlength="100" value="<?=esc_attr($manufacturerName);?>" id="manufacturer_name" class="required form-input" /></td>
</tr>
<tr>
    <td><strong>Manufacturer Logo:</strong></td>
    <td><input type="file" name="manufacturer_logo" class="form-input" title="<?=esc_attr($lang['LANG_LOGO_TEXT']);?>" />
        <?php if($manufacturerLogoURL != ""): ?>
            <span>
                &nbsp;&nbsp;&nbsp;<a rel="collection" href="<?=esc_url($manufacturerLogoURL);?>" target="_blank">
                    <strong><?=esc_html($lang[$demoManufacturerLogo ? 'LANG_LOGO_VIEW_DEMO_TEXT' : 'LANG_LOGO_VIEW_TEXT']);?></strong>
                </a>
                &nbsp;&nbsp;&nbsp;&nbsp;<span >
                    <strong><?=esc_html($lang[$demoManufacturerLogo ? 'LANG_LOGO_UNSET_DEMO_TEXT' : 'LANG_LOGO_DELETE_TEXT']);?></strong>
                </span> &nbsp;
                <input type="checkbox" name="delete_manufacturer_logo"
                       title="<?=esc_attr($lang[$demoManufacturerLogo ? 'LANG_LOGO_UNSET_DEMO_TEXT' : 'LANG_LOGO_DELETE_TEXT']);?>" />
            </span>
        <?php else: ?>
            &nbsp;&nbsp;&nbsp;&nbsp; <strong><?=esc_html($lang['LANG_LOGO_NONE_TEXT']);?></strong>
        <?php endif; ?>
    </td>
</tr>
<tr>
    <td></td>
    <td><input type="submit" value="Save manufacturer" name="save_manufacturer" class="save-button"/></td>
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

