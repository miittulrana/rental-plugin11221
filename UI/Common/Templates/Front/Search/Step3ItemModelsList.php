<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Scripts
wp_enqueue_script('jquery');
wp_enqueue_script('jquery-validate');
wp_enqueue_script('jquery.mousewheel'); // Optional for fancyBox
if($settings['conf_load_fancybox_from_plugin'] == 1):
    wp_enqueue_script('fancybox');
endif;

// Styles
if($settings['conf_load_fancybox_from_plugin'] == 1):
    wp_enqueue_style('fancybox');
endif;
if($settings['conf_load_font_awesome_from_plugin'] == 1):
    wp_enqueue_style('font-awesome');
endif;
wp_enqueue_style('fleet-management-main');
if($newOrder == true && $settings['conf_universal_analytics_enhanced_ecommerce'] == 1):
    include 'Shared/Step3EnhancedEcommercePartial.php';
endif;
?>
<div class="fleet-management-wrapper <?=esc_attr($extCSS_Prefix);?>wrapper fleet-management-item-model-search-results <?=esc_attr($extCSS_Prefix);?>item-model-search-results">
    <?php
    if($complexPickup || $complexReturn):
        include 'Shared/LocationsSummaryComplexPartial.php';
    else:
        include 'Shared/LocationsSummarySimplePartial.php';
    endif;
    ?>
    <div id="search-results-title"><h2 class="search-label"><?=esc_html($lang['LANG_SEARCH_RESULTS_TEXT']);?></h2></div>
    <div class="content item-models-list-header">
        <div class="col1 item-data">
            <?=esc_html($lang['LANG_ITEM_MODEL_TEXT']);?>
        </div>
        <div class="col3 item-model-price">
            <?=esc_html($lang['LANG_TOTAL_TEXT']);?>
        </div>
        <?php if($settings['conf_deposit_enabled'] == 1): ?>
            <div class="col4 item-model-deposit">
                <?=esc_html($lang['LANG_DEPOSIT_TEXT']);?>
            </div>
        <?php endif; ?>
        <div class="col5 item-select">
            &nbsp;
        </div>
    </div>
    <?php
    foreach($itemModels AS $itemModel):
        include 'Shared/Step3SingleItemModelPartial.php';
    endforeach;
    ?>
    <?php if($newOrder == false): ?>
        <form action="" name="form1" id="form1" method="POST">
            <div class="buttons search-result-buttons">
                <input type="hidden" name="<?=esc_attr($extPrefix.$orderCodeParam);?>" value="<?=esc_attr($orderCode);?>" />
                <input type="hidden" name="<?=esc_attr($extPrefix);?>do_not_flush" value="yes" />
                <input type="submit" name="<?=esc_attr($extPrefix);?>cancel_order" value="<?=esc_html($lang['LANG_CANCEL_ORDER_TEXT']);?>" />
                <input type="submit" name="<?=esc_attr($extPrefix);?>do_search0" value="<?=esc_html($lang['LANG_ORDER_CHANGE_DATE_TIME_AND_LOCATION_TEXT']);?>" />
            </div>
        </form>
    <?php endif; ?>
    <div class="clear">&nbsp;</div>
</div>
<?php add_action('wp_footer', function() { // A workaround until #48098 will be resolved ( https://core.trac.wordpress.org/ticket/48098 ). Scripts are printed with the '20' priority. ?>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
    jQuery('.fancybox').fancybox();
});
</script>
<?php }, 100); ?>