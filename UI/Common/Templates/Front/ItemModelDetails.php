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
<div class="fleet-management-wrapper <?=esc_attr($extCSS_Prefix);?>wrapper fleet-management-item-model-details <?=esc_attr($extCSS_Prefix);?>item-model-details">
    <?php if($itemModel['items_in_stock'] > 0 || $itemModel['items_in_stock'] == -1): ?>
        <?php
        if($itemModel['item_model_sku'] != "" && $settings['conf_universal_analytics_enhanced_ecommerce'] == 1):
            include 'Shared/ItemModelEnhancedEcommercePartial.php';
        endif;
        ?>
        <div class="item-model-images">
            <div class="item-model-big-image">
                <?php if($itemModel['item_model_big_thumb_1_url']): ?>
                    <a class="item-main-image fancybox" rel="group" href="<?=esc_url($itemModel['item_model_image_1_url']);?>" title="<?=($itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name'].' '.esc_attr($itemModel['via_partner']));?>">
                        <img src="<?=esc_url($itemModel['item_model_big_thumb_1_url']);?>" alt="<?=($itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name'].' '.esc_attr($itemModel['via_partner']));?>" /><br />
                    </a>
                <?php else: ?>
                    &nbsp;
                <?php endif; ?>
            </div>
            <div class="item-model-small-images">
                <?php if($itemModel['item_model_mini_thumb_1_url'] && ($itemModel['item_model_mini_thumb_2_url'] || $itemModel['item_model_mini_thumb_3_url'])): ?>
                    <div class="item-model-small-image">
                        <a class="item-model-image-1 fancybox" rel="group" href="<?=esc_url($itemModel['item_model_image_1_url']);?>" title="<?=($itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name'].' '.esc_attr($itemModel['via_partner']));?>">
                            <img src="<?=esc_url($itemModel['item_model_mini_thumb_1_url']);?>" title="<?=esc_url($itemModel['item_model_big_thumb_1_url']);?>" alt="<?=($itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name'].' '.esc_attr($itemModel['via_partner']));?>" />
                        </a>
                    </div>
                <?php endif; ?>
                <?php if($itemModel['item_model_mini_thumb_2_url']): ?>
                    <div class="item-model-small-image">
                        <a class="item-model-image-2 fancybox" rel="group" href="<?=esc_url($itemModel['item_model_image_2_url']);?>" title="<?=($itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name'].' '.esc_attr($itemModel['via_partner']));?>">
                            <img src="<?=esc_url($itemModel['item_model_mini_thumb_2_url']);?>" title="<?=esc_url($itemModel['item_model_big_thumb_2_url']);?>" alt="<?=($itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name'].' '.esc_attr($itemModel['via_partner']));?>" />
                        </a>
                    </div>
                <?php endif; ?>
                <?php if($itemModel['item_model_mini_thumb_3_url']): ?>
                    <div class="item-model-small-image">
                        <a class="item-model-image-3 fancybox" rel="group" href="<?=esc_url($itemModel['item_model_image_3_url']);?>" title="<?=($itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name'].' '.esc_attr($itemModel['via_partner']));?>">
                            <img src="<?=esc_url($itemModel['item_model_mini_thumb_3_url']);?>" title="<?=esc_url($itemModel['item_model_big_thumb_3_url']);?>" alt="<?=($itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name'].' '.esc_attr($itemModel['via_partner']));?>" />
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="item-model-description">
            <?php if($itemModel['partner_profile_url']): ?>
                <div class="info-line">
                    <i class="fa fa-user" aria-hidden="true"></i>
                    <span class="highlight"><?=esc_html($lang['LANG_PARTNER_TEXT']);?>:</span> <?=$itemModel['trusted_partner_link_html'];?>
                </div>
            <?php endif; ?>

            <?php if($itemModel['class_id'] > 0): ?>
                <div class="info-line">
                    <i class="fa fa-car" aria-hidden="true"></i>
                    <span class="highlight"><?=esc_html($lang['LANG_CLASS_TEXT']);?>:</span> <?=$itemModel['print_translated_class_name'];?>
                </div>
            <?php endif; ?>

            <?php if($itemModel['show_attribute1']): ?>
                <div class="info-line">
                    <i class="fa fa-tachometer" aria-hidden="true"></i>
                    <span class="highlight"><?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_LABEL1_TEXT']);?>:</span> <?=$itemModel['print_translated_attribute1_title'];?>
                </div>
            <?php endif; ?>

            <?php if($itemModel['show_attribute2']): ?>
                <div class="info-line">
                    <i class="fa fa-cogs" aria-hidden="true"></i>
                    <span class="highlight"><?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_LABEL2_TEXT']);?>:</span> <?=$itemModel['print_translated_attribute2_title'];?>
                </div>
            <?php endif; ?>

            <?php if($itemModel['show_attribute3']): ?>
                <div class="info-line">
                    <i class="fa fa-bar-chart" aria-hidden="true"></i>
                    <span class="highlight"><?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_LABEL3_TEXT']);?>:</span> <?=esc_html($itemModel['attribute3']);?>
                </div>
            <?php endif; ?>

            <?php if($itemModel['show_attribute4']): ?>
                <div class="info-line">
                    <i class="fa fa-users" aria-hidden="true"></i>
                    <span class="highlight"><?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_LABEL4_TEXT']);?>:</span> <?=esc_html($itemModel['attribute4']);?>
                </div>
            <?php endif; ?>

            <?php if($itemModel['show_attribute5']): ?>
                <div class="info-line">
                    <i class="fa fa-tachometer" aria-hidden="true"></i>
                    <span class="highlight"><?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_LABEL5_TEXT']);?>:</span> <?=esc_html($itemModel['attribute5']);?>
                </div>
            <?php endif; ?>

            <?php if($itemModel['show_attribute6']): ?>
                <div class="info-line">
                    <i class="fa fa-briefcase" aria-hidden="true"></i>
                    <span class="highlight"><?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_LABEL6_TEXT']);?>:</span> <?=esc_html($itemModel['attribute6']);?>
                </div>
            <?php endif; ?>

            <?php if($itemModel['show_attribute7']): ?>
                <div class="info-line">
                    <i class="fa fa-car" aria-hidden="true"></i>
                    <span class="highlight"><?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_LABEL7_TEXT']);?>:</span> <?=esc_html($itemModel['attribute7']);?>
                </div>
            <?php endif; ?>

            <?php if($itemModel['show_attribute8']): ?>
                <div class="info-line">
                    <i class="fa fa-tachometer" aria-hidden="true"></i>
                    <span class="highlight"><?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_LABEL8_TEXT']);?>:</span> <?=esc_html($itemModel['attribute8_text']);?>
                </div>
            <?php endif; ?>

            <?php if($itemModel['show_min_driver_age']): ?>
                <div class="info-line">
                    <i class="fa fa-user" aria-hidden="true"></i>
                    <span class="highlight"><?=esc_html($lang['LANG_ITEM_MODEL_MIN_ALLOWED_AGE_TEXT']);?>:</span> <?=esc_html($itemModel['min_driver_age']);?>
                </div>
            <?php endif; ?>

            <div class="info-line">
                <i class="fa fa-credit-card" aria-hidden="true"></i>
                <span class="highlight"><?=esc_html($lang['LANG_PRICING_PRICE_FROM_TEXT']);?>:</span>
                <span title="<?=$itemModel['unit_long_print']['discounted_total_dynamic'];?>">
                    <?=$itemModel['unit_long_without_fraction_print']['discounted_total_dynamic'];?>
                </span> / <?=$itemModel['time_ext_long_print'];?>
            </div>

            <?php if($settings['conf_deposit_enabled'] == 1): ?>
                <div class="info-line">
                    <i class="fa fa-credit-card" aria-hidden="true"></i>
                    <span class="highlight"><?=esc_html($lang['LANG_DEPOSIT_TEXT']);?>:</span>
                    <span title="<?=$itemModel['unit_long_print']['fixed_deposit'];?>">
                        <?=$itemModel['unit_long_without_fraction_print']['fixed_deposit'];?>
                    </span>
                </div>
            <?php endif; ?>

            <?php if(sizeof($itemModel['features']) > 0): ?>
                <div class="features-section">
                    <div class="section-title"><?=esc_html($lang['LANG_ITEM_MODEL_ADDITIONAL_INFORMATION_TEXT']);?></div>
                    <ul class="feature-list">
                        <?php foreach($itemModel['features'] AS $feature): ?>
                            <li><?=$feature;?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
        <div class="clear">&nbsp;</div>
    <?php else: ?>
        <div class="no-item-models-available"><?=esc_html($lang['LANG_ITEM_MODEL_NO_ITEMS_AVAILABLE_TEXT']);?></div>
    <?php endif; ?>
</div>
<?php add_action('wp_footer', function() { // A workaround until #48098 will be resolved ( https://core.trac.wordpress.org/ticket/48098 ). Scripts are printed with the '20' priority. ?>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
    jQuery('.fancybox').fancybox();
    var mainImageHref = jQuery(".item-main-image");
    var mainImageFile = jQuery(".item-main-image img");
    var image1Href = jQuery(".item-model-image-1");
    var image1File = jQuery(".item-model-image-1 img");
    var image2Href = jQuery(".item-model-image-2");
    var image2File = jQuery(".item-model-image-2 img");
    var image3Href = jQuery(".item-model-image-3");
    var image3File = jQuery(".item-model-image-3 img");
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