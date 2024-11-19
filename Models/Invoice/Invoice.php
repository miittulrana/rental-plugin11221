<?php
/**
 * Invoice element

 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Invoice;
use FleetManagement\Models\AbstractStack;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;


final class Invoice extends AbstractStack
{
    private $conf 	                    = null;
    private $lang 		                = null;
    private $debugMode 	                = 0;
    private $invoiceId                  = 0;
    private $shortDateFormat            = "m/d/Y";
    private $currencySymbolLocation     = 0;

    /**
     * @note - invoice series are saved with each invoice and that's why it is not getting set in the constructor,
     *         as it may be different for older invoices
     * @param ConfigurationInterface &$paramConf
     * @param LanguageInterface &$paramLang
     * @param array $paramSettings
     * @param $paramInvoiceId = unique element identifier, mandatory, for managing invoices
     */
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings, $paramInvoiceId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;

        // Set invoice id
        $this->invoiceId = StaticValidator::getValidPositiveInteger($paramInvoiceId, 0);
        $this->shortDateFormat = StaticValidator::getValidSetting($paramSettings, 'conf_short_date_format', "date_format", "m/d/Y");
        $this->currencySymbolLocation = StaticValidator::getValidSetting($paramSettings, 'conf_currency_symbol_location', 'positive_integer', 0, array(0, 1));
    }

    private function getDataFromDatabaseById($paramInvoiceId, $paramColumns = array('*'))
    {
        $validInvoiceId = StaticValidator::getValidPositiveInteger($paramInvoiceId, 0);
        $validSelect = StaticValidator::getValidSelect($paramColumns);
        $validSelect = str_replace(
            array("fixed_deposit"),
            array("fixed_deposit_amount AS fixed_deposit"),
            $validSelect
        );

        $sqlQuery = "
            SELECT {$validSelect}, fixed_deposit_amount AS fixed_deposit
            FROM {$this->conf->getPrefix()}invoices
            WHERE invoice_id='{$validInvoiceId}'
        ";
        $retData = $this->conf->getInternalWPDB()->get_row($sqlQuery, ARRAY_A);

        return $retData;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function getId()
    {
        return $this->invoiceId;
    }

    public function getPartnerId()
    {
        // Not used in FM 502
        $retPartnerId = 0;
        return $retPartnerId;
    }

    public function getOrderId()
    {
        $retPartnerId = 0;
        $invoiceData = $this->getDataFromDatabaseById($this->invoiceId, array('booking_id'));
        if(!is_null($invoiceData))
        {
            $retPartnerId = $invoiceData['booking_id'];
        }
        return $retPartnerId;
    }

    /**
     * Element-specific method
     * @return string
     */
    public function getGrandTotal()
    {
        $grandTotal = '$ 0.00';
        $invoiceData = $this->getDataFromDatabaseById($this->invoiceId, array('grand_total'));
        if(!is_null($invoiceData))
        {
            $grandTotal = stripslashes($invoiceData['grand_total']);
        }

        // Add HTML span with inline CSS to make the grand total red
    return '<span style="color: red;">' . $grandTotal . '</span>';

        return $grandTotal;
    }

    /**
     * Element-specific method
     * @return string
     */
    public function getTotalPayNow()
    {
        $totalPayNow = '$ 0.00';
        $invoiceData = $this->getDataFromDatabaseById($this->invoiceId, array('total_pay_now'));
        if(!is_null($invoiceData))
        {
            $totalPayNow = stripslashes($invoiceData['total_pay_now']);
        }

        return $totalPayNow;
    }

    /**
     * Element-specific method
     * @return string
     */
    public function getTotalPayLater()
    {
        $totalPayLater = '$ 0.00';
        $invoiceData = $this->getDataFromDatabaseById($this->invoiceId, array('total_pay_later'));
        if(!is_null($invoiceData))
        {
            $totalPayLater = stripslashes($invoiceData['total_pay_later']);
        }

        return $totalPayLater;
    }

    /**
     * @param bool $paramPrefillWhenNull - not used
     * @return mixed
     */
    public function getDetails($paramPrefillWhenNull = false)
    {
        $ret = $this->getDataFromDatabaseById($this->invoiceId);

        if(!is_null($ret))
        {
            // Make raw
            $ret['partner_id'] = -1; // Not used in FM 502
            $ret['invoice_type'] = 'OVERALL'; // Not used in FM 502
            $ret['customer_name'] = stripslashes($ret['customer_name']);
            $ret['customer_email'] = stripslashes($ret['customer_email']);
            $ret['grand_total'] = stripslashes($ret['grand_total']);
            $ret['fixed_deposit'] = stripslashes($ret['fixed_deposit']);
            $ret['total_pay_now'] = stripslashes($ret['total_pay_now']);
            $ret['total_pay_later'] = stripslashes($ret['total_pay_later']);
            $ret['pickup_location'] = stripslashes($ret['pickup_location']);
            $ret['return_location'] = stripslashes($ret['return_location']);
            $ret['invoice'] = stripslashes($ret['invoice']);
        } else if($paramPrefillWhenNull === true)
        {
            // Make blank data
            $ret = array();
            $ret['invoice_id'] = 0;
            $ret['invoice_type'] = '';
            $ret['order_id'] = 0;
            $ret['partner_id'] = -1;
            $ret['customer_name'] = '';
            $ret['customer_email'] = '';
            $ret['grand_total'] = '$ 0.00';
            $ret['fixed_deposit'] = '$ 0.00';
            $ret['total_pay_now'] = '$ 0.00';
            $ret['total_pay_later'] = '$ 0.00';
            $ret['pickup_location'] = '';
            $ret['return_location'] = '';
            $ret['invoice'] = '';
            $ret['blog_id'] = $this->conf->getBlogId();
        }

        if(!is_null($ret) || $paramPrefillWhenNull === true)
        {
            // No translations for invoice table. It has to be like that - what was generated, that was ok

            // Prepare output for print
            $ret['print_customer_name'] = esc_html($ret['customer_name']);
            $ret['print_customer_email'] = esc_html($ret['customer_email']);
            $ret['print_grand_total'] = esc_html($ret['grand_total']);
            $ret['print_fixed_deposit'] = esc_html($ret['fixed_deposit']);
            $ret['print_total_pay_now'] = esc_html($ret['total_pay_now']);
            $ret['print_total_pay_later'] = esc_html($ret['total_pay_later']);

            // Prepare output for edit
            $ret['edit_customer_name'] = esc_attr($ret['customer_name']); // for input field
            $ret['edit_customer_email'] = esc_attr($ret['customer_email']); // for input field
            $ret['edit_grand_total'] = esc_attr($ret['grand_total']); // for input field
            $ret['edit_fixed_deposit'] = esc_attr($ret['fixed_deposit']); // for input field
            $ret['edit_total_pay_now'] = esc_attr($ret['total_pay_now']); // for input field
            $ret['edit_total_pay_later'] = esc_attr($ret['total_pay_later']); // for input field
            $ret['edit_pickup_location'] = esc_textarea($ret['pickup_location']); // for textarea field
            $ret['edit_return_location'] = esc_textarea($ret['return_location']); // for textarea field
        }

        return $ret;
    }

    /**
     * Save invoice to database
     * @param array $params
     * @return false|int
     */
    public function save(array $params)
    {
        $saved = false;
        $ok = true;
        $validInvoiceId = StaticValidator::getValidPositiveInteger($this->invoiceId, 0);

        // Note: Invoice type used for insertion only and cannot be changed later
        if(isset($params['invoice_type']) && in_array($params['invoice_type'], array('OVERALL', 'LOCAL', 'PARTNER')))
        {
            $validInvoiceType = StaticValidator::getValidCode($params['invoice_series'], 'OVERALL', true, false, false);
        } else
        {
            $validInvoiceType = 'OVERALL';
        }
        // NOTE: Order ID used for insertion only and cannot be changed later
        $validOrderId = isset($params['order_id']) ? StaticValidator::getValidPositiveInteger($params['order_id']) : 0;
        // NOTE: Partner ID used for insertion only and cannot be changed later
        $validPartnerId = isset($params['partner_id']) ? StaticValidator::getValidInteger($params['partner_id']) : -1; // '-1' is supported
        $sanitizedCustomerName = isset($params['customer_name']) ? sanitize_text_field($params['customer_name']) : '';
        $validCustomerName = esc_sql($sanitizedCustomerName); // for sql query only
        $sanitizedCustomerEmail = isset($params['customer_email']) ? sanitize_email($params['customer_email']) : '';
        $validCustomerEmail = esc_sql($sanitizedCustomerEmail); // for sql query only
        // NOTE: orders can have discounts, but we never allow to make the final amount to be less than 0.00
        $validGrandTotal = isset($params['grand_total']) ? esc_sql(sanitize_text_field($params['grand_total'])) : '$ 0.00'; // Always positive
        $validFixedDeposit = isset($params['fixed_deposit']) ? esc_sql(sanitize_text_field($params['fixed_deposit'])) : '$ 0.00'; // Always positive
        $validTotalPayNow = isset($params['total_pay_now']) ? esc_sql(sanitize_text_field($params['total_pay_now'])) : '$ 0.00'; // Always positive
        $validTotalPayLater = isset($params['total_pay_later']) ? esc_sql(sanitize_text_field($params['total_pay_later'])) : '$ 0.00'; // Always positive

        // NOTE: We can't use sanitize_text_field function for $pickupLocation and $returnLocation,
        // because it has <br /> tags inside. So we must use 'wp_kses_post'.
        // Still, we sure that all data used for this field is generated from internal db content, not from user input
        $ksesedPickupLocation = isset($params['pickup_location']) ? wp_kses_post($params['pickup_location']) : '';
        $validPickupLocation = esc_sql($ksesedPickupLocation); // for sql query only
        $ksesedReturnLocation = isset($params['return_location']) ? wp_kses_post($params['return_location']) : '';
        $validReturnLocation = esc_sql($ksesedReturnLocation); // for sql query only
        $ksesedInvoice = isset($params['invoice']) ? wp_kses_post($params['invoice']) : '';
        $validInvoice = esc_sql($ksesedInvoice); // for sql query only

        // NOTE: Paid and refunded amounts are managed by actions, and are not getting updated here
        if($validInvoiceId > 0 && $ok)
        {
            /* update the invoice data in {$this->conf->getPrefix()}invoice table */
            $updateSQL = "
				UPDATE {$this->conf->getPrefix()}invoices SET
				customer_name='{$validCustomerName}', customer_email='{$validCustomerEmail}',
				grand_total='{$validGrandTotal}', fixed_deposit_amount='{$validFixedDeposit}',
				total_pay_now='{$validTotalPayNow}', total_pay_later='{$validTotalPayLater}',
				pickup_location='{$validPickupLocation}', return_location='{$validReturnLocation}',
				invoice='{$validInvoice}'
				WHERE invoice_id='{$validInvoiceId}' AND blog_id='{$this->conf->getBlogId()}'
			";
			$saved = $this->conf->getInternalWPDB()->query($updateSQL);

            if($saved === false)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_INVOICE_UPDATE_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_INVOICE_UPDATED_TEXT');
            }
        } else if($ok)
        {
            /* insert the invoice data in {$this->conf->getPrefix()}invoice table */
            $insertSQL = "
				INSERT INTO {$this->conf->getPrefix()}invoices
				(
					booking_id, customer_name, customer_email,
					grand_total, fixed_deposit_amount,
					total_pay_now, total_pay_later,
					pickup_location, return_location,
					invoice, blog_id
				) VALUES
				(
					'{$validOrderId}', '{$validCustomerName}', '{$validCustomerEmail}',
					'{$validGrandTotal}', '{$validFixedDeposit}',
					'{$validTotalPayNow}', '{$validTotalPayLater}',
					'{$validPickupLocation}', '{$validReturnLocation}',
					'{$validInvoice}', '{$this->conf->getBlogId()}'
				)
			";
            // Debug
            //echo esc_html($insertSQL); die();
            $saved = $this->conf->getInternalWPDB()->query($insertSQL);

            if($saved === false || $saved === 0)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_INVOICE_INSERTION_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_INVOICE_INSERTED_TEXT');
            }
        }

        return $saved;
    }
	public function updateInvoice(array $params)
    {
        $saved = false;
        $ok = true;
        $validInvoiceId = StaticValidator::getValidPositiveInteger($this->invoiceId, 0);

        // Note: Invoice type used for insertion only and cannot be changed later
        if(isset($params['invoice_type']) && in_array($params['invoice_type'], array('OVERALL', 'LOCAL', 'PARTNER')))
        {
            $validInvoiceType = StaticValidator::getValidCode($params['invoice_series'], 'OVERALL', true, false, false);
        } else
        {
            $validInvoiceType = 'OVERALL';
        }

		 $validGrandTotal = isset($params['grand_total']) ? esc_sql(sanitize_text_field($params['grand_total'])) : '$ 0.00'; // Always positive
              $validTotalPayLater = isset($params['total_pay_later']) ? esc_sql(sanitize_text_field($params['total_pay_later'])) : '$ 0.00'; // Always positive

        $validOrderId = isset($params['order_id']) ? StaticValidator::getValidPositiveInteger($params['order_id']) : 0;

        $ksesedInvoice = isset($params['invoice']) ? wp_kses_post($params['invoice']) : '';
        $validInvoice = esc_sql($ksesedInvoice); // for sql query only

        // NOTE: Paid and refunded amounts are managed by actions, and are not getting updated here

            /* update the invoice data in {$this->conf->getPrefix()}invoice table */

		 $updateSQL = "
				UPDATE {$this->conf->getPrefix()}invoices SET
				grand_total='{$validGrandTotal}', total_pay_later='{$validTotalPayLater}',
				invoice='{$validInvoice}'
				WHERE invoice_id='{$validInvoiceId}' AND blog_id='{$this->conf->getBlogId()}'
			";
			$saved = $this->conf->getInternalWPDB()->query($updateSQL);

            if($saved === false)
            {
                $this->errorMessages[] = $this->lang->getText('LANG_INVOICE_UPDATE_ERROR_TEXT');
            } else
            {
                $this->okayMessages[] = $this->lang->getText('LANG_INVOICE_UPDATED_TEXT');
            }


        return $saved;
    }

    /**
     * Class-specific method
     * Append invoice HTML in Database
     * @param string $paramHTML_ToAppend - HTML to append
     * @return false|int
     */
    public function appendHTML_ToFinalInvoice($paramHTML_ToAppend)
    {
        $appended = false;
        $validInvoiceId = intval($this->invoiceId);
        $ksesedHTML_ToAppend = wp_kses_post($paramHTML_ToAppend);
        $validHTML_ToAppend = esc_sql($ksesedHTML_ToAppend); // for sql query only

        if($validInvoiceId > 0)
        {
            /* update the invoice data in {$this->conf->getPrefix()}invoice table */
            $appendQuery = "
				UPDATE {$this->conf->getPrefix()}invoices SET
				invoice = CONCAT(invoice, '{$validHTML_ToAppend}')
				WHERE invoice_id='{$validInvoiceId}' AND blog_id='{$this->conf->getBlogId()}'
			";
            $appended = $this->conf->getInternalWPDB()->query($appendQuery);
        }

        if($appended === false || $appended === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_INVOICE_APPEND_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_INVOICE_APPENDED_TEXT');
        }

        return $appended;
    }

    public function registerForTranslation()
    {
        // Not used. Invoices has nothing to translate
    }

    public function delete()
    {
        $deleted = false;

        $validInvoiceId = intval($this->invoiceId);
        // We do not delete invoice that has order id = 0, because that might be a secured example invoice
        if($validInvoiceId > 0)
        {
            // Note: this call might be coming from customer deletion, so we do not check here blog_id or extension_code
            $deleted = $this->conf->getInternalWPDB()->query("
                DELETE FROM {$this->conf->getPrefix()}invoices
                WHERE invoice_id='{$validInvoiceId}'
            ");
        }

        if($deleted === false || $deleted === 0)
        {
            $this->errorMessages[] = $this->lang->getText('LANG_INVOICE_DELETION_ERROR_TEXT');
        } else
        {
            $this->okayMessages[] = $this->lang->getText('LANG_INVOICE_DELETED_TEXT');
        }

        return $deleted;
    }
}
