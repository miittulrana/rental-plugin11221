<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Scripts
wp_enqueue_script('jquery');
wp_enqueue_script('jquery-validate');
wp_enqueue_script( 'fleet-management-main' );

// Styles
if($settings['conf_load_font_awesome_from_plugin'] == 1):
    wp_enqueue_style('font-awesome');
endif;
wp_enqueue_style('fleet-management-main');
if($newOrder == true && $cameFromSingleStep1 && $settings['conf_universal_analytics_enhanced_ecommerce'] == 1):
    include 'Shared/Step4EnhancedEcommercePartial.php';
endif;
?>
<div class="fleet-management-wrapper <?=esc_attr($extCSS_Prefix);?>wrapper fleet-management-search-options-list <?=esc_attr($extCSS_Prefix);?>search-options-list">
    <?php
    if($complexPickup || $complexReturn)
    {
        include 'Shared/LocationsSummaryComplexPartial.php';
    } else
    {
        include 'Shared/LocationsSummarySimplePartial.php';
    }
    ?>
    <div class="clear">&nbsp;</div>
    <form name="form1" id="form1" method="POST" action="">
    <h2 class="search-label"><?=esc_html($lang['LANG_SELECTED_ITEMS_TEXT']);?></h2>

    <div class="content item-models-list-header">
        <div class="col1 item-model-details">
            <?=esc_html($lang['LANG_ITEM_MODEL_TEXT']);?>
        </div>
        <div class="col2 item-options">
            &nbsp;
        </div>
        <div class="col3 item-model-price">
            <?=esc_html($lang['LANG_TOTAL_TEXT']);?>
        </div>
        <div class="col4 item-model-deposit">
            <?php if($settings['conf_deposit_enabled'] == 1): ?>
                <?=esc_html($lang['LANG_DEPOSIT_TEXT']);?>
            <?php else: ?>
                &nbsp;
            <?php endif; ?>
        </div>
        <div class="col5 item-quantity">
            &nbsp;
        </div>
    </div>
    <?php foreach ($itemModels AS $itemModel): ?>
        <div class="selected-item-model">
            <div class="col1 item-model-details">
                <?=($itemModel['print_translated_class_name'] ? $itemModel['print_translated_class_name']."," : "");?>
                <?=($itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name'].' '.esc_html($itemModel['via_partner']));?>
            </div>
            <div class="col2 item-options">
                <?php if($itemModel['options_html'] != ""): ?>
                    <?=$itemModel['options_html'];?>
                <?php else: ?>
                    &nbsp;
                <?php endif; ?>
            </div>
            <div class="col3 item-model-price">
                <span class="mobile-only"><?=esc_html($lang['LANG_TOTAL_TEXT']);?>:</span>
                <span title="<?php
                if($itemModel['tax_percentage'] > 0):
                    print($itemModel['unit_print']['discounted_total'].' '.esc_html($lang['LANG_TAX_WITHOUT_TEXT']).' + ');
                    print( $itemModel['unit_print']['discounted_tax_amount'].' '.esc_html($lang['LANG_TAX_SHORT_TEXT']).' = ');
                    print($itemModel['unit_print']['discounted_total_with_tax']);
                endif;
                ?>" style="cursor:pointer">
                    <?=$itemModel['unit_long_print']['discounted_total_dynamic'];?>
                </span>
            </div>
            <div class="col4 item-model-deposit">
                <?php if($settings['conf_deposit_enabled'] == 1): ?>
                    <span class="mobile-only"><?=esc_html($lang['LANG_DEPOSIT_TEXT']);?>:</span>
                    <?=$itemModel['unit_long_print']['fixed_deposit'];?>
                <?php else: ?>
                    &nbsp;
                <?php endif; ?>
            </div>
            <div class="col5 item-quantity">
                <?php if($itemModel['max_allowed_units'] > 1): ?>
                    <span class="mobile-only"><?=esc_html($lang['LANG_QUANTITY_TEXT']);?>:</span>
                        <select name="item_model_units[<?=esc_attr($itemModel['item_model_id']);?>]" id="item_model_units_<?=esc_attr($itemModel['item_model_id']);?>" class="required">
                            <?=$itemModel['quantity_dropdown_options'];?>
                        </select>
                <?php else: ?>
                    <input type="hidden" name="item_model_units[<?=esc_attr($itemModel['item_model_id']);?>]" value="1" />
                    &nbsp;
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="clear">&nbsp;</div>
    <h2 class="search-label top-padded"><?=esc_html($lang['LANG_RENTAL_OPTIONS_TEXT']);?></h2>
    <div class="content extras-list-header">
        <div class="col1 extra-name">
            <?=esc_html($lang['LANG_EXTRA_TEXT']);?>
        </div>
        <div class="col2 extra-options">
            &nbsp;
        </div>
        <div class="col3 extra-price">
            <?=esc_html($lang['LANG_TOTAL_TEXT']);?>
        </div>
        <div class="col4 extra-deposit">
            <?php if($settings['conf_deposit_enabled'] == 1): ?>
                <?=esc_html($lang['LANG_DEPOSIT_TEXT']);?>
            <?php else: ?>
                &nbsp;
            <?php endif; ?>
        </div>
        <div class="col5 extra-select">
            &nbsp;
        </div>
    </div>


    <?php foreach ($extras AS $extra): ?>
        <div class="extra">
            <div class="col1 extra-name">
                <?=esc_html($extra['translated_extra']);?>
            </div>
            <div class="col2 extra-options">
                <?php if($extra['options_html'] != ""): ?>
                    <?=$extra['options_html'];?>
                <?php else: ?>
                    &nbsp;
                <?php endif; ?>
            </div>
            <div class="col3 extra-price">
                <span class="mobile-only"><?=esc_html($lang['LANG_TOTAL_TEXT']);?>:</span>
                <span title="<?php
                if($extra['tax_percentage'] > 0):
                    print($extra['unit_print']['discounted_total'].' '.esc_html($lang['LANG_TAX_WITHOUT_TEXT']).' + ');
                    print($extra['unit_print']['discounted_tax_amount'].' '.esc_html($lang['LANG_TAX_SHORT_TEXT']).' = '.$extra['unit_print']['discounted_total_with_tax']);
                endif;
                ?>" style="cursor:pointer">
                    <?=$extra['unit_long_print']['discounted_total_dynamic'];?>
                </span>
            </div>
            <div class="col4 extra-deposit">
                <?php if($settings['conf_deposit_enabled'] == 1): ?>
                    <span class="mobile-only"><?=esc_html($lang['LANG_DEPOSIT_TEXT']);?>:</span>
                    <?=$extra['unit_long_print']['fixed_deposit'];?>
                <?php else: ?>
                    &nbsp;
                <?php endif; ?>
            </div>
            <div class="col5 extra-select">
                <span class="mobile-only"><?=esc_html($lang['LANG_QUANTITY_TEXT']);?>:</span>
                <input type="hidden" name="extra_ids[]" value="<?=esc_attr($extra['extra_id']);?>" />
                    <select name="extra_units[<?=esc_attr($extra['extra_id']);?>]" id="extra_units_<?=esc_attr($extra['extra_id']);?>" class="required">
                        <?=$extra['quantity_dropdown_options'];?>
                    </select>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if($gotAnyExtras == false):  ?>
        <div class="no-extras">
            <?=esc_html($lang['LANG_NO_EXTRAS_AVAILABLE_CLICK_CONTINUE_TEXT']);?>
        </div>
    <?php endif; ?>
    <div class="buttons">
        <input type="hidden" name="<?=esc_attr($extPrefix.$orderCodeParam);?>" value="<?=esc_attr($orderCode);?>" />
        <input type="hidden" name="<?=esc_attr($extPrefix);?>do_not_flush" value="yes" />
        <?php if($newOrder == false): ?>
            <input type="submit" name="<?=esc_attr($extPrefix);?>cancel_order" value="<?=esc_html($lang['LANG_CANCEL_ORDER_TEXT']);?>" />
            <input type="submit" name="<?=esc_attr($extPrefix);?>do_search0" value="<?=esc_html($lang['LANG_ORDER_CHANGE_DATE_TIME_AND_LOCATION_TEXT']);?>" />
            <input type="submit" name="<?=esc_attr($extPrefix);?>do_search" value="<?=esc_html($lang['LANG_ORDER_CHANGE_ORDERED_ITEMS_TEXT']);?>" />
            <button id="<?=esc_attr($extPrefix);?>do_search" name="<?=esc_attr($extPrefix);?>do_search4" type="submit"><?=esc_html($lang['LANG_CONTINUE_TEXT']);?></button>
        <?php else: ?>
            <?php if($settings['conf_universal_analytics_events_tracking'] == 1): ?>
                <!-- Note: Do not translate events to track well inter-language events -->
                <button id="<?=esc_attr($extPrefix);?>do_search" name="<?=esc_attr($extPrefix);?>do_search4" type="submit"
                        onclick="ga('send', 'event', '<?=esc_js($extName);?>', 'Click', '4. Continue to summary');"><?=esc_html($lang['LANG_CONTINUE_TEXT']);?></button>
            <?php else: ?>
                <button id="<?=esc_attr($extPrefix);?>do_search" name="<?=esc_attr($extPrefix);?>do_search4" type="submit"><?=esc_html($lang['LANG_CONTINUE_TEXT']);?></button>
            <?php endif; ?>
        <?php endif; ?>
        <input type="hidden" name="<?=esc_attr($extPrefix);?>came_from_step3" value="yes" />
    </div>
    </form>
    <div class="clear">&nbsp;</div>
</div>