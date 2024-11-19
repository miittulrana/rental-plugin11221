<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1>
    <span><?=esc_html($lang['LANG_CLOSINGS_FOR_GLOBAL_LOCATIONS_TEXT']);?></span>
</h1>
<div class="closings-for-global-locations">
    <p class="big-text padded"><?=esc_html($lang['LANG_CLOSINGS_CLOSED_DATES_CLICK_ON_DATES_IN_CALENDAR_TEXT']);?>:</p>
    <div class="big-labels">
        <select name="location_id" class="location" title="<?=esc_attr($lang['LANG_LOCATION_GLOBAL_TEXT']);?>">
            <?=$trustedLocationDropdownOptionsHTML;?>
        </select>
        <input type="button" class="save-closed-dates" value="<?=esc_attr($lang['LANG_CLOSINGS_CLOSED_DATES_SAVE_TEXT']);?>" />
    </div>
    <?php foreach($closingsForGlobalLocations AS $closingsForGlobalLocation): ?>
        <div class="closed-dates-calendar closed-dates-<?=esc_attr($closingsForGlobalLocation['location_id']);?> box" style="display:none;"></div>
        <input type="hidden" name="selected_dates_<?=esc_attr($closingsForGlobalLocation['location_id']);?>" value="<?=esc_attr($closingsForGlobalLocation['closed_dates']);?>" class="selected-dates-<?=esc_attr($closingsForGlobalLocation['location_id']);?>" />
    <?php endforeach; ?>
    <input type="hidden" name="selected_dates" class="selected-dates" />
</div>
<script type="text/javascript">
jQuery(document).ready(function()
{
    'use strict';
    // Show selected location calendar and hide previously selected calendar
    FleetManagementAdmin.showClosingsCalendar('<?=esc_js($extCode);?>', 'closings-for-global-locations',0);
    var prevId = 0;
    jQuery('.closings-for-global-locations .location').on('change', function()
    {
        jQuery('.closings-for-global-locations .closed-dates-' + prevId).hide();
        FleetManagementAdmin.showClosingsCalendar('<?=esc_js($extCode);?>', 'closings-for-global-locations', this.value);
        prevId = this.value;
    });

    jQuery('.closings-for-global-locations .save-closed-dates').on( "click", function()
    {
        var selectedLocationId = jQuery('.closings-for-global-locations .location').val();
        var selectedDates = jQuery('.closings-for-global-locations .selected-dates-' + selectedLocationId).val();
        //console.log('Selected Dates:'); console.log(selectedDates);
        FleetManagementAdmin.saveClosings('<?=esc_js($extCode);?>', selectedLocationId, selectedDates);
    });
});
</script>
