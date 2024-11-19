<?php
/**
 * Network updates observer
 *
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Models\Update;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\File\StaticFile;
use FleetManagement\Models\Language\Language;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\PostType\ItemModelPostType;
use FleetManagement\Models\PostType\LocationPostType;
use FleetManagement\Models\PostType\PagePostType;
use FleetManagement\Models\PrimitiveObserverInterface;
use FleetManagement\Models\Status\SingleStatus;

final class NetworkUpdatesObserver implements PrimitiveObserverInterface
{
    private $conf 	                    = null;
    private $lang 		                = null;
    private $debugMode 	                = 0;
    private $savedMessages              = array();

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;
    }

    public function inDebug()
    {
        return ($this->debugMode >= 1 ? true : false);
    }

    public function getSavedDebugMessages()
    {
        return isset($this->savedMessages['debug']) ? $this->savedMessages['debug'] : array();
    }

    public function getSavedOkayMessages()
    {
        return isset($this->savedMessages['okay']) ? $this->savedMessages['okay'] : array();
    }

    public function getSavedErrorMessages()
    {
        return isset($this->savedMessages['error']) ? $this->savedMessages['error'] : array();
    }

    private function saveAllMessages($paramArrMessages)
    {
        if(isset($paramArrMessages['debug']))
        {
            $this->savedMessages['debug'] = array_merge($this->getSavedDebugMessages(), $paramArrMessages['debug']);
        }
        if(isset($paramArrMessages['okay']))
        {
            $this->savedMessages['okay'] = array_merge($this->getSavedOkayMessages(), $paramArrMessages['okay']);
        }
        if(isset($paramArrMessages['error']))
        {
            $this->savedMessages['error'] = array_merge($this->getSavedErrorMessages(), $paramArrMessages['error']);
        }
    }

    /**
     * For updating across multisite the network-enabled plugin from 4.3.0 to 5.0.0
     * @note - Works only with WordPress 4.6+
     * @return bool
     * @throws \Exception
     */
    public function do430_UpdateTo500()
    {
        // Create mandatory instances
        $objNetworkDB_Update = new Update500($this->conf, $this->lang, "IGNORE", $this->conf->getBlogId());
        $allSitesVersionUpdated = true; // NOTE: In V4 and V5 there still was a 'version'

        // Alter the database early structure for all sites (because they use same database tables)
        $networkEarlyStructUpdated = $objNetworkDB_Update->alterDatabaseEarlyStructure();

        // NOTE: Network site is one of the sites. So it will update network site id as well.
        $sites = get_sites();
        foreach ($sites AS $site)
        {
            $blogId = $site->blog_id;
            switch_to_blog($blogId);

            $lang = new Language(
                $this->conf->getTextDomain(), $this->conf->getGlobalPluginLangPath(),
                $this->conf->getLocalLangPath(), $this->conf->getExtFolderName(), get_locale(), false
            );

            // Update the database data
            $objSingleDB_Update = new Update500($this->conf, $lang, "IGNORE", $blogId);
            $objSingleStatus = new SingleStatus($this->conf, $lang, $blogId);

            // Process ONLY if the current blog has populated extension data, network struct is already updated
            // and current site database was not yet updated
            if($networkEarlyStructUpdated && $objSingleStatus->checkExtDataExistsOf('4.3.0') && version_compare($objSingleStatus->getExtSemverInDatabase(), '4.3.0', '=='))
            {
                // We register post types here only because we want to run 'flush_rewrite_rules()' bellow.
                $objPagePostType = new PagePostType($this->conf, $this->lang, $objNetworkDB_Update::POST_TYPE_PREFIX.'page');
                $objItemModelPostType = new ItemModelPostType($this->conf, $this->lang, $objNetworkDB_Update::POST_TYPE_PREFIX.'item');
                $objLocationPostType = new LocationPostType($this->conf, $this->lang, $objNetworkDB_Update::POST_TYPE_PREFIX.'location');

                $dataUpdated = $objSingleDB_Update->updateDatabaseData();
                if($dataUpdated === false)
                {
                    $allSitesVersionUpdated = false;
                } else
                {
                    // Update the current site database version to 5.0.0
                    $versionUpdated = $objSingleDB_Update->updateDatabaseSemver();

                    // Update roles
                    $objSingleDB_Update->updateCustomRoles();
                    // Update capabilities
                    $objSingleDB_Update->updateCustomCapabilities();

                    if($versionUpdated == false)
                    {
                        $allSitesVersionUpdated = false;
                    }
                }

                // Rename uploads folder
                $uploadsDir = wp_upload_dir();
                $oldGalleryPathWithoutEndSlash = str_replace('\\', DIRECTORY_SEPARATOR, $uploadsDir['basedir']).DIRECTORY_SEPARATOR.$objSingleDB_Update::OLD_GALLERY_FOLDER_NAME;
                $newGalleryPathWithoutEndSlash = str_replace('\\', DIRECTORY_SEPARATOR, $uploadsDir['basedir']).DIRECTORY_SEPARATOR.$objSingleDB_Update::GALLERY_FOLDER_NAME;
                // We don't check here is that was successfully or not, as we still allow to process anyway
                StaticFile::renameFolder($oldGalleryPathWithoutEndSlash, $newGalleryPathWithoutEndSlash);

                // There were changes for links - so we need to flush the rewrite rules
                $objPagePostType->register($this->lang->getText('LANG_SETTINGS_DEFAULT_PAGE_URL_SLUG_TEXT'), 95);
                $objItemModelPostType->register($this->lang->getText('LANG_SETTINGS_DEFAULT_ITEM_MODEL_URL_SLUG_TEXT'), 96);
                $objLocationPostType->register($this->lang->getText('LANG_SETTINGS_DEFAULT_LOCATION_URL_SLUG_TEXT'), 97);
                flush_rewrite_rules();
            }

            $this->saveAllMessages(array(
                'debug' => $objSingleDB_Update->getDebugMessages(),
                'okay' => $objSingleDB_Update->getOkayMessages(),
                'error' => $objSingleDB_Update->getErrorMessages(),
            ));
        }
        // Switch back to current network blog id. Restore current blog won't work here, as it would just restore to previous blog of the long loop
        switch_to_blog($this->conf->getBlogId());

        // Process ONLY if the data was updated in ALL sites - because what if it crashed in the middle of the process
        if($allSitesVersionUpdated)
        {
            // Alter the database late structure - we not going to pay attention if the crash will happen in here,
            // because the database is already valid with just extra data, which we may just skip
            $objNetworkDB_Update->alterDatabaseLateStructure();
        }

        $this->saveAllMessages(array(
            'debug' => $objNetworkDB_Update->getDebugMessages(),
            'okay' => $objNetworkDB_Update->getOkayMessages(),
            'error' => $objNetworkDB_Update->getErrorMessages(),
        ));

        return $allSitesVersionUpdated;
    }
}