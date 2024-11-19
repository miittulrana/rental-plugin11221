<?php
/**
 * Demo data
 * @package     FleetManagement
 * @author      Kestutis Matuliauskas
 * @copyright   Kestutis Matuliauskas
 * @License     See Legal/License.txt for details.
 *
 * @car-rental-plugin-demo
 * Demo UID: 2
 * Demo Name: Car Rental Agency - Steel Blue
 */
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );

$wpPostsAI = !isset($wpPostsAI) ? 0 : $wpPostsAI; // Define WordPress Auto Increment id if not set
$extAI = !isset($extAI) ? 0 : $extAI; // Define Extension Auto Increment id if not set
$arrReplaceSQL = array();
$arrPluginReplaceSQL = array();

// First - include a common demo SQL data, to avoid repeatedness
include('Shared/CarRentalAgencySQLPartial.php');

// Then - list tables that are different for each demo version

// This table requires $wpPostsAI
$arrPluginReplaceSQL['settings'] = "(`conf_key`, `conf_value`, `blog_id`) VALUES
('conf_api_max_failed_requests_per_period', '3', [BLOG_ID]),
('conf_api_max_requests_per_period', '50', [BLOG_ID]),
('conf_booking_model', '1', [BLOG_ID]),
('conf_cancelled_payment_page_id', '".($wpPostsAI+31)."', [BLOG_ID]),
('conf_classify_items', '1', [BLOG_ID]),
('conf_company_city', 'San Francisco', [BLOG_ID]),
('conf_company_country', '', [BLOG_ID]),
('conf_company_email', 'info@yourdomain.com', [BLOG_ID]),
('conf_company_name', 'Car Rental Company, Ltd.', [BLOG_ID]),
('conf_company_notification_emails', '1', [BLOG_ID]),
('conf_company_phone', '(450) 600 4000', [BLOG_ID]),
('conf_company_state', 'CA', [BLOG_ID]),
('conf_company_street_address', '625 2nd Street', [BLOG_ID]),
('conf_company_zip_code', '94107', [BLOG_ID]),
('conf_confirmation_page_id', '".($wpPostsAI+32)."', [BLOG_ID]),
('conf_currency_code', 'USD', [BLOG_ID]),
('conf_currency_symbol', '$', [BLOG_ID]),
('conf_currency_symbol_location', '0', [BLOG_ID]),
('conf_customer_birthdate_required', '1', [BLOG_ID]),
('conf_customer_birthdate_visible', '1', [BLOG_ID]),
('conf_customer_city_required', '1', [BLOG_ID]),
('conf_customer_city_visible', '1', [BLOG_ID]),
('conf_customer_comments_required', '0', [BLOG_ID]),
('conf_customer_comments_visible', '1', [BLOG_ID]),
('conf_customer_country_required', '0', [BLOG_ID]),
('conf_customer_country_visible', '1', [BLOG_ID]),
('conf_customer_email_required', '1', [BLOG_ID]),
('conf_customer_email_visible', '1', [BLOG_ID]),
('conf_customer_first_name_required', '1', [BLOG_ID]),
('conf_customer_first_name_visible', '1', [BLOG_ID]),
('conf_customer_last_name_required', '1', [BLOG_ID]),
('conf_customer_last_name_visible', '1', [BLOG_ID]),
('conf_customer_phone_required', '1', [BLOG_ID]),
('conf_customer_phone_visible', '1', [BLOG_ID]),
('conf_customer_state_required', '0', [BLOG_ID]),
('conf_customer_state_visible', '1', [BLOG_ID]),
('conf_customer_street_address_required', '1', [BLOG_ID]),
('conf_customer_street_address_visible', '1', [BLOG_ID]),
('conf_customer_title_required', '1', [BLOG_ID]),
('conf_customer_title_visible', '1', [BLOG_ID]),
('conf_customer_zip_code_required', '0', [BLOG_ID]),
('conf_customer_zip_code_visible', '1', [BLOG_ID]),
('conf_deposit_enabled', '1', [BLOG_ID]),
('conf_distance_measurement_unit', 'Mi', [BLOG_ID]),
('conf_item_big_thumb_h', '225', [BLOG_ID]),
('conf_item_big_thumb_w', '360', [BLOG_ID]),
('conf_item_mini_thumb_h', '63', [BLOG_ID]),
('conf_item_mini_thumb_w', '100', [BLOG_ID]),
('conf_item_thumb_h', '150', [BLOG_ID]),
('conf_item_thumb_w', '240', [BLOG_ID]),
('conf_item_url_slug', 'car-model', [BLOG_ID]),
('conf_load_datepicker_from_plugin', '1', [BLOG_ID]),
('conf_load_fancybox_from_plugin', '1', [BLOG_ID]),
('conf_load_font_awesome_from_plugin', '1', [BLOG_ID]),
('conf_load_slick_slider_from_plugin', '1', [BLOG_ID]),
('conf_location_big_thumb_h', '225', [BLOG_ID]),
('conf_location_big_thumb_w', '360', [BLOG_ID]),
('conf_location_mini_thumb_h', '63', [BLOG_ID]),
('conf_location_mini_thumb_w', '100', [BLOG_ID]),
('conf_location_thumb_h', '179', [BLOG_ID]),
('conf_location_thumb_w', '179', [BLOG_ID]),
('conf_location_url_slug', 'car-location', [BLOG_ID]),
('conf_manufacturer_thumb_h', '179', [BLOG_ID]),
('conf_manufacturer_thumb_w', '179', [BLOG_ID]),
('conf_maximum_booking_period', '31622400', [BLOG_ID]),
('conf_minimum_block_period_between_bookings', '7199', [BLOG_ID]),
('conf_minimum_booking_period', '28800', [BLOG_ID]),
('conf_minimum_period_until_pickup', '86400', [BLOG_ID]),
('conf_noon_time', '12:00:00', [BLOG_ID]),
('conf_page_url_slug', 'car-rental', [BLOG_ID]),
('conf_prepayment_enabled', '1', [BLOG_ID]),
('conf_price_calculation_type', '1', [BLOG_ID]),
('conf_recaptcha_enabled', '0', [BLOG_ID]),
('conf_recaptcha_secret_key', '', [BLOG_ID]),
('conf_recaptcha_site_key', '', [BLOG_ID]),
('conf_reveal_partner', '1', [BLOG_ID]),
('conf_search_body_type_required', '0', [BLOG_ID]),
('conf_search_body_type_visible', '1', [BLOG_ID]),
('conf_search_booking_code_required', '0', [BLOG_ID]),
('conf_search_booking_code_visible', '0', [BLOG_ID]),
('conf_search_coupon_code_required', '0', [BLOG_ID]),
('conf_search_coupon_code_visible', '1', [BLOG_ID]),
('conf_search_enabled', '1', [BLOG_ID]),
('conf_search_fuel_type_required', '0', [BLOG_ID]),
('conf_search_fuel_type_visible', '1', [BLOG_ID]),
('conf_search_manufacturer_required', '0', [BLOG_ID]),
('conf_search_manufacturer_visible', '0', [BLOG_ID]),
('conf_search_partner_required', '0', [BLOG_ID]),
('conf_search_partner_visible', '0', [BLOG_ID]),
('conf_search_pickup_date_required', '1', [BLOG_ID]),
('conf_search_pickup_date_visible', '1', [BLOG_ID]),
('conf_search_pickup_location_required', '1', [BLOG_ID]),
('conf_search_pickup_location_visible', '1', [BLOG_ID]),
('conf_search_return_date_required', '1', [BLOG_ID]),
('conf_search_return_date_visible', '1', [BLOG_ID]),
('conf_search_return_location_required', '1', [BLOG_ID]),
('conf_search_return_location_visible', '1', [BLOG_ID]),
('conf_search_transmission_type_required', '0', [BLOG_ID]),
('conf_search_transmission_type_visible', '0', [BLOG_ID]),
('conf_send_emails', '0', [BLOG_ID]),
('conf_short_date_format', 'm/d/Y', [BLOG_ID]),
('conf_show_price_with_taxes', '0', [BLOG_ID]),
('conf_system_style', 'Steel Blue', [BLOG_ID]),
('conf_terms_and_conditions_page_id', '".($wpPostsAI+33)."', [BLOG_ID]),
('conf_universal_analytics_enhanced_ecommerce', '0', [BLOG_ID]),
('conf_universal_analytics_events_tracking', '0', [BLOG_ID]),
('conf_updated', '0', [BLOG_ID])";