<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Notification;
use FleetManagement\Models\Cache\StaticSession;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Notification\EmailNotification;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Controllers\Admin\AbstractController;

final class AddEditEmailController extends AbstractController
{
    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        parent::__construct($paramConf, $paramLang);
    }

    private function processSave()
    {
        $selectedEmailId = isset($_POST['email_id']) ? StaticValidator::getValidPositiveInteger($_POST['email_id'], 0) : 0;
        $objEmail = new EmailNotification($this->conf, $this->lang, $this->dbSets->getAll(), $selectedEmailId);
        if($objEmail->canEdit())
        {
            $saved = $objEmail->save($_POST);
            if($saved && $this->lang->canTranslateSQL())
            {
                $objEmail->registerForTranslation();
            }

            StaticSession::cacheHTML_Array('admin_debug_html', $objEmail->getDebugMessages());
            StaticSession::cacheValueArray('admin_okay_message', $objEmail->getOkayMessages());
            StaticSession::cacheValueArray('admin_error_message', $objEmail->getErrorMessages());
        }

        wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'notification-manager&tab=email-notifications&email='.$selectedEmailId);
        exit;
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function printContent()
    {
        // First - process actions
        if(isset($_POST['update_email'])) { $this->processSave(); }
    }
}
