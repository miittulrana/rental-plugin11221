<?php
/**
 * Extension install insert sql data
 * @note        Supports all installation BB codes
 * @package     FleetManagement
 * @author      Kestutis Matuliauskas
 * @copyright   Kestutis Matuliauskas
 * @license     See Legal/License.txt for details.
 */
defined( 'ABSPATH' ) or die( 'No script kiddies, please!' );

$arrInsertSQL = array();
$arrPluginInsertSQL = array();

$arrPluginInsertSQL['emails'] = "(email_type, email_subject, email_body, blog_id) VALUES
(1, '[LANG_EMAIL_DEFAULT_TITLE_ORDER_DETAILS_TEXT]',
'[LANG_EMAIL_DEFAULT_DEAR_TEXT], [CUSTOMER_NAME],\n\n[LANG_EMAIL_DEFAULT_BODY_ORDER_RECEIVED_TEXT]\n\n[LANG_EMAIL_DEFAULT_BODY_ORDER_DETAILS_TEXT]\n\n[INVOICE]\n\n[LANG_EMAIL_DEFAULT_REGARDS_TEXT],\n[COMPANY_NAME],\n[COMPANY_PHONE],\n[COMPANY_EMAIL]',
[BLOG_ID]),
(2, '[LANG_EMAIL_DEFAULT_TITLE_ORDER_CONFIRMED_TEXT]',
'[LANG_EMAIL_DEFAULT_DEAR_TEXT], [CUSTOMER_NAME],\n\n[LANG_EMAIL_DEFAULT_BODY_PAYMENT_RECEIVED_TEXT]\n\n[LANG_EMAIL_DEFAULT_BODY_ORDER_DETAILS_TEXT]\n\n[INVOICE]\n\n[LANG_EMAIL_DEFAULT_REGARDS_TEXT],\n[COMPANY_NAME],\n[COMPANY_PHONE],\n[COMPANY_EMAIL]',
[BLOG_ID]),
(3, '[LANG_EMAIL_DEFAULT_TITLE_ORDER_CANCELLED_TEXT]',
'[LANG_EMAIL_DEFAULT_DEAR_TEXT], [CUSTOMER_NAME],\n\n[LANG_EMAIL_DEFAULT_BODY_ORDER_CANCELLED_TEXT]\n\n[LANG_EMAIL_DEFAULT_REGARDS_TEXT],\n[COMPANY_NAME],\n[COMPANY_PHONE],\n[COMPANY_EMAIL]',
[BLOG_ID]),
(4, '[LANG_EMAIL_DEFAULT_ADM_TITLE_ORDER_DETAILS_TEXT]',
'[LANG_EMAIL_DEFAULT_ADM_BODY_ORDER_RECEIVED_TEXT]\n\n[LANG_EMAIL_DEFAULT_ADM_BODY_ORDER_DETAILS_TEXT]\n[INVOICE]',
[BLOG_ID]),
(5, '[LANG_EMAIL_DEFAULT_ADM_TITLE_ORDER_CONFIRMED_TEXT]',
'[LANG_EMAIL_DEFAULT_ADM_BODY_ORDER_PAID_TEXT]\n\n[LANG_EMAIL_DEFAULT_ADM_BODY_ORDER_DETAILS_TEXT]\n[INVOICE]',
[BLOG_ID]),
(6, '[LANG_EMAIL_DEFAULT_ADM_TITLE_ORDER_CANCELLED_TEXT]',
'[LANG_EMAIL_DEFAULT_ADM_BODY_ORDER_CANCELLED_TEXT]\n\n[LANG_EMAIL_DEFAULT_ADM_BODY_CANCELLED_ORDER_DETAILS_TEXT]\n[INVOICE]',
[BLOG_ID])";

$arrPluginInsertSQL['invoices'] = '(`booking_id`, `customer_name`, `customer_email`, `grand_total`, `fixed_deposit_amount`, `total_pay_now`, `total_pay_later`, `pickup_location`, `return_location`, `invoice`, `blog_id`) VALUES
(\'[NEXT_INVOICE_ID]\', \'Mr. John Smith\', \'john.smith@gmail.com\', \'$ 189.24\', \'$ 230.00\', \'$ 189.24\', \'$ 0.00\', \'San Francisco Intl. Airport (SFO) - Hwy 101, San Francisco, CA 94128\', \'Oakland Intl. Airport (OAK) - 1 Airport Dr, Oakland, CA 94621\',
\'<table style="font-family:Verdana, Geneva, sans-serif;font-size: 12px;background-color:#eeeeee;width:840px;border:none" cellpadding="5" cellspacing="1">
    <tbody>
    <tr>
        <td align="left" style="font-weight:bold;background-color:#eeeeee;padding-left:5px" colspan="2">Customer Details</td>
    </tr>
    <tr>
        <td align="left" style="width:160px;background-color:#ffffff;padding-left:5px">Reservation code</td>
        <td align="left" style="background-color:#ffffff;padding-left:5px">R1AHKECN</td>
    </tr>
            <tr>
            <td align="left" style="background-color:#ffffff;padding-left:5px">Customer</td>
            <td align="left" style="background-color:#ffffff;padding-left:5px">Mr. John Smith</td>
        </tr>
                <tr>
            <td align="left" style="background-color:#ffffff;padding-left:5px">Date of Birth</td>
            <td align="left" style="background-color:#ffffff;padding-left:5px">1980-06-09</td>
        </tr>
                <tr>
            <td align="left" style="background-color:#ffffff;padding-left:5px">Address</td>
            <td align="left" style="background-color:#ffffff;padding-left:5px">625 2nd Street</td>
        </tr>
                <tr>
            <td align="left" style="background-color:#ffffff;padding-left:5px">City</td>
            <td align="left" style="background-color:#ffffff;padding-left:5px">San Francisco</td>
        </tr>
                <tr>
            <td align="left" style="background-color:#ffffff;padding-left:5px">State</td>
            <td align="left" style="background-color:#ffffff;padding-left:5px">CA</td>
        </tr>
                <tr>
            <td align="left" style="background-color:#ffffff;padding-left:5px">Postal Code</td>
            <td align="left" style="background-color:#ffffff;padding-left:5px">94107</td>
        </tr>
                <tr>
            <td align="left" style="background-color:#ffffff;padding-left:5px">Country</td>
            <td align="left" style="background-color:#ffffff;padding-left:5px">United States</td>
        </tr>
                <tr>
            <td align="left" style="background-color:#ffffff;padding-left:5px">Phone</td>
            <td align="left" style="background-color:#ffffff;padding-left:5px">+14506004790</td>
        </tr>
                <tr>
            <td align="left" style="background-color:#ffffff;padding-left:5px">Email</td>
            <td align="left" style="background-color:#ffffff;padding-left:5px">john.smith@gmail.com</td>
        </tr>
                <tr>
            <td align="left" style="background-color:#ffffff;padding-left:5px">Additional Comments</td>
            <td align="left" style="background-color:#ffffff;padding-left:5px">Please leave car keys by the front door upon delivery.</td>
        </tr>
        </tbody>
</table>
<br />
<table style="font-family:Verdana, Geneva, sans-serif;font-size: 12px;background:#999999;width:840px;border:none" cellpadding="5" cellspacing="1">
<tbody>
<tr>
    <td align="left" style="font-weight:bold;background-color:#eeeeee;padding-left:5px" colspan="3">Rental Details</td>
</tr>
            <tr style="background-color:#343434;color: white" class="location-headers">
            <td align="left" class="col1" style="padding-left:5px"><strong>Pick-up Location</strong></td>
            <td align="left" class="col2" style="padding-left:5px" colspan="2"><strong>Return Location</strong></td>
        </tr>

    <tr style="background-color:#FFFFFF" class="location-details">
                    <td align="left" class="col1" style="padding-left:5px" colspan="1">
		San Francisco Intl. Airport (SFO) - Hwy 101, San Francisco, CA 94128            </td>
                            <td align="left" class="col2" style="padding-left:5px" colspan="2">
		Oakland Intl. Airport (OAK) - 1 Airport Dr, Oakland, CA 94621            </td>
            </tr>


    <tr style="background-color:#343434;color: white" class="duration-headers">
                    <td align="left" class="col1" style="padding-left:5px" colspan="1"><strong>Pick-up Date &amp; Time</strong></td>
                            <td align="left" class="col2" style="padding-left:5px" colspan="1"><strong>Return Date &amp; Time</strong></td>
            <td align="right" class="col3" style="padding-right:5px"><strong>Period</strong></td>
            </tr>

    <tr style="background-color:#FFFFFF" class="duration-details">
                    <td align="left" class="col1" style="padding-left:5px" colspan="1">
		October 16, 2015 &nbsp;&nbsp; 11:00 AM            </td>
                            <td align="left" class="col2" style="padding-left:5px" colspan="1">
		October 19, 2015 &nbsp;&nbsp; 10:00 PM            </td>
            <td align="right" class="col3" style="padding-right:5px">
		4 days            </td>
            </tr>

<!-- ITEMS -->
    <tr class="item-models-header" style="background-color:#343434;color: white">
        <td align="left" class="col1" style="padding-left:5px"><strong>Selected Cars</strong></td>
        <td align="left" class="col2" style="padding-left:5px"><strong>Price</strong></td>
        <td align="right" class="col3" style="padding-right:5px"><strong>Total</strong></td>
    </tr>
    <tr style="background-color:#FFFFFF" class="items">
        <td align="left" class="col1" style="padding-left:5px">
		Mazda 6, Intermediate        </td>
        <td align="left" class="col2" style="padding-left:5px">
            <span title="1 vehicle(s) x $ 115.72 w/o Tax + $ 10.41 Tax = 1 x $ 126.14" style="cursor:pointer">
		$ 115.72            </span>
        </td>
        <td align="right" class="col3" style="padding-right:5px">
            <span title="$ 115.72 w/o Tax + $ 10.41 Tax = $ 126.14" style="cursor:pointer">
		$ 115.72            </span>
        </td>
    </tr>

<!-- PICKUP FEES -->
    <tr style="background-color:#343434;color: white" class="location-fees-header">
        <td align="left" class="col1" style="padding-left:5px" colspan="3"><strong>Location Fees</strong></td>
    </tr>
    <tr style="background-color:#FFFFFF" class="location-fee">
        <td align="left" class="col1" style="padding-left:5px">Pick-up fee                    </td>
        <td align="left" class="col2" style="padding-left:5px">
            <span title="1 vehicle(s) x $ 7.44 w/o Tax + $ 0.67 Tax = 1 x $ 8.11" style="cursor:pointer">
		$ 7.44            </span>
        </td>
        <td align="right" class="col3" style="padding-right:5px">
            <span title="$ 7.44 w/o Tax + $ 0.67 Tax = $ 8.11" style="cursor:pointer">
		$ 7.44            </span>
        </td>
    </tr>



<!-- RETURN FEES -->
    <tr style="background-color:#FFFFFF" class="location-fee">
        <td align="left" class="col1" style="padding-left:5px">Return fee           (Nightly rate applied)        </td>
        <td align="left" class="col2" style="padding-left:5px">
            <span title="1 vehicle(s) x $ 23.97 w/o Tax + $ 2.16 Tax = 1 x $ 26.13" style="cursor:pointer">
		$ 23.97            </span>
        </td>
        <td align="right" class="col3" style="padding-right:5px">
            <span title="$ 23.97 w/o Tax + $ 2.16 Tax = $ 26.13" style="cursor:pointer">
		$ 23.97            </span>
        </td>
    </tr>

<!-- EXTRAS -->
    <tr class="extras-header" style="background-color:#343434;color: white">
       <td align="left" class="col1" colspan="3"><strong>Rental Options</strong></td>
    </tr>
    <tr style="background-color:#FFFFFF" class="extras">
         <td align="left" class="col1" style="padding-left:5px">Baby Seat</td>
         <td align="left" class="col2" style="padding-left:5px">
             <span title="1 extra(s) x $ 13.24 w/o Tax + $ 1.19 Tax = 1 x $ 14.43" style="cursor:pointer">
		$ 13.24             </span>
         </td>
         <td align="right" class="col3" style="padding-right:5px">
             <span title="$ 13.24 w/o Tax + $ 1.19 Tax = $ 14.43" style="cursor:pointer">
		$ 13.24            </span>
         </td>
    </tr>
    <tr style="background-color:#FFFFFF" class="extras">
         <td align="left" class="col1" style="padding-left:5px">GPS</td>
         <td align="left" class="col2" style="padding-left:5px">
             <span title="1 extra(s) x $ 13.24 w/o Tax + $ 1.19 Tax = 1 x $ 14.43" style="cursor:pointer">
		$ 13.24             </span>
         </td>
         <td align="right" class="col3" style="padding-right:5px">
             <span title="$ 13.24 w/o Tax + $ 1.19 Tax = $ 14.43" style="cursor:pointer">
		$ 13.24            </span>
         </td>
    </tr>


<!-- TOTAL -->
<tr style="background-color:#343434;color: white" class="total-headers">
    <td align="left" class="col1" colspan="3" style="padding-left:5px"><strong>Total</strong></td>
</tr>
    <tr style="background-color:#FFFFFF">
        <td align="right" class="col1" style="padding-right:5px" colspan="2">
            <strong>Sub Total:</strong>
        </td>
        <td align="right" class="col3" style="padding-right:5px">
            <strong>$ 173.61</strong>
        </td>
    </tr>
    <tr style="background-color:#f2f2f2">
        <td align="right" class="col1" style="padding-right:5px" colspan="2">
		Tax (9.00 %):
        </td>
        <td align="right" class="col3" style="padding-right:5px">
		$ 15.62        </td>
    </tr>
<tr style="background-color:#FFFFFF">
    <td align="right" class="col1" style="padding-right:5px" colspan="2">
        <strong>Grand Total:</strong>
    </td>
    <td align="right" class="col3" style="padding-right:5px">
        <strong>$ 189.24</strong>
    </td>
</tr>
    <tr style="background-color:#f2f2f2">
        <td align="right" class="col1" style="padding-right:5px" colspan="2">
		Deposit:
        </td>
        <td align="right" class="col3" style="padding-right:5px">
            <span title="Cars Deposit ($ 230.00) + Extras Deposit ($ 0.00) = $ 230.00" style="cursor:pointer">
		$ 230.00            </span>
        </td>
    </tr>
</tbody>
</table>

<!-- PAYMENT METHOD DETAILS -->
    <br />
    <table style="font-family:Verdana, Geneva, sans-serif;font-size: 12px;background:#999999;width:840px;border:none" cellpadding="4" cellspacing="1">
        <tr>
            <td align="left" colspan="2" style="font-weight:bold;background-color:#eeeeee;padding-left:5px">Payment Details</td>
        </tr>
        <tr>
            <td align="left" width="30%" style="font-weight:bold;background-color:#ffffff;padding-left:5px">Pay By</td>
            <td align="left" style="background-color:#ffffff;padding-left:5px">Bank Transfer</td>
        </tr>
        <tr>
            <td align="left" width="30%" style="font-weight:bold;background-color:#ffffff;padding-left:5px">Payment Details</td>
            <td align="left" style="background-color:#ffffff;padding-left:5px">Receiver: NATIVE RENTAL, LTD<br />
Account no.: US27 7300 0204 2870 6432<br />
Bank: Bank of America, Inc.</td>
        </tr>
        <tr>
            <td align="left" width="30%" style="font-weight:bold;background-color:#ffffff;padding-left:5px">Transaction ID</td>
            <td align="left" style="background-color:#ffffff;padding-left:5px">N/A</td>
        </tr>
    </table>
\',
\'[BLOG_ID]\')';

// Note: the location page, will not show location content if the location is used for the 2nd site of multisite
// So we need to update the content of location page after insert of line below
$arrPluginInsertSQL['locations'] = "(`location_code`, `location_page_id`, `location_name`, `location_image_1`, `location_image_2`, `location_image_3`, `location_image_4`, `demo_location_image_1`, `demo_location_image_2`, `demo_location_image_3`, `demo_location_image_4`, `street_address`, `city`, `state`, `zip_code`, `country`, `phone`, `email`, `pickup_fee`, `return_fee`, `open_mondays`, `open_tuesdays`, `open_wednesdays`, `open_thursdays`, `open_fridays`, `open_saturdays`, `open_sundays`, `open_time_mon`, `open_time_tue`, `open_time_wed`, `open_time_thu`, `open_time_fri`, `open_time_sat`, `open_time_sun`, `close_time_mon`, `close_time_tue`, `close_time_wed`, `close_time_thu`, `close_time_fri`, `close_time_sat`, `close_time_sun`, `lunch_enabled`, `lunch_start_time`, `lunch_end_time`, `afterhours_pickup_allowed`, `afterhours_pickup_location_id`, `afterhours_pickup_fee`, `afterhours_return_allowed`, `afterhours_return_location_id`, `afterhours_return_fee`, `location_order`, `blog_id`) VALUES
('LO_1', '[COMPANY_LOCATION_PAGE_ID]', '[LANG_COMPANY_DEFAULT_NAME_TEXT]', '', '', '', '', 0, 0, 0, 0, '[LANG_COMPANY_DEFAULT_STREET_ADDRESS_TEXT]', '[LANG_COMPANY_DEFAULT_CITY_TEXT]', '[LANG_COMPANY_DEFAULT_STATE_TEXT]', '[LANG_COMPANY_DEFAULT_ZIP_CODE_TEXT]', '[LANG_COMPANY_DEFAULT_COUNTRY_TEXT]', '[LANG_COMPANY_DEFAULT_PHONE_TEXT]', '', 0.00, 0.00, 1, 1, 1, 1, 1, 1, 1, '08:00:00', '08:00:00', '08:00:00', '08:00:00', '08:00:00', '08:00:00', '08:00:00', '18:00:00', '18:00:00', '18:00:00', '18:00:00', '18:00:00', '18:00:00', '18:00:00', 1, '12:00:00', '13:00:00', 0, 0, 0.00, 0, 0, 0.00, 1, [BLOG_ID])";

$arrPluginInsertSQL['payment_methods'] = "(`payment_method_code`, `class_name`, `payment_method_name`, `payment_method_email`, `payment_method_description`, `public_key`, `private_key`, `sandbox_mode`, `check_certificate`,  `ssl_only`, `online_payment`, `payment_method_enabled`, `payment_method_order`, `expiration_time`, `blog_id`) VALUES
('paypal', 'PayPalToFleetManagementTranspiler', '[LANG_PAYMENT_METHOD_DEFAULT_PAYPAL_TEXT]', 'yourpaypal@email.com', '[LANG_PAYMENT_METHOD_DEFAULT_PAYPAL_DETAILS_TEXT]', '', '', 0, 0, 0, 1, 0, 1, 0, [BLOG_ID]),
('stripe', 'StripeToFleetManagementTranspiler', '[LANG_PAYMENT_METHOD_DEFAULT_STRIPE_TEXT]', '', '', '', '', 0, 0, 1, 1, 0, 2, 0, [BLOG_ID]),
('bank', '', '[LANG_PAYMENT_METHOD_DEFAULT_BANK_TEXT]', '', '[LANG_PAYMENT_METHOD_DEFAULT_BANK_DETAILS_TEXT]', '', '', 0, 0, 0, 0, 1, 3, 0, [BLOG_ID]),
('phone', '', '[LANG_PAYMENT_METHOD_DEFAULT_PAY_OVER_THE_PHONE_TEXT]', '', '[LANG_COMPANY_DEFAULT_PHONE_TEXT]', '', '', 0, 0, 0, 0, 0, 4, 0, [BLOG_ID]),
('pay-at-pickup', '', '[LANG_PAYMENT_METHOD_DEFAULT_PAY_ON_ARRIVAL_TEXT]', '', '[LANG_PAYMENT_METHOD_DEFAULT_PAY_ON_ARRIVAL_DETAILS_TEXT]', '', '', 0, 0, 0, 0, 1, 5, 0, [BLOG_ID]);";

$arrPluginInsertSQL['prepayments'] = "(`period_from`, `period_till`, `item_prices_included`, `item_deposits_included`, `extra_prices_included`, `extra_deposits_included`, `pickup_fees_included`, `distance_fees_included`, `return_fees_included`, `prepayment_percentage`, `blog_id`) VALUES
(0, 31622399, 1, 0, 1, 0, 1, 1, 1, 100.00, [BLOG_ID])";

$arrPluginInsertSQL['settings'] = "(`conf_key`, `conf_value`, `blog_id`) VALUES
('conf_api_max_failed_requests_per_period', '3', [BLOG_ID]),
('conf_api_max_requests_per_period', '50', [BLOG_ID]),
('conf_booking_model', '1', [BLOG_ID]),
('conf_cancelled_payment_page_id', '[CANCELLED_PAYMENT_PAGE_ID]', [BLOG_ID]),
('conf_classify_items', '1', [BLOG_ID]),
('conf_company_city', '[LANG_COMPANY_DEFAULT_CITY_TEXT]', [BLOG_ID]),
('conf_company_country', '[LANG_COMPANY_DEFAULT_COUNTRY_TEXT]', [BLOG_ID]),
('conf_company_email', '[LANG_COMPANY_DEFAULT_EMAIL_TEXT]', [BLOG_ID]),
('conf_company_name', '[LANG_COMPANY_DEFAULT_NAME_TEXT]', [BLOG_ID]),
('conf_company_notification_emails', '1', [BLOG_ID]),
('conf_company_phone', '[LANG_COMPANY_DEFAULT_PHONE_TEXT]', [BLOG_ID]),
('conf_company_state', '[LANG_COMPANY_DEFAULT_STATE_TEXT]', [BLOG_ID]),
('conf_company_street_address', '[LANG_COMPANY_DEFAULT_STREET_ADDRESS_TEXT]', [BLOG_ID]),
('conf_company_zip_code', '[LANG_COMPANY_DEFAULT_ZIP_CODE_TEXT]', [BLOG_ID]),
('conf_confirmation_page_id', '[CONFIRMATION_PAGE_ID]', [BLOG_ID]),
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
('conf_item_url_slug', '[LANG_SETTINGS_DEFAULT_ITEM_MODEL_URL_SLUG_TEXT]', [BLOG_ID]),
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
('conf_location_url_slug', '[LANG_SETTINGS_DEFAULT_LOCATION_URL_SLUG_TEXT]', [BLOG_ID]),
('conf_manufacturer_thumb_h', '221', [BLOG_ID]),
('conf_manufacturer_thumb_w', '221', [BLOG_ID]),
('conf_maximum_booking_period', '31622400', [BLOG_ID]),
('conf_minimum_block_period_between_bookings', '7199', [BLOG_ID]),
('conf_minimum_booking_period', '28800', [BLOG_ID]),
('conf_minimum_period_until_pickup', '86400', [BLOG_ID]),
('conf_noon_time', '12:00:00', [BLOG_ID]),
('conf_page_url_slug', '[LANG_SETTINGS_DEFAULT_PAGE_URL_SLUG_TEXT]', [BLOG_ID]),
('conf_plugin_semver', '[PLUGIN_SEMVER]', [BLOG_ID]),
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
('conf_send_emails', '1', [BLOG_ID]),
('conf_short_date_format', 'm/d/Y', [BLOG_ID]),
('conf_show_price_with_taxes', '0', [BLOG_ID]),
('conf_system_style', 'Crimson Red', [BLOG_ID]),
('conf_terms_and_conditions_page_id', '[TERMS_AND_CONDITIONS_PAGE_ID]', [BLOG_ID]),
('conf_universal_analytics_enhanced_ecommerce', '0', [BLOG_ID]),
('conf_universal_analytics_events_tracking', '0', [BLOG_ID]),
('conf_updated', '0', [BLOG_ID])";

$arrPluginInsertSQL['taxes'] = "(`tax_name`, `location_id`, `location_type`, `tax_percentage`, `blog_id`) VALUES
('[LANG_TAX_SHORT_TEXT]', 0, 1, '9.00', [BLOG_ID])";