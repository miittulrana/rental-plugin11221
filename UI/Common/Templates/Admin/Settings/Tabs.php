<?php
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );
// Scripts
wp_enqueue_script('jquery');
wp_enqueue_script('jquery-validate');
wp_enqueue_script('fleet-management-admin');

// Styles
wp_enqueue_style('font-awesome');
wp_enqueue_style('modern-tabs');
wp_enqueue_style('jquery-validate');
wp_enqueue_style('fleet-management-admin');
?>
<div class="car-rental-settings-admin fleet-management-tabbed-admin <?=esc_attr($extCSS_Prefix);?>tabbed-admin fleet-management-tabbed-admin-wide <?=esc_attr($extCSS_Prefix);?>tabbed-admin-wide bg-cyan">
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
			<input type="radio" name="modern-tabs"<?=(!empty($tabs['global-settings']) ? ' checked="checked"' : '');?> id="modern-tab1" class="modern-tab-content-1">
			<label for="modern-tab1"><span><span><i class="fa fa-gear" aria-hidden="true"></i><?=esc_html($lang['LANG_SETTINGS_GLOBAL_SHORT_TEXT']);?></span></span></label>

            <input type="radio" name="modern-tabs"<?=(!empty($tabs['tracking-settings']) ? ' checked="checked"' : '');?> id="modern-tab2" class="modern-tab-content-2">
            <label for="modern-tab2"><span><span><i class="fa fa-gear" aria-hidden="true"></i><?=esc_html($lang['LANG_SETTINGS_TRACKING_SHORT_TEXT']);?></span></span></label>

            <input type="radio" name="modern-tabs"<?=(!empty($tabs['security-settings']) ? ' checked="checked"' : '');?> id="modern-tab3" class="modern-tab-content-3">
            <label for="modern-tab3"><span><span><i class="fa fa-shield" aria-hidden="true"></i><?=esc_html($lang['LANG_SETTINGS_SECURITY_SHORT_TEXT']);?></span></span></label>

			<input type="radio" name="modern-tabs"<?=(!empty($tabs['customer-settings']) ? ' checked="checked"' : '');?> id="modern-tab4" class="modern-tab-content-4">
			<label for="modern-tab4"><span><span><i class="fa fa-gear" aria-hidden="true"></i><?=esc_html($lang['LANG_SETTINGS_CUSTOMER_SHORT_TEXT']);?></span></span></label>

			<input type="radio" name="modern-tabs"<?=(!empty($tabs['search-settings']) ? ' checked="checked"' : '');?> id="modern-tab5" class="modern-tab-content-5">
			<label for="modern-tab5"><span><span><i class="fa fa-gear" aria-hidden="true"></i><?=esc_html($lang['LANG_SETTINGS_SEARCH_SHORT_TEXT']);?></span></span></label>

            <input type="radio" name="modern-tabs"<?=(!empty($tabs['order-settings']) ? ' checked="checked"' : '');?> id="modern-tab6" class="modern-tab-content-6">
			<label for="modern-tab6"><span><span><i class="fa fa-gear" aria-hidden="true"></i><?=esc_html($lang['LANG_SETTINGS_ORDER_SHORT_TEXT']);?></span></span></label>

            <input type="radio" name="modern-tabs"<?=(!empty($tabs['company-settings']) ? ' checked="checked"' : '');?> id="modern-tab7" class="modern-tab-content-7">
            <label for="modern-tab7"><span><span><i class="fa fa-gear" aria-hidden="true"></i><?=esc_html($lang['LANG_SETTINGS_COMPANY_SHORT_TEXT']);?></span></span></label>

			<input type="radio" name="modern-tabs"<?=(!empty($tabs['price-settings']) ? ' checked="checked"' : '');?> id="modern-tab8" class="modern-tab-content-8">
			<label for="modern-tab8"><span><span><i class="fa fa-gear" aria-hidden="true"></i><?=esc_html($lang['LANG_SETTINGS_PRICE_SHORT_TEXT']);?></span></span></label>

			<input type="radio" name="modern-tabs"<?=(!empty($tabs['notification-settings']) ? ' checked="checked"' : '');?> id="modern-tab9" class="modern-tab-content-9">
			<label for="modern-tab9"><span><span><i class="fa fa-gear" aria-hidden="true"></i><?=esc_html($lang['LANG_SETTINGS_NOTIFICATION_SHORT_TEXT']);?></span></span></label>

			<ul>
				<li class="modern-tab-content-1">
					<div class="typography">
						<?php include 'Shared/GlobalSettingsPartial.php'; ?>
					</div>
				</li>
                <li class="modern-tab-content-2">
                    <div class="typography">
                        <?php include 'Shared/TrackingSettingsPartial.php'; ?>
                    </div>
                </li>
                <li class="modern-tab-content-3">
                    <div class="typography">
                        <?php include 'Shared/SecuritySettingsPartial.php'; ?>
                    </div>
                </li>
				<li class="modern-tab-content-4">
					<div class="typography">
						<?php include 'Shared/CustomerSettingsPartial.php'; ?>
					</div>
				</li>
				<li class="modern-tab-content-5">
					<div class="typography">
						<?php include 'Shared/SearchSettingsPartial.php'; ?>
					</div>
				</li>
                <li class="modern-tab-content-6">
                    <div class="typography">
                        <?php include 'Shared/OrderSettingsPartial.php'; ?>
                    </div>
                </li>
                <li class="modern-tab-content-7">
					<div class="typography">
						<?php include 'Shared/CompanySettingsPartial.php'; ?>
					</div>
				</li>
				<li class="modern-tab-content-8">
					<div class="typography">
						<?php include 'Shared/PriceSettingsPartial.php'; ?>
					</div>
				</li>
				<li class="modern-tab-content-9">
					<div class="typography">
						<?php include 'Shared/NotificationSettingsPartial.php'; ?>
					</div>
				</li>
			</ul>
		</div>
		<!--/ tabs -->
	</div>
</div>