<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Notification;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Notification\EmailNotificationsObserver;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Controllers\Admin\AbstractController;

final class NotificationController extends AbstractController
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
        // Tab - email settings
        $localSelectedEmailId = isset($_GET['email']) ? StaticValidator::getValidPositiveInteger($_GET['email'], 0) : 0;
        $localPickupLocationVisible = $this->dbSets->getSearchFieldStatus("pickup_location", "VISIBLE");
        $localReturnLocationVisible = $this->dbSets->getSearchFieldStatus("return_location", "VISIBLE");
        $objEmailsObserver = new EmailNotificationsObserver($this->conf, $this->lang, $this->dbSets->getAll());
        $this->view->showLocationBBCodes = $localPickupLocationVisible || $localReturnLocationVisible;
        $this->view->emailNotificationsTabFormAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'add-edit-email-notification&noheader=true');
        $this->view->urlPrefix = $this->conf->getExtURL_Prefix();
        $this->view->emailList = $objEmailsObserver->getTrustedAdminListHTML($localSelectedEmailId);
        $this->view->orderBBCode = $this->conf->getOrderCodeBBCode();
        $this->view->changeOrderURL_BBCode = $this->conf->getChangeOrderURL_BBCode();


        // 1. Set the view variables - Tabs
        $this->view->tabs = StaticFormatter::getTabParams(array(
            'email-notifications'
        ), 'email-notifications', isset($_GET['tab']) ? $_GET['tab'] : '');

        // Print the template
        $templateRelPathAndFileName = 'Notification'.DIRECTORY_SEPARATOR.'ManagerTabs.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
