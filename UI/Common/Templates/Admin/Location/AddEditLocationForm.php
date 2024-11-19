<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Scripts
wp_enqueue_script('jquery');
wp_enqueue_script('jquery-ui-core'); // NOTE: We need it for datatables & datepicker in search params
wp_enqueue_script('datatables-jquery-datatables');
wp_enqueue_script('jquery-validate');
wp_enqueue_script('fleet-management-admin');

// Styles
wp_enqueue_style('jquery-validate');
wp_enqueue_style('fleet-management-admin');
?>
<p>&nbsp;</p>
<div class="fleet-management-tabbed-admin">
<div id="container-inside" >
    <span class="title">Location Add/Edit</span>
    <input type="button" value="Back To Location List" onclick="window.location.href='<?=esc_js($backToListURL);?>'" class="button-back"/>
    <hr/>
    <form action="<?=esc_url($formAction);?>" method="POST" id="form1" enctype="multipart/form-data">
        <table cellpadding="5" cellspacing="2" border="0">
            <input type="hidden" name="location_id" value="<?=esc_attr($locationId);?>"/>

<tr>
    <td class="label"><strong>Location Name:<span class="is-required">*</span></strong></td>
    <td><input type="text" name="location_name" value="<?=esc_attr($locationName);?>" id="location_name" class="required location-form-input" /></td>
</tr>
<?php if($networkEnabled): ?>
     <tr>
        <td class="label"><strong>Location Code:<span class="is-required">*</span></strong></td>
        <td><input type="text" name="location_code" maxlength="50" value="<?=esc_attr($locationUniqueIdentifier);?>" id="location_code" class="required location-form-input" /><br />
            <em>(Used when plugin is network-enabled in multisite mode)</em></td>
    </tr>
<?php endif; ?>
<?php if($locationPagesDropdown): ?>
    <tr>
        <td><strong>Location Page:</strong></td>
        <td><?=$locationPagesDropdown;?></td>
    </tr>
<?php endif; ?>
<tr>
    <td class="label"><strong>Street Address:</strong></td>
    <td><input type="text" name="street_address" value="<?=esc_attr($streetAddress);?>" class="location-form-input" /></td>
</tr>
<tr>
    <td class="label"><strong>City:</strong></td>
    <td><input type="text" name="city" value="<?=esc_attr($city);?>" class="location-form-input" /></td>
</tr>
<tr>
    <td class="label"><strong>State:</strong></td>
    <td><input type="text" name="state" value="<?=esc_attr($state);?>" class="location-form-input" /></td>
</tr>
<tr>
    <td class="label"><strong>Zip Code:</strong></td>
    <td><input type="text" name="zip_code" value="<?=esc_attr($zipCode);?>" class="location-form-input" /></td>
</tr>
<tr>
    <td class="label"><strong>Country:</strong></td>
    <td><input type="text" name="country" value="<?=esc_attr($country);?>" class="location-form-input"  /></td>
</tr>
<tr>
    <td class="label"><strong>Phone:</strong></td>
    <td><input type="text" name="phone" value="<?=esc_attr($phone);?>" class="location-form-input" /></td>
</tr>
<tr>
    <td class="label"><strong>E-mail:</strong></td>
    <td><input type="text" name="email" value="<?=esc_attr($email);?>" class="location-form-input" /></td>
</tr>
<tr>
    <td class="label"><strong>Pick-up Fee:<span class="is-required">*</span></strong></td>
    <td>
        <?=esc_html($settings['conf_currency_symbol']);?>
        <input type="text" name="pickup_fee" value="<?=esc_attr($pickupFee);?>" id="pickup_fee" class="required number" size="4" />
        <em>(excl. <?=esc_html($lang['LANG_TAX_SHORT_TEXT']);?>)</em>
    </td>
</tr>
<tr>
    <td class="label"><strong>Return Fee:<span class="is-required">*</span></strong></td>
    <td>
        <?=esc_html($settings['conf_currency_symbol']);?>
        <input type="text" name="return_fee" value="<?=esc_attr($returnFee);?>" id="return_fee" class="required number" size="4" />
        <em>(excl. <?=esc_html($lang['LANG_TAX_SHORT_TEXT']);?>)</em>
    </td>
</tr>
<tr>
    <td class="label" colspan="2" align="center"><br /><strong><?=esc_html($lang['LANG_LOCATIONS_BUSINESS_HOURS_TEXT']);?></strong></td>
</tr>
<tr>
    <td align="right" class="location-form-td"><?=esc_html($lang['LANG_MONDAYS_TEXT']);?>:</td>
    <td>
        <select name="open_time_mon" class="open-time-mon">
            <?=$trustedOpenTimeMondaysDropdownOptionsHTML;?>
        </select> -
        <select name="close_time_mon" class="close-time-mon">
            <?=$trustedCloseTimeMondaysDropdownOptionsHTML;?>
        </select> &nbsp;
        <input type="checkbox" id="open_mondays" name="open_mondays" value="yes"<?=$openMondays;?>/> Open
    </td>
</tr>
<tr>
    <td align="right" class="location-form-td"><?=esc_html($lang['LANG_TUESDAYS_TEXT']);?>:</td>
    <td>
        <select name="open_time_tue" class="open-time-tue">
            <?=$trustedOpenTimeTuesdaysDropdownOptionsHTML;?>
        </select> -
        <select name="close_time_tue" class="close-time-tue">
            <?=$trustedCloseTimeTuesdaysDropdownOptionsHTML;?>
        </select> &nbsp;
        <input type="checkbox" id="open_tuesdays" name="open_tuesdays" value="yes"<?=$openTuesdays;?>/> Open
    </td>
</tr>
<tr>
    <td align="right" class="location-form-td"><?=esc_html($lang['LANG_WEDNESDAYS_TEXT']);?>:</td>
    <td>
        <select name="open_time_wed" class="open-time-wed">
            <?=$trustedOpenTimeWednesdaysDropdownOptionsHTML;?>
        </select> -
        <select name="close_time_wed" class="close-time-wed">
            <?=$trustedCloseTimeWednesdaysDropdownOptionsHTML;?>
        </select> &nbsp;
        <input type="checkbox" id="open_wednesdays" name="open_wednesdays" value="yes"<?=$openWednesdays;?>/> Open
    </td>
</tr>
<tr>
    <td align="right" class="location-form-td"><?=esc_html($lang['LANG_THURSDAYS_TEXT']);?>:</td>
    <td>
        <select name="open_time_thu" class="open-time-thu">
            <?=$trustedOpenTimeThursdaysDropdownOptionsHTML;?>
        </select> -
        <select name="close_time_thu" class="close-time-thu">
            <?=$trustedCloseTimeThursdaysDropdownOptionsHTML;?>
        </select> &nbsp;
        <input type="checkbox" id="open_thursdays" name="open_thursdays" value="yes"<?=$openThursdays;?>/> Open
    </td>
</tr>
<tr>
    <td align="right" class="location-form-td"><?=esc_html($lang['LANG_FRIDAYS_TEXT']);?>:</td>
    <td>
        <select name="open_time_fri" class="open-time-fri">
            <?=$trustedOpenTimeFridaysDropdownOptionsHTML;?>
        </select> -
        <select name="close_time_fri" class="close-time-fri">
            <?=$trustedCloseTimeFridaysDropdownOptionsHTML;?>
        </select> &nbsp;
        <input type="checkbox" id="open_fridays" name="open_fridays" value="yes"<?=$openFridays;?>/> Open
    </td>
</tr>
<tr>
    <td align="right" class="location-form-td"><?=esc_html($lang['LANG_SATURDAYS_TEXT']);?>:</td>
    <td>
        <select name="open_time_sat" class="open-time-sat">
            <?=$trustedOpenTimeSaturdaysDropdownOptionsHTML;?>
        </select> -
        <select name="close_time_sat" class="close-time-sat">
            <?=$trustedCloseTimeSaturdaysDropdownOptionsHTML;?>
        </select> &nbsp;
        <input type="checkbox" id="open_saturdays" name="open_saturdays" value="yes"<?=$openSaturdays;?>/> Open
    </td>
</tr>
<tr>
    <td align="right" class="location-form-td"><?=esc_html($lang['LANG_SUNDAYS_TEXT']);?>:</td>
    <td>
        <select name="open_time_sun" class="open-time-sun">
            <?=$trustedOpenTimeSundaysDropdownOptionsHTML;?>
        </select> -
        <select name="close_time_sun" class="close-time-sun">
            <?=$trustedCloseTimeSundaysDropdownOptionsHTML;?>
        </select> &nbsp;
        <input type="checkbox" id="open_sundays" name="open_sundays" value="yes"<?=$openSundays;?>/> Open
    </td>
</tr>
<tr>
    <td class="label" colspan="2" align="center"><strong><?=esc_html($lang['LANG_LOCATION_LUNCH_TIME_TEXT']);?></strong></td>
</tr>
<tr>
    <td align="right" class="location-form-td"><?=esc_html($lang['LANG_MON_TEXT']);?> - <?=esc_html($lang['LANG_SUN_TEXT']);?>:</td>
    <td>
        <select name="lunch_start_time" class="lunch-start-time">
            <?=$trustedLunchStartTimeDropdownOptionsHTML;?>
        </select> -
        <select name="lunch_end_time" class="lunch-end-time">
            <?=$trustedLunchEndTimeDropdownOptionsHTML;?>
        </select> &nbsp;
        <input type="checkbox" id="lunch_enabled" name="lunch_enabled" value="yes"<?=$lunchEnabled;?>/> Enabled
    </td>
</tr>
<tr>
    <td colspan="2"><br /></td>
</tr>
<tr>
    <td><strong>Big Map:<br />(Image)</strong></td>
    <td><input type="file" name="location_image_1" title="<?=esc_attr($lang['LANG_IMAGE_TEXT']);?>" />
        <?php if($locationImage1_URL != ""): ?>
            <span>
                &nbsp;&nbsp;&nbsp;<a rel="collection" href="<?=esc_url($locationImage1_URL);?>" target="_blank">
                    <strong><?=esc_html($lang[$demoLocationImage1 ? 'LANG_IMAGE_VIEW_DEMO_TEXT' : 'LANG_IMAGE_VIEW_TEXT']);?></strong>
                </a>
                &nbsp;&nbsp;&nbsp;&nbsp;<span>
                    <strong><?=esc_html($lang[$demoLocationImage1 ? 'LANG_IMAGE_UNSET_DEMO_TEXT' : 'LANG_IMAGE_DELETE_TEXT']);?></strong>
                </span> &nbsp;
                <input type="checkbox" name="delete_location_image_1"
                       title="<?=esc_attr($lang[$demoLocationImage1 ? 'LANG_IMAGE_UNSET_DEMO_TEXT' : 'LANG_IMAGE_DELETE_TEXT']);?>" />
            </span>
        <?php else: ?>
            &nbsp;&nbsp;&nbsp;&nbsp; <strong><?=esc_html($lang['LANG_IMAGE_NONE_TEXT']);?></strong>
        <?php endif; ?>
    </td>
</tr>
<tr>
    <td><strong>Outside - Street View:<br />(Image)</strong></td>
    <td><input type="file" name="location_image_2" title="<?=esc_attr($lang['LANG_IMAGE_TEXT']);?>" />
        <?php if($locationImage2_URL != ""): ?>
            <span>
                &nbsp;&nbsp;&nbsp;<a rel="collection" href="<?=esc_url($locationImage2_URL);?>" target="_blank">
                    <strong><?=esc_html($lang[$demoLocationImage2 ? 'LANG_IMAGE_VIEW_DEMO_TEXT' : 'LANG_IMAGE_VIEW_TEXT']);?></strong>
                </a>
                &nbsp;&nbsp;&nbsp;&nbsp;<span >
                    <strong><?=esc_html($lang[$demoLocationImage2 ? 'LANG_IMAGE_UNSET_DEMO_TEXT' : 'LANG_IMAGE_DELETE_TEXT']);?></strong>
                </span> &nbsp;
                <input type="checkbox" name="delete_location_image_2"
                       title="<?=esc_attr($lang[$demoLocationImage2 ? 'LANG_IMAGE_UNSET_DEMO_TEXT' : 'LANG_IMAGE_DELETE_TEXT']);?>" />
            </span>
        <?php else: ?>
            &nbsp;&nbsp;&nbsp;&nbsp; <strong><?=esc_html($lang['LANG_IMAGE_NONE_TEXT']);?></strong>
        <?php endif; ?>
    </td>
</tr>
<tr>
    <td><strong>Inside - Office:<br />(Image)</strong></td>
    <td><input type="file" name="location_image_3" title="<?=esc_attr($lang['LANG_IMAGE_TEXT']);?>" />
        <?php if($locationImage3_URL != ""): ?>
            <span>
                &nbsp;&nbsp;&nbsp;<a rel="collection" href="<?=esc_url($locationImage3_URL);?>" target="_blank">
                    <strong><?=esc_html($lang[$demoLocationImage3 ? 'LANG_IMAGE_VIEW_DEMO_TEXT' : 'LANG_IMAGE_VIEW_TEXT']);?></strong>
                </a>
                &nbsp;&nbsp;&nbsp;&nbsp;<span >
                    <strong><?=esc_html($lang[$demoLocationImage3 ? 'LANG_IMAGE_UNSET_DEMO_TEXT' : 'LANG_IMAGE_DELETE_TEXT']);?></strong>
                </span> &nbsp;
                <input type="checkbox" name="delete_location_image_3"
                       title="<?=esc_attr($lang[$demoLocationImage3 ? 'LANG_IMAGE_UNSET_DEMO_TEXT' : 'LANG_IMAGE_DELETE_TEXT']);?>" />
            </span>
        <?php else: ?>
            &nbsp;&nbsp;&nbsp;&nbsp; <strong><?=esc_html($lang['LANG_IMAGE_NONE_TEXT']);?></strong>
        <?php endif; ?>
    </td>
</tr>
<tr>
    <td><strong>Small List Map:<br />(Image)</strong></td>
    <td><input type="file" name="location_image_4" title="<?=esc_attr($lang['LANG_IMAGE_TEXT']);?>" />
        <?php if($locationImage4_URL != ""): ?>
            <span>
                &nbsp;&nbsp;&nbsp;<a rel="collection" href="<?=esc_url($locationImage4_URL);?>" target="_blank">
                    <strong><?=esc_html($lang[$demoLocationImage4 ? 'LANG_IMAGE_VIEW_DEMO_TEXT' : 'LANG_IMAGE_VIEW_TEXT']);?></strong>
                </a>
                &nbsp;&nbsp;&nbsp;&nbsp;<span >
                    <strong><?=esc_html($lang[$demoLocationImage4 ? 'LANG_IMAGE_UNSET_DEMO_TEXT' : 'LANG_IMAGE_DELETE_TEXT']);?></strong>
                </span> &nbsp;
                <input type="checkbox" name="delete_location_image_4"
                       title="<?=esc_attr($lang[$demoLocationImage4 ? 'LANG_IMAGE_UNSET_DEMO_TEXT' : 'LANG_IMAGE_DELETE_TEXT']);?>" />
            </span>
        <?php else: ?>
            &nbsp;&nbsp;&nbsp;&nbsp; <strong><?=esc_html($lang['LANG_IMAGE_NONE_TEXT']);?></strong>
        <?php endif; ?>
    </td>
</tr>
<tr>
    <td><strong>After Hours<br />Pick-up:</strong></td>
    <td>
        &nbsp;<input type="checkbox" id="afterhours_pickup_allowed" name="afterhours_pickup_allowed"<?=$afterHoursPickupAllowedChecked;?>/> Allowed
    </td>
</tr>
<tr>
    <td class="label"><strong>After Hours<br />Pick-up Location:</strong></td>
    <td>
        <select name="afterhours_pickup_location_id" id="afterhours_pickup_location_id">
            <?=$trustedAfterHoursPickupDropdownOptionsHTML;?>
        </select>
    </td>
</tr>
<tr>
    <td class="label"><strong>After Hours<br />Pick-up Fee:</strong></td>
    <td>
        <?=esc_html($settings['conf_currency_symbol']);?>
        <input type="text" name="afterhours_pickup_fee" value="<?=esc_attr($afterHoursPickupFee);?>" id="afterhours_pickup_fee" class="number" size="4" />
        <em>(<?=($locationId > 0 ? 'excl. '.esc_html($lang['LANG_TAX_SHORT_TEXT']) : 'optional, excl. '.esc_html($lang['LANG_TAX_SHORT_TEXT']));?>)</em>
    </td>
</tr>
<tr>
    <td><strong>After Hours<br />Return:</strong></td>
    <td>
        &nbsp;<input type="checkbox" id="afterhours_return_allowed" name="afterhours_return_allowed"<?=$afterHoursReturnAllowedChecked;?>/> Allowed
    </td>
</tr>
<tr>
    <td class="label"><strong>After Hours<br />Return Location:</strong></td>
    <td>
        <select name="afterhours_return_location_id" id="afterhours_return_location_id">
            <?=$trustedAfterHoursReturnDropdownOptionsHTML;?>
        </select>
    </td>
</tr>
<tr>
    <td class="label"><strong>After Hours<br />Return Fee:</strong></td>
    <td>
        <?=esc_html($settings['conf_currency_symbol']);?>
        <input type="text" name="afterhours_return_fee" value="<?=esc_attr($afterHoursReturnFee);?>" id="afterhours_return_fee" class="number" size="4" />
        <em>(<?=($locationId > 0 ? 'excl. '.esc_html($lang['LANG_TAX_SHORT_TEXT']) : 'optional, excl. '.esc_html($lang['LANG_TAX_SHORT_TEXT']));?>)</em>
    </td>
</tr>
<tr>
    <td><strong>Location Order:</strong></td>
    <td>
        <input type="text" name="location_order" value="<?=esc_attr($locationOrder);?>" id="location_order" class="number" />
        <em><?=($locationId > 0 ? '' : '(optional, leave blank to add to the end)');?></em>
    </td>
</tr>

<tr>
    <td><strong>Is On Remote Website:</strong></td>
    <td>
        &nbsp;<input type="checkbox" id="on_remote_website" name="on_remote_website"<?=$onRemoteWebsiteChecked;?> disabled="disabled"/>
    </td>
</tr>

<tr>
    <td class="label"></td>
    <td><input type="submit" value="Save Location" name="save_location" class="save-button"/></td>
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