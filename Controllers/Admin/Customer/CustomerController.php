<?php
/**
 * @package FleetManagement
 * @note Variables prefixed with 'local' are not used in templates
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Customer;
use FleetManagement\Controllers\Admin\AbstractController;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Customer\CustomersObserver;
use FleetManagement\Models\User\UsersObserver;

final class CustomerController extends AbstractController
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
        // Create mandatory instances
        $objCustomersObserver = new CustomersObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $objUsersObserver = new UsersObserver($this->conf, $this->lang, $this->dbSets->getAll());

        // Customer List: Start
        $backToCustomersURL_Part = "&back_page={$this->conf->getExtURL_Prefix()}order-manager&back_tab=customers";
        // Customer List: End

        // 1. Set the view variables - Tabs
        $this->view->tabs = StaticFormatter::getTabParams(array(
            'customers',
        ), 'customers', isset($_GET['tab']) ? $_GET['tab'] : '');

        // 2. Set the view variables - other variables
        $this->view->html = "";
        $this->view->noonTime = date_i18n(get_option('time_format'), strtotime(date("Y-m-d")." ".$this->dbSets->get('conf_noon_time')), true);
        $this->view->customerSearchFormAction = admin_url('admin.php');
        $this->view->customerSearchPage = $this->conf->getExtURL_Prefix().'customer-search-results';
        $this->view->usersDropdownOptionsHTML = $objUsersObserver->getTrustedDropdownOptionsHTML(
            -1, -1, $this->dbSets->getSelect('LANG_USER_ACCOUNT_SELECT_TEXT', 'LANG_USER_ACCOUNT_SELECT2_TEXT'), true, array()
        );
        $this->view->addNewCustomerURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-customer&amp;customer_id=0');
        $this->view->trustedAdminCustomerListHTML = $objCustomersObserver->getTrustedAdminListHTML(-1, $backToCustomersURL_Part);

        // Print the template
        $templateRelPathAndFileName = 'Customer'.DIRECTORY_SEPARATOR.'ManagerTabs.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
