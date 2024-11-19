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
<div class="fleet-management-wrapper <?=esc_attr($extCSS_Prefix);?>wrapper fleet-management-single-location <?=esc_attr($extCSS_Prefix);?>single-location">
    <div class="car-rental-list-single-location">
        <div class="location-images">
            <div class="location-big-image">
                <?php if($location['location_big_thumb_1_url']): ?>
                    <a class="location-main-image fancybox" rel="group" href="<?=esc_url($location['location_image_1_url']);?>" title="<?=$location['print_translated_location_name'];?>">
                        <img src="<?=esc_url($location['location_big_thumb_1_url']);?>" alt="<?=$location['print_translated_location_name'];?>" /><br />
                    </a>
                <?php else: ?>
                    &nbsp;
                <?php endif; ?>
            </div>
            <div class="location-small-images">
                <?php if($location['location_mini_thumb_1_url'] && ($location['location_mini_thumb_2_url'] || $location['location_mini_thumb_3_url'])): ?>
                    <div class="location-small-image">
                        <a class="location-image-1 fancybox" rel="group" href="<?=esc_url($location['location_image_1_url']);?>" title="<?=$location['print_translated_location_name'];?>">
                            <img src="<?=esc_url($location['location_mini_thumb_1_url']);?>" title="<?=esc_url($location['location_big_thumb_1_url']);?>" alt="<?=$location['print_translated_location_name'];?>" />
                        </a>
                    </div>
                <?php endif; ?>
                <?php if($location['location_mini_thumb_2_url']): ?>
                    <div class="location-small-image">
                        <a class="location-image-2 fancybox" rel="group" href="<?=esc_url($location['location_image_2_url']);?>" title="<?=$location['print_translated_location_name'];?>">
                            <img src="<?=esc_url($location['location_mini_thumb_2_url']);?>" title="<?=esc_url($location['location_big_thumb_2_url']);?>" alt="<?=$location['print_translated_location_name'];?>" />
                        </a>
                    </div>
                <?php endif; ?>
                <?php if($location['location_mini_thumb_3_url']): ?>
                    <div class="location-small-image">
                        <a class="location-image-3 fancybox" rel="group" href="<?=esc_url($location['location_image_3_url']);?>" title="<?=$location['print_translated_location_name'];?>">
                            <img src="<?=esc_url($location['location_mini_thumb_3_url']);?>" title="<?=esc_url($location['location_big_thumb_3_url']);?>" alt="<?=$location['print_translated_location_name'];?>" />
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="location-description">
            <div class="location-more">
                <?php if($location['show_full_address'] || $location['show_phone']): ?>
                    <div class="section-title"><?=esc_html($lang['LANG_CONTACT_DETAILS_TEXT']);?></div><hr />
                <?php endif; ?>
                <?php if($location['show_full_address']): ?>
                    <div class="data-row">
                        <i class="fa fa-map-signs" aria-hidden="true"></i>
                        <span class="highlight"><?=esc_html($lang['LANG_ADDRESS_TEXT']);?>:</span>
                        <?=$location['print_full_address'];?>
                    </div>
                <?php endif; ?>

                <?php if($location['show_phone']): ?>
                    <div class="data-row">
                        <i class="fa fa-phone" aria-hidden="true"></i>
                        <span class="highlight"><?=esc_html($lang['LANG_PHONE_TEXT']);?>:</span>
                        <?=$location['print_phone'];?>
                    </div>
                <?php endif; ?>

                <div class="section-title<?=($location['show_full_address'] || $location['show_phone'] ? ' top-padded' : '');?>"><?=esc_html($lang['LANG_BUSINESS_HOURS_FEES_TEXT']);?></div><hr />
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
                </ul>
                <?php if($location['afterhours_pickup_allowed'] == 1 || $location['afterhours_return_allowed'] == 1): ?>
                    <div class="section-title top-padded"><?=esc_html($lang['LANG_AFTERHOURS_FEES_TEXT']);?></div><hr />
                    <ul class="fees-list">
                        <?php if($location['afterhours_pickup_allowed'] == 1): ?>
                            <li>
                                <i class="fa fa-money" aria-hidden="true"></i>
                                <span class="highlight"><?=esc_html($lang['LANG_PICKUP_TEXT']);?>:</span>
                                <?=$location['unit_long_without_fraction_print']['afterhours_pickup_fee_dynamic'];?>
                            </li>
                        <?php endif; ?>
                        <?php if($location['afterhours_return_allowed'] == 1): ?>
                            <li>
                                <i class="fa fa-money" aria-hidden="true"></i>
                                <span class="highlight"><?=esc_html($lang['LANG_RETURN_TEXT']);?>:</span>
                                <?=$location['unit_long_without_fraction_print']['afterhours_return_fee_dynamic'];?>
                            </li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>
            </div>
            <div class="location-more">
                <div class="section-title"><?=esc_html($lang['LANG_LOCATIONS_BUSINESS_HOURS_TEXT']);?></div><hr />
                <ul class="business-hours-list">
                    <li>
                        <i class="fa fa-clock-o" aria-hidden="true"></i>
                        <span class="highlight"><?=esc_html($lang['LANG_MONDAYS_TEXT']);?>:</span>
                        <?=$location['business_hours']['mon'];?>
                    </li>
                    <li>
                        <i class="fa fa-clock-o" aria-hidden="true"></i>
                        <span class="highlight"><?=esc_html($lang['LANG_TUESDAYS_TEXT']);?>:</span>
                        <?=$location['business_hours']['mon'];?>
                    </li>
                    <li>
                        <i class="fa fa-clock-o" aria-hidden="true"></i>
                        <span class="highlight"><?=esc_html($lang['LANG_WEDNESDAYS_TEXT']);?>:</span>
                        <?=$location['business_hours']['mon'];?>
                    </li>
                    <li>
                        <i class="fa fa-clock-o" aria-hidden="true"></i>
                        <span class="highlight"><?=esc_html($lang['LANG_THURSDAYS_TEXT']);?>:</span>
                        <?=$location['business_hours']['thu'];?>
                    </li>
                    <li>
                        <i class="fa fa-clock-o" aria-hidden="true"></i>
                        <span class="highlight"><?=esc_html($lang['LANG_FRIDAYS_TEXT']);?>:</span>
                        <?=$location['business_hours']['fri'];?>
                    </li>
                    <li>
                        <i class="fa fa-clock-o" aria-hidden="true"></i>
                        <span class="highlight"><?=esc_html($lang['LANG_SATURDAYS_TEXT']);?>:</span>
                        <?=$location['business_hours']['sat'];?>
                    </li>
                    <li>
                        <i class="fa fa-clock-o" aria-hidden="true"></i>
                        <span class="highlight"><?=esc_html($lang['LANG_SUNDAYS_TEXT']);?>:</span>
                        <?=$location['business_hours']['sun'];?>
                    </li>
                </ul>

                <?php if($location['print_lunch_hours']): ?>
                    <div class="section-title top-padded"><?=esc_html($lang['LANG_LOCATION_LUNCH_TIME_TEXT']);?></div><hr />
                    <ul class="lunch-hours-list">
                        <li>
                            <i class="fa fa-clock-o" aria-hidden="true"></i>
                            <span class="highlight"><?=esc_html($lang['LANG_MON_TEXT']);?>-<?=esc_html($lang['LANG_SUN_TEXT']);?>:</span>
                            <?=$location['print_lunch_hours'];?>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        <div class="clear">&nbsp;</div>
    </div>
</div>
<?php add_action('wp_footer', function() { // A workaround until #48098 will be resolved ( https://core.trac.wordpress.org/ticket/48098 ). Scripts are printed with the '20' priority. ?>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
    jQuery('.fancybox').fancybox();
    var mainImageHref = jQuery(".location-main-image");
    var mainImageFile = jQuery(".location-main-image img");
    var image1Href = jQuery(".location-image-1");
    var image1File = jQuery(".location-image-1 img");
    var image2Href = jQuery(".location-image-2");
    var image2File = jQuery(".location-image-2 img");
    var image3Href = jQuery(".location-image-3");
    var image3File = jQuery(".location-image-3 img");
    if(image1Href.length)
    {
        image1File.on( "mouseenter",
            function() {
                mainImageHref.attr("href", image1Href.attr("href"));
                mainImageFile.attr("src", image1File.attr("title"));
            }
        );
    }
    if(image2Href.length)
    {
        image2File.on( "mouseenter",
            function() {
                mainImageHref.attr("href", image2Href.attr("href"));
                mainImageFile.attr("src", image2File.attr("title"));
            }
        );

    }
    if(image3Href.length)
    {
        image3File.on( "mouseenter",
            function() {
                mainImageHref.attr("href", image3Href.attr("href"));
                mainImageFile.attr("src", image3File.attr("title"));
            }
        );

    }
});
</script>
<?php }, 100); ?>