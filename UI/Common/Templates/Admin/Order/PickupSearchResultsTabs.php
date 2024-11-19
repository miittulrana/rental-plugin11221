<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Scripts
wp_enqueue_script('jquery');
wp_enqueue_script('jquery-ui-core'); // NOTE: We need it for datatables
wp_enqueue_script('datatables-jquery-datatables');
wp_enqueue_script('datatables-jqueryui');
wp_enqueue_script('datatables-responsive-datatables');
wp_enqueue_script('datatables-responsive-jqueryui');
wp_enqueue_script('fleet-management-admin');

// Styles
wp_enqueue_style('font-awesome');
wp_enqueue_style('modern-tabs');
wp_enqueue_style('jquery-ui-theme'); // NOTE: We need it for datatables
wp_enqueue_style('datatables-jqueryui');
wp_enqueue_style('datatables-responsive-jqueryui');
wp_enqueue_style('fleet-management-admin');
?>
<div class="car-rental-list-admin customer-search-results fleet-management-tabbed-admin <?=esc_attr($extCSS_Prefix);?>tabbed-admin fleet-management-tabbed-admin-wide <?=esc_attr($extCSS_Prefix);?>tabbed-admin-wide bg-cyan">
	<div class="body">
		<!-- tabs -->
		<div class="modern-tabs modern-tabs-pos-top-left modern-tabs-anim-flip modern-tabs-response-to-icons">
			<input type="radio" name="modern-tabs" checked="checked" id="modern-tab1" class="modern-tab-content-1">
			<label for="modern-tab1"><span><span><i class="fa fa-car" aria-hidden="true"></i>Search Results for Pick-up Date Range</span></span></label>
			<ul>
				<li class="modern-tab-content-1">
					<div class="typography">
						<h1 class="search-results-title">
                            <?=esc_html($fromDateI18n.' - '.$tillDateI18n);?>
                            <?=$customerName != "" ? sprintf($lang['LANG_ORDERS_BY_S_TEXT'], $customerName) : $lang['LANG_ORDERS_BY_CUSTOMER_TEXT'];?>
						</h1>
						<div class="col-search">
                            &nbsp;&nbsp; <input class="back-to" type="button" value="Back to Today&#39;s Pick-ups"
                            onclick="window.location.href='<?=esc_js($backToPickupListURL);?>'"
                            />
						</div>
						<?php include 'Shared/PickupsPartial.php'; ?>
					</div>
				</li>
			</ul>
		</div>
		<!--/ tabs -->
	</div>
</div>