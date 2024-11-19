<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Scripts
wp_enqueue_script('jquery');
wp_enqueue_script('jquery-ui-core'); // NOTE: We need it for datatables & datepicker in search params
wp_enqueue_script('datatables-jquery-datatables');
wp_enqueue_script('datatables-jqueryui');
wp_enqueue_script('datatables-responsive-datatables');
wp_enqueue_script('datatables-responsive-jqueryui');
wp_enqueue_script('jquery-ui-datepicker', array('jquery','jquery-ui-core'));
wp_enqueue_script('jquery-ui-datepicker-locale');
wp_enqueue_script('jquery-validate');
wp_enqueue_script('fleet-management-admin');

// Load Nice Admin Tabs CSS
wp_enqueue_style('font-awesome');
wp_enqueue_style('modern-tabs');

// Styles
wp_enqueue_style('jquery-ui-theme');
wp_enqueue_style('datatables-jqueryui');
wp_enqueue_style('jquery-ui-theme'); // NOTE: We need it for datatables & datepicker in search params
wp_enqueue_style('datatables-responsive-jqueryui');
wp_enqueue_style('jquery-validate');
wp_enqueue_style('fleet-management-admin');
?>
<div class="car-rental-list-admin fleet-management-tabbed-admin <?=esc_attr($extCSS_Prefix);?>tabbed-admin fleet-management-tabbed-admin-wide <?=esc_attr($extCSS_Prefix);?>tabbed-admin-wide bg-cyan">
    <?php if ($errorMessage != ""): ?>
        <div class="admin-info-message admin-wide-message admin-error-message"><?=esc_br_html($errorMessage);?></div>
    <?php elseif ($okayMessage != ""): ?>
        <div class="admin-info-message admin-wide-message admin-okay-message"><?=esc_br_html($okayMessage);?></div>
    <?php endif; ?>
    <?php if ($ksesedDebugHTML != ""): ?>
        <div class="admin-info-message admin-wide-message admin-debug-html"><?=$ksesedDebugHTML;?></div>
    <?php endif; ?>
    <div class="body">
        <!-- tabs -->
        <div class="modern-tabs modern-tabs-pos-top-left modern-tabs-anim-flip modern-tabs-response-to-icons">
            <input type="radio" name="modern-tabs"<?=(!empty($tabs['pickups']) ? ' checked="checked"' : '');?> id="modern-tab1" class="modern-tab-content-1">
            <label for="modern-tab1"><span><span><i class="fa fa-car" aria-hidden="true"></i>Pick-ups</span></span></label>

            <input type="radio" name="modern-tabs"<?=(!empty($tabs['returns']) ? ' checked="checked"' : '');?> id="modern-tab2" class="modern-tab-content-2">
            <label for="modern-tab2"><span><span><i class="fa fa-globe" aria-hidden="true"></i>Returns</span></span></label>

            <input type="radio" name="modern-tabs"<?=(!empty($tabs['orders']) ? ' checked="checked"' : '');?> id="modern-tab3" class="modern-tab-content-3">
            <label for="modern-tab3"><span><span><i class="fa fa-suitcase" aria-hidden="true"></i>Reservations</span></span></label>

            <input type="radio" name="modern-tabs"<?=(!empty($tabs['item-models-availability']) ? ' checked="checked"' : '');?> id="modern-tab4" class="modern-tab-content-4">
            <label for="modern-tab4"><span><span><i class="fa fa-calendar" aria-hidden="true"></i><?=esc_html($lang['LANG_SINGLE_VEHICLE_TITLE_UPPERCASE']);?> Models Calendar</span></span></label>

            <input type="radio" name="modern-tabs"<?=(!empty($tabs['extras-availability']) ? ' checked="checked"' : '');?> id="modern-tab5" class="modern-tab-content-5">
            <label for="modern-tab5"><span><span><i class="fa fa-calendar" aria-hidden="true"></i>Extras Calendar</span></span></label>

            <ul>
                <li class="modern-tab-content-1">
                    <div class="typography">
                        <?php include 'Shared/SearchFormPickupDatePartial.php'; ?>
                        <?php include 'Shared/PickupsPartial.php'; ?>
                    </div>
                </li>
                <li class="modern-tab-content-2">
                    <div class="typography">
                        <?php include 'Shared/SearchFormReturnDatePartial.php'; ?>
                        <?php include 'Shared/ReturnsPartial.php'; ?>
                    </div>
                </li>
                <li class="modern-tab-content-3">
                    <div class="typography">
                        <?php include 'Shared/SearchFormOrderDatePartial.php'; ?>
                        <?php include 'Shared/OrdersPartial.php'; ?>
                    </div>
                </li>
                <li class="modern-tab-content-4">
                    <div class="typography">
                        <?php include 'Shared/SearchFormItemModelsAvailabilityPartial.php'; ?>
                        <?php
                        if($settings['conf_classify_items'] == 1):
                            include 'Shared/ClassifiedItemModelsAvailabilityCalendarPartial.php';
                        else:
                            include 'Shared/ItemModelsAvailabilityCalendarPartial.php';
                        endif;
                        ?>
                    </div>
                </li>
                <li class="modern-tab-content-5">
                    <div class="typography">
                        <?php include 'Shared/SearchFormExtrasAvailabilityPartial.php'; ?>
                        <?php include 'Shared/ExtrasAvailabilityCalendarPartial.php'; ?>
                    </div>
                </li>
            </ul>
        </div>
        <!--/ tabs -->
    </div>
</div>