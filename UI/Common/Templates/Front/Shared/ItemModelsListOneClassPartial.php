<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<div class="item-model-wrap">
    <div class="item-model-image">
         <?php if($itemModel['item_model_thumb_1_url'] != ""): ?>
            <a class="fancybox" href="<?=esc_url($itemModel['item_model_image_1_url']);?>" title="<?=($itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name'].' '.esc_attr($itemModel['via_partner']));?>">
                <img src="<?=esc_url($itemModel['item_model_thumb_1_url']);?>" alt="<?=($itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name'].' '.esc_attr($itemModel['via_partner']));?>" />
            </a>
        <?php else: ?>
            &nbsp;
        <?php endif; ?>
    </div>
    <div class="item-model-description">
        <?php
        if($itemModel['item_model_page_url']):
            print('<a href="'.esc_url($itemModel['item_model_page_url']).'" title="'.esc_attr($lang['LANG_ITEM_MODEL_VISIT_PAGE_TEXT']).'">');
            print('<span class="item-model-name">'.$itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name'].'</span>');
            print('</a>');
        else:
            print('<span class="item-model-name">'.$itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name'].'</span>');
        endif;
        ?>
        <br /><hr />
        <?php if($itemModel['partner_profile_url']): ?>
            <div class="info-line">
                <i class="fa fa-user" aria-hidden="true"></i>
                <span class="highlight"><?=esc_html($lang['LANG_PARTNER_TEXT']);?>:</span> <?=$itemModel['trusted_partner_link_html'];?>
            </div>
        <?php endif; ?>

        <?php if($itemModel['show_attribute1']): ?>
            <div class="info-line">
                <i class="fa fa-tachometer" aria-hidden="true"></i>
                <span class="highlight"> <?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_LABEL1_TEXT']);?>:</span> <?=$itemModel['print_translated_attribute1_title'];?>
            </div>
        <?php endif; ?>

        <?php if($itemModel['show_class']): ?>
            <div class="info-line">
                <i class="fa fa-car" aria-hidden="true"></i>
                <span class="highlight"><?=esc_html($lang['LANG_CLASS_TEXT']);?>:</span> <?=$itemModel['print_translated_class_name'];?>
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

        <div class="info-line">
            <i class="fa fa-credit-card" aria-hidden="true"></i>
            <span class="highlight"><?=esc_html($lang['LANG_PRICING_PRICE_FROM_TEXT']);?>:</span>
                <span title="<?=$itemModel['unit_long_print']['discounted_total_dynamic'];?>">
                    <?=$itemModel['unit_long_without_fraction_print']['discounted_total_dynamic'];?>
                </span> / <?=$itemModel['time_ext_long_print'];?>
        </div>
    </div>
    <div class="item-model-more">
        <?php if($itemModel['show_features']): ?>
            <div class="section-title"><?=esc_html($lang['LANG_FEATURES_TEXT']);?></div><hr />
            <ul class="feature-list">
                <?php foreach($itemModel['features'] AS $feature): ?>
                    <li><?=$feature;?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <div class="action-buttons">
            <?php if($itemModel['item_model_page_url']): ?>
                <div class="single-button">
                    <?=('<a href="'.esc_url($itemModel['item_model_page_url']).'" title="'.esc_attr($lang['LANG_PRICING_GET_IT_TEXT']).'">'.esc_html($lang['LANG_PRICING_GET_IT_TEXT']).'</a>');?>
                </div>
            <?php elseif($itemModel['item_model_page_url'] && $itemModel['price_group_id'] == 0): ?>
                <div class="single-button">
                    <?=('<a href="'.esc_url($itemModel['item_model_page_url']).'" title="'.esc_attr($lang['LANG_PRICING_GET_A_QUOTE_TEXT']).'">'.esc_html($lang['LANG_PRICING_GET_A_QUOTE_TEXT']).'</a>');?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
