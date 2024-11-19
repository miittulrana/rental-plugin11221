<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<tr class="item-model-row">
    <td class="item-model-image">
        <?php if($itemModel['item_model_mini_thumb_1_url'] != ""): ?>
            <a class="fancybox" href="<?=esc_url($itemModel['item_model_image_1_url']);?>" title="<?=($itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name'].' '.esc_attr($itemModel['via_partner']));?>">
                <img src="<?=esc_url($itemModel['item_model_mini_thumb_1_url']);?>" alt="<?=($itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name'].' '.esc_attr($itemModel['via_partner']));?>" />
            </a>
        <?php else: ?>
            &nbsp;
        <?php endif; ?>
    </td>
    <td class="item-model-description">
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
        <?=(esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_LABEL2_TEXT']).': '.$itemModel['print_translated_attribute2_title']);?>
        <?php if($itemModel['partner_profile_url']): ?>
            <br /><?=(esc_html($lang['LANG_PARTNER_TEXT']).': '.$itemModel['trusted_partner_link_html']);?>
        <?php endif; ?>
    </td>
    <?php
    foreach($itemModel['day_list'] as $day)
    {
        print( '<td class="quantity-left-in-day '.$day['print_quantity_class'].'">');
        print('<div class="quantity-hover"
            title="'.esc_attr($lang['LANG_ITEMS_AVAILABILITY_FOR_TEXT']).' '.esc_html($lang['LANG_ALL_DAY_TEXT']).'
            '.esc_html($lang['LANG_ON_TEXT']).' '.$day['print_month_name'].' '.$day['print_day'].',
            '.esc_html($lang['LANG_TOTAL_ITEMS_TEXT']).' '.$day['units_in_stock'].'">'.$day['print_units_available'].'</div>');
        print('<div class="partial-quantity-hover"
            title="'.esc_attr($lang['LANG_ITEMS_PARTIAL_AVAILABILITY_FOR_TEXT']).'
            '.sprintf(esc_html($lang['LANG_S_PARTIAL_DAY_TEXT']), $noonTime).'">'.$day['print_partial_units_available'].'</div>');
        print('</td>');
    }
    ?>
</tr>