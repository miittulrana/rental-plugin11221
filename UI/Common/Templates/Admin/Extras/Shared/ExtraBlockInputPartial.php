<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<script type="text/javascript">
jQuery(document).ready(function(){
    'use strict';
    jQuery('.start-date').datepicker({
        minDate: 0,
        maxDate: "+365D",
        numberOfMonths: 2,
        dateFormat: '<?=esc_js($settings['conf_datepicker_date_format']);?>',
        firstDay: <?=esc_js(get_option('start_of_week'));?>,
        onSelect: function(selected) {
    	    var date = jQuery(this).datepicker('getDate');
            if(date)
            {
                date.setDate(date.getDate());
            }
            jQuery('.end-date').datepicker("option","minDate", date);
        }
    });
 
    jQuery('.end-date').datepicker({
        minDate: 0,
        maxDate:"+365D",
        numberOfMonths: 2,
        dateFormat: '<?=esc_js($settings['conf_datepicker_date_format']);?>',
        firstDay: <?=esc_js(get_option('start_of_week'));?>,
        onSelect: function(selected) {
           jQuery('.end-date').datepicker("option","maxDate", selected)
        }
    });  
 jQuery('.start-date-datepicker').on( "click", function() {
    jQuery('.start-date').datepicker('show');
  });
 jQuery('.end-date-datepicker').on( "click", function() {
   jQuery('.end-date').datepicker('show');
  });
});
</script>
<form action="<?=esc_url($blockFormAction);?>" method="POST" class="block-form">
    <table cellpadding="0" cellspacing="7" border="0" align="left" class="extra-block-input-table">
        <tr>
            <td><strong>Start Date:<span class="is-required">*</span></strong></td>
            <td>
                <input type="text" name="start_date" value="<?=esc_attr($startDate);?>" class="start-date" readonly="readonly" />
                <img src="<?=esc_url($staticURLs['PLUGIN_COMMON']['ADMIN_IMAGES'].'Month.png');?>" class="start-date-datepicker" />
                <select name="start_time" class="start-time">
                    <?=$trustedStartTimeDropdownOptionsHTML;?>
                </select>
            </td>
        </tr>
        <tr>
            <td>
                <strong>End Date:<span class="is-required">*</span></strong>
            </td>
            <td>
                <input type="text" name="end_date" value="<?=esc_attr($endDate);?>" class="end-date" readonly="readonly"/>
                <img src="<?=esc_url($staticURLs['PLUGIN_COMMON']['ADMIN_IMAGES'].'Month.png');?>" class="end-date-datepicker" />
                <select name="end_time" class="end-time">
                    <?=$trustedEndTimeDropdownOptionsHTML;?>
                </select>
            </td>
        </tr>
        <tr>
            <td></td>
            <td><input type="submit" name="search_to_block" value="Search for Extras to Block" class="save-button"/></td>
        </tr>
    </table>
</form>
<script type="text/javascript">
jQuery().ready(function() {
    jQuery('.block-form').validate();

 });
</script> 