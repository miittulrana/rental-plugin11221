<?php
/**
 * Order Notifications Observer (no setup for single order notifications)
 * Final class cannot be inherited anymore. We use them when creating new instances
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Order;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Extra\Extra;
use FleetManagement\Models\Invoice\Invoice;
use FleetManagement\Models\Invoice\InvoicesObserver;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\ItemModel\ItemModelOption;
use FleetManagement\Models\Payment\PaymentMethod;
use FleetManagement\Models\Payment\PaymentMethodsObserver;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Customer\Customer;
use FleetManagement\Models\ItemModel\ItemModel;

final class OrdersObserver implements ObserverInterface
{
    private $conf 	                    = null;
    private $lang 		                = null;
    private $debugMode 	                = 0;
    private $settings                   = array();
    private $savedMessages              = array();
    private $minOrderPeriod             = 0;
    private $maxOrderPeriod             = 0;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        $this->settings = $paramSettings;

        // set minimum booking period
        $this->minOrderPeriod = StaticValidator::getValidSetting($paramSettings, 'conf_minimum_booking_period', 'positive_integer', 0);
        // Set maximum booking period
        $this->maxOrderPeriod = StaticValidator::getValidSetting($paramSettings, 'conf_maximum_booking_period', 'positive_integer', 0);
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function getSavedDebugMessages()
    {
        return isset($this->savedMessages['debug']) ? $this->savedMessages['debug'] : array();
    }

    public function getSavedOkayMessages()
    {
        return isset($this->savedMessages['okay']) ? $this->savedMessages['okay'] : array();
    }

    public function getSavedErrorMessages()
    {
        return isset($this->savedMessages['error']) ? $this->savedMessages['error'] : array();
    }

    private function saveAllMessages($paramArrMessages)
    {
        if(isset($paramArrMessages['debug']))
        {
            $this->savedMessages['debug'] = array_merge($this->getSavedDebugMessages(), $paramArrMessages['debug']);
        }
        if(isset($paramArrMessages['okay']))
        {
            $this->savedMessages['okay'] = array_merge($this->getSavedOkayMessages(), $paramArrMessages['okay']);
        }
        if(isset($paramArrMessages['error']))
        {
            $this->savedMessages['error'] = array_merge($this->getSavedErrorMessages(), $paramArrMessages['error']);
        }
    }

    public function getMinPeriod()
    {
        return $this->minOrderPeriod;
    }

    public function getMaxPeriod()
    {
        return $this->maxOrderPeriod;
    }

    public function getIdByCode($paramOrderCode)
    {
        $retOrderId = 0;
        $validOrderCode = esc_sql(sanitize_text_field($paramOrderCode)); // For sql query only

        $orderData = $this->conf->getInternalWPDB()->get_row("
                SELECT booking_id
                FROM {$this->conf->getPrefix()}bookings
                WHERE booking_code='{$validOrderCode}'
            ", ARRAY_A);
        if(!is_null($orderData))
        {
            $retOrderId = $orderData['booking_id'];
        }

        return $retOrderId;
    }

    /**
     * @note - We don't use a BLOG_ID parameter here, as we want to return orders from all blogs in network!
     * @param int $paramTimestampFrom
     * @param int $paramTimestampTill
     * @param int $paramCustomerId
     * @param string $paramList - "ORDERS", "PICKUPS", "RETURNS"
     * @return array
     */
    public function getAllIds($paramTimestampFrom = -1, $paramTimestampTill = -1, $paramCustomerId = -1, $paramList = "ORDERS")
    {
        $validCustomerId = StaticValidator::getValidInteger($paramCustomerId, -1);
        $validTimestampFrom = StaticValidator::getValidInteger($paramTimestampFrom, -1);
        $validTimestampTill = StaticValidator::getValidInteger($paramTimestampTill, -1);

        // DEBUG
        //echo "<br />FROM: $validTimestampFrom ($paramTimestampFrom), TO: $validTimestampTill ($paramTimestampTill)";

        // If not filled - return all orders
        $sqlAdd = "";
        $sqlOrderBy = "booking_timestamp ASC";
        // If there is a listing property set
        if($paramList == "PICKUPS")
        {
            if($validTimestampFrom > 0 && $validTimestampTill > 0)
            {
                $sqlAdd .= " AND pickup_timestamp BETWEEN {$validTimestampFrom} AND {$validTimestampTill}";
            } else if($validTimestampFrom > 0)
            {
                $sqlAdd .= " AND pickup_timestamp >= {$validTimestampFrom}";
            } else if($validTimestampTill > 0)
            {
                $sqlAdd .= " AND pickup_timestamp <= {$validTimestampTill}";
            }
            $sqlOrderBy = "pickup_timestamp ASC";
        } else if($paramList == "RETURNS")
        {
            if($validTimestampFrom > 0 && $validTimestampTill > 0)
            {
                $sqlAdd .= " AND return_timestamp BETWEEN {$validTimestampFrom} AND {$validTimestampTill}";
            } else if($validTimestampFrom > 0)
            {
                $sqlAdd .= " AND return_timestamp >= {$validTimestampFrom}";
            } else if($validTimestampTill > 0)
            {
                $sqlAdd .= " AND return_timestamp <= {$validTimestampTill}";
            }
            $sqlOrderBy = "return_timestamp ASC";
        } else if($paramList == "ORDERS")
        {
            if($validTimestampFrom > 0 && $validTimestampTill > 0)
            {
                $sqlAdd .= " AND booking_timestamp BETWEEN {$validTimestampFrom} AND {$validTimestampTill}";
            } else if($validTimestampFrom > 0)
            {
                $sqlAdd .= " AND booking_timestamp >= {$validTimestampFrom}";
            } else if($validTimestampTill > 0)
            {
                $sqlAdd .= " AND booking_timestamp <= {$validTimestampTill}";
            }
            $sqlOrderBy = "booking_timestamp ASC";
        }

        // If there is a customer id set
        if($validCustomerId > 0)
        {
            $sqlAdd .= " AND customer_id='{$validCustomerId}'";
        }

        $sql = "
            SELECT booking_id
            FROM {$this->conf->getPrefix()}bookings
            WHERE is_block='0'
            {$sqlAdd}
            ORDER BY {$sqlOrderBy}
        ";

        // DEBUG
        //echo nl2br($sql);

        $orderIds = $this->conf->getInternalWPDB()->get_col($sql);

        return $orderIds;
    }

    /**
     * Get reservation period list, if not date and time used
     * @param string $paramFieldText
     * @param int $paramSelectedPeriod
     * @param string $paramEmptyValue
     * @return string
     */
    public function getTrustedPeriodsDropdownOptionsHTML($paramFieldText = "", $paramSelectedPeriod = 7200, $paramEmptyValue = "0")
    {
        $selectPeriods = '';
        $sanitizedFieldText = sanitize_text_field($paramFieldText);

        // For up to 1 week
        $hourSpeedUpLimit1 = 1; // hours
        $hourSpeedUpLimit2 = 3; // hours
        $hourSpeedUpLimit3 = 24; // hours
        for($hour = 0; $hour <= 720; $hour++)
        {
            if($hour > $hourSpeedUpLimit3)
            {
                // For more than two days, we have to advance by 24 hours
                $hour = $hour+23;
            }
            for($minute = 0; $minute < 60; $minute = $minute+15)
            {
                //echo "hour: $hour, MIN: $minute<br />";
                if($hour == 0 && $minute == 0)
                {
                    // Special case for first item
                    if($paramEmptyValue == "0")
                    {
                        if($paramSelectedPeriod == 0)
                        {
                            $selectPeriods .= '<option value="0" selected="selected">'.esc_html($sanitizedFieldText).'</option>';
                        } else
                        {
                            $selectPeriods .= '<option value="0">'.esc_html($sanitizedFieldText).'</option>';
                        }
                    } else
                    {
                        if($paramSelectedPeriod == "")
                        {
                            $selectPeriods .= '<option value="" selected="selected">'.esc_html($sanitizedFieldText).'</option>';
                        } else
                        {
                            $selectPeriods .= '<option value="">'.esc_html($sanitizedFieldText).'</option>';
                        }
                    }
                } else
                {
                    if($hour >= $hourSpeedUpLimit2 && $minute > 0)
                    {
                        // For more than a day, we have to advance not by 30 minutes, but by the whole hour
                        break;
                    }
                    if($hour < $hourSpeedUpLimit1 || ($hour >= $hourSpeedUpLimit1 && ($minute == 0 || $minute == 30)))
                    {
                        // For more than 60 minutes, we advance by 30 more minutes

                        // All other items
                        $currentDay = $hour > 23 ? floor($hour/24) : 0;
                        $currentHour = $hour > 23 ? $hour - $currentDay*24 : $hour;
                        $currentMinute = $minute;
                        $currentFullTimeInMinutes = $hour*60 + $minute;

                        $daysText = $this->lang->getTimeText($currentDay, $this->lang->getText('LANG_DAY1_TEXT'), $this->lang->getText('LANG_DAYS2_TEXT'), $this->lang->getText('LANG_DAYS10_TEXT'));
                        $hoursText = $this->lang->getTimeText($currentHour, $this->lang->getText('LANG_HOUR1_TEXT'), $this->lang->getText('LANG_HOURS2_TEXT'), $this->lang->getText('LANG_HOURS10_TEXT'));
                        $minutesText = $this->lang->getTimeText($currentMinute, $this->lang->getText('LANG_MINUTE1_TEXT'), $this->lang->getText('LANG_MINUTES2_TEXT'), $this->lang->getText('LANG_MINUTES10_TEXT'));

                        $optionTitle = "";
                        if($hour >= $hourSpeedUpLimit3)
                        {
                            $optionTitle .= $currentDay." ".$daysText;
                        } else if($hour >= $hourSpeedUpLimit2)
                        {
                            $optionTitle .= $hour." ".$hoursText;
                        } else
                        {
                            $optionTitle .= $currentFullTimeInMinutes." ".$minutesText;
                        }
                        $optionValueInSeconds = $currentDay*86400 + $currentHour*3600 + $currentMinute*60;

                        if(
                            $optionValueInSeconds >= $this->minOrderPeriod &&
                            $optionValueInSeconds <= $this->maxOrderPeriod
                        ){
                            // Output
                            if($optionValueInSeconds == $paramSelectedPeriod)
                            {
                                $selectPeriods .= '<option value="'.esc_attr($optionValueInSeconds).'" selected="selected">'.esc_html($optionTitle).'</option>';
                            } else
                            {
                                $selectPeriods .= '<option value="'.esc_attr($optionValueInSeconds).'">'.esc_html($optionTitle).'</option>';
                            }
                        }
                    }
                }
            }
        }

        return $selectPeriods;
    }


    /**
     * @param $paramLocationId
     * @return array
     */
    public function getUpcomingIdsByLocationId($paramLocationId)
    {
        $validLocationId= StaticValidator::getValidPositiveInteger($paramLocationId, 0);
        // OPTIMIZED SQL: We used Php function time() instead of UNIX_TIMESTAMP() to perform more faster search
        // If there exists booking with this pickup or return location id
        $sql = "
              SELECT booking_id
              FROM {$this->conf->getPrefix()}bookings
              WHERE pickup_timestamp>='".time()."' AND
              (pickup_location_id='{$validLocationId}' OR return_location_id='{$validLocationId}')
              AND is_block='0' AND blog_id='{$this->conf->getBlogId()}'
        ";

        $orderIds = $this->conf->getInternalWPDB()->get_col($sql);

        return $orderIds;
    }

    /**
     * @param $paramCustomerId
     * @return array
     */
    public function getIndependentUpcomingIdsByCustomerId($paramCustomerId)
    {
        $validCustomerId = StaticValidator::getValidPositiveInteger($paramCustomerId, 0);
        $sql = "
            SELECT booking_id
            FROM {$this->conf->getPrefix()}bookings
            WHERE customer_id='{$validCustomerId}' AND pickup_timestamp>='".time()."' AND is_block='0' AND blog_id='{$this->conf->getBlogId()}'
        ";
        $orderIds = $this->conf->getInternalWPDB()->get_col($sql);

        return $orderIds;
    }

    /**
     * @note1 - we don't use blog_id here, as we want to clear for all sites
     * @note2 - we can cancel automatically only UNCONFIRMED ORDER's
     */
    public function cancelExpired()
    {
        // OPTIMIZED - we don't use UNIX_TIMESTAMP() here is SQL WHERE, to save resource and not to recalculate time every time
        // so we use PHP's time() function instead, which does the work pretty well,
        // and do not needs to be calculated on every SQL query
        $sqlRows = $this->conf->getInternalWPDB()->get_results("
			SELECT b.booking_id
			FROM {$this->conf->getPrefix()}bookings b
			JOIN {$this->conf->getPrefix()}payment_methods pm ON b.payment_method_code=pm.payment_method_code
			WHERE b.payment_successful='0' AND pm.expiration_time!='0'
			AND (
				(b.booking_timestamp < (".time()."-pm.expiration_time))
				OR (b.pickup_timestamp < (".(time()-86400)."))
			) AND is_block='0'
		", ARRAY_A);

        foreach ($sqlRows AS $currentRow)
        {
            $this->conf->getInternalWPDB()->query("
				DELETE FROM {$this->conf->getPrefix()}bookings
				WHERE booking_id='".$currentRow['booking_id']."' AND is_block='0'
			");
            $this->conf->getInternalWPDB()->query("
				DELETE FROM {$this->conf->getPrefix()}booking_options
				WHERE booking_id='".$currentRow['booking_id']."'
			");
            $this->conf->getInternalWPDB()->query("
				DELETE FROM {$this->conf->getPrefix()}invoices
				WHERE booking_id='".$currentRow['booking_id']."'
			");
        }
    }

    /**
     * Update pickup and return location unique identifiers in orders table for specific blog_id
     * @param string $paramOldCode
     * @param string $paramNewCode
     * @return bool
     */
    public function changeLocationUniqueIdentifier($paramOldCode, $paramNewCode)
    {
        $changed = false;
        $validOldCode = esc_sql(sanitize_text_field($paramOldCode)); // For sql queries only
        $validNewCode = esc_sql(sanitize_text_field($paramNewCode)); // For sql queries only

        $pickupUpdateQuery = "
            UPDATE {$this->conf->getPrefix()}bookings SET pickup_location_code='{$validNewCode}'
            WHERE pickup_location_code='{$validOldCode}' AND blog_id='{$this->conf->getBlogId()}'
        ";
        $returnUpdateQuery = "
            UPDATE {$this->conf->getPrefix()}bookings SET return_location_code='{$validNewCode}'
            WHERE return_location_code='{$validOldCode}' AND blog_id='{$this->conf->getBlogId()}'
        ";

        $pickupResult = $this->conf->getInternalWPDB()->query($pickupUpdateQuery);
        $returnResult = $this->conf->getInternalWPDB()->query($returnUpdateQuery);

        // For update we only check for 'false'
        if($pickupResult === false || $returnResult === false)
        {
            $this->savedMessages['error'][] = $this->lang->getText('LANG_ORDERS_UPDATE_ERROR_TEXT');
        } else
        {
            $changed = true;
            $totalAffectedRows = intval($pickupResult) + intval($returnResult);
            $this->savedMessages['okay'][] = sprintf($this->lang->getText('LANG_ORDERS_D_UPDATED_TEXT'), $totalAffectedRows);
        }

        return $changed;
    }

    /**
     * Update payment method code in bookings table for specific blog_id
     * @param string $paramOldCode
     * @param string $paramNewCode
     * @return bool
     */
    public function changePaymentMethodCode($paramOldCode, $paramNewCode)
    {
        $changed = false;
        $validOldCode = esc_sql(sanitize_text_field($paramOldCode)); // For sql queries only
        $validNewCode = esc_sql(sanitize_text_field($paramNewCode)); // For sql queries only

        $updateQuery = "
            UPDATE {$this->conf->getPrefix()}bookings SET payment_method_code='{$validNewCode}'
            WHERE payment_method_code='{$validOldCode}' AND blog_id='{$this->conf->getBlogId()}'
        ";

        $updateResult = $this->conf->getInternalWPDB()->query($updateQuery);

        // For update we only check for 'false'
        if($updateResult === false)
        {
            $this->savedMessages['error'][] = $this->lang->getText('LANG_ORDERS_UPDATE_ERROR_TEXT');
        } else
        {
            $changed = true;
            $totalAffectedRows = intval($updateResult);
            $this->savedMessages['okay'][] = sprintf($this->lang->getText('LANG_ORDERS_D_UPDATED_TEXT'), $totalAffectedRows);
        }

        return $changed;
    }

    /**
     * Update item SKU in booking_options table for specific blog_id
     * @param string $paramOldSKU
     * @param string $paramNewSKU
     * @return bool
     */
    public function changeItemModelSKU($paramOldSKU, $paramNewSKU)
    {
        $changed = false;
        $validOldSKU = esc_sql(sanitize_text_field($paramOldSKU)); // For sql queries only
        $validNewSKU = esc_sql(sanitize_text_field($paramNewSKU)); // For sql queries only

        $updateQuery = "
            UPDATE {$this->conf->getPrefix()}booking_options SET item_sku='{$validNewSKU}'
            WHERE item_sku='{$validOldSKU}' AND blog_id='{$this->conf->getBlogId()}'
        ";

        $updateResult = $this->conf->getInternalWPDB()->query($updateQuery);

        // For update we only check for 'false'
        if($updateResult === false)
        {
            $this->savedMessages['error'][] = $this->lang->getText('LANG_ORDERS_UPDATE_ERROR_TEXT');
        } else
        {
            $changed = true;
            $totalAffectedRows = intval($updateResult);
            $this->savedMessages['okay'][] = sprintf($this->lang->getText('LANG_ORDERS_D_UPDATED_TEXT'), $totalAffectedRows);
        }

        return $changed;
    }

    /**
     * Update extra SKU in booking_options table for specific blog_id
     * @param string $paramOldSKU
     * @param string $paramNewSKU
     * @return bool
     */
    public function changeExtraSKU($paramOldSKU, $paramNewSKU)
    {
        $changed = false;
        $validOldSKU = esc_sql(sanitize_text_field($paramOldSKU)); // For sql queries only
        $validNewSKU = esc_sql(sanitize_text_field($paramNewSKU)); // For sql queries only

        $updateQuery = "
            UPDATE {$this->conf->getPrefix()}booking_options SET extra_sku='{$validNewSKU}'
            WHERE extra_sku='{$validOldSKU}' AND blog_id='{$this->conf->getBlogId()}'
        ";

        $updateResult= $this->conf->getInternalWPDB()->query($updateQuery);

        // For update we only check for 'false'
        if($updateResult === false)
        {
            $this->savedMessages['error'][] = $this->lang->getText('LANG_ORDERS_UPDATE_ERROR_TEXT');
        } else
        {
            $changed = true;
            $totalAffectedRows = intval($updateResult);
            $this->savedMessages['okay'][] = sprintf($this->lang->getText('LANG_ORDERS_D_UPDATED_TEXT'), $totalAffectedRows);
        }

        return $changed;
    }


    /**
     * TODO: Instead of online payment check, this should be a check for amount
     * @param int $paramExistingOrderId - if no order exists, it will be '0'
     * @param $paramIsOnlinePayment
     * @param array $params
     * @return int
     */
    public function saveOrder_ItsOptions_AndGetSavedOrderId($paramExistingOrderId, $paramIsOnlinePayment, array $params)
    {
        // DEBUG
        // echo "<br />Order \$params: ".nl2br(print_r($params, true)); die();

        $finalOrderId = 0;
        $orderSavedSuccessfully = false;

        // Process params
        $customerId = isset($params['customer_id']) ? $params['customer_id'] : 0;
        $pickupLocationUniqueIdentifier = isset($params['pickup_location_unique_identifier']) ? $params['pickup_location_unique_identifier'] : "";
        $returnLocationUniqueIdentifier = isset($params['return_location_unique_identifier']) ? $params['return_location_unique_identifier'] : "";
        $itemModelIds = isset($params['item_model_ids']) ? $params['item_model_ids'] : array();
        $extraIds = isset($params['extra_ids']) ? $params['extra_ids'] : array();
        $paymentMethodCode = isset($params['payment_method_code']) ? $params['payment_method_code'] : array();

        // Create mandatory instances
        $onlyUpdateOrder = $paramExistingOrderId > 0 && $paramIsOnlinePayment === false ? true : false;
        if($onlyUpdateOrder === true)
        {
            // Otherwise - use current order as an object
            $objOrder = new Order($this->conf, $this->lang, $this->settings, $paramExistingOrderId);
        } else
        {
            // Reset order object and order id to 0 for online payment to create a new order
            $objOrder = new Order($this->conf, $this->lang, $this->settings, 0);
        }

        $saved = $objOrder->save($customerId, $paymentMethodCode, $pickupLocationUniqueIdentifier, $returnLocationUniqueIdentifier, $params);
        if($onlyUpdateOrder === true && $saved !== false || $onlyUpdateOrder === false && $saved > 0)
        {
            $orderSavedSuccessfully = true;
        }

        if($orderSavedSuccessfully)
        {
            // Get existing or updated order id
            $finalOrderId = $objOrder->getId();

            // If we are only updating existing order
            if($onlyUpdateOrder === true)
            {
                // Delete existing order options first
                $objOrder->deleteAllOptions();
            }

            // Add new item model order options
            foreach($itemModelIds AS $itemModelId)
            {
                $objItemModel = new ItemModel($this->conf, $this->lang, $this->settings, $itemModelId);
                $objOrderItemModel = new OrderItemModel($this->conf, $this->lang, $this->settings, $finalOrderId, $objItemModel->getSKU());
                $itemModelOptionId = isset($params['item_model_options'][$itemModelId]) ? $params['item_model_options'][$itemModelId] : 0;
                $itemModelUnitsOrdered = isset($params['item_model_units'][$itemModelId]) ? $params['item_model_units'][$itemModelId] : 0;
                if($itemModelUnitsOrdered > 0)
                {
                    $objOrderItemModel->save($itemModelOptionId, $itemModelUnitsOrdered);
                    $this->saveAllMessages($objOrderItemModel->getAllMessages());
                }

            }

            // Add new extra order options
            foreach($extraIds AS $extraId)
            {
                $objExtra = new Extra($this->conf, $this->lang, $this->settings, $extraId);
                $objOrderExtra = new OrderExtra($this->conf, $this->lang, $this->settings, $finalOrderId, $objExtra->getSKU());
                $extraOptionId = isset($params['extra_options'][$extraId]) ? $params['extra_options'][$extraId] : 0;
                $extraUnitsOrdered = isset($params['extra_units'][$extraId]) ? $params['extra_units'][$extraId] : 0;
                if($extraUnitsOrdered > 0)
                {
                    $objOrderExtra->save($extraOptionId, $extraUnitsOrdered);
                    $this->saveAllMessages($objOrderExtra->getAllMessages());
                }
            }
        }

        // Add errors to error stack
        $this->saveAllMessages(array('error' => $objOrder->getErrorMessages()));

        // Prepare debug messages
        $debugMessages = array_merge(
            array('Order message list:')
        );
        $debugMessages[] = "Order params: ".print_r($params, true);
        $debugMessages[] = "Order saved successfully: ".var_export($orderSavedSuccessfully, true);

        // Add to debug log stack
        $this->saveAllMessages(array('debug' => $debugMessages));

        return $finalOrderId;
    }


    /* --------------------------------------------------------------------------- */
    /* ---------------------- METHODS FOR ADMIN ACCESS ONLY ---------------------- */
    /* --------------------------------------------------------------------------- */

    /**
     * @param int $paramTimestampFrom
     * @param int $paramTimestampTill
     * @param int $paramCustomerId
     * @param string $paramBackToURL_Part
     * @return string
     */
    public function getTrustedAdminPickupsHTML($paramTimestampFrom = -1, $paramTimestampTill = -1, $paramCustomerId = -1, $paramBackToURL_Part = "")
    {
        return $this->getTrustedAdminListHTML($paramTimestampFrom, $paramTimestampTill, $paramCustomerId, $paramBackToURL_Part, "PICKUPS");
    }

    public function getTrustedAdminReturnsHTML($paramTimestampFrom = -1, $paramTimestampTill = -1, $paramCustomerId = -1, $paramBackToURL_Part = "")
    {
        return $this->getTrustedAdminListHTML($paramTimestampFrom, $paramTimestampTill, $paramCustomerId, $paramBackToURL_Part, "RETURNS");
    }

    public function getTrustedAdminOrdersHTML($paramTimestampFrom = -1, $paramTimestampTill = -1, $paramCustomerId = -1, $paramBackToURL_Part = "")
    {
        return $this->getTrustedAdminListHTML($paramTimestampFrom, $paramTimestampTill, $paramCustomerId, $paramBackToURL_Part, "ORDERS");
    }

    /**
     * @note - we don't use blog_id here, because we want to see order from all sites
     * @param int $paramTimestampFrom
     * @param int $paramTimestampTill
     * @param int $paramCustomerId
     * @param string $paramBackToURL_Part - Optional back to ulr part, i.e. &back_from_date=1234567890&back_till_date=2234567890
     * @param string $paramList
     * @return string
     */
    public function getTrustedAdminListHTML($paramTimestampFrom = -1, $paramTimestampTill = -1, $paramCustomerId = -1, $paramBackToURL_Part = "", $paramList = "ORDERS")
    {
        // Create mandatory instances
        $objInvoicesObserver = new InvoicesObserver($this->conf, $this->lang, $this->settings);
        $objPaymentMethodsObserver = new PaymentMethodsObserver($this->conf, $this->lang, $this->settings);

        // Set limitations
        $maxResults = 500;

        $sanitizedBackURL_Part = sanitize_text_field($paramBackToURL_Part); // TEST: do not escape it, as it is for url redirect
        //$validBackURL_Part = esc_attr($sanitizedBackURL_Part); // escaped, as it is attribute for JS

        $retHTML = '';

        // Note: we don't pass maxResults limitation to getAllIds method, because the final results amount can be different depending on access permissions
        $orderIds = $this->getAllIds($paramTimestampFrom, $paramTimestampTill, $paramCustomerId, $paramList);
        $i = 0;
        foreach($orderIds AS $orderId)
        {
            $objOrder = new Order($this->conf, $this->lang, $this->settings, $orderId);
            $orderDetails = $objOrder->getDetails();

            if(is_multisite() && $orderDetails['blog_id'] != $this->conf->getBlogId())
            {
                // Important! switch_to_blog action MUST happen before the canEdit() check, otherwise it will fail to pass
                switch_to_blog($orderDetails['blog_id']);
            }

            $canEdit = $objOrder->canEdit();
            if($canEdit || current_user_can('view_'.$this->conf->getExtPrefix().'all_bookings'))
            {
                $i++;
                $objCustomer = new Customer($this->conf, $this->lang, $this->settings, $objOrder->getCustomerId());
                $customerDetails = $objCustomer->getDetails();

                $paymentMethodId = $objPaymentMethodsObserver->getIdByCode($orderDetails['payment_method_code']);
                $objPaymentMethod = new PaymentMethod($this->conf, $this->lang, $this->settings, $paymentMethodId);

                $arrOrderedItemModelNames = array();
                $counter = 0;

                // Check and set if we are in call for price mode
                foreach($orderDetails['item_models'] AS $orderedItemModels)
                {
                    $counter++;
                    $objItemModel = new ItemModel($this->conf, $this->lang, $this->settings, $orderedItemModels['item_model_id']);

                    // Process item model details & prices
                    $itemModelDetails = $objItemModel->getExtendedDetails();
                    $objSelectedOption = new ItemModelOption($this->conf, $this->lang, $this->settings, $orderedItemModels['option_id']);
                    $selectedOptionDetails = $objSelectedOption->getDetails();
                    $printTranslatedSelectedOptionName = isset($selectedOptionDetails['print_translated_option_name']) ? $selectedOptionDetails['print_translated_option_name'] : "";
                    $printTranslatedItemModelWithSelectedOption = $counter.'. '.$itemModelDetails['print_translated_manufacturer_name'].' '.$itemModelDetails['print_translated_item_model_name'];
                    $printTranslatedItemModelWithSelectedOption .= $itemModelDetails['partner_profile_url'] ? ' '.esc_html($itemModelDetails['via_partner']) : '';
                    $printTranslatedItemModelWithSelectedOption .= $itemModelDetails['print_translated_class_name'] ? ', '.$itemModelDetails['print_translated_class_name'] : '';
                    $printTranslatedItemModelWithSelectedOption .= $printTranslatedSelectedOptionName ? ', '.$printTranslatedSelectedOptionName : '';
                    if($orderedItemModels['units_ordered'] > 1)
                    {
                        $printTranslatedItemModelWithSelectedOption .= ' x '.$orderedItemModels['units_ordered'];
                    }

                    // Add item model name to ordered item models list
                    $arrOrderedItemModelNames[] = $printTranslatedItemModelWithSelectedOption;
                }

                // Set defaults
                $printGrandTotal = '';
                $printFixedDeposit = '';
                $printTotalPayNow = '';
                $printTotalPayLater = '';
                $trustedPickupLocationHTML = '';
                $trustedReturnLocationHTML = '';

                // Prints
                $printOrderedItemModels = implode("<br />", $arrOrderedItemModelNames);

                // Get customer details and all related invoices to this order
                $overallInvoiceId = $objInvoicesObserver->getIdByParams('OVERALL', $orderId);
                $objOverallInvoice = new Invoice($this->conf, $this->lang, $this->settings, $overallInvoiceId);
                $overallInvoiceDetails = $objOverallInvoice->getDetails();
                if(!is_null($overallInvoiceDetails))
                {
                    // Get overalls
                    $printGrandTotal = $overallInvoiceDetails['print_grand_total'];
                    $printFixedDeposit = $overallInvoiceDetails['print_fixed_deposit'];
                    $printTotalPayNow = $overallInvoiceDetails['print_total_pay_now'];
                    $printTotalPayLater = $overallInvoiceDetails['print_total_pay_later'];

                    $trustedPickupLocationHTML = $overallInvoiceDetails['pickup_location'];
                    $trustedReturnLocationHTML = $overallInvoiceDetails['return_location'];
                }

                if($objPaymentMethod->isOnlinePayment())
                {
                    $payNowText = $this->lang->getText('LANG_STEP5_PAY_ONLINE_TEXT');
                } else
                {
                    $payNowText = $this->lang->getText('LANG_STEP5_PAY_AT_PICKUP_TEXT');
                }

                $invoiceTitle = sprintf($this->lang->getText('LANG_INVOICE_S_TEXT'), $orderDetails['booking_code']);
                $links = "";
                if($canEdit && $orderDetails['return_timestamp'] >= time() && $orderDetails['is_cancelled'] == 0)
                {
                    if($orderDetails['payment_successful'] == 1)
                    {
                        $links .= '<a href="javascript:;" onclick="javascript:FleetManagementAdmin.refundOrder(\''.esc_js($this->conf->getExtCode()).'\', '.esc_js($orderId).', \''.$sanitizedBackURL_Part.'\');" class="bodytext">'.$this->lang->escHTML('LANG_ORDER_REFUND_TEXT').'</a><br />';
                    } else if($orderDetails['payment_successful'] == 0)
                    {
                        $links .= '<a href="javascript:;" onclick="javascript:FleetManagementAdmin.confirmOrder(\''.esc_js($this->conf->getExtCode()).'\', '.esc_js($orderId).', \''.$sanitizedBackURL_Part.'\');" class="bodytext">'.$this->lang->escHTML('LANG_ORDER_MARK_PAID_TEXT').'</a><br />';
                    }
                    // Early return button section
                    if($orderDetails['pickup_timestamp'] <= time() && $orderDetails['is_completed_early'] == 0)
                    {
                        $links .= '<a href="javascript:;" onclick="javascript:FleetManagementAdmin.markCompletedEarly(\''.esc_js($this->conf->getExtCode()).'\', '.esc_js($orderId).', \''.$sanitizedBackURL_Part.'\');" class="bodytext">'.$this->lang->escHTML('LANG_ORDER_MARK_COMPLETED_EARLY_TEXT').'</a><br />';
                    }
                    $links .= '<a href="javascript:;" onclick="javascript:FleetManagementAdmin.cancelOrder(\''.esc_js($this->conf->getExtCode()).'\', '.esc_js($orderId).', \''.$sanitizedBackURL_Part.'\');">'.$this->lang->escHTML('LANG_CANCEL_TEXT').'</a>';
                } else if($canEdit && $orderDetails['return_timestamp'] < time() && $orderDetails['is_cancelled'] == 0)
                {
                    if($orderDetails['payment_successful'] == 1)
                    {
                        $links .= '<a href="javascript:;" onclick="javascript:FleetManagementAdmin.refundOrder(\''.esc_js($this->conf->getExtCode()).'\', '.esc_js($orderId).', \''.$sanitizedBackURL_Part.'\');" class="bodytext">'.$this->lang->escHTML('LANG_ORDER_REFUND_TEXT').'</a><br />';
                    } else if($orderDetails['payment_successful'] == 0)
                    {
                        $links .= '<a href="javascript:;" onclick="javascript:FleetManagementAdmin.confirmOrder(\''.esc_js($this->conf->getExtCode()).'\', '.esc_js($orderId).', \''.$sanitizedBackURL_Part.'\');" class="bodytext">'.$this->lang->escHTML('LANG_ORDER_MARK_PAID_TEXT').'</a><br />';
                    }
                    $links .= '<a href="javascript:;" onclick="javascript:FleetManagementAdmin.deleteOrder(\''.esc_js($this->conf->getExtCode()).'\', '.esc_js($orderId).', \''.$sanitizedBackURL_Part.'\');" class="bodytext">'.$this->lang->escHTML('LANG_DELETE_TEXT').'</a>';
                } else if($canEdit)
                {
                    // Cancelled (is_cancelled = 1)
                    $links .= '<a href="javascript:;" onclick="javascript:FleetManagementAdmin.deleteOrder(\''.esc_js($this->conf->getExtCode()).'\', '.esc_js($orderId).', \''.$sanitizedBackURL_Part.'\');" class="bodytext">'.$this->lang->escHTML('LANG_DELETE_TEXT').'</a>';
                }

                $retHTML .= '<tr>
                    <td>'.$i.'</td>
                    <td>'.$orderDetails['booking_code'].'<br /><hr />'.$customerDetails['print_full_name'].'<br />'.$printOrderedItemModels.'</td>
                    <td>'.$orderDetails['print_pickup_date'].' '.$orderDetails['print_pickup_time'].'<br /><hr />'.$trustedPickupLocationHTML.'</td>
                    <td>'.$orderDetails['print_return_date'].' '.$orderDetails['print_return_time'].'<br /><hr />'.$trustedReturnLocationHTML.'</td>
                    <td>'.$orderDetails['print_order_date'].' '.$orderDetails['print_order_time'].'<br /><hr />
                        <span style="font-weight: bold;color:'.esc_attr($orderDetails['payment_status_color']).';">'.esc_html($orderDetails['payment_status_text']).'</span>,
                        <span style="font-weight: bold;color:'.esc_attr($orderDetails['status_color']).';">'.esc_html($orderDetails['status_text']).'</span>
                    </td>
                    <td>
                        '.$this->lang->escHTML('LANG_TOTAL_TEXT').': '.$printGrandTotal.'<br /><hr />
                        '.$this->lang->escHTML('LANG_DEPOSIT_TEXT').': '.$printFixedDeposit.'<br />
                        '.$payNowText.': '.$printTotalPayNow.'<br />
                        '.$this->lang->escHTML('LANG_PAYMENT_PAY_ON_RETURN_TEXT').': '.$printTotalPayLater.'<br />
                    </td>
                    <td align="right">
                        <a href="'.esc_url(admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'view-order&amp;order_id='.$orderId)).'" class="bodytext">'.$this->lang->escHTML('LANG_VIEW_DETAILS_TEXT').'</a><br />
                        <a href="javascript:;" onclick="javascript:FleetManagementAdmin.printInvoicePopup(\''.esc_js($this->conf->getExtCode()).'\', \''.esc_js($overallInvoiceId).'\', \''.esc_js($orderId).'\');" class="bodytext">'.$this->lang->escHTML('LANG_INVOICE_PRINT_TEXT').'</a><br />
                        '.$links.'
                    </td>
                 </tr>';
            }

            if($i >= $maxResults)
            {
                // Break if max results is reached
                break;
            }
        }

        if(is_multisite())
        {
            // Switch back to current blog id. Restore current blog won't work here, as it would just restore to previous blog of the long loop
            switch_to_blog($this->conf->getBlogId());
        }

        return $retHTML;
    }
}