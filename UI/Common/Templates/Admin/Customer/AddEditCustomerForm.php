<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Scripts
wp_enqueue_script('jquery');
wp_enqueue_script('jquery-ui-core'); // NOTE: We need it for datatables & datepicker in search params
wp_enqueue_script('datatables-jquery-datatables');
wp_enqueue_script('jquery-ui-datepicker', array('jquery','jquery-ui-core'));
wp_enqueue_script('jquery-ui-datepicker-locale');
wp_enqueue_script('jquery-validate');
wp_enqueue_script('fleet-management-admin');

// Styles
wp_enqueue_style('jquery-ui-theme');
wp_enqueue_style('jquery-validate');
wp_enqueue_style('fleet-management-admin');
?>
<p>&nbsp;</p>
<div class="fleet-management-tabbed-admin">
<div id="container-inside" class="fleet-management-add-customer">
  <span class="title"><?=esc_html($lang['LANG_CUSTOMER_ADD_EDIT_TEXT']);?></span>
  <input type="button" value="<?=esc_attr($lang['LANG_CUSTOMER_BACK_TO_LIST_TEXT']);?>" onclick="window.location.href='<?=esc_js($backToListURL);?>'" class="button-back"/>
  <hr/>
  <form action="<?=esc_url($formAction);?>" method="POST" class="customer-form">
    <table cellpadding="5" cellspacing="2" border="0">
      <input type="hidden" name="customer_id" value="<?=esc_attr($customerId);?>">
      <?php if($titleVisible): ?>
          <tr>
            <td><strong><?=esc_html($lang['LANG_TITLE_TEXT']);?>:<span class="dynamic-requirement<?=esc_attr($titleRequired);?>">*</span></strong></td>
            <td>
                <select name="title" class="title<?=esc_attr($titleRequired);?>">
                    <?=$trustedTitlesDropdownOptionsHTML;?>
                </select>
            </td>
          </tr>
      <?php endif; ?>
      <?php if($firstNameVisible): ?>
          <tr>
            <td align="left"><strong><?=esc_html($lang['LANG_FIRST_NAME_TEXT']);?>:<span class="dynamic-requirement<?=esc_attr($firstNameRequired);?>">*</span></strong></td>
            <td><input type="text" name="first_name" value="<?=esc_attr($firstName);?>" class="first-name<?=esc_attr($firstNameRequired);?> form-input" /></td>
          </tr>
      <?php endif; ?>
      <?php if($lastNameVisible): ?>
          <tr>
            <td align="left"><strong><?=esc_html($lang['LANG_LAST_NAME_TEXT']);?>:<span class="dynamic-requirement<?=esc_attr($lastNameRequired);?>">*</span></strong></td>
            <td><input type="text" name="last_name" value="<?=esc_attr($lastName);?>" class="last-name<?=esc_attr($lastNameRequired);?> form-input" /></td>
          </tr>
      <?php endif; ?>
      <?php if($birthdateVisible): ?>
          <tr>
            <td align="left"><strong><?=esc_html($lang['LANG_DATE_OF_BIRTH_TEXT']);?>:<span class="dynamic-requirement<?=esc_attr($birthdateRequired);?>">*</span></strong></td>
            <td>
                <select name="birth_year" class="birth-year<?=esc_attr($birthdateRequired);?>"><?=$trustedBirthYearDropdownOptionsHTML;?></select>
                <select name="birth_month" class="birth-month<?=esc_attr($birthdateRequired);?>"><?=$trustedBirthMonthDropdownOptionsHTML;?></select>
                <select name="birth_day" class="birth-day<?=esc_attr($birthdateRequired);?>"><?=$trustedBirthDayDropdownOptionsHTML;?></select>
            </td>
          </tr>
      <?php endif; ?>
      <?php if($streetAddressVisible): ?>
          <tr>
            <td align="left"><strong><?=esc_html($lang['LANG_STREET_ADDRESS_TEXT']);?>:<span class="dynamic-requirement<?=esc_attr($streetAddressRequired);?>">*</span></strong></td>
            <td><input type="text" name="street_address" value="<?=esc_attr($streetAddress);?>" class="street-address<?=esc_attr($streetAddressRequired);?> form-input"  /></td>
          </tr>
      <?php endif; ?>
      <?php if($cityVisible): ?>
          <tr>
            <td align="left"><strong><?=esc_html($lang['LANG_CITY_TEXT']);?>:<span class="dynamic-requirement<?=esc_attr($cityRequired);?>">*</span></strong></td>
            <td><input type="text" name="city" value="<?=esc_attr($city);?>" class="city<?=esc_attr($cityRequired);?> form-input" /></td>
          </tr>
      <?php endif; ?>
      <?php if($stateVisible): ?>
          <tr>
            <td align="left"><strong><?=esc_html($lang['LANG_STATE_TEXT']);?>:<span class="dynamic-requirement<?=esc_attr($stateRequired);?>">*</span></strong></td>
            <td><input type="text" name="state" value="<?=esc_attr($state);?>" class="state<?=esc_attr($stateRequired);?> form-input" /></td>
          </tr>
      <?php endif; ?>
      <?php if($zipCodeVisible): ?>
          <tr>
            <td align="left"><strong><?=esc_html($lang['LANG_ZIP_CODE_TEXT']);?>:<span class="dynamic-requirement<?=esc_attr($zipCodeRequired);?>">*</span></strong></td>
            <td><input type="text" name="zip_code" value="<?=esc_attr($zipCode);?>" class="zip-code<?=esc_attr($zipCodeRequired);?> form-input" /></td>
          </tr>
      <?php endif; ?>
      <?php if($countryVisible): ?>
          <tr>
            <td align="left"><strong><?=esc_html($lang['LANG_COUNTRY_TEXT']);?>:<span class="dynamic-requirement<?=esc_attr($countryRequired);?>">*</span></strong></td>
            <td><input type="text" name="country" value="<?=esc_attr($country);?>" class="country<?=esc_attr($countryRequired);?> form-input"  /></td>
          </tr>
      <?php endif; ?>
      <?php if($phoneVisible): ?>
          <tr>
            <td align="left"><strong><?=esc_html($lang['LANG_PHONE_TEXT']);?>:<span class="dynamic-requirement<?=esc_attr($phoneRequired);?>">*</span></strong></td>
            <td>
              <input type="text" name="phone" value="<?=esc_attr($phone);?>" class="phone<?=esc_attr($phoneRequired);?> form-input" />
            </td>
          </tr>
      <?php endif; ?>
      <?php if($emailVisible): ?>
          <tr>
            <td align="left"><strong><?=esc_html($lang['LANG_EMAIL_TEXT']);?>:<span class="dynamic-requirement<?=esc_attr($emailRequired);?>">*</span></strong></td>
            <td>
              <input type="text" name="email" value="<?=esc_attr($email);?>" class="email<?=esc_attr($emailRequired);?> form-input" />
            </td>
          </tr>
      <?php endif; ?>
      <?php if($commentsVisible): ?>
          <tr>
            <td align="left"><strong><?=esc_html($lang['LANG_ADDITIONAL_COMMENTS_TEXT']);?>:<span class="dynamic-requirement<?=esc_attr($commentsRequired);?>">*</span></strong></td>
            <td>
              <textarea name="comments" class="comments<?=esc_attr($commentsRequired);?>" rows="3" cols="50"><?=esc_textarea($comments);?></textarea>
            </td>
          </tr>
      <?php endif; ?>
      <?php if($existingCustomer): ?>
        <tr>
          <td align="left"><strong><?=esc_html($lang['LANG_CUSTOMER_ID_TEXT']);?>:</strong></td>
          <td>
            <strong><?=$customerId;?></strong>
          </td>
        </tr>
        <tr>
          <td align="left"><strong><?=esc_html($lang['LANG_IP_ADDRESS_TEXT']);?>:</strong></td>
          <td>
            <strong><?=$ip;?></strong>
          </td>
        </tr>
        <tr>
          <td align="left"><strong><?=esc_html($lang['LANG_CUSTOMER_EXISTING_TEXT']);?>:</strong></td>
          <td>
            <strong><?=($existingCustomer ? esc_html($lang['LANG_YES_TEXT']) : esc_html($lang['LANG_NO_TEXT']));?></strong>
          </td>
        </tr>
      <?php endif; ?>
      <tr>
        <td></td>
        <td>
          <input name="save_customer" type="submit" value="Save Customer" class="save-button"/>
        </td>
      </tr> 
    </table>
  </form>
</div>
</div>
<script type="text/javascript">
jQuery().ready(function() {
    'use strict';
  jQuery.extend(jQuery.validator.messages, {
    required: "<?=esc_html($lang['LANG_REQUIRED_TEXT']);?>"
  });
    jQuery(".customer-form").validate();
});
</script>