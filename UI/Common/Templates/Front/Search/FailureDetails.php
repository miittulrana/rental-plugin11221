<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Styles
wp_enqueue_style('fleet-management-main');
?>
<div class="fleet-management-wrapper <?=esc_attr($extCSS_Prefix);?>wrapper fleet-management-search-failure <?=esc_attr($extCSS_Prefix);?>search-failure">
    <div class="failure-title"><?=esc_html($lang['LANG_ORDER_FAILURE_TEXT']);?></div>
    <div class="failure-content">
        <?=esc_br_html($errorMessages);?>
        <div class="buttons">
            <button type="submit" class="back-button" onclick="window.location.href='<?=get_site_url();?>'"><?=esc_html($lang['LANG_BACK_TEXT']);?></button>
        </div>
    </div>
    <div class="clear"> </div>
</div>