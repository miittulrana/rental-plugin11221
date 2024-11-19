<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Styles
wp_enqueue_style('fleet-management-main');
?>
<?php
// Global variable are needed here, because only then we will be able to access them inside the 'add_action' hook
$GLOBALS['EXT_CSS_PREFIX'] = $extCSS_Prefix;
$GLOBALS['LANG_ORDER_CODE_INPUT_TEXT'] = $lang['LANG_ORDER_CODE_INPUT_TEXT'];
$GLOBALS['LANG_ORDER_CODE_INPUT2_TEXT'] = $lang['LANG_ORDER_CODE_INPUT2_TEXT'];
$GLOBALS['LANG_ORDER_NO_CODE_ERROR_TEXT'] = $lang['LANG_ORDER_NO_CODE_ERROR_TEXT'];
?>
<?php add_action('wp_footer', function() { // A workaround until #48098 will be resolved ( https://core.trac.wordpress.org/ticket/48098 ). Scripts are printed with the '20' priority. ?>
<script type="text/javascript">
jQuery(document).ready(function(){
    jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>order-code-input-form .change-order-button').on( "click", function()
    {
        'use strict';
        var canProceed = true;
        var objOrderCode = jQuery('.<?=esc_js($GLOBALS['EXT_CSS_PREFIX']);?>order-code-input-form .order-code');
        var orderCode = "";
        if(objOrderCode.length)
        {
            orderCode = objOrderCode.val();
        }
        //alert('orderCode[len]:' + objOrderCode.length + ', orderCode[val]:' + orderCode);

        if(orderCode === "" || orderCode === "<?=esc_js($GLOBALS['LANG_ORDER_CODE_INPUT_TEXT']);?>" || orderCode === "<?=esc_js($GLOBALS['LANG_ORDER_CODE_INPUT2_TEXT']);?>")
        {
            alert('<?=esc_js($GLOBALS['LANG_ORDER_NO_CODE_ERROR_TEXT']);?>');
            canProceed = false;
        }

        return canProceed;
    });
});
</script>
<?php }, 100); ?>
<div class="fleet-management-wrapper <?=esc_attr($extCSS_Prefix);?>wrapper fleet-management-order-code-input-form <?=esc_attr($extCSS_Prefix);?>order-code-input-form">
    <form name="change-order-form" action="<?=esc_url($formAction);?>" method="GET" class="change-order-form">
        <div class="search-field">
            <div class="search-field-header">
                <div class="search-field-title">
                    <?=esc_html($lang['LANG_SEARCH_CHANGE_ORDER_TEXT']);?>
                </div>
            </div>
            <div class="search-field-body">
                <input type="hidden" name="<?=esc_attr($extPrefix);?>came_from_step1" value="yes" />
                <div class="top-padded">
                    <?php if($inputStyle == 1): ?>
                        <input value="<?=esc_attr($lang['LANG_ORDER_CODE_INPUT_TEXT']);?>" type="text" name="<?=esc_attr($extPrefix.$orderCodeParam);?>" class="order-code"
                               onfocus="if(this.value === '<?=esc_js($lang['LANG_ORDER_CODE_INPUT_TEXT']);?>') {this.value=''}"
                               onblur="if(this.value === ''){this.value ='<?=esc_js($lang['LANG_ORDER_CODE_INPUT_TEXT']);?>'}"
                               title="<?=esc_attr($lang['LANG_ORDER_CODE_TEXT']);?>" />
                    <?php else: ?>
                        <input value="<?=esc_attr($lang['LANG_ORDER_CODE_INPUT2_TEXT']);?>" type="text" name="<?=esc_attr($extPrefix.$orderCodeParam);?>" class="order-code"
                               onfocus="if(this.value === '<?=esc_js($lang['LANG_ORDER_CODE_INPUT2_TEXT']);?>') {this.value=''}"
                               onblur="if(this.value === ''){this.value ='<?=esc_js($lang['LANG_ORDER_CODE_INPUT2_TEXT']);?>'}"
                               title="<?=esc_attr($lang['LANG_ORDER_CODE_TEXT']);?>" />
                    <?php endif; ?>
                </div>
                <div class="top-padded-submit">
                    <?php if($settings['conf_universal_analytics_events_tracking'] == 1): ?>
                        <!-- Note: Do not translate events to track well inter-language events -->
                        <input type="submit" name="<?=esc_attr($extPrefix);?>change_order" value="<?=esc_attr($lang['LANG_SEARCH_CHANGE_ORDER2_TEXT']);?>" class="change-order-button"
                               onclick="ga('send', 'event', '<?=esc_js($extName);?>', 'Click', 'Change Reservation');" />
                    <?php else: ?>
                        <input type="submit" name="<?=esc_attr($extPrefix);?>change_order" value="<?=esc_attr($lang['LANG_SEARCH_CHANGE_ORDER2_TEXT']);?>" class="change-order-button" />
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </form>
</div>