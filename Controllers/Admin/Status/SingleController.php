<?php
/**
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin\Status;
use FleetManagement\Controllers\Admin\InstallController;
use FleetManagement\Models\Cache\StaticSession;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Formatting\StaticFormatter;
use FleetManagement\Models\Install\Install;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\PostType\PostTypesObserver;
use FleetManagement\Models\Status\SingleStatus;
use FleetManagement\Models\Update\SinglePatchesObserver;
use FleetManagement\Models\Update\SingleUpdatesObserver;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Views\PageView;

final class SingleController
{
    protected $conf             = null;
    protected $lang 	        = null;
    protected $view 	        = null;
    protected $debugNoReload    = false;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
    }

    /**
     * Activate (enable+install or enable only) plugin for across the whole network
     * @note - 'get_sites' function requires WordPress 4.6 or newer!
     * @throws \Exception
     */
    public function processPopulateData()
    {
        // Create mandatory instances
        $objStatus = new SingleStatus($this->conf, $this->lang, $this->conf->getBlogId());

        // Set defaults
        $completed = true;

        // We only allow to populate the data if the newest plugin database struct exists
        if ($objStatus->checkPluginDB_StructExistsOf($this->conf->getPluginSemver()))
        {
            $objInstaller = new InstallController($this->conf, $this->lang, $this->conf->getBlogId());

            // Populate the data (without table creation)
            $objInstaller->setCustomRoles();
            $objInstaller->setCustomCapabilities();
            $objInstaller->setCustomWP_RestAPI_Prefix();
            $objInstaller->setCustomPostTypes();
            // NOTE: This plugin does not use any custom taxonomies registration
            $objInstaller->setContent();
            $objInstaller->replaceResettableContent();
            $objInstaller->registerAllForTranslation();
        } else
        {
            $completed = false;
        }

        if($completed === false)
        {
            // Failed
            if($this->debugNoReload)
            {
                die('Failed');
            } else
            {
                wp_safe_redirect(admin_url('plugins.php'));
            }
        } else
        {
            // Completed
            if($this->debugNoReload)
            {
                die('Completed');
            } else
            {
                wp_safe_redirect(admin_url('plugins.php?completed=1'));
            }
        }
        exit;
    }

    /**
     * Note: for data drop, we do not drop the roles, to protect from issues to happen on other extensions
     */
    public function processDropData()
    {
        // Set defaults
        $completed = true;

        // Delete any old table content if exists
        foreach(Install::getTableClasses() AS $tableClass)
        {
            if(class_exists($tableClass))
            {
                $objTable = new $tableClass($this->conf, $this->lang, $this->conf->getBlogId());
                if(method_exists($objTable, 'deleteContent') && method_exists($objTable, 'getDebugMessages') && method_exists($objTable, 'getErrorMessages'))
                {
                    $objTable->deleteContent();
                    StaticSession::cacheHTML_Array('admin_debug_html', $objTable->getDebugMessages());
                    // We don't process okay messages here
                    StaticSession::cacheValueArray('admin_error_message', $objTable->getErrorMessages());
                } else
                {
                    $completed = false;
                }
            } else
            {
                $completed = false;
            }
        }

        // Delete any custom type old WP posts if exists
        $objPostTypesObserver = new PostTypesObserver($this->conf, $this->lang);
        $objPostTypesObserver->clearAll();
        StaticSession::cacheHTML_Array('admin_debug_html', $objPostTypesObserver->getSavedDebugMessages());
        // NOTE: To void a errors on WordPress page deletion error, we skip exception raising for them

        if($completed === false)
        {
            // Failed
            wp_safe_redirect(admin_url('plugins.php'));
        } else
        {
            // Completed
            wp_safe_redirect(admin_url('plugins.php?completed=1'));
        }
        exit;
    }

    /**
     * @throws \Exception
     */
    private function processUpdate()
    {
        // Create mandatory instances
        $objStatus = new SingleStatus($this->conf, $this->lang, $this->conf->getBlogId());
        $objUpdatesObserver = new SingleUpdatesObserver($this->conf, $this->lang);
        $objPatchesObserver = new SinglePatchesObserver($this->conf, $this->lang);

        // Allow only one update at-a-time per site refresh. We need that to save resources of server to not to get to timeout phase
        $semverUpdated = false;
        $extSemverInDatabase = $objStatus->getExtSemverInDatabase();
        $latestSemver = $this->conf->getPluginSemver();

        if($this->conf->isNetworkEnabled() === false)
        {
            if(version_compare($extSemverInDatabase, '4.3.0', '=='))
            {
                $semverUpdated = $objUpdatesObserver->do430_UpdateTo500();
            } else if(version_compare($extSemverInDatabase, $latestSemver, '=='))
            {
                // It's a last version
                $semverUpdated = true;
            }

            // Run 6.0.Z patches
            if(version_compare($extSemverInDatabase, '5.0.0', '>=') && version_compare($extSemverInDatabase, '5.1.0', '<'))
            {
                $semverUpdated = $objPatchesObserver->doPatch(5, 0);
            }

            // Cache update messages
            StaticSession::cacheHTML_Array('admin_debug_html', $objUpdatesObserver->getSavedDebugMessages());
            StaticSession::cacheValueArray('admin_okay_message', $objUpdatesObserver->getSavedOkayMessages());
            StaticSession::cacheValueArray('admin_error_message', $objUpdatesObserver->getSavedErrorMessages());

            // Cache patch messages
            StaticSession::cacheHTML_Array('admin_debug_html', $objPatchesObserver->getSavedDebugMessages());
            StaticSession::cacheValueArray('admin_okay_message', $objPatchesObserver->getSavedOkayMessages());
            StaticSession::cacheValueArray('admin_error_message', $objPatchesObserver->getSavedErrorMessages());
        }

        // Check if plugin is up-to-date
        $pluginUpToDate = $objStatus->isExtDataUpToDateInDatabase();

        if($semverUpdated === false || $pluginUpToDate === false)
        {
            // Failed or if there is more updates to go
            wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'single-status&tab=status');
        } else
        {
            // Completed
            wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'single-status&tab=status&completed=1');
        }
        exit;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function printContent()
    {
        // Message handler - should always be at the begging of method (in the very first line)
        $ksesedDebugHTML = StaticValidator::inWP_Debug() ? StaticSession::getKsesedHTML_Once('admin_debug_html') : '';
        $errorMessage = StaticSession::getValueOnce('admin_error_message');
        $okayMessage = StaticSession::getValueOnce('admin_okay_message');

        // Both - _POST and _GET supported
        if(isset($_GET['populate_data']) || isset($_POST['populate_data'])) { $this->processPopulateData(); }
        if(isset($_GET['drop_data']) || isset($_POST['drop_data'])) { $this->processDropData(); }
        if(isset($_GET['update']) || isset($_POST['update'])) { $this->processUpdate(); }

        // Create mandatory instances
        $objStatus = new SingleStatus($this->conf, $this->lang, $this->conf->getBlogId());

        // Create view
        $objView = new PageView();

        // 1. Set the view variables - Tabs
        $objView->tabs = StaticFormatter::getTabParams(array('status'), 'status', isset($_GET['tab']) ? $_GET['tab'] : '');

        // 2. Set the view variables - other
        $objView->extCode = $this->conf->getExtCode();
        $objView->extName = $this->conf->getExtName();
        $objView->extURL_Prefix = $this->conf->getExtURL_Prefix();
        $objView->extCSS_Prefix = $this->conf->getExtCSS_Prefix();
        $objView->staticURLs = array_merge($this->conf->getRouting()->getFolderURLs(), array('GALLERY' => $this->conf->getGlobalGalleryURL()));
        $objView->lang = $this->lang->getAll();
        // NOTE: Settings stack for update templates is not needed
        $objView->ksesedDebugHTML = $ksesedDebugHTML;
        $objView->errorMessage = $errorMessage;
        $objView->okayMessage = $okayMessage;
        $objView->statusTabFormAction = admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'single-status&noheader=true');
        $objView->networkEnabled = $this->conf->isNetworkEnabled();
        $objView->goToNetworkAdmin = $this->conf->isNetworkEnabled() ? true : false;
        $objView->updateExists = $objStatus->checkExtUpdateExists();
        $objView->updateAvailable = $objStatus->canUpdateExtDataInDatabase();
        $objView->majorUpgradeAvailable = $objStatus->canMajorlyUpgradeExtDataInDatabase();
        $objView->canUpdate = $objStatus->canUpdateExtDataInDatabase();
        $objView->canMajorlyUpgrade = $objStatus->canMajorlyUpgradeExtDataInDatabase();
        $objView->databaseMatchesCodeSemver = $objStatus->isExtDataUpToDateInDatabase();
        $objView->databaseSemver = $objStatus->getExtSemverInDatabase();
        $objView->microframeworkName = ConfigurationInterface::MICROFRAMEWORK_NAME;
        $objView->microframeworkSemver = ConfigurationInterface::MICROFRAMEWORK_SEMVER;
        $objView->newestExistingSemver = $this->conf->getPluginSemver();
        $objView->newestSemverAvailable = $this->conf->getPluginSemver();

        // Print the template
        $templateRelPathAndFileName = 'Status'.DIRECTORY_SEPARATOR.'SingleTabs.php';
        echo $objView->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
