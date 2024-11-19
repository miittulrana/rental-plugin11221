<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );

print('<tr class="item-model-row">');
    print('<td class="item-model-description">');
    print('<span class="item-model-name">'.$itemModel['print_translated_manufacturer_name'].' '.$itemModel['print_translated_item_model_name'].'</span>');
        print('<br /><hr />');
        if($itemModel['show_attribute2']):
            print(esc_html($lang['LANG_ITEM_MODEL_ID_TEXT']).' '.$itemModel['item_model_id'].', ');
            print(esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_LABEL2_TEXT']).': '.$itemModel['print_translated_attribute2_title']);
        endif;
        if($itemModel['partner_profile_url']):
            print('<br />'.esc_html($lang['LANG_PARTNER_TEXT']).': '.$itemModel['trusted_partner_link_html']);
        endif;
    print('</td>');
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