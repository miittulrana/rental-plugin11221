<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<div class="content item-model-wrap">
    <div class="col1 item-model-image">
        <?php if($itemModel['item_model_thumb_1_url'] != ""): ?>
            <a class="fancybox" href="<?=esc_url($itemModel['item_model_image_1_url']);?>" title="<?=($itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name'].' '.esc_attr($itemModel['via_partner']));?>">
                <img src="<?=esc_url($itemModel['item_model_thumb_1_url']);?>" alt="<?=($itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name'].' '.esc_attr($itemModel['via_partner']));?>" />
            </a>
        <?php else: ?>
            &nbsp;
        <?php endif; ?>
    </div>
    <div class="col2 item-model-details">
        <?php
        if($itemModel['item_model_page_url']):
            // Because this is a search process, we should open the link in new tab
            print('<a href="'.esc_url($itemModel['item_model_page_url']).'" target="_blank" title="'.esc_attr($lang['LANG_ITEM_MODEL_VISIT_PAGE_TEXT']).'">');
            print('<span class="item-model-name">'.$itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name'].'</span>');
            print('</a>');
        else:
            print('<span class="item-model-name">'.$itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name'].'</span>');
        endif;
        ?>

        <?php if($itemModel['partner_profile_url']): ?>
            <div class="info-line">
                <i class="fa fa-user" aria-hidden="true"></i> <span class="highlight"><?=esc_html($lang['LANG_PARTNER_TEXT']);?>:</span> <?=$itemModel['trusted_partner_link_html'];?>
            </div>
        <?php endif; ?>

        <?php if($itemModel['show_class']): ?>
            <div class="info-line">
                <i class="fa fa-car" aria-hidden="true"></i> <span class="highlight"><?=esc_html($lang['LANG_CLASS_TEXT']);?>:</span> <?=$itemModel['print_translated_class_name'];?>
            </div>
        <?php endif; ?>

        <?php if($itemModel['show_attribute2']): ?>
            <div class="info-line">
                <i class="fa fa-cogs" aria-hidden="true"></i> <span class="highlight"><?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_LABEL2_TEXT']);?>:</span> <?=$itemModel['print_translated_attribute2_title'];?>
            </div>
        <?php endif; ?>

        <?php if($itemModel['show_attribute3']): ?>
            <div class="info-line">
                <i class="fa fa-bar-chart" aria-hidden="true"></i> <span class="highlight"><?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_LABEL3_TEXT']);?>:</span> <?=$itemModel['fuel_consumption'];?>
            </div>
        <?php endif; ?>

        <?php if($itemModel['show_attribute4']): ?>
            <div class="info-line">
                <i class="fa fa-users" aria-hidden="true"></i> <span class="highlight"><?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_LABEL4_TEXT']);?>:</span> <?=$itemModel['max_passengers'];?>
            </div>
        <?php endif; ?>

        <?php if($itemModel['show_attribute1']): ?>
            <div class="info-line">
                <i class="fa fa-tachometer" aria-hidden="true"></i> <span class="highlight"><?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_LABEL1_TEXT']);?>:</span> <?=$itemModel['print_translated_attribute1_title'];?>
            </div>
        <?php endif; ?>

        <?php if ($itemModel['show_features']): ?>
            <ul class="feature-list">
                <?php foreach($itemModel['features'] AS $feature): ?>
                    <li><?=$feature;?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <div class="col3 item-model-price">
        <span class="mobile-only"><?=esc_html($lang['LANG_TOTAL_TEXT']);?>:</span>
        <span title="<?php
        if($itemModel['tax_percentage'] > 0):
            print($itemModel['unit_print']['discounted_total'].' '.esc_html($lang['LANG_TAX_WITHOUT_TEXT']).' + ');
            print($itemModel['unit_print']['discounted_tax_amount'].' '.esc_html($lang['LANG_TAX_SHORT_TEXT']).' = ');
            print($itemModel['unit_print']['discounted_total_with_tax']);
        endif;
        ?>" style="cursor:pointer">
            <?=$itemModel['unit_long_print']['discounted_total_dynamic'];?>
        </span><br />
        <span title="<?php
        if($itemModel['tax_percentage'] > 0):
            print($itemModel['unit_per_period_print']['discounted_total'].' '.esc_html($lang['LANG_TAX_WITHOUT_TEXT']).' + ');
            print($itemModel['unit_per_period_print']['discounted_tax_amount'].' '.esc_html($lang['LANG_TAX_SHORT_TEXT']).' = ');
            print($itemModel['unit_per_period_print']['discounted_total_with_tax']);
        endif;
        ?>" class="price-per-period" style="cursor:pointer">
            <?=($itemModel['unit_per_period_print']['discounted_total_dynamic'].' / '.$itemModel['time_ext_long_print']);?>
        </span>
    </div>
    <?php if($settings['conf_deposit_enabled'] == 1): ?>
        <div class="col4 item-model-deposit">
            <span class="mobile-only"><?=esc_html($lang['LANG_DEPOSIT_TEXT']);?>:</span>
            <?=$itemModel['unit_long_print']['fixed_deposit'];?>
        </div>
    <?php endif; ?>
    <div class="col5 item-select">
        <form action="" name="form1" id="form_item_model<?=esc_attr($itemModel['item_model_id']);?>" method="POST">
            <input type="hidden" name="<?=esc_attr($extPrefix.$orderCodeParam);?>" value="<?=esc_attr($orderCode);?>" />
            <input type="hidden" name="<?=esc_attr($extPrefix);?>do_not_flush" value="yes" />
            <input type="hidden" name="<?=esc_attr($extPrefix);?>came_from_step2" value="yes" />
            <input type="hidden" name="item_model_ids[]" value="<?=esc_attr($itemModel['item_model_id']);?>" />
            <?php if($newOrder == false): ?>
                <button id="<?=esc_attr($extPrefix);?>do_search_item_model<?=esc_attr($itemModel['item_model_id']);?>"
                        name="<?=esc_attr($extPrefix);?>do_search3" type="submit" class="<?=$itemModel['print_selected'];?>"><?=esc_html($lang['LANG_CHOOSE_TEXT']);?></button>
            <?php else: ?>
                <?php if($settings['conf_universal_analytics_events_tracking'] == 1): ?>
                    <!-- Note: Do not translate events to track well inter-language events -->
                    <button id="<?=esc_attr($extPrefix);?>do_search_item_model<?=esc_attr($itemModel['item_model_id']);?>"
                            name="<?=esc_attr($extPrefix);?>do_search3" type="submit" class="<?=$itemModel['print_selected'];?>"
                            onclick="ga('send', 'event', '<?=esc_js($extName);?>', 'Click', '3. Continue to extras');"><?=esc_html($lang['LANG_CHOOSE_TEXT']);?></button>
                <?php else: ?>
                    <button id="<?=esc_attr($extPrefix);?>do_search_item_model<?=esc_attr($itemModel['item_model_id']);?>"
                            name="<?=esc_attr($extPrefix);?>do_search3" type="submit" class="<?=$itemModel['print_selected'];?>"><?=esc_html($lang['LANG_CHOOSE_TEXT']);?></button>
                <?php endif; ?>
            <?php endif; ?>
        </form>
    </div>
</div>