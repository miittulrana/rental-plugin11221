<?php
/**
 * Invoices Observer (no setup for single invoice)
 * Final class cannot be inherited anymore. We use them when creating new instances
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Invoice;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\ObserverInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;

final class InvoicesObserver implements ObserverInterface
{
    private $conf 	                    = null;
    private $lang 		                = null;
    private $debugMode 	                = 0;
    private $settings                   = array();

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, array $paramSettings)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        $this->settings = $paramSettings;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    /**
     * @note - for invoices the ID is always unique, so we skip blog id check here
     * @param string $paramInvoiceType - 'OVERALL', 'LOCAL' or 'PARTNER'
     * @param int $paramOrderId
     * @param int $paramPartnerId
     * @return int
     */
    public function getIdByParams($paramInvoiceType, $paramOrderId, $paramPartnerId = 0)
    {
        $retInvoiceId = 0;
        $validInvoiceType = StaticValidator::getValidCode($paramInvoiceType, '', true, true, false); // (Not used in FM 502)
        $validOrderId = StaticValidator::getValidPositiveInteger($paramOrderId, 0);
        $validPartnerId = StaticValidator::getValidPositiveInteger($paramPartnerId, 0); // (Not used in FM 502)

        $invoiceData = $this->conf->getInternalWPDB()->get_row("
            SELECT invoice_id
            FROM {$this->conf->getPrefix()}invoices
            WHERE booking_id='{$validOrderId}'
            AND blog_id='".$this->conf->getBlogId()."'
            ORDER BY invoice_id DESC LIMIT 1
        ", ARRAY_A);
        if(!is_null($invoiceData))
        {
            $retInvoiceId = $invoiceData['invoice_id'];
        }

        return $retInvoiceId;
    }

    /**
     * @param string $paramInvoiceType - 'ANY', 'OVERALL', 'LOCAL' or 'PARTNER'
     * @param int $paramOrderId
     * @param int $paramPartnerId
     * @return array
     */
    public function getAllIds($paramInvoiceType = "ANY", $paramOrderId = -1, $paramPartnerId = -1)
    {
        $validInvoiceType = StaticValidator::getValidCode($paramInvoiceType, '', true, true, false); // (Not used in FM 502)
        $validOrderId = StaticValidator::getValidInteger($paramOrderId, -1);
        $validPartnerId = StaticValidator::getValidInteger($paramPartnerId, -1); // (not used in FM 502)

        // If not filled - return all invoices
        $sqlAdd = "";

        // If there is a order id set
        if($validOrderId > 0)
        {
            $sqlAdd .= " AND booking_id='{$validOrderId}'";
        }

        $sql = "
            SELECT invoice_id
            FROM {$this->conf->getPrefix()}invoices
            WHERE ext_code='{$this->conf->getExtCode()}' AND blog_id='".$this->conf->getBlogId()."'
            {$sqlAdd}
            ORDER BY invoice_id ASC
        ";

        // DEBUG
        //echo nl2br($sql);

        $orderIds = $this->conf->getInternalWPDB()->get_col($sql);

        return $orderIds;
    }

    // No admin lists for invoices, as they fully depend on orders
}