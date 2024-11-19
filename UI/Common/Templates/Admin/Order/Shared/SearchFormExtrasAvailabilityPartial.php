<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1 class="search-title">
    30 Days / Search
</h1>
<div class="calendar-search-form">
	<form action="<?=esc_url(admin_url('admin.php'));?>" method="GET" class="form_extras_calendar">
        <input type="hidden" name="page" value="<?=esc_attr($objConf->getExtURL_Prefix());?>extras-availability-search-results" />
		<div class="col-date-field">
			<?=esc_html($lang['LANG_FROM_TEXT']);?> &nbsp;
			<input type="text" name="from_date" class="from_extras_calendar required" />
			<img src="<?=esc_url($staticURLs['PLUGIN_COMMON']['ADMIN_IMAGES'].'Month.png');?>" alt="Date Selector" class="extras_calendar_datepicker_from_image date-selector-image" />
			 &nbsp;&nbsp;&nbsp;&nbsp;
			<strong><?=esc_html($lang['LANG_TO_TEXT']);?></strong> &nbsp;
			<input type="text" name="till_date" class="to_extras_calendar required" />
			<img src="<?=esc_url($staticURLs['PLUGIN_COMMON']['ADMIN_IMAGES'].'Month.png');?>" alt="Date Selector" class="extras_calendar_datepicker_to_image date-selector-image" />
		</div>
		<div class="col-search">
			<input type="submit" value="Show Calendar" name="search_extras_calendar" />
		</div>
	</form>
</div>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
    jQuery.extend(jQuery.validator.messages, {
        required: "<?=esc_html($lang['LANG_REQUIRED_TEXT']);?>"
    });
	jQuery(".form_extras_calendar").validate();

	jQuery(".from_extras_calendar").datepicker({
		maxDate: "+365D",
		numberOfMonths: 2,
        dateFormat: '<?=esc_js($settings['conf_datepicker_date_format']);?>',
        firstDay: <?=esc_js(get_option('start_of_week'));?>
	});
	jQuery(".to_extras_calendar").datepicker({
		maxDate: "+365D",
		numberOfMonths: 2,
        dateFormat: '<?=esc_js($settings['conf_datepicker_date_format']);?>',
        firstDay: <?=esc_js(get_option('start_of_week'));?>
	});
	jQuery(".extras_calendar_datepicker_from_image").on( "click", function() {
		jQuery(".from_extras_calendar").datepicker("show");
	});
	jQuery(".extras_calendar_datepicker_to_image").on( "click", function() {
		jQuery(".to_extras_calendar").datepicker("show");
	});
});
</script>