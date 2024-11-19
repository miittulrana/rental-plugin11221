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

// Load Nice Admin Tabs CSS
wp_enqueue_style('font-awesome');
wp_enqueue_style('modern-tabs');

// Styles
wp_enqueue_style('jquery-ui-theme');
wp_enqueue_style('jquery-validate');
wp_enqueue_style('fleet-management-admin');
?>
<div class="car-rental-list-admin calendar-search-results fleet-management-tabbed-admin <?=esc_attr($extCSS_Prefix);?>tabbed-admin fleet-management-tabbed-admin-wide <?=esc_attr($extCSS_Prefix);?>tabbed-admin-wide bg-cyan">
	<div class="body">
		<!-- tabs -->
		<div class="modern-tabs modern-tabs-pos-top-left modern-tabs-anim-flip modern-tabs-response-to-icons">
			<input type="radio" name="modern-tabs" checked="checked" id="modern-tab1" class="modern-tab-content-1">
			<label for="modern-tab1"><span><span><i class="fa fa-car" aria-hidden="true"></i>Available Extras</span></span></label>
			<ul>
				<li class="modern-tab-content-1">
					<div class="typography">
						<h1 class="search-results-title">
							Period: <?=esc_html($fromDateI18n.' - '.$tillDateI18n);?>
						</h1>
						<div class="col-search">
							<input class="back-to" type="button" value="Back to This Month Extras Calendar"
								onclick="window.location.href='<?=esc_js($backToCurrentAvailabilityURL);?>'"
								/>
						</div>
						<?php foreach($arrExtrasAvailabilityCalendars AS $extrasAvailabilityCalendar): ?>
							<?php include 'Shared/ExtrasAvailabilityCalendarPartial.php'; ?>
						<?php endforeach; ?>
						<?php if(sizeof($arrExtrasAvailabilityCalendars) == 0):  ?>
							<div class="no-calendars-found"><?=esc_html($lang['LANG_CALENDAR_NO_CALENDARS_FOUND_TEXT']);?></div>
						<?php endif; ?>
					</div>
				</li>
			</ul>
		</div>
		<!--/ tabs -->
	</div>
</div>