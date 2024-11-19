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

// Load Nice Admin Tabs CSS
wp_enqueue_style('font-awesome');
wp_enqueue_style('modern-tabs');

// Styles
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
        <input type="radio" name="modern-tabs"<?=(!empty($tabs['taxes']) ? ' checked="checked"' : '');?> id="modern-tab1" class="modern-tab-content-1">
        <label for="modern-tab1"><span><span><i class="fa fa-money" aria-hidden="true"></i>Taxes</span></span></label>

		<ul>
            <li class="modern-tab-content-1">
                <div class="typography">
                    <?php include 'Shared/TaxesPartial.php'; ?>
                </div>
            </li>
		</ul>
	</div>
	<!--/ tabs -->
	</div>
</div>