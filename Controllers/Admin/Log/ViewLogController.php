<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Log;
use FleetManagement\Controllers\Admin\AbstractController;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Log\Log;

final class ViewLogController extends AbstractController
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
        $paramLogId = isset($_GET['log_id']) ? $_GET['log_id'] : 0;
        $paramBackTab = isset($_GET['back_tab']) ? $_GET['back_tab'] : '';
        $validBackTab = sanitize_key($paramBackTab);
        $objLog = new Log($this->conf, $this->lang, $this->dbSets->getAll(), $paramLogId);
        $localDetails = $objLog->getDetails(true);

        // Set the view variables
        $this->view->backToListURL = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'logs-manager&tab='.$validBackTab);
        $this->view->log = $localDetails;

        // Print the template
        $templateRelPathAndFileName = 'Log'.DIRECTORY_SEPARATOR.'LogDetails.php';
        echo $this->view->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
