<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<h1 class="customer-search-title">
	All / Search
</h1>
<form action="<?=esc_url($customerSearchFormAction);?>" method="GET" class="customer-search-form">
    <input type="hidden" name="page" value="<?=esc_attr($customerSearchPage);?>" />
    <div class="customer-search-fields">
        <select name="account_id" title="<?=esc_attr($lang['LANG_USER_ACCOUNT_TEXT']);?>">
            <?=$usersDropdownOptionsHTML;?>
        </select> &nbsp;&nbsp;&nbsp;&nbsp;
        <select name="date_type" title="<?=esc_attr($lang['LANG_SEARCH_FOR_TEXT']);?>">
            <option value="DATE_CREATED"><?=esc_html($lang['LANG_CUSTOMER_DATE_CREATED_TEXT']);?></option>
            <option value="LAST_USED"><?=esc_html($lang['LANG_CUSTOMER_LAST_USED_TEXT']);?></option>
        </select> &nbsp;&nbsp;&nbsp;&nbsp;
        <strong><?=esc_html($lang['LANG_FROM_TEXT']);?></strong> &nbsp;
        <input type="text" name="from_date" class="from-date required" title="<?=esc_attr($lang['LANG_FROM_TEXT']);?>" />
        <img src="<?=esc_url($staticURLs['PLUGIN_COMMON']['ADMIN_IMAGES'].'Month.png');?>" alt="<?=esc_html($lang['LANG_DATE_SELECTOR_TEXT']);?>" class="from-datepicker date-selector-image" />
         &nbsp;&nbsp;&nbsp;&nbsp;
        <strong><?=esc_html($lang['LANG_TILL_TEXT']);?></strong> &nbsp;
        <input type="text" name="till_date" class="till-date required" title="<?=esc_attr($lang['LANG_TILL_TEXT']);?>" />
        <img src="<?=esc_url($staticURLs['PLUGIN_COMMON']['ADMIN_IMAGES'].'Month.png');?>" alt="<?=esc_html($lang['LANG_DATE_SELECTOR_TEXT']);?>" class="till-datepicker date-selector-image" />
    </div>
    <div class="col-search">
        <input type="submit" value="<?=esc_attr($lang['LANG_FIND_TEXT']);?>" name="search_customer_date" class="medium-button" />
        <input type="button" value="<?=esc_attr($lang['LANG_ADD_TEXT']);?>" class="wide-button"
           onclick="window.location.href='<?=esc_js($addNewCustomerURL);?>'"
        />
    </div>
</form>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
	jQuery('.customer-search-form').validate();

	jQuery('.customer-search-form .from-date').datepicker({
		maxDate: "+365D",
		numberOfMonths: 2,
        dateFormat: '<?=esc_js($settings['conf_datepicker_date_format']);?>',
        firstDay: <?=esc_js(get_option('start_of_week'));?>
	});
	jQuery('.customer-search-form .till-date').datepicker({
		maxDate: "+365D",
		numberOfMonths: 2,
        dateFormat: '<?=esc_js($settings['conf_datepicker_date_format']);?>',
        firstDay: <?=esc_js(get_option('start_of_week'));?>
	});
	jQuery('.customer-search-form .from-datepicker').on( "click", function() {
		jQuery('.customer-search-form .from-date').datepicker('show');
	});
	jQuery('.customer-search-form .till-datepicker').on( "click", function() {
		jQuery('.customer-search-form .till-date').datepicker('show');
	});
});
</script>