<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Scripts
wp_enqueue_script('jquery');
wp_enqueue_script('jquery-validate');
wp_enqueue_script('fleet-management-admin');

// Styles
wp_enqueue_style('jquery-validate');
wp_enqueue_style('fleet-management-admin');
?>
<div class="fleet-management-tabbed-admin">
<div id="container-inside">
 <p class="title"><?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Model Add/Edit</p>

<input type="button" value="Back to <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Model List" onclick="window.location.href='<?=esc_js($backToListURL);?>'" class="button-back"/><p><br />
<hr>
  <form action="<?=esc_url($formAction);?>" method="POST" class="add-edit-item-model" enctype="multipart/form-data">
   <table cellpadding="5" cellspacing="2" border="0">
    <input type="hidden" name="item_model_id" value="<?=esc_attr($itemModelId);?>"/>

<tr>
   <td><strong>Model Name:<span class="is-required">*</span></strong></td>
   <td colspan="2"><input type="text" name="item_model_name" value="<?=esc_attr($itemModelName);?>" id="item_model_name" class="required" /></td>
</tr>
<?php if($networkEnabled): ?>
   <tr>
       <td><strong>Stock Keeping Unit:<span class="is-required">*</span></strong></td>
       <td>
           <input type="text" name="item_model_sku" maxlength="20" value="<?=esc_attr($itemModelSKU);?>" id="item_model_sku" class="required" />
       </td>
       <td>
           <em>(Used for Google Enhanced Ecommerce tracking<br />
               and when plugin is network-enabled in multisite mode)</em>
       </td>
   </tr>
<?php endif; ?>
<?php if($isManager): ?>
   <tr>
       <td><strong>Partner:</strong></td>
       <td colspan="2">
           <select name="partner_id" id="partner_id">
                <?=$trustedPartnersDropdownOptionsHTML;?>
           </select>
       </td>
   </tr>
<?php endif; ?>
<tr>
   <td><strong>Manufacturer:</strong></td>
   <td colspan="2">
       <select name="manufacturer_id" id="manufacturer_id">
           <?=$trustedManufacturersDropdownOptionsHTML;?>
       </select>
   </td>
</tr>
<?php if($itemModelPagesDropdown): ?>
   <tr>
       <td><strong><?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Model Page:</strong></td>
       <td colspan="2"><?=$itemModelPagesDropdown;?></td>
   </tr>
<?php endif; ?>
<tr>
  <td><strong>Class:</strong></td>
  <td colspan="2">
      <select name="class_id" id="class_id">
          <?=$trustedClassesDropdownOptionsHTML;?>
      </select>
  </td>
</tr>
<tr>
   <td><strong><?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_NAME1_TEXT']);?>:</strong></td>
   <td colspan="2">
       <select name="attribute_id1" id="attribute_id1">
           <?=$trustedAttributeGroup1AttributesDropdownOptionsHTML;?>
       </select>
   </td>
</tr>
<tr>
  <td><strong><?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_NAME2_TEXT']);?>:</strong></td>
  <td colspan="2">
      <select name="attribute_id2" id="attribute_id2">
          <?=$trustedAttributeGroup2AttributesDropdownOptionsHTML;?>
      </select>
  </td>
</tr>
<tr>
   <td><strong><?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_NAME3_TEXT']);?>:</strong></td>
   <td colspan="2">
       <input type="text" name="fuel_consumption" value="<?=esc_attr($attribute3);?>" id="attribute3" />
       &nbsp;&nbsp;&nbsp; <em>(Leave blank hide the field from displaying)</em>
   </td>
</tr>
<tr>
   <td><strong><?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_NAME4_TEXT']);?>:</strong></td>
   <td>
       <select name="max_passengers" id="attribute4" class="" title="<?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_NAME4_TEXT']);?>">
           <?=$trustedAttribute4DropdownOptionsHTML;?>
       </select>
   </td>
</tr>
<tr>
   <td><strong><?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_NAME5_TEXT']);?>:</strong></td>
   <td>
       <input type="text" name="engine_capacity" value="<?=esc_attr($attribute5);?>" id="attribute5" title="<?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_NAME7_TEXT']);?>"/>
       &nbsp;&nbsp;&nbsp; <em>(Leave blank hide the field from displaying)</em>
   </td>
</tr>
<tr>
   <td><strong><?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_NAME6_TEXT']);?>:</strong></td>
   <td colspan="2">
       <select name="max_luggage" id="attribute6" class="" title="<?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_NAME6_TEXT']);?>">
           <?=$trustedAttribute6DropdownOptionsHTML;?>
       </select>
   </td>
</tr>
<tr>
   <td><strong><?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_NAME7_TEXT']);?>:</strong></td>
   <td colspan="2">
       <select name="item_doors" id="attribute7" class="" title="<?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_NAME7_TEXT']);?>">
           <?=$trustedAttribute7DropdownOptionsHTML;?>
       </select>
   </td>
</tr>
<tr>
   <td><strong><?=esc_html($lang['LANG_ATTRIBUTE_GROUP_DEFAULT_NAME8_TEXT']);?>:</strong></td>
   <td colspan="2">
       <input type="text" name="item_mileage" value="<?=esc_attr($attribute8);?>" id="attribute8" class="number" />
       &nbsp;<strong><?=esc_html($settings['conf_distance_measurement_unit']);?></strong>
       &nbsp;&nbsp;&nbsp; <em>(Leave blank for Unlimited, or enter 0 to hide the field from displaying)</em>
   </td>
</tr>
<tr>
  <td><strong>Total <?=esc_html($lang['LANG_MULTIPLE_VEHICLE_TITLE_UPPERCASE']);?> in Garage:<span class="is-required">*</span></strong></td>
  <td colspan="2">
      <select name="units_in_stock" id="units_in_stock" class="required">
          <?=$trustedItemsInStockDropdownOptionsHTML;?>
      </select>
  </td>
</tr>
<tr>
   <td><strong>Max. <?=esc_html($lang['LANG_MULTIPLE_VEHICLE_TITLE_UPPERCASE']);?> per Reservation:<span class="is-required">*</span></strong></td>
   <td colspan="2">
       <select name="max_units_per_booking" id="max_units_per_booking" class="required">
           <?=$trustedMaxItemsPerOrderDropdownOptionsHTML;?>
       </select>
       &nbsp;&nbsp;&nbsp; <em>(Can&#39;t be more than total <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> units in garage)</em>
   </td>
</tr>

<!-- LOCATIONS: START -->
<tr>
    <td valign="top">
        <strong>Pick-up Locations:<span class="is-required">*</span></strong><br />
        <em>(hold SHIFT or CTRL button<br />to select multiple locations)</em>
    </td>
    <td colspan="2">
        <select multiple="multiple" name="pickup_location_ids[]" class="required select-locations">
            <?=$pickupSelectOptions;?>
        </select>
    </td>
</tr>
<tr>
    <td valign="top">
        <strong>Return Locations:<span class="is-required">*</span></strong><br />
        <em>(hold SHIFT or CTRL button<br />to select multiple locations)</em>
    </td>
    <td colspan="2">
        <select multiple="multiple" name="return_location_ids[]" class="required select-locations">
            <?=$returnSelectOptions;?>
        </select>
    </td>
</tr>
<!-- LOCATIONS: END -->

<tr>
   <td><strong><?=esc_html($lang['LANG_ITEM_MODEL_MINIMUM_ALLOWED_AGE_TEXT']);?>:</strong></td>
   <td colspan="2">
       <select name="min_driver_age" id="min_driver_age" class="" title="<?=esc_html($lang['LANG_ITEM_MODEL_MINIMUM_ALLOWED_AGE_TEXT']);?>">
            <?=$trustedMinAllowedAgeDropdownOptionsHTML;?>
       </select>
   </td>
</tr>
<tr>
    <td><strong><?=esc_html($lang['LANG_ITEM_MODEL_IMAGE1_TEXT']);?>:</strong></td>
    <td colspan="2">
        <input type="file" name="item_image_1" title="<?=esc_attr($lang['LANG_ITEM_MODEL_IMAGE1_TEXT']);?>" />
        <?php if($itemModelImage1_URL != ""): ?>
            <span>
                &nbsp;&nbsp;&nbsp;<a rel="collection" href="<?=esc_url($itemModelImage1_URL);?>" target="_blank">
                    <strong><?=esc_html($lang[$demoItemModelImage1 ? 'LANG_IMAGE_VIEW_DEMO_TEXT' : 'LANG_IMAGE_VIEW_TEXT']);?></strong>
                </a>
                &nbsp;&nbsp;&nbsp;&nbsp;<span >
                    <strong><?=esc_html($lang[$demoItemModelImage1 ? 'LANG_IMAGE_UNSET_DEMO_TEXT' : 'LANG_IMAGE_DELETE_TEXT']);?></strong>
                </span> &nbsp;
                <input type="checkbox" name="delete_item_model_image_1"
                       title="<?=esc_attr($lang[$demoItemModelImage1 ? 'LANG_IMAGE_UNSET_DEMO_TEXT' : 'LANG_IMAGE_DELETE_TEXT']);?>" />
            </span>
        <?php else: ?>
            &nbsp;&nbsp;&nbsp;&nbsp; <strong><?=esc_html($lang['LANG_IMAGE_NONE_TEXT']);?></strong>
        <?php endif; ?>
    </td>
</tr>
<tr>
   <td><strong><?=esc_html($lang['LANG_ITEM_MODEL_IMAGE2_TEXT']);?>:</strong></td>
   <td colspan="2"><input type="file" name="item_image_2" title="<?=esc_attr($lang['LANG_ITEM_MODEL_IMAGE2_TEXT']);?>" />
       <?php if($itemModelImage2_URL != ""): ?>
           <span>
                &nbsp;&nbsp;&nbsp;<a rel="collection" href="<?=esc_url($itemModelImage1_URL);?>" target="_blank">
                    <strong><?=esc_html($lang[$demoItemModelImage2 ? 'LANG_IMAGE_VIEW_DEMO_TEXT' : 'LANG_IMAGE_VIEW_TEXT']);?></strong>
                </a>
                &nbsp;&nbsp;&nbsp;&nbsp;<span >
                    <strong><?=esc_html($lang[$demoItemModelImage2 ? 'LANG_IMAGE_UNSET_DEMO_TEXT' : 'LANG_IMAGE_DELETE_TEXT']);?></strong>
                </span> &nbsp;
                <input type="checkbox" name="delete_item_model_image_2"
                       title="<?=esc_attr($lang[$demoItemModelImage2 ? 'LANG_IMAGE_UNSET_DEMO_TEXT' : 'LANG_IMAGE_DELETE_TEXT']);?>" />
            </span>
       <?php else: ?>
           &nbsp;&nbsp;&nbsp;&nbsp; <strong><?=esc_html($lang['LANG_IMAGE_NONE_TEXT']);?></strong>
       <?php endif; ?>
   </td>
</tr>
<tr>
    <td><strong><?=esc_html($lang['LANG_ITEM_MODEL_IMAGE3_TEXT']);?>:</strong></td>
    <td colspan="2"><input type="file" name="item_image_3" title="<?=esc_attr($lang['LANG_ITEM_MODEL_IMAGE3_TEXT']);?>" />
        <?php if($itemModelImage3_URL != ""): ?>
            <span>
                &nbsp;&nbsp;&nbsp;<a rel="collection" href="<?=esc_url($itemModelImage3_URL);?>" target="_blank">
                    <strong><?=esc_html($lang[$demoItemModelImage3 ? 'LANG_IMAGE_VIEW_DEMO_TEXT' : 'LANG_IMAGE_VIEW_TEXT']);?></strong>
                </a>
                &nbsp;&nbsp;&nbsp;&nbsp;<span >
                    <strong><?=esc_html($lang[$demoItemModelImage3 ? 'LANG_IMAGE_UNSET_DEMO_TEXT' : 'LANG_IMAGE_DELETE_TEXT']);?></strong>
                </span> &nbsp;
                <input type="checkbox" name="delete_item_model_image_3"
                       title="<?=esc_attr($lang[$demoItemModelImage3 ? 'LANG_IMAGE_UNSET_DEMO_TEXT' : 'LANG_IMAGE_DELETE_TEXT']);?>" />
            </span>
        <?php else: ?>
            &nbsp;&nbsp;&nbsp;&nbsp; <strong><?=esc_html($lang['LANG_IMAGE_NONE_TEXT']);?></strong>
        <?php endif; ?>
    </td>
</tr>
<tr>
    <td valign="top"><strong><?=esc_html($lang['LANG_ITEM_MODEL_FEATURES_TEXT']);?>:</strong></td>
    <td colspan="2"><?=$trustedItemModelFeaturesHTML;?></td>
</tr>
<tr>
   <td><strong><?=esc_html($lang['LANG_PRICE_GROUP_TEXT']);?>:</strong></td>
   <td>
       <select name="price_group_id" class="price-group" title="<?=esc_attr($lang['LANG_PRICE_GROUP_TEXT']);?>">
           <?=$trustedPriceGroupsDropdownOptionsHTML;?>
       </select>
   </td>
   <td>
       <em>(<?=esc_html($lang['LANG_ITEM_MODEL_PRICE_GROUP_OPTIONAL_TEXT']);?>)</em>
   </td>
</tr>
<?php if($settings['conf_deposit_enabled'] == 1): ?>
    <tr>
        <td><strong><?=esc_html($lang['LANG_ITEM_MODEL_FIXED_DEPOSIT_TEXT']);?>:<span class="is-required">*</span></strong></td>
        <td>
            <input type="text" name="fixed_deposit" value="<?=esc_attr($fixedDeposit);?>" class="required number"  title="<?=esc_attr($lang['LANG_ITEM_MODEL_FIXED_DEPOSIT_TEXT']);?>" />
            &nbsp; <?=esc_html($settings['conf_currency_code']);?>
        </td>
        <td>
            <em>(<?=esc_html($lang['LANG_ITEM_MODEL_FIXED_DEPOSIT_NOTES_TEXT']);?>)</em>
        </td>
    </tr>
<?php else: ?>
    <input type="hidden" name="fixed_deposit" value="<?=esc_attr($fixedDeposit);?>" />
<?php endif; ?>
<tr>
  <td><strong>Display in:</strong></td>
  <td colspan="2">
      <table width="100%">
          <tr>
              <td><input type="checkbox" id="display_in_slider" name="display_in_slider"<?=$displayInSliderChecked;?>/> <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Model Slider</td>
              <td><input type="checkbox" id="display_in_item_list" name="display_in_item_list"<?=$displayInItemModelListChecked;?>/> <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Model List</td>
          </tr>
          <tr>
              <td><input type="checkbox" id="display_in_price_table" name="display_in_price_table"<?=$displayInPriceTableChecked;?>/> <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Model Price Table</td>
              <td><input type="checkbox" id="display_in_calendar" name="display_in_calendar"<?=$displayInCalendarChecked;?>/> <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Model Calendar</td>
          </tr>
      </table>
  </td>
</tr>
<tr>
      <td></td>
      <td colspan="2"><input type="submit" value="Save <?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE']);?> model" name="save_item_model" class="save-button"/></td>
</tr>

        </table>
    </form>
</div>
</div>
<script type="text/javascript">
jQuery(document).ready(function() {
    'use strict';
    jQuery('.add-edit-item-model').validate();
});
</script>