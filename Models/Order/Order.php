<?php
/**
 * Order Element

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Order;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ElementInterface;
use FleetManagement\Models\StackInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class Order extends AbstractStack implements StackInterface
{
    private $conf 	                = null;
    private $lang 		            = null;
    private $settings		        = array();
    private $debugMode 	            = 0;
    private $orderId                = 0;
    private $shortDateFormat        = "m/d/Y";

    /**
     * @param ConfigurationInterface &$paramConf
     * @param LanguageInterface &$paramLang
     * @param array $paramSettings
     * @param int $paramOrderId
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramOrderId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        // Set saved settings
        $this->settings = $paramSettings;

        // Set order id
        $this->orderId = StaticValidator::getValidPositiveInteger($paramOrderId, 0);
        $this->shortDateFormat = StaticValidator::getValidSetting($paramSettings, 'conf_short_date_format', "date_format", "m/d/Y");
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * For internal class use only
     * @param $paramOrderId
     * @return mixed
     */
    public function getDataFromDatabaseById($paramOrderId)
    {
        $validOrderId = StaticValidator::getValidPositiveInteger($paramOrderId, 0);
        $sqlData = "
            SELECT *, body_type_id AS class_id, transmission_type_id AS attribute_id1, fuel_type_id AS attribute_id2
            FROM {$this->conf->getPrefix()}bookings
            WHERE booking_id='{$validOrderId}' AND is_block='0'
        ";

        $orderData = $this->conf->getInternalWPDB()->get_row($sqlData, ARRAY_A);

        return $orderData;
    }

    public function getId()
    {
        return $this->orderId;
    }

    /**
     * Take the customer Id data from booking data
     * @return int
     */
    public function getCustomerId()
    {
        $customerId = 0;
        $orderData = $this->getDataFromDatabaseById($this->orderId);
        if(!is_null($orderData))
        {
            $customerId = $orderData['customer_id'];
        }

        return $customerId;
    }

    public function getCode()
    {
        $orderCode = "";
        $orderData = $this->getDataFromDatabaseById($this->orderId);
        if(!is_null($orderData))
        {
            $orderCode = stripslashes($orderData['booking_code']); // Make raw
        }

        return $orderCode;
    }

    public function getPrintCode()
    {
        return esc_html($this->getCode());
    }

    public function getEditCode()
    {
        return esc_attr($this->getCode());
    }

    /**
     * If will get next insert id of next order id by running SQL, not via param
     * @return string
     */
    public function generateCode()
    {
        $validNextMySQLInsertId = 1;
        $row = $this->conf->getInternalWPDB()->get_row("SHOW TABLE STATUS LIKE '{$this->conf->getPrefix()}bookings'", ARRAY_A);
        if(!is_null($row))
        {
            $validNextMySQLInsertId = $row['Auto_increment']; // This is current max it+1, capital first letter is ok - it is there like that
        }
        $newOrderCode = $this->conf->getOrderCodePrefix().$validNextMySQLInsertId."A".$this->getIncrementalHash(5);

        return $newOrderCode;
    }

    private function getIncrementalHash($length = 5)
    {
        //$charset = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
        $charset = "ABCDEFGHIJKLMNPRSTUVYZ"; // fits LT & EN, O is skipped to similarity to Zero
        $charsetLength = strlen($charset);
        $result = '';

        $microTimesList = explode(' ', microtime());
        $now = $microTimesList[1];
        while ($now >= $charsetLength)
        {
            $i = $now % $charsetLength;
            $result = $charset[$i] . $result;
            $now /= $charsetLength;
        }
        return substr($result, -$length);
    }

    public function getCouponCode()
    {
        $couponCode = "";
        $orderData = $this->getDataFromDatabaseById($this->orderId);
        if(!is_null($orderData))
        {
            $couponCode = stripslashes($orderData['coupon_code']); // Make raw
        }

        return $couponCode;
    }

    public function getPrintCouponCode()
    {
        return esc_html($this->getCouponCode());
    }

    public function getEditCouponCode()
    {
        return esc_attr($this->getCouponCode());
    }

    public function getPaymentMethodCode()
    {
        $paymentMethodCode = "";
        $orderData = $this->getDataFromDatabaseById($this->orderId);
        if(!is_null($orderData))
        {
            $paymentMethodCode = stripslashes($orderData['payment_method_code']); // Make raw
        }

        return $paymentMethodCode;
    }

    public function getPrintPaymentMethodCode()
    {
        return esc_html($this->getCode());
    }

    public function getEditPaymentMethodCode()
    {
        return esc_attr($this->getCode());
    }

    /**
     * Allow to edit booking if at least one item in booking owned by partner or it is a manager
     * Checks if current user can edit the element
     * @return bool
     */
    public function canEdit()
    {
        $validOrderId = StaticValidator::getValidPositiveInteger($this->orderId, 0);
        $validPartnerId = StaticValidator::getValidPositiveInteger(get_current_user_id(), 0);
        $canEdit = false;

        if($this->orderId > 0)
        {
            $bookedItemsSQL = "
                SELECT bop.item_sku
                FROM {$this->conf->getPrefix()}booking_options bop
                JOIN {$this->conf->getPrefix()}items it ON it.item_sku=bop.item_sku
                WHERE bop.booking_id='{$validOrderId}' AND it.partner_id='{$validPartnerId}'
            ";
            $resultsExists = $this->conf->getInternalWPDB()->get_row($bookedItemsSQL, ARRAY_A);
            if(!is_null($resultsExists) && current_user_can('manage_'.$this->conf->getExtPrefix().'partner_bookings'))
            {
                $canEdit = true;
            } else if(current_user_can('manage_'.$this->conf->getExtPrefix().'all_bookings'))
            {
                $canEdit = true;
            }
        }

        return $canEdit;
    }

    /**
     * A positive check
     * @return bool
     */
    public function isUpcoming()
    {
        $orderData = $this->getDataFromDatabaseById($this->orderId);
        if(!is_null($orderData))
        {
            // For upcoming orders we use 'expected'
            if($orderData['pickup_timestamp'] > time() && in_array($orderData['payment_successful'], array(0, 1)))
            {
                return true;
            } else
            {
                return false;
            }
        } else
        {
            return false;
        }
    }

    public function isPickedUp()
    {
        $orderData = $this->getDataFromDatabaseById($this->orderId);
        if(!is_null($orderData) && $orderData['pickup_timestamp'] < time())
        {
            return true;
        } else
        {
            return false;
        }
    }

    public function isCancelled()
    {
        $orderData = $this->getDataFromDatabaseById($this->orderId);
        if(!is_null($orderData) && $orderData['is_cancelled'] == 1)
        {
            return true;
        } else
        {
            return false;
        }
    }

    public function isPaid()
    {
        $orderData = $this->getDataFromDatabaseById($this->orderId);
        if(!is_null($orderData) && $orderData['payment_successful'] == 1)
        {
            return true;
        } else
        {
            return false;
        }
    }

    public function isCompletedEarly()
    {
        $orderData = $this->getDataFromDatabaseById($this->orderId);
        if(!is_null($orderData) && $orderData['is_completed_early'] == 1)
        {
            return true;
        } else
        {
            return false;
        }
    }

    public function isRefunded()
    {
        $orderData = $this->getDataFromDatabaseById($this->orderId);
        if(!is_null($orderData) && $orderData['payment_successful'] == 2)
        {
            return true;
        } else
        {
            return false;
        }
    }

    /**
     * Check weather this order has any assigned items or extras to it or not
     * @return bool
     */
    public function isEmpty()
    {
        $validOrderId = StaticValidator::getValidPositiveInteger($this->orderId, 0);

        $relatedRows = $this->conf->getInternalWPDB()->get_var("
            SELECT booking_id
            FROM {$this->conf->getPrefix()}booking_options
            WHERE booking_id='{$validOrderId}' AND blog_id='{$this->conf->getBlogId()}'
        ");
        // If no related elements found to this order
        if(!is_null($relatedRows))
        {
            return false;
        } else
        {
            return true;
        }
    }

    /**
     * @note: ONLY admins can edit frozen orders
     * @return bool
     */
    public function isFrozen()
    {
        $isFrozen = true;

        if($this->isUpcoming())
        {
            $isFrozen = false;
        }

        if($this->getId() == 0)
        {
            // Order code passed, but not exist in database
            $isFrozen = false;
            $this->errorMessages[] = $this->lang->getText('LANG_ORDER_INVALID_CODE_ERROR_TEXT');
        } else if($this->isPickedUp())
        {
            $isFrozen = false;
            $this->errorMessages[] = sprintf($this->lang->getText('LANG_ORDER_NO_S_PICKED_UP_ERROR_TEXT'), $this->getCode());
        } else if($this->isCancelled())
        {
            $isFrozen = false;
            $this->errorMessages[] = sprintf($this->lang->getText('LANG_ORDER_NO_S_CANCELLED_ERROR_TEXT'), $this->getCode());
        }

        return $isFrozen;
    }

    /**
     * Used as a initializer and data puller of existing order BEFORE search engine functions
     * @param bool $paramPrefillWhenNull
     * @return mixed
     */
    public function getDetails($paramPrefillWhenNull = false)
    {
        $ret = $this->getDataFromDatabaseById($this->orderId);
        if(!is_null($ret))
        {
            // Make raw
            $ret['booking_code'] = stripslashes($ret['booking_code']);
            $ret['coupon_code'] = stripslashes($ret['coupon_code']);
            $ret['pickup_location_code'] = stripslashes($ret['pickup_location_code']);
            $ret['return_location_code'] = stripslashes($ret['return_location_code']);
            $ret['payment_method_code'] = stripslashes($ret['payment_method_code']);
        } else if($paramPrefillWhenNull === true)
        {
            // Make blank data
            $ret = array();
            $ret['booking_id'] = 0;
            $ret['booking_code'] = '';
            $ret['booking_timestamp'] = 0;
            $ret['last_edit_timestamp'] = 0;
            $ret['expiration_timestamp'] = 0;
            $ret['pickup_timestamp'] = 0;
            $ret['return_timestamp'] = 0;

            $ret['pickup_location_code'] = '';

            $ret['return_location_code'] = '';

            $ret['coupon_code'] = '';
            $ret['partner_id'] = 0;

            $ret['manufacturer_id'] = 0;
            $ret['body_type_id'] = 0;
            $ret['transmission_type_id'] = 0;
            $ret['fuel_type_id'] = 0;
            $ret['customer_id'] = 0;
            $ret['payment_successful'] = 0;
            $ret['payment_transaction_id'] = '';
            $ret['payer_email'] = '';
            $ret['payment_method_code'] = 0;
            $ret['is_block'] = 0;
            $ret['is_cancelled'] = 0;
            $ret['is_completed_early'] = 0;
            $ret['blog_id'] = $this->conf->getBlogId();
        }

        if(!is_null($ret) || $paramPrefillWhenNull === true)
        {
            if($ret['booking_timestamp'] > 0)
            {
                $ret['order_date'] = date_i18n($this->shortDateFormat, $ret['booking_timestamp'] + get_option('gmt_offset') * 3600, true);
                $ret['order_time'] = date_i18n('H:i', $ret['booking_timestamp'] + get_option('gmt_offset') * 3600, true);
                $printOrderDate = date_i18n(get_option('date_format'), $ret['booking_timestamp'] + get_option('gmt_offset') * 3600, true);
                $printOrderTime = date_i18n(get_option('time_format'), $ret['booking_timestamp'] + get_option('gmt_offset') * 3600, true);
            } else
            {
                $ret['order_date'] = '';
                $ret['order_time'] = '';
                $printOrderDate = '';
                $printOrderTime = '';
            }

            if($ret['pickup_timestamp'] > 0)
            {
                $ret['pickup_date'] = date_i18n($this->shortDateFormat, $ret['pickup_timestamp'] + get_option('gmt_offset') * 3600, true);
                $ret['pickup_time'] = date_i18n('H:i', $ret['pickup_timestamp'] + get_option('gmt_offset') * 3600, true);
                $printPickupDate = date_i18n(get_option('date_format'), $ret['pickup_timestamp'] + get_option('gmt_offset') * 3600, true);
                $printPickupTime = date_i18n(get_option('time_format'), $ret['pickup_timestamp'] + get_option('gmt_offset') * 3600, true);
            } else
            {
                $ret['pickup_date'] = '';
                $ret['pickup_time'] = '';
                $printPickupDate = '';
                $printPickupTime = '';
            }

            if($ret['return_timestamp'] > 0)
            {
                $ret['return_date'] = date_i18n($this->shortDateFormat, $ret['return_timestamp'] + get_option('gmt_offset') * 3600, true);
                $ret['return_time'] = date_i18n('H:i', $ret['return_timestamp'] + get_option('gmt_offset') * 3600, true);
                $printReturnDate = date_i18n(get_option('date_format'), $ret['return_timestamp'] + get_option('gmt_offset') * 3600, true);
                $printReturnTime = date_i18n(get_option('time_format'), $ret['return_timestamp'] + get_option('gmt_offset') * 3600, true);
            } else
            {
                $ret['return_date'] = '';
                $ret['return_time'] = '';
                $printReturnDate = '';
                $printReturnTime = '';
            }

            $validOrderId = intval($ret['booking_id']);
            $orderedItemModelsSQL = "
                SELECT bo.option_id, bo.units_booked AS units_ordered,
                it.item_id AS item_model_id, it.manufacturer_id, it.body_type_id AS class_id, it.fuel_type_id AS attribute_id1, it.transmission_type_id AS attribute_id2
                FROM {$this->conf->getPrefix()}booking_options bo
                JOIN {$this->conf->getPrefix()}items it ON it.item_sku=bo.item_sku AND it.blog_id='{$ret['blog_id']}'
                WHERE booking_id='{$validOrderId}'
            ";
            $orderedExtrasSQL = "
                SELECT ex.extra_id, bo.option_id, bo.units_booked AS units_ordered
                FROM {$this->conf->getPrefix()}booking_options bo
                JOIN {$this->conf->getPrefix()}extras ex ON ex.extra_sku=bo.extra_sku AND ex.blog_id='{$ret['blog_id']}'
                WHERE booking_id='{$validOrderId}'
            ";
            $orderedItemModels = $this->conf->getInternalWPDB()->get_results($orderedItemModelsSQL, ARRAY_A);
            $orderedExtras = $this->conf->getInternalWPDB()->get_results($orderedExtrasSQL, ARRAY_A);

            // DEBUG
            // echo "<br />ORDER: ".$validOrderId.", ITEMS: ".nl2br(print_r($orderedItemModels, true));

            // Cars and Car Units
            $ret['item_model_ids'] = array();
            $ret['item_model_units'] = array();
            $ret['item_model_options'] = array();
            $ret['item_models'] = array();
            foreach($orderedItemModels AS $orderedItemModel)
            {
                $ret['item_model_ids'][] = $orderedItemModel['item_model_id'];
                $ret['item_model_units'][$orderedItemModel['item_model_id']] = $orderedItemModel['units_ordered'];
                $ret['item_model_options'][$orderedItemModel['item_model_id']] = $orderedItemModel['option_id'];
                $ret['item_models'][] = array(
                    "item_model_id" => $orderedItemModel['item_model_id'],
                    "manufacturer_id" => $orderedItemModel['manufacturer_id'],
                    "class_id" => $orderedItemModel['class_id'],
                    "attribute_id1" => $orderedItemModel['attribute_id1'],
                    "attribute_id2" => $orderedItemModel['attribute_id2'],
                    "option_id" => $orderedItemModel['option_id'],
                    "units_ordered" => $orderedItemModel['units_ordered'],
                );
            }

            // Extras and Extra Units
            $ret['extra_ids'] = array();
            $ret['extra_options'] = array();
            $ret['extra_units'] = array();
            $ret['extras'] = array();
            foreach($orderedExtras AS $reservedExtra)
            {
                $ret['extra_ids'][] = $reservedExtra['extra_id'];
                $ret['extra_options'][$reservedExtra['extra_id']] = $reservedExtra['option_id'];
                $ret['extra_units'][$reservedExtra['extra_id']] = $reservedExtra['units_ordered'];
                $ret['extras'][] = array(
                    "extra_id"      => $reservedExtra['extra_id'],
                    "option_id"     => $reservedExtra['option_id'],
                    "units_ordered"  => $reservedExtra['units_ordered'],
                );
            }

            // Get payment status text and color
            $ret['payment_status_text'] = "";
            $ret['payment_status_color'] = "#FF0000";
            if($ret['payment_successful'] == 0)
            {
                $ret['payment_status_text'] = $this->lang->getText('LANG_ORDER_STATUS_UNPAID_TEXT');
                $ret['payment_status_color'] = "#FF0000";
            } else if($ret['payment_successful'] == 1)
            {
                $ret['payment_status_text'] = $this->lang->getText('LANG_ORDER_STATUS_PAID_TEXT');
                $ret['payment_status_color'] = "black";
            } else if($ret['payment_successful'] == 2)
            {
                $ret['payment_status_text'] = $this->lang->getText('LANG_ORDER_STATUS_REFUNDED_TEXT');
                $ret['payment_status_color'] = "navy";
            }

            // Get booking status text and color
            $ret['status_text'] = "";
            $ret['status_color'] = "black";
            if($ret['is_cancelled'] == 0 && $ret['return_timestamp'] >= time())
            {
                if($ret['pickup_timestamp'] <= time())
                {
                    // Departed
                    $ret['status_text'] = $this->lang->getText('LANG_ORDER_STATUS_DEPARTED_TEXT');
                    $ret['status_color'] = "blue";
                } else
                {
                    // Upcoming
                    $ret['status_text'] = $this->lang->getText('LANG_ORDER_STATUS_UPCOMING_TEXT');
                    $ret['status_color'] = "green";
                }
            } else if($ret['is_cancelled'] == 0 && $ret['return_timestamp'] < time())
            {
                if($ret['is_completed_early'] == 1)
                {
                    $ret['status_text'] = $this->lang->getText('LANG_ORDER_STATUS_COMPLETED_EARLY_TEXT');
                    $ret['status_color'] = "black";
                } else
                {
                    $ret['status_text'] = $this->lang->getText('LANG_ORDER_STATUS_COMPLETED_TEXT');
                    $ret['status_color'] = "black";
                }
            } else if($ret['is_cancelled'] == 1)
            {
                $ret['status_text'] = $this->lang->getText('LANG_ORDER_STATUS_CANCELLED_TEXT');
                $ret['status_color'] = "red";
            }


            // Prepare output for print
            $ret['print_order_date'] = $printOrderDate;
            $ret['print_order_time'] = $printOrderTime;
            $ret['print_pickup_date'] = $printPickupDate;
            $ret['print_pickup_time'] = $printPickupTime;
            $ret['print_return_date'] = $printReturnDate;
            $ret['print_return_time'] = $printReturnTime;
            $ret['print_booking_code'] = esc_html($ret['booking_code']);
            $ret['print_coupon_code'] = esc_html($ret['coupon_code']);
            $ret['print_pickup_location_code'] = esc_html($ret['pickup_location_code']);
            $ret['print_return_location_code'] = esc_html($ret['return_location_code']);
            $ret['print_payment_method_code'] = esc_html($ret['payment_method_code']);

            // Prepare output for edit
            $ret['edit_booking_code'] = esc_attr($ret['booking_code']); // for input field
            $ret['edit_coupon_code'] = esc_attr($ret['coupon_code']); // for input field
            $ret['edit_pickup_location_code'] = esc_attr($ret['pickup_location_code']); // for input field
            $ret['edit_return_location_code'] = esc_attr($ret['return_location_code']); // for input field
            $ret['edit_payment_method_code'] = esc_attr($ret['payment_method_code']); // for input field
        }

        return $ret;
    }

    /**
     * Save order data
     * @param int $paramCustomerId
     * @param string $paramPaymentMethodCode
     * @param string $pickupLocationUniqueIdentifier
     * @param string $returnLocationUniqueIdentifier
     * @param array $paramDataArray
     * @return false|int
     */
    public function save($paramCustomerId, $paramPaymentMethodCode, $pickupLocationUniqueIdentifier, $returnLocationUniqueIdentifier, $paramDataArray = array())
    {
        $ok = true;
        $saved = false;
        $validOrderId = StaticValidator::getValidPositiveInteger($this->orderId, 0);
        $validCustomerId = StaticValidator::getValidPositiveInteger($paramCustomerId, 0);

        if($this->debugMode == 1)
        {
            echo "<br /><strong>Reservation Id:</strong> {$validOrderId}, ";
            echo "<br /><strong>Customer Id:</strong> {$validCustomerId} ";
            echo "<br /><strong>Search params array:</strong> "; echo nl2br(print_r($paramDataArray, true));
        }

        $validPaymentMethodCode = esc_sql(sanitize_key($paramPaymentMethodCode)); // only for sql query
        $sanitizedCouponCode = isset($paramDataArray['coupon_code']) ? StaticValidator::getValidValue($paramDataArray['coupon_code'], 'guest_text_validation', '') : "";
        $validCouponCode = esc_sql($sanitizedCouponCode); // only for sql query
        $validPickupTimestamp = isset($paramDataArray['pickup_timestamp']) ? intval($paramDataArray['pickup_timestamp']) : 0;
        $validReturnTimestamp = isset($paramDataArray['return_timestamp']) ? intval($paramDataArray['return_timestamp']) : 0;
        $validPickupLocationUniqueIdentifier = esc_sql(sanitize_text_field($pickupLocationUniqueIdentifier)); // for sql queries only
        $validReturnLocationUniqueIdentifier = esc_sql(sanitize_text_field($returnLocationUniqueIdentifier)); // for sql queries only
        $validPartnerId = isset($paramDataArray['partner_id']) ? intval($paramDataArray['partner_id']) : -1;
        $validManufacturerId = isset($paramDataArray['manufacturer_id']) ? intval($paramDataArray['manufacturer_id']) : -1;
        $validClassId = isset($paramDataArray['class_id']) ? intval($paramDataArray['class_id']) : -1;
        $validAttributeId1 = isset($paramDataArray['attribute_id1']) ? intval($paramDataArray['attribute_id1']) : -1;
        $validAttributeId2 = isset($paramDataArray['attribute_id2']) ? intval($paramDataArray['attribute_id2']) : -1;

        // If this is an existing order
        if($validOrderId > 0)
        {
            if($this->debugMode == 1)
            {
                echo "<br /><strong>FINALIZE ORDER EDIT:</strong> UPDATE EVERYTHING EXCEPT BOOKING TIME. USE EDIT_TIME INSTEAD OF THAT";
            }
            // UPDATE EVERYTHING EXCEPT ORDER TIMESTAMP. UPDATES EDIT_TIMESTAMP INSTEAD
            $updateSQL = "
				UPDATE {$this->conf->getPrefix()}bookings SET
				last_edit_timestamp='".time()."',
				coupon_code='{$validCouponCode}',
				pickup_timestamp='{$validPickupTimestamp}',
				return_timestamp='{$validReturnTimestamp}',
				pickup_location_code='{$validPickupLocationUniqueIdentifier}',
				return_location_code='{$validReturnLocationUniqueIdentifier}',
				partner_id='{$validPartnerId}',
				manufacturer_id='{$validManufacturerId}',
				body_type_id='{$validClassId}',
				fuel_type_id='{$validAttributeId1}',
				transmission_type_id='{$validAttributeId2}',
				customer_id='{$validCustomerId}',
				payment_method_code='{$validPaymentMethodCode}'
				WHERE booking_id='{$validOrderId}' AND blog_id='{$this->conf->getBlogId()}'
			";
            $saved = $this->conf->getInternalWPDB()->query($updateSQL);

            if($saved === false)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_ORDER_UPDATE_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_ORDER_UPDATED_TEXT');
            }
        } else if($ok)
        {
            if($this->debugMode == 1)
            {
                echo "<br /><strong>FINALIZE NEW ORDER:</strong> INSERT NEW DATA TO DATABASE.";
            }
            $validNewOrderCode = esc_sql(sanitize_text_field($this->generateCode()));
$vehicle_registration_number = 'none';
            $insertSQL = "
				INSERT INTO {$this->conf->getPrefix()}bookings
				(
					booking_code, coupon_code, booking_timestamp,
					pickup_timestamp, return_timestamp,
					pickup_location_code, return_location_code,
					partner_id, manufacturer_id,
					body_type_id, fuel_type_id, transmission_type_id,
					customer_id,
					payment_method_code, blog_id, vehicle_registration_number
				) VALUES
				(
					'{$validNewOrderCode}', '{$validCouponCode}', '".time()."',
					'{$validPickupTimestamp}', '{$validReturnTimestamp}',
					'{$validPickupLocationUniqueIdentifier}', '{$validReturnLocationUniqueIdentifier}',
					'{$validPartnerId}', '{$validManufacturerId}',
					'{$validClassId}', '{$validAttributeId1}', '{$validAttributeId2}',
					'{$validCustomerId}',
					'{$validPaymentMethodCode}', '{$this->conf->getBlogId()}', '{$vehicle_registration_number}'
				)
			";

            //echo "<br />INSERT ORDER: ".nl2br($insertSQL); die();
            $saved = $this->conf->getInternalWPDB()->query($insertSQL);

            if($saved)
            {
                // Assign new object id for future use
                $this->orderId = $this->conf->getInternalWPDB()->insert_id;
            }

            if($saved === false || $saved === 0)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_ORDER_INSERTION_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_ORDER_INSERTED_TEXT');
            }
        }

        return $saved;
    }

    public function registerForTranslation()
    {
        // Not used. Order has nothing to translate
    }

    /**
     * @return 1 ir false
     */
    public function cancel()
    {
        $cancelled = false;
        $orderData = $this->getDataFromDatabaseById($this->orderId);
        // If there exists unpaid booking under this booking id
        if(!is_null($orderData) && $orderData['is_cancelled'] == 0)
        {
            $cancelled = $this->conf->getInternalWPDB()->query("
                  UPDATE {$this->conf->getPrefix()}bookings SET
                  is_cancelled='1'
                  WHERE booking_id='{$orderData['booking_id']}' AND blog_id='{$this->conf->getBlogId()}'
            ");
        }

        if($cancelled === false || $cancelled === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_ORDER_CANCEL_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_ORDER_CANCELLED_TEXT');
        }

        return $cancelled;
    }

    public function delete()
    {
        $validOrderId = StaticValidator::getValidPositiveInteger($this->orderId);
        $deleted = $this->conf->getInternalWPDB()->query("
            DELETE FROM {$this->conf->getPrefix()}bookings
            WHERE booking_id='{$validOrderId}' AND blog_id='{$this->conf->getBlogId()}'
        ");

        if($deleted === false || $deleted === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_ORDER_DELETION_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_ORDER_DELETED_TEXT');
        }

        return $deleted;
    }

    /**
     * Element-specific method
     * Delete all order options related with this order id
     */
    public function deleteAllOptions()
    {
        $validOrderId = StaticValidator::getValidPositiveInteger($this->orderId);
        // Note: this call might be coming from customer deletion, so we do not check here blog_id or extension_code
        $deleted = $this->conf->getInternalWPDB()->query("
            DELETE FROM {$this->conf->getPrefix()}booking_options
            WHERE booking_id='{$validOrderId}' AND blog_id='{$this->conf->getBlogId()}'
        ");

        if($deleted === false || $deleted === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_ORDER_OPTIONS_DELETION_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_ORDER_OPTIONS_DELETED_TEXT');
        }

        return $deleted;
    }

    public function confirm($paramExternalTransactionId = "", $paramPayerEmail = "")
    {
        $confirmed = false;
        $sanitizedExternalTransactionId = sanitize_text_field($paramExternalTransactionId); // Optional
        $validExternalTransactionId = esc_sql($sanitizedExternalTransactionId); // Optional, for sql query only
        $sanitizedPayerEmail = sanitize_email($paramPayerEmail); // Optional
        $validPayerEmail = esc_sql($sanitizedPayerEmail); // Optional, for sql query only

        $orderData = $this->getDataFromDatabaseById($this->orderId);
        // If there exists unpaid booking under this booking id
        if(!is_null($orderData) && $orderData['payment_successful'] == 0)
        {
            $confirmed = $this->conf->getInternalWPDB()->query("
                  UPDATE {$this->conf->getPrefix()}bookings SET
                  payment_successful='1', payment_transaction_id='{$validExternalTransactionId}',
                  payer_email='{$validPayerEmail}'
                  WHERE booking_id='{$orderData['booking_id']}' AND is_block='0' AND blog_id='{$this->conf->getBlogId()}'
            ");
            // Note - we don't use blog_id here, to make it site-independent
            $this->conf->getInternalWPDB()->query("
                UPDATE {$this->conf->getPrefix()}customers SET
                existing_customer='1'
                WHERE customer_id='{$orderData['customer_id']}'
            ");
        }

        if($confirmed === false || $confirmed === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_ORDER_CONFIRMATION_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_ORDER_CONFIRMED_TEXT');
        }

        return $confirmed;
    }





	public function updateVehicleRegistrationNumber($vehicleRegistrationNumber)
    {
        $confirmed = false;

        $orderData = $this->getDataFromDatabaseById($this->orderId);
        // If there exists unpaid booking under this booking id
        if(!is_null($orderData))
        {
           $confirmed = $this->conf->getInternalWPDB()->query("
                  UPDATE {$this->conf->getPrefix()}bookings SET
                  vehicle_registration_number='{$vehicleRegistrationNumber}'
                  WHERE booking_id='{$orderData['booking_id']}' AND is_block='0' AND blog_id='{$this->conf->getBlogId()}'
            ");
            // Note - we don't use blog_id here, to make it site-independent
        }
}


		public function updateReturnTime($return_timestamp){
        $confirmed = false;

        $orderData = $this->getDataFromDatabaseById($this->orderId);
        // If there exists unpaid booking under this booking id
        if(!is_null($orderData))
        {
           $confirmed = $this->conf->getInternalWPDB()->query("
                  UPDATE {$this->conf->getPrefix()}bookings SET
                  return_timestamp='{$return_timestamp}'
                  WHERE booking_id='{$orderData['booking_id']}' AND is_block='0' AND blog_id='{$this->conf->getBlogId()}'
            ");
            // Note - we don't use blog_id here, to make it site-independent
        }



            $this->okayMessages[] = "Timestamp Updated Successfully";


       return $confirmed;
    }

    public function updatePickupTime($pickup_timestamp){
    $confirmed = false;

    $orderData = $this->getDataFromDatabaseById($this->orderId);
    // If there exists unpaid booking under this booking id
    if(!is_null($orderData))
    {
        $confirmed = $this->conf->getInternalWPDB()->query("
            UPDATE {$this->conf->getPrefix()}bookings SET
            pickup_timestamp='{$pickup_timestamp}'
            WHERE booking_id='{$orderData['booking_id']}' AND is_block='0' AND blog_id='{$this->conf->getBlogId()}'
        ");
        // Note - we don't use blog_id here, to make it site-independent
    }

    if ($confirmed) {
        $this->okayMessages[] = "Timestamp Updated Successfully";
    }

    return $confirmed;
}





    public function unconfirm()
    {
        $unconfirmed = false;

        $orderData = $this->getDataFromDatabaseById($this->orderId);
        // If there exists unpaid booking under this booking id
        if(!is_null($orderData) && $orderData['payment_successful'] == 0)
        {
            $unconfirmed = $this->conf->getInternalWPDB()->query("
                  UPDATE {$this->conf->getPrefix()}bookings SET
                  payment_successful='0', payment_transaction_id='',
                  payer_email=''
                  WHERE booking_id='{$orderData['booking_id']}' AND is_block='0' AND blog_id='{$this->conf->getBlogId()}'
            ");
        }

        if($unconfirmed === false || $unconfirmed === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_ORDER_UNCONFIRMATION_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_ORDER_UNCONFIRMED_TEXT');
        }

        return $unconfirmed;
    }

    public function markCompletedEarly()
    {
        $markedAsCompletedEarly = false;

        $orderData = $this->getDataFromDatabaseById($this->orderId);
        // If there exists unpaid booking under this booking id
        if(!is_null($orderData) && $orderData['pickup_timestamp'] <= time() && $orderData['is_completed_early'] == 0)
        {
            $markedAsCompletedEarly = $this->conf->getInternalWPDB()->query("
                  UPDATE {$this->conf->getPrefix()}bookings SET
                  return_timestamp='".time()."', is_completed_early='1'
                  WHERE booking_id='{$orderData['booking_id']}' AND is_block='0' AND blog_id='{$this->conf->getBlogId()}'
            ");
        }

        if($markedAsCompletedEarly === false || $markedAsCompletedEarly === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_ORDER_MARK_COMPLETED_EARLY_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_ORDER_MARKED_COMPLETED_EARLY_TEXT');
        }

        return $markedAsCompletedEarly;
    }

    /**
     * Money were sent back to the customer
     * @return bool|false|int
     */
    public function refund()
    {
        $refunded = false;
        $orderData = $this->getDataFromDatabaseById($this->orderId);

        // If there exists unpaid booking under this booking id
        if(!is_null($orderData) && $orderData['payment_successful'] == 1)
        {
            $refunded = $this->conf->getInternalWPDB()->query("
                  UPDATE {$this->conf->getPrefix()}bookings SET
                  payment_successful='2', is_cancelled='1'
                  WHERE booking_id='{$orderData['booking_id']}' AND is_block='0' AND blog_id='{$this->conf->getBlogId()}'
            ");
        }

        if($refunded === false || $refunded === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_ORDER_REFUND_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_ORDER_REFUNDED_TEXT');
        }

        return $refunded;
    }
}
