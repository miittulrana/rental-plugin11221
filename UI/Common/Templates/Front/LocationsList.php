<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Scripts
wp_enqueue_script('jquery');
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
?>
<div class="fleet-management-wrapper <?=esc_attr($extCSS_Prefix);?>wrapper fleet-management-locations-list <?=esc_attr($extCSS_Prefix);?>locations-list">
    <?php if($gotResults): ?>
        <?php foreach($locations as $location): ?>
            <div class="location">
<div class="location-description">
    <?php
    if($location['location_page_url'])
    {
        print('<a href="'.esc_url($location['location_page_url']).'" title="'.esc_attr($lang['LANG_LOCATION_VIEW_TEXT']).'">');
        print('<span class="location-name">'.$location['print_translated_location_name'].'</span>');
        print('</a>');
    } else
    {
        print('<span class="location-name">'.$location['print_translated_location_name'].'</span>');
    }
    ?>
    <br /><hr />

    <?php if($location['show_full_address'] != ''): ?>
        <div class="data-row">
            <i class="fa fa-map-signs" aria-hidden="true"></i>
            <span class="highlight"><?=esc_html($lang['LANG_ADDRESS_TEXT']);?>:</span>
            <?=$location['print_full_address'];?>
        </div>
    <?php endif; ?>

    <?php if($location['show_phone']): ?>
        <div class="data-row">
            <i class="fa fa-phone" aria-hidden="true"></i>
            <span class="highlight">&nbsp;<?=esc_html($lang['LANG_PHONE_TEXT']);?>:</span>
            <?=$location['print_phone'];?>
        </div>
    <?php endif; ?>

    <div class="action-buttons">
        <?php
        if($location['location_page_url'])
        {
            print('<div class="single-button"><a href="'.esc_url($location['location_page_url']).'" title="'.esc_attr($lang['LANG_LOCATION_VIEW_TEXT']).'">');
            print(esc_html($lang['LANG_LOCATION_VIEW_TEXT']));
            print('</div></a>');
        }
        ?>
    </div>
</div>
<div class="location-business-hours">
    <div class="section-title"><?=esc_html($lang['LANG_LOCATIONS_BUSINESS_HOURS_TEXT']);?></div><hr />
    <ul class="business-hours-list">
        <li>
            <i class="fa fa-clock-o" aria-hidden="true"></i>
            <span class="highlight"><?=esc_html($lang['LANG_MON_TEXT']);?>:</span>
            <?=$location['business_hours']['mon'];?>
        </li>
        <li>
            <i class="fa fa-clock-o" aria-hidden="true"></i>
            <span class="highlight"><?=esc_html($lang['LANG_TUE_TEXT']);?>:</span>
            <?=$location['business_hours']['mon'];?>
        </li>
        <li>
            <i class="fa fa-clock-o" aria-hidden="true"></i>
            <span class="highlight"><?=esc_html($lang['LANG_WED_TEXT']);?>:</span>
            <?=$location['business_hours']['mon'];?>
        </li>
        <li>
            <i class="fa fa-clock-o" aria-hidden="true"></i>
            <span class="highlight"><?=esc_html($lang['LANG_THU_TEXT']);?>:</span>
            <?=$location['business_hours']['thu'];?>
        </li>
        <li>
            <i class="fa fa-clock-o" aria-hidden="true"></i>
            <span class="highlight"><?=esc_html($lang['LANG_FRI_TEXT']);?>:</span>
            <?=$location['business_hours']['fri'];?>
        </li>
        <li>
            <i class="fa fa-clock-o" aria-hidden="true"></i>
            <span class="highlight"><?=esc_html($lang['LANG_SAT_TEXT']);?>:</span>
            <?=$location['business_hours']['sat'];?>
        </li>
        <li>
            <i class="fa fa-clock-o" aria-hidden="true"></i>
            <span class="highlight"><?=esc_html($lang['LANG_SUN_TEXT']);?>:</span>
            <?=$location['business_hours']['sun'];?>
        </li>
    </ul>
</div>
<div class="location-more">
    <?php if($location['print_lunch_hours']): ?>
        <div class="section-title"><?=esc_html($lang['LANG_LOCATION_LUNCH_TIME_TEXT']);?></div><hr />
        <ul class="lunch-hours-list">
            <li>
                <i class="fa fa-clock-o" aria-hidden="true"></i>
                <span class="highlight"><?=(esc_html($lang['LANG_MON_TEXT']).'-'.esc_html($lang['LANG_SUN_TEXT']));?>:</span>
                <?=$location['print_lunch_hours'];?>
            </li>
        </ul>
    <?php endif; ?>
    <div class="section-title"><?=esc_html($lang['LANG_LOCATION_FEES_TEXT']);?></div><hr />
    <ul class="fees-list">
        <li>
            <i class="fa fa-money" aria-hidden="true"></i>
            <span class="highlight"><?=esc_html($lang['LANG_PICKUP_TEXT']);?>:</span>
            <?=$location['unit_long_without_fraction_print']['pickup_fee_dynamic'];?>
        </li>
        <li>
            <i class="fa fa-money" aria-hidden="true"></i>
            <span class="highlight"><?=esc_html($lang['LANG_RETURN_TEXT']);?>:</span>
            <?=$location['unit_long_without_fraction_print']['return_fee_dynamic'];?>
        </li>
        <?php if($location['afterhours_pickup_allowed'] == 1): ?>
            <li>
                <i class="fa fa-money" aria-hidden="true"></i>
                <span class="highlight"><?=esc_html($lang['LANG_EARLY_PICKUP_TEXT']);?>:</span>
                <?=$location['unit_long_without_fraction_print']['afterhours_pickup_fee_dynamic'];?>
            </li>
        <?php endif; ?>
        <?php if($location['afterhours_return_allowed'] == 1): ?>
            <li>
                <i class="fa fa-money" aria-hidden="true"></i>
                <span class="highlight"><?=esc_html($lang['LANG_LATE_RETURN_TEXT']);?>:</span>
                <?=$location['unit_long_without_fraction_print']['afterhours_return_fee_dynamic'];?>
            </li>
        <?php endif; ?>
    </ul>
</div>
<div class="location-image">
    <?php if($location['location_thumb_4_url'] != ""): ?>
        <a class="fancybox" href="<?=esc_url($location['location_image_4_url']);?>" title="<?=$location['print_translated_location_name'];?>">
            <img src="<?=esc_url($location['location_thumb_4_url']);?>" alt="<?=$location['print_translated_location_name'];?>" />
        </a>
    <?php else: ?>
        &nbsp;
    <?php endif; ?>
</div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="no-locations-available"><?=esc_html($lang['LANG_LOCATIONS_NONE_AVAILABLE_TEXT']);?></div>
    <?php endif; ?>
</div>
<?php add_action('wp_footer', function() { // A workaround until #48098 will be resolved ( https://core.trac.wordpress.org/ticket/48098 ). Scripts are printed with the '20' priority. ?>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
    jQuery('.fancybox').fancybox();
});
</script>
<?php }, 100); ?>