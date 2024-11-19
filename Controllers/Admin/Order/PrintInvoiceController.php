<?php
/**
 * @package FleetManagement
 * @note - this has to be loaded with &noheader _GET param
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Order;
use FleetManagement\Controllers\Admin\AbstractController;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Invoice\Invoice;
use FleetManagement\Models\Order\Order;


 

final class PrintInvoiceController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    /**
     * @throws \Exception
     * @return void
     */
	
    public function printContent()
    {
		$objOrder = new Order($this->conf, $this->lang, $this->dbSets->getAll(), $_GET['order_id']);
        $reservation = $objOrder->getDataFromDatabaseById($_GET['order_id']);
		$this->view->vehicleRegistrationNumber = $reservation["vehicle_registration_number"];
		
        // Process params
        $paramInvoiceId = isset($_GET['invoice_id']) ? $_GET['invoice_id'] : 0;

        // Create mandatory instances
        $objInvoice = new Invoice($this->conf, $this->lang, $this->dbSets->getAll(), $paramInvoiceId);
        $localDetails = $objInvoice->getDetails();

        // Set the view variables
        if(!is_null($localDetails))
        {
            $this->view->invoiceHTML = $localDetails['invoice'];
        } else
        {
            $this->view->invoiceHTML = '';
        }

        // Print the template
        $templateRelPathAndFileName = 'Order'.DIRECTORY_SEPARATOR.'PrintInvoiceTable.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));

    }
}