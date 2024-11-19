<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Payment;
use FleetManagement\Models\Cache\StaticSession;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Prepayment\Prepayment;
use FleetManagement\Controllers\Admin\AbstractController;

final class AddEditPrepaymentController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processDelete($paramPrepaymentId)
    {
        $objPrepayment = new Prepayment($this->conf, $this->lang, $this->dbSets->getAll(), $paramPrepaymentId);
        $objPrepayment->delete();

        StaticSession::cacheHTML_Array('admin_debug_html', $objPrepayment->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objPrepayment->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objPrepayment->getErrorMessages());

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'payment-manager&tab=prepayments');
        exit;
    }

    private function processSave($paramPrepaymentId)
    {
        $objPrepayment = new Prepayment($this->conf, $this->lang, $this->dbSets->getAll(), $paramPrepaymentId);
        $objPrepayment->save($_POST);

        StaticSession::cacheHTML_Array('admin_debug_html', $objPrepayment->getDebugMessages());
        StaticSession::cacheValueArray('admin_okay_message', $objPrepayment->getOkayMessages());
        StaticSession::cacheValueArray('admin_error_message', $objPrepayment->getErrorMessages());

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'payment-manager&tab=prepayments');
        exit;
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        if(isset($_GET['delete_prepayment'])) { $this->processDelete($_GET['delete_prepayment']); }
        if(isset($_POST['save_prepayment'], $_POST['prepayment_id'])) { $this->processSave($_POST['prepayment_id']); }

        $paramPrepaymentId = isset($_GET['prepayment_id']) ? $_GET['prepayment_id'] : 0;
        $objPrepayment = new Prepayment($this->conf, $this->lang, $this->dbSets->getAll(), $paramPrepaymentId);
        $localDetails = $objPrepayment->getDetails();

        // Set the view variables
        $this->view->backToListURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'payment-manager&tab=prepayments');
        $this->view->formAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-prepayment&noheader=true');
        if(!is_null($localDetails))
        {
            $this->view->prepaymentId = $localDetails['prepayment_id'];
            $this->view->durationFromDays = $this->dbSets->getAdminDaysByPriceTypeFromPeriod($localDetails['period_from']);
            $this->view->durationFromHours = $this->dbSets->getAdminHoursByPriceTypeFromPeriod($localDetails['period_from']);
            $this->view->durationTillDays = $this->dbSets->getAdminDaysByPriceTypeFromPeriod($localDetails['period_till']);
            $this->view->durationTillHours = $this->dbSets->getAdminHoursByPriceTypeFromPeriod($localDetails['period_till']);
            $this->view->itemPricesIncludedChecked = $localDetails['item_prices_included'] == 1 ? ' checked="checked"' : '';
            $this->view->itemDepositsIncludedChecked = $localDetails['item_deposits_included'] == 1 ? ' checked="checked"' : '';
            $this->view->extraPricesIncludedChecked = $localDetails['extra_prices_included'] == 1 ? ' checked="checked"' : '';
            $this->view->extraDepositsIncludedChecked = $localDetails['extra_deposits_included'] == 1 ? ' checked="checked"' : '';
            $this->view->pickupFeesIncludedChecked = $localDetails['pickup_fees_included'] == 1 ? ' checked="checked"' : '';
            $this->view->additionalFeesIncludedCheckedChecked = $localDetails['additional_fees_included'] == 1 ? ' checked="checked"' : '';
            $this->view->returnFeesIncludedChecked = $localDetails['return_fees_included'] == 1 ? ' checked="checked"' : '';
            $this->view->prepaymentPercentage = $localDetails['prepayment_percentage'];
        } else
        {
            $this->view->prepaymentId = 0;
            $this->view->durationFromDays = "";
            $this->view->durationFromHours = "";
            $this->view->durationTillDays = "";
            $this->view->durationTillHours = "";
            $this->view->itemPricesIncludedChecked = ' checked="checked"';
            $this->view->itemDepositsIncludedChecked = '';
            $this->view->extraPricesIncludedChecked = ' checked="checked"';
            $this->view->extraDepositsIncludedChecked = '';
            $this->view->pickupFeesIncludedChecked = ' checked="checked"';
            $this->view->additionalFeesIncludedCheckedChecked = ' checked="checked"';
            $this->view->returnFeesIncludedChecked = ' checked="checked"';
            $this->view->prepaymentPercentage = "";
        }

        // Print the template
        $templateRelPathAndFileName = 'Payment'.DIRECTORY_SEPARATOR.'AddEditPrepaymentForm.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
