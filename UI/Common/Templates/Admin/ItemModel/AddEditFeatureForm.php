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
<p>&nbsp;</p>
<div class="fleet-management-tabbed-admin">
<div id="container-inside">
  <span class="title"><?=esc_html($lang['LANG_FEATURE_ADD_EDIT_TEXT']);?></span>
  <input type="button" value="<?=esc_attr($lang['LANG_FEATURE_BACK_TO_LIST_TEXT']);?>" onclick="window.location.href='<?=esc_js($backToListURL);?>'" class="button-back"/>
  <hr/>
  <form action="<?=esc_url($formAction);?>" method="POST" id="form1">
      <table cellpadding="5" cellspacing="2" border="0">
          <input type="hidden" name="feature_id" value="<?=esc_attr($featureId);?>"/>
          <tr>
              <td><strong><?=esc_html($lang['LANG_FEATURE_TITLE_TEXT']);?>:</strong></td>
              <td><input type="text" name="feature_title" maxlength="100" value="<?=esc_attr($featureTitle);?>" id="feature_title" class="required" class="form-input" /></td>
          </tr>
          <tr>
              <td><strong><?=esc_html($lang['LANG_FEATURE_KEY_TEXT']);?>:</strong></td>
              <td><input type="checkbox" id="key_feature" name="key_feature"<?=($isKeyFeature ? ' checked="checked"' : '');?>/></td>
          </tr>
          <?php if($featureId == 0): ?>
              <tr>
                  <td>&nbsp;</td>
                  <td>
                      <input type="checkbox" name="add_to_all_item_models"
                             title="<?=esc_attr($lang['LANG_FEATURE_ADD_TO_ALL_ITEM_MODELS_TEXT']);?>"<?=($addToAllItemModels ? ' checked="checked"' : '');?> /> <?=esc_html($lang['LANG_FEATURE_ADD_TO_ALL_ITEM_MODELS_TEXT']);?>
                  </td>
              </tr>
          <?php endif; ?>
          <tr>
                <td>&nbsp;</td>
                <td><input type="submit" value="<?=esc_attr($lang['LANG_FEATURE_SAVE_TEXT']);?>" name="save_feature" class="save-button"/></td>
          </tr>
        </table>
    </form>
</div>
</div>
<script type="text/javascript">
	jQuery().ready(function() {
        'use strict';
		jQuery("#form1").validate();
     });
</script>