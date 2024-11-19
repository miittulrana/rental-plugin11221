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
<div class="car-rental-list-admin customer-search-results fleet-management-tabbed-admin <?=esc_attr($extCSS_Prefix);?>tabbed-admin fleet-management-tabbed-admin-ultra-wide <?=esc_attr($extCSS_Prefix);?>tabbed-admin-ultra-wide bg-cyan">
	<div class="body">
		<!-- tabs -->
		<div class="modern-tabs modern-tabs-pos-top-left modern-tabs-anim-flip modern-tabs-response-to-icons">
			<input type="radio" name="modern-tabs" checked="checked" id="modern-tab1" class="modern-tab-content-1">
			<label for="modern-tab1"><span><span><i class="fa fa-user" aria-hidden="true"></i><?=esc_html($lang['LANG_CUSTOMER_SEARCH_RESULTS_TEXT']);?></span></span></label>
			<ul>
				<li class="modern-tab-content-1">
					<div class="typography">
						<h1 class="search-results-title">
                            <?=($userName != '' ? esc_html($userName).', ' : '');?>
                            <?=$lang[$dateType == "DATE_CREATED" ? 'LANG_CUSTOMERS_BY_DATE_CREATED_PERIOD_TEXT' : 'LANG_CUSTOMERS_BY_LAST_USED_PERIOD_TEXT'];?>:
                            <?=esc_html($fromDateI18n.' - '.$tillDateI18n);?>
						</h1>
						<div class="col-search">
							<input class="back-to" type="button" value="Back to Customer List"
								   onclick="window.location.href='<?=esc_js($backToCustomerListURL);?>'"
								/>
						</div>
						<?php include 'Shared/CustomersPartial.php'; ?>
					</div>
				</li>
			</ul>
		</div>
		<!--/ tabs -->
	</div>
</div>