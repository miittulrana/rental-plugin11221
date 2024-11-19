<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Scripts
wp_enqueue_script('jquery');
wp_enqueue_script('jquery-ui-core'); // NOTE: We need it for datatables & datepicker in search params
wp_enqueue_script('datatables-jquery-datatables');
wp_enqueue_script('datatables-jqueryui');
wp_enqueue_script('datatables-responsive-datatables');
wp_enqueue_script('datatables-responsive-jqueryui');
wp_enqueue_script('fleet-management-admin');

// Styles
wp_enqueue_style('font-awesome');
wp_enqueue_style('modern-tabs');
wp_enqueue_style('jquery-ui-theme'); // NOTE: We need it for datatables & datepicker in search params
wp_enqueue_style('datatables-jqueryui');
wp_enqueue_style('datatables-responsive-jqueryui');
wp_enqueue_style('fleet-management-admin');
?>
<div class="car-rental-payments-admin fleet-management-tabbed-admin <?=esc_attr($extCSS_Prefix);?>tabbed-admin fleet-management-tabbed-admin-wide <?=esc_attr($extCSS_Prefix);?>tabbed-admin-wide bg-cyan">
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
        <input type="radio" name="modern-tabs"<?=(!empty($tabs['customer-lookups']) ? ' checked="checked"' : '');?> id="modern-tab1" class="modern-tab-content-1">
        <label for="modern-tab1"><span><span><i class="fa fa-eye" aria-hidden="true"></i>Customer Lookups</span></span></label>

        <?php if($settings['conf_prepayment_enabled'] == 1): ?>
            <input type="radio" name="modern-tabs"<?=(!empty($tabs['payments']) ? ' checked="checked"' : '');?> id="modern-tab2" class="modern-tab-content-2">
            <label for="modern-tab2"><span><span><i class="fa fa-eye" aria-hidden="true"></i>Payments</span></span></label>
        <?php endif; ?>

		<ul>
            <li class="modern-tab-content-1">
                <div class="typography">
                    <?php include 'Shared/CustomerLookupsPartial.php'; ?>
                </div>
            </li>
            <?php if($settings['conf_prepayment_enabled'] == 1): ?>
                <li class="modern-tab-content-2">
                    <div class="typography">
                        <?php include 'Shared/PaymentsPartial.php'; ?>
                    </div>
                </li>
            <?php endif; ?>
		</ul>
	</div>
	<!--/ tabs -->
	</div>
</div>