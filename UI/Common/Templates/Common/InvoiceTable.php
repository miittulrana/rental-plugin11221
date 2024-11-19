<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// No styles can be enqueued here
?>
<!-- We put all content in a div, so that center and other tags looks well everywhere, especially in e-mails -->
<div style="width:840px;">
<div id = 'invoiceFinal'>
	<table style="font-family:Verdana, Geneva, sans-serif; font-size: 12px; background-color:#999999; width:840px; border:none;" cellpadding="5" cellspacing="1" >
    <tbody>
    <tr>
        <td align="left" style="font-weight:bold; background-color:#eeeeee; padding-left:5px;" colspan="2"><?=esc_html($lang['LANG_CUSTOMER_DETAILS_TEXT']);?></td>
    </tr>
    <tr>
        <td align="left" style="width:160px; background-color:#ffffff; padding-left:5px;"><?=esc_html($lang['LANG_ORDER_CODE_TEXT']);?></td>
        <td align="left" style="background-color:#ffffff; padding-left:5px;"><?=$orderCode;?></td>
    </tr>
    <?php if($couponCodeVisible && $couponCode != ""): ?>
        <tr>
            <td align="left" style="background-color:#ffffff; padding-left:5px;"><?=esc_html($lang['LANG_COUPON_CODE_TEXT']);?></td>
            <td align="left" style="background-color:#ffffff; padding-left:5px;"><?=esc_html($couponCode);?></td>
        </tr>
    <?php endif; ?>
    <?php if($customerTitleVisible || $customerFirstNameVisible || $customerLastNameVisible): ?>
        <tr>
            <td align="left" style="background-color:#ffffff; padding-left:5px;"><?=esc_html($lang['LANG_CUSTOMER_TEXT']);?></td>
            <td align="left" style="background-color:#ffffff; padding-left:5px;"><?=$customer['print_full_name'];?></td>
        </tr>
    <?php endif; ?>
    <?php if($customerBirthdateVisible): ?>
        <tr>
            <td align="left" style="background-color:#ffffff; padding-left:5px;"><?=esc_html($lang['LANG_DATE_OF_BIRTH_TEXT']);?></td>
            <td align="left" style="background-color:#ffffff; padding-left:5px;"><?=$customer['birthdate'];?></td>
        </tr>
    <?php endif; ?>
    <?php if($customerStreetAddressVisible): ?>
        <tr>
            <td align="left" style="background-color:#ffffff; padding-left:5px;"><?=esc_html($lang['LANG_STREET_ADDRESS_TEXT']);?></td>
            <td align="left" style="background-color:#ffffff; padding-left:5px;"><?=$customer['street_address'];?></td>
        </tr>
    <?php endif; ?>
    <?php if($customerCityVisible): ?>
        <tr>
            <td align="left" style="background-color:#ffffff; padding-left:5px;"><?=esc_html($lang['LANG_CITY_TEXT']);?></td>
            <td align="left" style="background-color:#ffffff; padding-left:5px;"><?=$customer['city'];?></td>
        </tr>
    <?php endif; ?>
    <?php if($customerStateVisible): ?>
        <tr>
            <td align="left" style="background-color:#ffffff; padding-left:5px;"><?=esc_html($lang['LANG_STATE_TEXT']);?></td>
            <td align="left" style="background-color:#ffffff; padding-left:5px;"><?=$customer['state'];?></td>
        </tr>
    <?php endif; ?>
    <?php if($customerZIP_CodeVisible): ?>
        <tr>
            <td align="left" style="background-color:#ffffff; padding-left:5px;"><?=esc_html($lang['LANG_ZIP_CODE_TEXT']);?></td>
            <td align="left" style="background-color:#ffffff; padding-left:5px;"><?=$customer['zip_code'];?></td>
        </tr>
    <?php endif; ?>
    <?php if($customerCountryVisible): ?>
        <tr>
            <td align="left" style="background-color:#ffffff; padding-left:5px;"><?=esc_html($lang['LANG_COUNTRY_TEXT']);?></td>
            <td align="left" style="background-color:#ffffff; padding-left:5px;"><?=$customer['country'];?></td>
        </tr>
    <?php endif; ?>
    <?php if($customerPhoneVisible): ?>
        <tr>
            <td align="left" style="background-color:#ffffff; padding-left:5px;"><?=esc_html($lang['LANG_PHONE_TEXT']);?></td>
            <td align="left" style="background-color:#ffffff; padding-left:5px;"><?=$customer['phone'];?></td>
        </tr>
    <?php endif; ?>
    <?php if($customerEmailVisible): ?>
        <tr>
            <td align="left" style="background-color:#ffffff; padding-left:5px;"><?=esc_html($lang['LANG_EMAIL_TEXT']);?></td>
            <td align="left" style="background-color:#ffffff; padding-left:5px;"><?=$customer['email'];?></td>
        </tr>
    <?php endif; ?>
    <?php if($customerCommentsVisible): ?>
        <tr>
            <td align="left" style="background-color:#ffffff; padding-left:5px;"><?=esc_html($lang['LANG_ADDITIONAL_COMMENTS_TEXT']);?></td>
            <td align="left" style="background-color:#ffffff; padding-left:5px;"><?=$customer['print_comments'];?></td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>
<!-- ORDER DETAILS -->
<br />
<table style="font-family:Verdana, Geneva, sans-serif; font-size: 12px; background:#999999; width:840px; border:none;" cellpadding="5" cellspacing="1">
<tbody>
<tr>
    <td align="left" style="font-weight:bold; background-color:#eeeeee; padding-left:5px;" colspan="3"><?=esc_html($lang['LANG_ORDER_RENTAL_DETAILS_TEXT']);?></td>
</tr>
<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
?>
<?php if(($pickupLocationVisible || $returnLocationVisible) && ($priceSummary['pickup_location_id'] > 0 || $priceSummary['return_location_id'] > 0)): ?>
    <?php if($pickupLocationVisible && $returnLocationVisible && $priceSummary['pickup_location_id'] > 0 && $priceSummary['return_location_id'] > 0): ?>
        <tr style="background-color:#343434; color: white;" class="location-headers">
            <td align="left" class="col1" style="padding-left:5px;"><strong><?=esc_html($lang['LANG_LOCATION_PICKUP_TEXT']);?></strong></td>
            <td align="left" class="col2" style="padding-left:5px;" colspan="2"><strong><?=esc_html($lang['LANG_LOCATION_RETURN_TEXT']);?></strong></td>
        </tr>
    <?php elseif($pickupLocationVisible && $priceSummary['pickup_location_id'] > 0): ?>
        <tr style="background-color:#343434; color: white;" class="location-headers">
            <td align="left" class="col1" style="padding-left:5px;" colspan="3"><strong><?=esc_html($lang['LANG_LOCATION_PICKUP_TEXT']);?></strong></td>
        </tr>
    <?php elseif($returnLocationVisible && $priceSummary['return_location_id'] > 0): ?>
        <tr style="background-color:#343434; color: white;" class="location-headers">
            <td align="left" class="col1" style="padding-left:5px;" colspan="3"><strong><?=esc_html($lang['LANG_LOCATION_RETURN_TEXT']);?></strong></td>
        </tr>
    <?php endif; ?>

    <tr style="background-color:#FFFFFF" class="location-details">
        <?php if($pickupLocationVisible): ?>
            <td align="left" class="col1" style="padding-left:5px;" colspan="<?=esc_attr($pickupMainColspan);?>">
                <?=$pickupLocations;?>
            </td>
        <?php endif; ?>
        <?php if($returnLocationVisible): ?>
            <td align="left" class="col2" style="padding-left:5px;" colspan="<?=esc_attr($returnMainColspan);?>">
                <?=$returnLocations;?>
            </td>
        <?php endif; ?>
    </tr>
<?php endif; ?>
<!-- LOCATIONS: END -->

<?php if($pickupDateVisible || $returnDateVisible): ?>
    <tr style="background-color:#343434; color: white" class="duration-headers">
        <?php if($pickupDateVisible): ?>
            <td align="left" class="col1" style="padding-left:5px;" colspan="<?=esc_attr($pickupColspan);?>"><strong><?=esc_html($lang['LANG_PICKUP_DATE_AND_TIME_TEXT']);?></strong></td>
        <?php endif; ?>
        <?php if($returnDateVisible): ?>
            <td align="left" class="col2" style="padding-left:5px;" colspan="<?=esc_attr($returnColspan);?>"><strong><?=esc_html($lang['LANG_RETURN_DATE_AND_TIME_TEXT']);?></strong></td>
            <td align="right" class="col3" style="padding-right:5px;"><strong><?=esc_html($lang['LANG_PERIOD_TEXT']);?></strong></td>
        <?php endif; ?>
    </tr>

    <tr style="background-color:#FFFFFF" class="duration-details">
        <?php if($pickupDateVisible): ?>
            <td align="left" class="col1" style="padding-left:5px;" colspan="<?=esc_attr($pickupColspan);?>">
                <?=esc_html($priceSummary['expected_pickup_date_i18n'].'    '.$priceSummary['expected_pickup_time_i18n']);?>
            </td>
        <?php endif; ?>
        <?php if($returnDateVisible): ?>
            <td align="left" class="col2" style="padding-left:5px;" colspan="<?=esc_attr($returnColspan);?>">
                <?=esc_html($priceSummary['expected_return_date_i18n'].'    '.$priceSummary['expected_return_time_i18n']);?>
            </td>
            <td align="right" class="col3" style="padding-right:5px;">
                <?=esc_html($priceSummary['order_duration_text']);?>
            </td>
        <?php endif; ?>
    </tr>
<?php endif; ?>

<!-- ITEM MODELS -->
<?php if(sizeof($priceSummary['item_models']) > 0): ?>
    <tr class="item-models-header" style="background-color:#343434; color: white;">
        <td align="left" class="col1" style="padding-left:5px;"><strong><?=esc_html($lang['LANG_SELECTED_ITEMS_TEXT']);?></strong></td>
        <td align="left" class="col2" style="padding-left:5px;"><strong><?=esc_html($lang['LANG_PRICE_TEXT']);?></strong></td>
        <td align="right" class="col3" style="padding-right:5px;"><strong><?=esc_html($lang['LANG_TOTAL_TEXT']);?></strong></td>
    </tr>
<?php endif; ?>
<?php foreach ($priceSummary['item_models'] AS $itemModel): ?>
    <tr style="background-color:#FFFFFF" class="item-models">
        <td align="left" class="col1" style="padding-left:5px;">
            <?=esc_html($itemModel['translated_item_model_with_option']);?>
        </td>
        <td align="left" class="col2" style="padding-left:5px;">
            <span title="<?php
            if($itemModel['tax_percentage'] > 0):
                print($itemModel['unit_print']['discounted_total'].' '.esc_html($lang['LANG_TAX_WITHOUT_TEXT']).' x ');
                print($itemModel['multiplier'].' '.esc_html($lang['LANG_UNITS_SUFFIX2_TEXT']).' + ');
                print($itemModel['unit_print']['discounted_tax_amount'].' '.esc_html($lang['LANG_TAX_SHORT_TEXT']).' = ');
            endif;
            print($itemModel['unit_print']['discounted_total_with_tax'].' x '.$itemModel['multiplier']);
            ?>" style="cursor:pointer" id = "itemPrice">
                <?=$itemModel['unit_print']['discounted_total'];?>
                <?=($itemModel['multiplier'] > 1 ? ' x '.$itemModel['multiplier'] : '');?>
            </span>
        </td>
        <td align="right" class="col3" style="padding-right:5px;">
            <span title="<?php
            if($itemModel['tax_percentage'] > 0):
                print($itemModel['multiplied_print']['discounted_total'].' '.esc_html($lang['LANG_TAX_WITHOUT_TEXT']).' + ');
                print($itemModel['multiplied_print']['discounted_tax_amount'].' '.esc_html($lang['LANG_TAX_SHORT_TEXT']).' = ');
                print($itemModel['multiplied_print']['discounted_total_with_tax']);
            endif;
            ?>" style="cursor:pointer" id = "itemTotal">
                <?=$itemModel['multiplied_print']['discounted_total'];?>
            </span>
        </td>
    </tr>
<?php endforeach; ?>

<!-- PICKUP FEE -->
<?php if($showLocationFees && ($pickupLocationVisible || $returnLocationVisible) && ($priceSummary['pickup_location_id'] > 0 || $priceSummary['return_location_id'] > 0)): ?>
    <tr style="background-color:#343434; color: white" class="location-fees-header">
        <td align="left" class="col1" style="padding-left:5px;" colspan="3"><strong><?=esc_html($lang['LANG_LOCATION_FEES_TEXT']);?></strong></td>
    </tr>
<?php endif; ?>
<?php if($showLocationFees && $pickupLocationVisible && $priceSummary['pickup_location_id'] > 0): ?>
    <tr style="background-color:#FFFFFF" class="location-fee">
        <td align="left" class="col1" style="padding-left:5px;"><?=esc_html($lang['LANG_LOCATION_PICKUP_FEE2_TEXT']);?>
            <?php if($priceSummary['pickup_in_afterhours']) { print(" ".esc_html($lang['LANG_LOCATION_NIGHTLY_RATE_APPLIED_TEXT'])); } ?>
        </td>
        <td align="left" class="col2" style="padding-left:5px;">
            <span title="<?=$priceSummary['pickup']['print_current_pickup_fee_details'];?>" style="cursor:pointer">
                <?=$priceSummary['pickup']['unit_print']['current_pickup_fee'];?>
                <?=esc_html($priceSummary['pickup']['multiplier'] > 1 ? ' x '.$priceSummary['pickup']['multiplier'] : '');?>
            </span>
        </td>
        <td align="right" class="col3" style="padding-right:5px;">
            <span title="<?=$priceSummary['pickup']['print_multiplied_current_pickup_fee_details'];?>" style="cursor:pointer">
                <?=$priceSummary['pickup']['multiplied_print']['current_pickup_fee'];?>
            </span>
        </td>
    </tr>
<?php endif; ?>



<!-- RETURN FEE -->
<?php if($showLocationFees && $returnLocationVisible && $priceSummary['return_location_id'] > 0): ?>
    <tr style="background-color:#FFFFFF" class="location-fee">
        <td align="left" class="col1" style="padding-left:5px;"><?=esc_html($lang['LANG_LOCATION_RETURN_FEE2_TEXT']);?>
            <?php if($priceSummary['return_in_afterhours']) { print(" ".esc_html($lang['LANG_LOCATION_NIGHTLY_RATE_APPLIED_TEXT'])); } ?>
        </td>
        <td align="left" class="col2" style="padding-left:5px;">
            <span title="<?=$priceSummary['return']['print_current_return_fee_details'];?>" style="cursor:pointer">
                <?=$priceSummary['return']['unit_print']['current_return_fee'];?>
                <?=esc_html($priceSummary['return']['multiplier'] > 1 ? ' x '.$priceSummary['return']['multiplier'] : '');?>
            </span>
        </td>
        <td align="right" class="col3" style="padding-right:5px;">
            <span title="<?=$priceSummary['return']['print_multiplied_current_return_fee_details'];?>" style="cursor:pointer">
                <?=$priceSummary['return']['multiplied_print']['current_return_fee'];?>
            </span>
        </td>
    </tr>
<?php endif; ?>

<!-- EXTRAS -->
<?php if(sizeof($priceSummary['extras']) > 0): ?>
    <tr class="extras-header" style="background-color:#343434; color: white;">
        <td align="left" class="col1" colspan="3"><strong><?=esc_html($lang['LANG_RENTAL_OPTIONS_TEXT']);?></strong></td>
    </tr>
<?php endif; ?>
<?php foreach($priceSummary['extras'] AS $extra): ?>
    <tr style="background-color:#FFFFFF" class="extra">
        <td align="left" class="col1" style="padding-left:5px;"><?=esc_html($extra['translated_extra_with_option']);?></td>
        <td align="left" class="col2" style="padding-left:5px;">
            <span title="<?php
            if($extra['tax_percentage'] > 0):
                print($extra['unit_print']['discounted_total'].' '.esc_html($lang['LANG_TAX_WITHOUT_TEXT']).' x ');
                print($extra['multiplier'].' '.esc_html($lang['LANG_EXTRAS_QUANTITY_SUFFIX_TEXT']).' + ');
                print($extra['unit_print']['discounted_tax_amount'].' '.esc_html($lang['LANG_TAX_SHORT_TEXT']).' = ');
            endif;
            print($extra['unit_print']['discounted_total_with_tax'].' x '.$extra['multiplier']);
            ?>" style="cursor:pointer">
                <?=$extra['unit_print']['discounted_total'];?>
                <?=esc_html($extra['multiplier'] > 1 ? ' x '.$extra['multiplier'] : '');?>
            </span>
        </td>
        <td align="right" class="col3" style="padding-right:5px;">
            <span title="<?php
            if($extra['tax_percentage'] > 0):
                print($extra['multiplied_print']['discounted_total'].' '.esc_html($lang['LANG_TAX_WITHOUT_TEXT']).' + ');
                print($extra['multiplied_print']['discounted_tax_amount'].' '.esc_html($lang['LANG_TAX_SHORT_TEXT']).' = ');
                print($extra['multiplied_print']['discounted_total_with_tax']);
            endif;
            ?>" style="cursor:pointer">
                <?=$extra['multiplied_print']['discounted_total'];?>
            </span>
        </td>
    </tr>
<?php endforeach; ?>

<!-- ADDITIONAL FEES -->
<?php if(sizeof($priceSummary['additional_fees']) >= 1): ?>
    <tr style="background-color:#343434; color: white" class="additional-fees-header">
        <td align="left" class="col1" style="padding-left:5px;" colspan="3"><strong><?=esc_html($lang['LANG_ADDITIONAL_FEES_TEXT']);?></strong></td>
    </tr>
    <?php foreach($priceSummary['additional_fees'] AS $additionalFee): ?>
        <tr style="background-color:#FFFFFF" class="additional-fee">
            <td align="left" class="col1" style="padding-left:5px;">
                <?=esc_html($additionalFee['translated_additional_fee_name']);?>
            </td>
            <td align="left" class="col2" style="padding-left:5px;">
                <span title="<?php
                if($additionalFee['tax_percentage'] > 0):
                    print($additionalFee['single_print']['total'].' '.esc_html($lang['LANG_TAX_WITHOUT_TEXT']).' x ');
                    print($additionalFee['applications'].' '.esc_html($lang['LANG_APPLICATIONS_SUFFIX2_TEXT']).' + ');
                    print($additionalFee['single_print']['tax_amount'].' '.esc_html($lang['LANG_TAX_SHORT_TEXT']).' = ');
                endif;
                print($additionalFee['single_print']['total_with_tax'].' x '.$additionalFee['applications']);
                ?>" style="cursor:pointer">
                    <?=$additionalFee['single_print']['total'];?>
                    <?=$additionalFee['applications'] > 1 ? ' x '.$additionalFee['applications'] : '';?>
                </span>
            </td>
            <td align="right" class="col3" style="padding-right:5px;">
                <span title="<?php
                if($additionalFee['tax_percentage'] > 0):
                    print($additionalFee['multiplied_print']['total'].' '.esc_html($lang['LANG_TAX_WITHOUT_TEXT']).' + ');
                    print($additionalFee['multiplied_print']['tax_amount'].' '.esc_html($lang['LANG_TAX_SHORT_TEXT']).' = ');
                    print($additionalFee['multiplied_print']['total_with_tax']);
                endif;
                ?>" style="cursor:pointer">
                    <?=$additionalFee['multiplied_print']['total'];?>
                </span>
            </td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>

<!-- TOTAL -->
<?php $counter = 0; ?>
<tr style="background-color:#343434; color: white;" class="total-headers">
    <td align="left" class="col1" colspan="3" style="padding-left:5px;"><strong><?=esc_html($lang['LANG_TOTAL_TEXT']);?></strong></td>
</tr>
<?php if($priceSummary['overall']['gross_total'] < $priceSummary['overall']['grand_total']): ?>
    <?php $counter++; ?>
    <tr style="<?=($counter % 2 == 0 ? 'background-color:#f2f2f2' : 'background-color:#FFFFFF');?>">
        <td align="right" class="col1" style="padding-right:5px;" colspan="2">
            <?=esc_html($lang['LANG_PAYMENT_GROSS_TOTAL_TEXT']);?>:
        </td>
        <td align="right" class="col3" style="padding-right:5px;" id = "subTotal">
            <?=$priceSummary['overall_print']['gross_total'];?>
        </td>
    </tr>
<?php endif; ?>
<?php if(sizeof($priceSummary['taxes']) > 0): ?>
    <!-- TAXES: START -->
    <?php $counter++; ?>
    <?php foreach($priceSummary['taxes'] AS $tax): ?>
        <tr style="<?=($counter % 2 == 0 ? 'background-color:#f2f2f2' : 'background-color:#FFFFFF');?>">
            <td align="right" class="col1" style="padding-right:5px;" colspan="2">
                <?=($tax['print_translated_tax_name'].' ('.esc_html($tax['formatted_tax_percentage']).')');?>:
            </td>
            <td align="right" class="col3" style="padding-right:5px;" id= "vatTax">
                <?=$tax['print_tax_amount'];?>
            </td>
			<span id="vatTaxPercent" style="display:none;><?=$tax['formatted_tax_percentage'];?></span>
        </tr>
    <?php endforeach; ?>
    <!-- TAXES: END -->
<?php endif; ?>
<?php $counter++; ?>
<tr style="<?=($counter % 2 == 0 ? 'background-color:#f2f2f2' : 'background-color:#FFFFFF');?>">
    <td align="right" class="col1" style="padding-right:5px;" colspan="2">
        <strong><?=esc_html($lang['LANG_PAYMENT_GRAND_TOTAL_TEXT']);?>:</strong>
    </td>
    <td align="right" class="col3" style="padding-right:5px;">
        <strong id ="grandTotal"><?=$priceSummary['overall_print']['grand_total'];?></strong>
    </td>
</tr>
<?php if($settings['conf_deposit_enabled'] == 1 && $priceSummary['overall']['fixed_deposit'] > 0): ?>
    <?php $counter++; ?>
    <tr style="<?=($counter % 2 == 0 ? 'background-color:#f2f2f2' : 'background-color:#FFFFFF');?>">
        <td align="right" class="col1" style="padding-right:5px;" colspan="2">
            <?=esc_html($lang['LANG_DEPOSIT_TEXT']);?>:
        </td>
        <td align="right" class="col3" style="padding-right:5px;">
            <span title="<?php
            print(esc_html($lang['LANG_ITEM_MODELS_TEXT']).' '.esc_html($lang['LANG_DEPOSIT_TEXT']).' ');
            print('('.$priceSummary['overall_print']['fixed_item_model_deposit'].') + ');
            print(esc_html($lang['LANG_EXTRAS_TEXT']).' '.esc_html($lang['LANG_DEPOSIT_TEXT']).' ');
            print('('.$priceSummary['overall_print']['fixed_extra_deposit'].') = ');
            print($priceSummary['overall_print']['fixed_deposit']);
            ?>" style="cursor:pointer">
                <?=$priceSummary['overall_print']['fixed_deposit'];?>
            </span>
        </td>
    </tr>
<?php endif; ?>
<?php if($settings['conf_prepayment_enabled'] == 1): ?>
    <?php $counter++; ?>
    <tr style="<?=($counter % 2 == 0 ? 'background-color:#f2f2f2' : 'background-color:#FFFFFF');?>">
        <td align="right" class="col1" style="padding-right:5px;" colspan="2">
            <strong><?=$payNowText;?>:</strong>
        </td>
        <td align="right" class="col3" style="padding-right:5px;">
            <span title="<?=$priceSummary['overall_print']['total_pay_now'];?>" style="cursor:pointer">
                <strong><?=$priceSummary['overall_print']['total_pay_now'];?></strong>
            </span>
        </td>
    </tr>
    <?php $counter++; ?>
    <tr style="<?=($counter % 2 == 0 ? 'background-color:#f2f2f2' : 'background-color:#FFFFFF');?>">
        <td align="right" class="col1" style="padding-right:5px;" colspan="2">
            <?=esc_html($lang['LANG_PAYMENT_PAY_LATER_TEXT']);?>:
        </td>
        <td align="right" class="col3" style="padding-right:5px;">
            <span title="<?php
            print(esc_html($lang['LANG_PAYMENT_GRAND_TOTAL_TEXT']).' ('.$priceSummary['overall_print']['grand_total'].') - ');
            print(esc_html($lang['LANG_PAYMENT_PAY_NOW_TEXT']).' ('.$priceSummary['overall_print']['total_pay_now'].') = ');
            print($priceSummary['overall_print']['total_pay_later']);
            ?>" style="cursor:pointer" id = "payLater">
                <?=$priceSummary['overall_print']['total_pay_later'];?>
            </span>
        </td>
    </tr>
<?php endif; ?>
</tbody>
</table>

<!-- PAYMENT METHOD DETAILS -->
<?php if($showPaymentDetails): ?>
    <br />
    <table style="font-family:Verdana, Geneva, sans-serif; font-size: 12px; background-color:#999999; width:840px; border:none;" cellpadding="4" cellspacing="1">
        <tr>
            <td align="left" colspan="2" style="font-weight:bold; background-color:#eeeeee; padding-left:5px;"><?=esc_html($lang['LANG_PAYMENT_DETAILS_TEXT']);?></td>
        </tr>
        <tr>
            <td align="left" width="30%" style="font-weight:bold; background-color:#ffffff; padding-left:5px;"><?=esc_html($lang['LANG_PAYMENT_OPTION_TEXT']);?></td>
            <td align="left" style="background-color:#ffffff; padding-left:5px;"><?=$paymentMethodName;?></td>
        </tr>
    </table>
	</div>

<?php endif; 