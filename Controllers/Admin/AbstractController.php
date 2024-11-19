<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Cache\StaticSession;
use FleetManagement\Models\Settings\SettingsObserver;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Views\PageView;

abstract class AbstractController
{
    protected $conf         = null;
    protected $lang 	    = null;
    protected $view 	    = null;
    protected $dbSets	    = null;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
        // Set database settings
        $this->dbSets = new SettingsObserver($this->conf, $this->lang);
        $this->dbSets->setAll();

        // Message handler - should always be at the begging of method
        $ksesedDebugHTML = StaticValidator::inWP_Debug() ? StaticSession::getKsesedHTML_Once('admin_debug_html') : '';
        $errorMessage = StaticSession::getValueOnce('admin_error_message');
        $okayMessage = StaticSession::getValueOnce('admin_okay_message');

        // Initialize the page view and set it's conf and lang objects
        $this->view = new PageView();
        $this->view->extCode = $this->conf->getExtCode();
        $this->view->extName = $this->conf->getExtName();
        $this->view->extPrefix = $this->conf->getExtPrefix();
        $this->view->extURL_Prefix = $this->conf->getExtURL_Prefix();
        $this->view->extCSS_Prefix = $this->conf->getExtCSS_Prefix();
        $this->view->staticURLs = array_merge($this->conf->getRouting()->getFolderURLs(), array('GALLERY' => $this->conf->getGlobalGalleryURL()));
        $this->view->lang = $this->lang->getAll();
        $this->view->settings = $this->dbSets->getAll();
        $this->view->objConf = $this->conf;
        $this->view->objSettings = $this->dbSets;
        $this->view->urlPrefix = $this->conf->getExtURL_Prefix();
        $this->view->ksesedDebugHTML = $ksesedDebugHTML;
        $this->view->errorMessage = $errorMessage;
        $this->view->okayMessage = $okayMessage;
    }
}