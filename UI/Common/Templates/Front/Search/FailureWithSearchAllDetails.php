<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Styles
wp_enqueue_style('fleet-management-main');
?>
<div class="fleet-management-wrapper <?=esc_attr($extCSS_Prefix);?>wrapper fleet-management-search-failure <?=esc_attr($extCSS_Prefix);?>search-failure">
    <div class="failure-title"><?=esc_html($lang['LANG_ORDER_FAILURE_TEXT']);?></div>
    <div class="failure-content">
        <?=esc_br_html($errorMessages);?>
        <?=esc_html($lang['LANG_SEARCH_TRY_DIFFERENT_DATE_OR_CRITERIA_ERROR_TEXT']);?>
        <div class="buttons">
            <form name="inputform" action="" method="POST">
                <input type="hidden" name="<?=esc_attr($extPrefix);?>came_from_step1" value="yes" />
                <input type="hidden" name="<?=esc_attr($extPrefix.$orderCodeParam);?>" value="" />
                <input type="hidden" name="coupon_code" value="<?=esc_attr($objSearch->getEditCouponCode());?>" />
                <input type="hidden" name="class_id" value="-1" />
                <input type="hidden" name="attribute_id1" value="-1" />
                <input type="hidden" name="attribute_id2" value="-1" />
                <input type="hidden" name="pickup_location_id" value="<?=esc_attr($objSearch->getPickupLocationId());?>" />
                <input type="hidden" name="pickup_date" value="<?=esc_attr($objSearch->getShortPickupDate());?>" />
                <input type="hidden" name="pickup_time" value="<?=esc_attr($objSearch->getISOPickupTime());?>" />
                <input type="hidden" name="return_location_id" value="<?=esc_attr($objSearch->getReturnLocationId());?>" />
                <input type="hidden" name="return_date" value="<?=esc_attr($objSearch->getShortReturnDate());?>" />
                <input type="hidden" name="return_time" value="<?=esc_attr($objSearch->getISOReturnTime());?>" />
                <input type="submit" name="go_back" value="<?=esc_html($lang['LANG_BACK_TEXT']);?>" class="back-button" onclick="window.location.href='index.php'" />
                <input type="submit" name="<?=esc_attr($extPrefix);?>do_search" value="<?=esc_html($lang['LANG_SEARCH_ALL_ITEM_MODELS_TEXT']);?>" class="back-button">
            </form>
        </div>
    </div>
    <div class="clear">&nbsp;</div>
</div>