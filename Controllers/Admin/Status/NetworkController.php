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
use FleetManagement\Models\Language\Language;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\PostType\PostTypesObserver;
use FleetManagement\Models\Status\NetworkStatus;
use FleetManagement\Models\Update\NetworkPatchesObserver;
use FleetManagement\Models\Update\NetworkUpdatesObserver;
use FleetManagement\Models\Validation\StaticValidator;
use FleetManagement\Views\PageView;

final class NetworkController
{
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
        $objNetworkStatus = new NetworkStatus($this->conf, $this->lang);

        // Set defaults
        $completed = true;

        // We only allow to populate the data if the newest plugin database struct exists
        if ($objNetworkStatus->checkPluginDB_StructExistsOf($this->conf->getPluginSemver()))
        {
            // Save original locale
            $orgLang = $this->lang;

            $sites = get_sites();
            foreach ($sites AS $site)
            {
                $blogId = $site->blog_id;
                switch_to_blog($blogId);

                $lang = new Language(
                    $this->conf->getTextDomain(), $this->conf->getGlobalPluginLangPath(),
                    $this->conf->getLocalLangPath(), $this->conf->getExtFolderName(), $this->conf->getBlogLocale($blogId), false
                );
                $objInstaller = new InstallController($this->conf, $lang, $blogId);

                // Populate the data (without table creation)
                $objInstaller->setCustomRoles();
                $objInstaller->setCustomCapabilities();
                $objInstaller->setCustomWP_RestAPI_Prefix();
                $objInstaller->setCustomPostTypes();
                // NOTE: This plugin does not use any custom taxonomies registration
                $objInstaller->setContent();
                $objInstaller->replaceResettableContent();
                $objInstaller->registerAllForTranslation();
            }
            // Switch back to current blog id. Restore current blog won't work here, as it would just restore to previous blog of the long loop
            switch_to_blog($this->conf->getBlogId());
            // Restore original locale
            $this->lang = $orgLang;
        } else
        {
            $completed = false;
        }

        if($completed === false)
        {
            // Failed
            wp_safe_redirect(network_admin_url('plugins.php'));
        } else
        {
            // Completed
            wp_safe_redirect(network_admin_url('plugins.php?completed=1'));
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

        $sites = get_sites();
        foreach($sites AS $site)
        {
            $blogId = $site->blog_id;
            switch_to_blog($blogId);

            // Delete any old table content if exists
            foreach(Install::getTableClasses() AS $tableClass)
            {
                if(class_exists($tableClass))
                {
                    $objTable = new $tableClass($this->conf, $this->lang, $blogId);
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
        }

        if($completed === false)
        {
            // Failed
            wp_safe_redirect(network_admin_url('plugins.php'));
        } else
        {
            // Completed
            wp_safe_redirect(network_admin_url('plugins.php?completed=1'));
        }
        exit;
    }

    /**
     * @throws \Exception
     */
    private function processUpdate()
    {
        // Create mandatory instances
        $objStatus = new NetworkStatus($this->conf, $this->lang);
        $objUpdatesObserver = new NetworkUpdatesObserver($this->conf, $this->lang);
        $objPatchesObserver = new NetworkPatchesObserver($this->conf, $this->lang);

        // Allow only one update at-a-time per site refresh. We need that to save resources of server to not to get to timeout phase
        $allUpdatableSitesSemverUpdated = false;
        $minExtSemverInDatabase = $objStatus->getMinExtSemverInDatabase();
        $maxExtSemverInDatabase = $objStatus->getMaxExtSemverInDatabase();
        $latestSemver = $this->conf->getPluginSemver();

        // ----------------------------------------
        // NOTE: A PLACE FOR UPDATE CODE
        // ----------------------------------------

        if($this->conf->isNetworkEnabled())
        {
            if(version_compare($minExtSemverInDatabase, '4.3.0', '=='))
            {
                $allUpdatableSitesSemverUpdated = $objUpdatesObserver->do430_UpdateTo500();
            } else if(version_compare($minExtSemverInDatabase, $latestSemver, '=='))
            {
                // It's a last version
                $allUpdatableSitesSemverUpdated = true;
            }

            // Process 5.0.Z patches
            if(version_compare($minExtSemverInDatabase, '5.0.0', '>=') && version_compare($maxExtSemverInDatabase, '5.1.0', '<'))
            {
                $allUpdatableSitesSemverUpdated = $objPatchesObserver->doPatch(5, 0);
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
        $pluginUpToDate = $objStatus->isAllBlogsWithExtDataUpToDate();

        if($allUpdatableSitesSemverUpdated === false || $pluginUpToDate === false)
        {
            // Failed or if there is more updates to go
            wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'network-status&tab=status');
        } else
        {
            // Completed
            wp_safe_redirect('admin.php?page='.$this->conf->getExtURL_Prefix().'network-status&tab=status&completed=1');
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
        $objStatus = new NetworkStatus($this->conf, $this->lang);

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
        $objView->statusTabFormAction = network_admin_url('admin.php?page='.$this->conf->getExtURL_Prefix().'network-status&noheader=true');
        $objView->networkEnabled = true;
        $objView->goToNetworkAdmin = false;
        $objView->updateExists = $objStatus->checkExtUpdateExistsForSomeBlog();
        $objView->updateAvailable = $objStatus->canUpdateExtDataInSomeBlog();
        $objView->majorUpgradeAvailable = $objStatus->canMajorlyUpgradeExtDataInSomeBlog();
        $objView->canUpdate = $objStatus->canUpdateExtDataInSomeBlog();
        $objView->canMajorlyUpgrade = $objStatus->canMajorlyUpgradeExtDataInSomeBlog();
        $objView->databaseMatchesCodeSemver = $objStatus->isAllBlogsWithExtDataUpToDate();
        $objView->minDatabaseSemver = $objStatus->getMinExtSemverInDatabase();
        $objView->microframeworkName = ConfigurationInterface::MICROFRAMEWORK_NAME;
        $objView->microframeworkSemver = ConfigurationInterface::MICROFRAMEWORK_SEMVER;
        $objView->newestExistingSemver = $this->conf->getPluginSemver();
        $objView->newestSemverAvailable = $this->conf->getPluginSemver();

        // Print the template
        $templateRelPathAndFileName = 'Status'.DIRECTORY_SEPARATOR.'NetworkTabs.php';
        echo $objView->render($this->conf->getRouting()->getAdminTemplatesPath($templateRelPathAndFileName));
    }
}
