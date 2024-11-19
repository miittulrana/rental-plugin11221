<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );

print('<tr class="item-model-row">');
    print('<td class="item-model-image">');
        if($itemModel['item_model_mini_thumb_1_url'] != ""):
            print('<a class="fancybox" href="'.esc_url($itemModel['item_model_image_1_url']).'" title="'.$itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name'].' '.esc_attr($itemModel['via_partner']).'">');
                print('<img src="'.esc_url($itemModel['item_model_mini_thumb_1_url']).'" alt="'.$itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name'].' '.esc_attr($itemModel['via_partner']).'" />');
            print('</a>');
        else:
            print('&nbsp;');
        endif;
    print('</td>');
    print('<td class="item-model-description">');
        if($itemModel['item_model_page_url']):
            print('<a href="'.esc_url($itemModel['item_model_page_url']).'" title="'.esc_attr($lang['LANG_ITEM_MODEL_VISIT_PAGE_TEXT']).'">');
            print('<span class="item-model-name">'.$itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name'].'</span>');
            print('</a>');
        else:
            print('<span class="item-model-name">'.$itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name'].'</span>');
        endif;
        print('<br /><hr />');
        if($itemModel['show_attribute2']):
            print(esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_LABEL2_TEXT']).': '.$itemModel['print_translated_attribute2_title']);
        endif;
        if($itemModel['partner_profile_url']):
            print('<br />'.esc_html($lang['LANG_PARTNER_TEXT']).': '.$itemModel['trusted_partner_link_html']);
        endif;
    print('</td>');
    print('<td class="item-model-description-separator"></td>');
    foreach($itemModel['period_list'] as $period)
    {
        print('<td class="item-model-price-on-duration">');
        print('<span title="'.$period['print_price_description'].'">'.$period['print_price'].'</span>');
        print('</td>');
    }
    if($settings['conf_deposit_enabled'] == 1):
        print('<td class="item-model-deposit">');
        print('<strong>'.$itemModel['unit_long_without_fraction_print']['fixed_deposit'].'</strong>');
        print('</td>');
    endif;
print('</tr>');