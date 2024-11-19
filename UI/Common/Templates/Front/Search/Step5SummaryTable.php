<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Scripts
wp_enqueue_script('jquery');
wp_enqueue_script('jquery-validate');
wp_enqueue_script('fleet-management-main');

// Styles
wp_enqueue_style('fleet-management-main');
if($newOrder == true && $settings['conf_universal_analytics_enhanced_ecommerce'] == 1):
    include 'Shared/Step5EnhancedEcommercePartial.php';
endif;
?>
<div class="fleet-management-wrapper <?=esc_attr($extCSS_Prefix);?>wrapper fleet-management-search-summary-table <?=esc_attr($extCSS_Prefix);?>search-summary-table">
<h2 class="summary-page-title"><?=esc_html($pageLabel);?></h2>
<table cellpadding="4" cellspacing="1" border="0" width="100%" bgcolor="#FFFFFF">
<tbody>
<!-- LOCATIONS: START -->
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
                <?=esc_html($priceSummary['expected_pickup_date_i18n'].' &nbsp;&nbsp; '.$priceSummary['expected_pickup_time_i18n']);?>
            </td>
        <?php endif; ?>
        <?php if($returnDateVisible): ?>
            <td align="left" class="col2" style="padding-left:5px;" colspan="<?=esc_attr($returnColspan);?>">
                <?=esc_html($priceSummary['expected_return_date_i18n'].' &nbsp;&nbsp; '.$priceSummary['expected_return_time_i18n']);?>
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
    <tr style="background-color:#FFFFFF" class="item-model">
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
            ?>" style="cursor:pointer">
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
            ?>" style="cursor:pointer">
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
        <td align="right" class="col3" style="padding-right:5px;">
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
            <td align="right" class="col3" style="padding-right:5px;">
                <?=$tax['print_tax_amount'];?>
            </td>
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
        <strong><?=$priceSummary['overall_print']['grand_total'];?></strong>
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
            ?>" style="cursor:pointer">
                <?=$priceSummary['overall_print']['total_pay_later'];?>
            </span>
        </td>
    </tr>
<?php endif; ?>
</tbody>
</table>


<?php if($isLoggedIn === false && $newOrder && $showLoginForm == true): ?>
    <h2 class="login-label top-padded login-result"><?=esc_html($lang['LANG_USER_ALREADY_HAVE_AN_ACCOUNT_TEXT']);?></h2>
    <form name="login_form" method="POST" action="" class="login-form">
        <div class="login-fields">
            <input type="text" name="login_account_id_or_email" class="login-account-id-or-email required" value="<?=esc_attr($lang['LANG_USER_ACCOUNT_ID_OR_EMAIL_TEXT']);?>"
                   onfocus="if(this.value === '<?=esc_js($lang['LANG_USER_ACCOUNT_ID_OR_EMAIL_TEXT']);?>') {this.value=''}"
                   onblur="if(this.value === ''){this.value ='<?=esc_js($lang['LANG_USER_ACCOUNT_ID_OR_EMAIL_TEXT']);?>'}"
                   title="<?=esc_attr($lang['LANG_USER_ACCOUNT_ID_OR_EMAIL_TEXT']);?>"
            />
            <input type="password" name="login_password" class="login-password required" value="******************"
                   onfocus="if(this.value === '******************') {this.value=''}"
                   onblur="if(this.value === ''){this.value ='******************'}"
                   title="<?=esc_attr($lang['LANG_USER_PASSWORD_TEXT']);?>"
            />
        </div>
        <div class="login-button">
            <button type="submit" name="<?=esc_attr($extPrefix);?>do_login" class="login-button"><?=esc_html($lang['LANG_USER_LOGIN_TEXT']);?></button>
        </div>
        <p class="login-status"></p>
    </form>
<?php endif; ?>

<!-- CUSTOMER SELECT FOR ACCOUNT HOLDERS: START (NOTE: OUTER SPAN HERE MUST ALWAYS EXIST TO HANDLE POST-LOGIN PROCESS CORRECTLY!) -->
<span class="customer-select-block">
<?php if($isLoggedIn === true && $trustedCustomersDropdownOptionsHTML != ""): ?>
    <h2 class="customer-select-label top-padded"><?=esc_html($lang['LANG_CUSTOMER_SELECT_TEXT']);?></h2>
    <div class="customer-select">
        <select name="customer_id" class="customer-dropdown" title="<?=esc_attr($lang['LANG_CUSTOMER_SELECT_TEXT']);?>"
                onchange="FleetManagementMain.setCustomerById('<?=esc_js($extCode);?>', this.value);">
            <?=$trustedCustomersDropdownOptionsHTML;?>
        </select>
    </div>
<?php endif; ?>
</span>
<!-- CUSTOMER SELECT FOR ACCOUNT HOLDERS: END -->

<!-- CUSTOMER SEARCH FOR GUESTS: START -->
<span class="guest-customer-lookup-section">
<?php if($isLoggedIn === false && $guestCustomerLookupAllowed && $newOrder && $boolCustomerEmailRequired && $boolCustomerBirthdateRequired): ?>
    <h2 class="search-label top-padded"><?=esc_html($lang['LANG_CUSTOMER_SEARCH_FOR_EXISTING_TEXT']);?></h2>
    <div class="form-full-row">
        <div class="customer-email-search">
            <input type="text" name="search_email_address" class="search-customer-email-address" value="<?=esc_attr($lang['LANG_EMAIL_ADDRESS_TEXT']);?>"
                   onfocus="if(this.value === '<?=esc_js($lang['LANG_EMAIL_ADDRESS_TEXT']);?>') {this.value=''}"
                   onblur="if(this.value === ''){this.value ='<?=esc_js($lang['LANG_EMAIL_ADDRESS_TEXT']);?>'}"
                   title="<?=esc_attr($lang['LANG_EMAIL_ADDRESS_TEXT']);?>"
            />
        </div>
        <div class="customer-birthdate-search">
            <?php if($this->settings['conf_short_date_format'] == "Y-m-d"): ?>
                <select name="search_birth_year" class="search-customer-birth-year" title="<?=esc_attr($lang['LANG_YEAR_OF_BIRTH_TEXT']);?>"><?=$trustedCustomerBirthYearSearchDropdownOptionsHTML;?></select>
                <select name="search_birth_month" class="search-customer-birth-month" title="<?=esc_attr($lang['LANG_MONTH_OF_BIRTH_TEXT']);?>"><?=$trustedCustomerBirthMonthSearchDropdownOptionsHTML;?></select>
                <select name="search_birth_day" class="search-customer-birth-day" title="<?=esc_attr($lang['LANG_DAY_OF_BIRTH_TEXT']);?>"><?=$trustedCustomerBirthDaySearchDropdownOptionsHTML;?></select>
            <?php elseif($this->settings['conf_short_date_format'] == "d/m/Y"): ?>
                <select name="search_birth_day" class="search-customer-birth-day" title="<?=esc_attr($lang['LANG_DAY_OF_BIRTH_TEXT']);?>"><?=$trustedCustomerBirthDaySearchDropdownOptionsHTML;?></select>
                <select name="search_birth_month" class="search-customer-birth-month" title="<?=esc_attr($lang['LANG_MONTH_OF_BIRTH_TEXT']);?>"><?=$trustedCustomerBirthMonthSearchDropdownOptionsHTML;?></select>
                <select name="search_birth_year" class="search-customer-birth-year" title="<?=esc_attr($lang['LANG_YEAR_OF_BIRTH_TEXT']);?>"><?=$trustedCustomerBirthYearSearchDropdownOptionsHTML;?></select>
            <?php elseif($this->settings['conf_short_date_format'] == "m/d/Y"): ?>
                <select name="search_birth_month" class="search-customer-birth-month" title="<?=esc_attr($lang['LANG_MONTH_OF_BIRTH_TEXT']);?>"><?=$trustedCustomerBirthMonthSearchDropdownOptionsHTML;?></select>
                <select name="search_birth_day" class="search-customer-birth-day" title="<?=esc_attr($lang['LANG_DAY_OF_BIRTH_TEXT']);?>"><?=$trustedCustomerBirthDaySearchDropdownOptionsHTML;?></select>
                <select name="search_birth_year" class="search-customer-birth-year" title="<?=esc_attr($lang['LANG_YEAR_OF_BIRTH_TEXT']);?>"><?=$trustedCustomerBirthYearSearchDropdownOptionsHTML;?></select>
            <?php endif; ?>
        </div>
        <div class="customer-lookup-button">
            <button type="submit" name="customer_lookup" class="customer-lookup"><?=esc_html($lang['LANG_CUSTOMER_FETCH_DETAILS_TEXT']);?></button>
        </div>
    </div>
    <div class="ajax-loader">&nbsp;</div>
    <div class="clear">&nbsp;</div>
<?php endif; ?>
</span>
<!-- CUSTOMER SEARCH FOR GUESTS: END -->


<!-- CUSTOMER DETAILS FORM: START -->
<?php if($isLoggedIn === false && $guestCustomerLookupAllowed && $newOrder && $boolCustomerEmailRequired && $boolCustomerBirthdateRequired): ?>
    <h2 class="search-label"><?=esc_html($lang['LANG_CUSTOMER_OR_ENTER_DETAILS_TEXT']);?></h2>
<?php else: ?>
    <h2 class="search-label top-padded"><?=esc_html($lang['LANG_CUSTOMER_DETAILS_TEXT']);?></h2>
<?php endif; ?>
<!-- Class order-form is used for jQuery and customer-details-form used form -->
<form name="order_form" method="POST" action="" class="<?=esc_attr($extCSS_Prefix);?>order-form customer-details-form">
    <?php if($customerTitleVisible): ?>
        <div class="form-row">
            <div class="customer-data-label">
                <strong><?=esc_html($lang['LANG_TITLE_TEXT']);?>:<span class="dynamic-requirement<?=esc_attr($customerTitleRequired);?>">*</span></strong>
            </div>
            <div class="customer-data-input">
                <select name="customer_title" class="customer-title<?=esc_attr($customerTitleRequired);?>" title="<?=esc_attr($lang['LANG_TITLE_TEXT']);?>">
                    <?=$trustedCustomerTitlesDropdownOptionsHTML;?>
                </select>
            </div>
        </div>
    <?php endif; ?>
    <?php if($customerFirstNameVisible): ?>
        <div class="form-row">
            <div class="customer-data-label">
                <strong><?=esc_html($lang['LANG_FIRST_NAME_TEXT']);?>:<span class="dynamic-requirement<?=esc_attr($customerFirstNameRequired);?>">*</span></strong>
            </div>
            <div class="customer-data-input">
                <input type="text" name="customer_first_name" maxlength="50" value="<?=esc_attr($customerFirstName);?>"
                       class="customer-first-name<?=esc_attr($customerFirstNameRequired);?>" title="<?=esc_attr($lang['LANG_FIRST_NAME_TEXT']);?>" />
            </div>
        </div>
    <?php endif; ?>
    <?php if($customerLastNameVisible): ?>
        <div class="form-row">
            <div class="customer-data-label">
                <strong><?=esc_html($lang['LANG_LAST_NAME_TEXT']);?>:<span class="dynamic-requirement<?=esc_attr($customerLastNameRequired);?>">*</span></strong>
            </div>
            <div class="customer-data-input">
                <input type="text" name="customer_last_name" maxlength="50" value="<?=esc_attr($customerLastName);?>"
                       class="customer-last-name<?=esc_attr($customerLastNameRequired);?>" title="<?=esc_attr($lang['LANG_LAST_NAME_TEXT']);?>" />
            </div>
        </div>
    <?php endif; ?>
    <?php if($customerBirthdateVisible): ?>
        <div class="form-row">
            <div class="customer-data-label">
                <strong><?=esc_html($lang['LANG_DATE_OF_BIRTH_TEXT']);?>:<span class="dynamic-requirement<?=esc_attr($customerBirthdateRequired);?>">*</span></strong>
            </div>
            <div class="customer-data-input customer-birthday-select">
                <?php if($this->settings['conf_short_date_format'] == "Y-m-d"): ?>
                    <select name="customer_birth_year" class="customer-birth-year<?=esc_attr($customerBirthdateRequired);?>" title="<?=esc_attr($lang['LANG_YEAR_SELECT_TEXT']);?>"><?=$trustedCustomerBirthYearDropdownOptionsHTML;?></select>
                    <select name="customer_birth_month" class="customer-birth-month<?=esc_attr($customerBirthdateRequired);?>" title="<?=esc_attr($lang['LANG_MONTH_SELECT_TEXT']);?>"><?=$trustedCustomerBirthMonthDropdownOptionsHTML;?></select>
                    <select name="customer_birth_day" class="customer-birth-day<?=esc_attr($customerBirthdateRequired);?>" title="<?=esc_attr($lang['LANG_DAY_SELECT_TEXT']);?>"><?=$trustedCustomerBirthDayDropdownOptionsHTML;?></select>
                <?php elseif($this->settings['conf_short_date_format'] == "d/m/Y"): ?>
                    <select name="customer_birth_day" class="customer-birth-day<?=esc_attr($customerBirthdateRequired);?>" title="<?=esc_attr($lang['LANG_DAY_SELECT_TEXT']);?>"><?=$trustedCustomerBirthDayDropdownOptionsHTML;?></select>
                    <select name="customer_birth_month" class="customer-birth-month<?=esc_attr($customerBirthdateRequired);?>" title="<?=esc_attr($lang['LANG_MONTH_SELECT_TEXT']);?>"><?=$trustedCustomerBirthMonthDropdownOptionsHTML;?></select>
                    <select name="customer_birth_year" class="customer-birth-year<?=esc_attr($customerBirthdateRequired);?>" title="<?=esc_attr($lang['LANG_YEAR_SELECT_TEXT']);?>"><?=$trustedCustomerBirthYearDropdownOptionsHTML;?></select>
                <?php elseif($this->settings['conf_short_date_format'] == "m/d/Y"): ?>
                    <select name="customer_birth_month" class="customer-birth-month<?=esc_attr($customerBirthdateRequired);?>" title="<?=esc_attr($lang['LANG_MONTH_SELECT_TEXT']);?>"><?=$trustedCustomerBirthMonthDropdownOptionsHTML;?></select>
                    <select name="customer_birth_day" class="customer-birth-day<?=esc_attr($customerBirthdateRequired);?>" title="<?=esc_attr($lang['LANG_DAY_SELECT_TEXT']);?>"><?=$trustedCustomerBirthDayDropdownOptionsHTML;?></select>
                    <select name="customer_birth_year" class="customer-birth-year<?=esc_attr($customerBirthdateRequired);?>" title="<?=esc_attr($lang['LANG_YEAR_SELECT_TEXT']);?>"><?=$trustedCustomerBirthYearDropdownOptionsHTML;?></select>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    <?php if($customerStreetAddressVisible): ?>
        <div class="form-row">
            <div class="customer-data-label">
                <strong><?=esc_html($lang['LANG_ADDRESS_TEXT']);?>:<span class="dynamic-requirement<?=esc_attr($customerStreetAddressRequired);?>">*</span></strong>
            </div>
            <div class="customer-data-input">
                <input type="text" name="customer_street_address" maxlength="50" value="<?=esc_attr($customerStreetAddress);?>"
                       class="customer-street-address<?=esc_attr($customerStreetAddressRequired);?>" title="<?=esc_attr($lang['LANG_ADDRESS_TEXT']);?>" />
            </div>
        </div>
    <?php endif; ?>
    <?php if($customerCityVisible): ?>
        <div class="form-row">
            <div class="customer-data-label">
                <strong><?=esc_html($lang['LANG_CITY_TEXT']);?>:<span class="dynamic-requirement<?=esc_attr($customerCityRequired);?>">*</span></strong>
            </div>
            <div class="customer-data-input">
                <input type="text" name="customer_city" maxlength="50" value="<?=esc_attr($customerCity);?>"
                       class="customer-city<?=esc_attr($customerCityRequired);?>" title="<?=esc_attr($lang['LANG_CITY_TEXT']);?>" />
            </div>
        </div>
    <?php endif; ?>
    <?php if($customerStateVisible): ?>
        <div class="form-row">
            <div class="customer-data-label">
                <strong><?=esc_html($lang['LANG_STATE_TEXT']);?>:<span class="dynamic-requirement<?=esc_attr($customerStateRequired);?>">*</span></strong>
            </div>
            <div class="customer-data-input">
                <input type="text" name="customer_state" value="<?=esc_attr($customerState);?>"
                       class="customer-state<?=esc_attr($customerStateRequired);?>" title="<?=esc_attr($lang['LANG_STATE_TEXT']);?>" />
            </div>
        </div>
    <?php endif; ?>
    <?php if($customerZIP_CodeVisible): ?>
        <div class="form-row">
            <div class="customer-data-label">
              <strong><?=esc_html($lang['LANG_ZIP_CODE_TEXT']);?>:<span class="dynamic-requirement<?=esc_attr($customerZIP_CodeRequired);?>">*</span></strong>
            </div>
            <div class="customer-data-input">
                <input type="text" name="customer_zip_code" maxlength="20" value="<?=esc_attr($customerZIP_Code);?>"
                       class="customer-zip-code<?=esc_attr($customerZIP_CodeRequired);?>" title="<?=esc_attr($lang['LANG_ZIP_CODE_TEXT']);?>" />
            </div>
        </div>
    <?php endif; ?>
    <?php if($customerCountryVisible): ?>
        <div class="form-row">
            <div class="customer-data-label">
                <strong><?=esc_html($lang['LANG_COUNTRY_TEXT']);?>:<span class="dynamic-requirement<?=esc_attr($customerCountryRequired);?>">*</span></strong>
            </div>
            <div class="customer-data-input">
                <input type="text" name="customer_country" value="<?=esc_attr($customerCountry);?>"
                       class="customer-country<?=esc_attr($customerCountryRequired);?>" title="<?=esc_attr($lang['LANG_COUNTRY_TEXT']);?>" />
            </div>
        </div>
    <?php endif; ?>
    <?php if($customerPhoneVisible): ?>
        <div class="form-row">
            <div class="customer-data-label">
                <strong><?=esc_html($lang['LANG_PHONE_TEXT']);?>:<span class="dynamic-requirement<?=esc_attr($customerPhoneRequired);?>">*</span></strong>
            </div>
            <div class="customer-data-input">
                <input type="text" name="customer_phone" maxlength="50" value="<?=esc_attr($customerPhone);?>"
                       class="customer-phone<?=esc_attr($customerPhoneRequired);?>" title="<?=esc_attr($lang['LANG_PHONE_TEXT']);?>" />
            </div>
        </div>
    <?php endif; ?>
    <?php if($customerEmailVisible): ?>
        <div class="form-row">
            <div class="customer-data-label">
                <strong><?=esc_html($lang['LANG_EMAIL_TEXT']);?>:<span class="dynamic-requirement<?=esc_attr($customerEmailRequired);?>">*</span></strong>
            </div>
            <div class="customer-data-input">
                <?php if($newOrder && $isLoggedIn) : ?>
                <strong><?=esc_html($customerEmail)?></strong>
                <input type="hidden" name="customer_email" maxlength="50" value="<?=esc_attr($customerEmail);?>"
                       class="customer-email<?=esc_attr($customerEmailRequired);?> email<?=($newOrder && $isLoggedIn ? ' disabled' : '');?>" title="<?=esc_attr($lang['LANG_EMAIL_TEXT']);?>" />
                <?php else : ?>
                <input type="text" name="customer_email" maxlength="50" value="<?=esc_attr($customerEmail);?>"
                       class="customer-email<?=esc_attr($customerEmailRequired);?> email<?=($newOrder && $isLoggedIn ? ' disabled' : '');?>" title="<?=esc_attr($lang['LANG_EMAIL_TEXT']);?>" />
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    <?php if($customerCommentsVisible): ?>
        <div class="form-row">
            <div class="customer-data-label">
                <strong><?=esc_html($lang['LANG_ADDITIONAL_COMMENTS_TEXT']);?>:<span class="dynamic-requirement<?=esc_attr($customerCommentsRequired);?>">*</span></strong>
            </div>
            <div class="customer-data-input customer-textarea">
                <textarea name="customer_comments" class="customer-comments<?=esc_attr($customerCommentsRequired);?>"><?=esc_textarea($customerComments);?></textarea>
            </div>
        </div>
    <?php endif; ?>
    <?php if($settings['conf_prepayment_enabled'] == 1 && sizeof($paymentMethods) > 0): ?>
        <!-- PAYMENT METHOD SELECTION: START -->
        <div class="form-row">
            <div class="customer-data-label">
                <strong><?=esc_html($lang['LANG_PAYMENT_PAY_BY_SHORT_TEXT']);?>:<span class="is-required">*</span></strong>
            </div>
            <div class="customer-data-input">
                <?php
                if($newOrder == false):
                    // only 1 payment method
                    if($selectedPaymentMethodName != ""):
                        print('<div class="payment-method-name">'.$selectedPaymentMethodName.'</div>');
                        if($selectedPaymentMethodDescription != ""):
                            print('<div class="payment-method-description">'.$selectedPaymentMethodDescription.'</div>');
                        endif;
                    endif;
                else:
                    if(sizeof($paymentMethods) > 1):
                        foreach($paymentMethods AS $paymentMethod):
                            print('<div class="payment-method-name">');
                            print('<input type="radio" name="payment_method_id" value="'.esc_attr($paymentMethod['payment_method_id']).'"'.$paymentMethod['print_checked'].' class="required" />');
                            print($paymentMethod['payment_method_name']);
                            print('</div>');
                            if($paymentMethod['payment_method_description_html'] != ""):
                                print('<div class="padded-payment-method-description">'.$paymentMethod['payment_method_description_html'].'</div>');
                            endif;
                        endforeach;
                    elseif(sizeof($paymentMethods) == 1):
                        // only 1 payment method
                        foreach($paymentMethods AS $paymentMethod):
                            print('<div class="payment-method-name">');
                            print('<input type="hidden" name="payment_method_id" value="'.esc_attr($paymentMethod['payment_method_id']).'" />');
                            print($paymentMethod['payment_method_name']);
                            print('</div>');
                            if($paymentMethod['payment_method_description_html'] != ""):
                                print('<div class="payment-method-description">'.$paymentMethod['payment_method_description_html'].'</div>');
                            endif;
                        endforeach;
                    endif;
                endif;
                ?>
                <label class="error" generated="true" for="payment_method_id" style="display:none;"><?=esc_html($lang['LANG_FIELD_REQUIRED_TEXT']);?>.</label>
            </div>
        </div>
        <!-- PAYMENT METHOD SELECTION: END -->
    <?php endif; ?>


    <?php if($newOrder && $showReCaptcha): ?>
        <!-- CAPTCHA INPUT: START -->
        <div class="form-row captcha-block">
            <div class="customer-data-label">&nbsp;</div>
            <div class="customer-data-input">
                <div class="g-recaptcha" data-sitekey="<?=esc_attr($reCaptchaSiteKey);?>"></div>
                <script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl=<?=esc_attr($lang['RECAPTCHA_LANG']);?>"></script>
            </div>
        </div>
        <!-- CAPTCHA INPUT: END -->
    <?php endif; ?>


    <?php if($settings['conf_terms_and_conditions_page_id'] != "" && $settings['conf_terms_and_conditions_page_id'] != 0): ?>
        <div class="form-row">
            <div class="customer-data-label">&nbsp;</div>
            <div class="customer-data-input">
                <?php
                if($newOrder == false):
                    print('<input type="checkbox" name="terms_and_conditions" value="" checked="checked" class="terms-and-conditions required" />');
                    print('&nbsp; <a href="'.esc_url($termsAndConditionsURL).'" target="_blank">'.esc_html($lang['LANG_SEARCH_I_AGREE_WITH_TERMS_AND_CONDITIONS_TEXT']).'</a>');
                else:
                    print('<input type="checkbox" name="terms_and_conditions" value="" class="terms-and-conditions required" />');
                    print('&nbsp; <a href="'.esc_url($termsAndConditionsURL).'" target="_blank">'.esc_html($lang['LANG_SEARCH_I_AGREE_WITH_TERMS_AND_CONDITIONS_TEXT']).'</a>');
                endif;
                ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="action-buttons">
        <input type="hidden" name="<?=esc_attr($extPrefix.$orderCodeParam);?>" value="<?=esc_attr($orderCode);?>" />
        <input type="hidden" name="<?=esc_attr($extPrefix);?>do_not_flush" value="yes" />
        <?php if($newOrder): ?>
            <?php if($settings['conf_universal_analytics_events_tracking'] == 1): ?>
                <!-- Note: Do not translate events to track well inter-language events -->
                <button type="submit" name="<?=esc_attr($extPrefix);?>do_search5" class="confirm-button"
                        onclick="ga('send', 'event', '<?=esc_js($extName);?>', 'Click', '5. Confirm reservation');"><?=esc_html($lang['LANG_CONFIRM_TEXT']);?></button>
            <?php else: ?>
                <button type="submit" name="<?=esc_attr($extPrefix);?>do_search5" class="confirm-button"><?=esc_html($lang['LANG_CONFIRM_TEXT']);?></button>
            <?php endif; ?>
        <?php else: ?>
            <input type="submit" name="<?=esc_attr($extPrefix);?>cancel_order" value="<?=esc_attr($lang['LANG_CANCEL_ORDER_TEXT']);?>" />
            <input type="submit" name="<?=esc_attr($extPrefix);?>do_search0" value="<?=esc_attr($lang['LANG_ORDER_CHANGE_DATE_TIME_AND_LOCATION_TEXT']);?>" />
            <input type="submit" name="<?=esc_attr($extPrefix);?>do_search" value="<?=esc_attr($lang['LANG_ORDER_CHANGE_ORDERED_ITEMS_TEXT']);?>" />
            <input type="submit" name="<?=esc_attr($extPrefix);?>do_search3" value="<?=esc_attr($lang['LANG_CHANGE_RENTAL_OPTIONS_TEXT']);?>" />
            <button name="<?=esc_attr($extPrefix);?>do_search5" class="confirm-button" type="submit"><?=esc_html($lang['LANG_ORDER_UPDATE_MY_ORDER_TEXT']);?></button>
        <?php endif; ?>
    </div>
</form>
</div>
<?php
// Global variable are needed here, because only then we will be able to access them inside the 'add_action' hook
$GLOBALS['EXT_CSS_PREFIX'] = $extCSS_Prefix;
$GLOBALS['EXT_CODE'] = $extCode;
$GLOBALS['LANG_REQUIRED_TEXT'] = $lang['LANG_REQUIRED_TEXT'];
$GLOBALS['IS_LOGGED_IN'] = $isLoggedIn;
$GLOBALS['NEW_ORDER'] = $newOrder;
$GLOBALS['BOOL_CUSTOMER_EMAIL_REQUIRED'] = $boolCustomerEmailRequired;
$GLOBALS['BOOL_CUSTOMER_BIRTHDATE_REQUIRED'] = $boolCustomerBirthdateRequired;
?>
<?php add_action('wp_footer', function() { // A workaround until #48098 will be resolved ( https://core.trac.wordpress.org/ticket/48098 ). Scripts are printed with the '20' priority. ?>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
    jQuery.extend(jQuery.validator.messages, {
        required: "<?=esc_html($GLOBALS['LANG_REQUIRED_TEXT']);?>"
    });
    jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>order-form').validate();

    <?php if($GLOBALS['IS_LOGGED_IN'] === false && $GLOBALS['BOOL_CUSTOMER_EMAIL_REQUIRED'] && $GLOBALS['BOOL_CUSTOMER_BIRTHDATE_REQUIRED']): ?>
        jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>search-summary-table .customer-lookup').on( "click", function()
        {
            var objCustomerEmailAddress = jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>search-summary-table .search-customer-email-address');
            var objCustomerYearOfBirth = jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>search-summary-table .search-customer-birth-year');
            var objCustomerMonthOfBirth = jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>search-summary-table .search-customer-birth-month');
            var objCustomerDayOfBirth = jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>search-summary-table .search-customer-birth-day');
            var customerEmailAddress = '';
            var isoCustomerBirthdate = '0000-00-00';

            if(objCustomerEmailAddress.length)
            {
                customerEmailAddress = objCustomerEmailAddress.val();
            }
            if(objCustomerYearOfBirth.length && objCustomerMonthOfBirth.length && objCustomerDayOfBirth.length)
            {
                isoCustomerBirthdate = objCustomerYearOfBirth.val() + '-' + objCustomerMonthOfBirth.val() + '-' + objCustomerDayOfBirth.val();
            }

            //console.log(customerEmailAddress);
            FleetManagementMain.setCustomerByEmailAndBirthdate('<?=esc_js($GLOBALS['EXT_CODE']);?>', customerEmailAddress, isoCustomerBirthdate);
        });
    <?php endif; ?>

    <?php if($GLOBALS['IS_LOGGED_IN'] === false && $GLOBALS['NEW_ORDER']): ?>
        // Perform AJAX login on form submit
        jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>search-summary-table form.login-form').on('submit', function(e)
        {
            FleetManagementMain.doLogin('<?=esc_js($GLOBALS['EXT_CODE']);?>');
            e.preventDefault();
        });
    <?php endif; ?>
});
</script>
<?php }, 100); ?>