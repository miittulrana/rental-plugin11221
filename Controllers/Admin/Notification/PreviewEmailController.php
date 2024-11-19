<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Notification;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Notification\EmailNotification;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Controllers\Admin\AbstractController;

final class PreviewEmailController extends AbstractController
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
        $selectedEmailId = isset($_GET['email']) ? StaticValidator::getValidPositiveInteger($_GET['email'], 1) : 1;
        $objEmail = new EmailNotification($this->conf, $this->lang, $this->dbSets->getAll(), $selectedEmailId);
        $localNotificationDetails = $objEmail->getPreview(); // We know that it will will always return the fields

        // Set the view variables
        $this->view->emailPreviewTabChecked = ' checked="checked"';
        $this->view->backToListURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'notification-manager&tab=email-notifications');
        $this->view->emailSubject = $localNotificationDetails['print_translated_email_subject']; // We know that it will always return the fields
        $this->view->emailBody = $localNotificationDetails['print_translated_email_body']; // We know that it will always return the fields

        // Print the template
        $templateRelPathAndFileName = 'Notification'.DIRECTORY_SEPARATOR.'PreviewEmailTabs.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
