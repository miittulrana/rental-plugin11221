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
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\PostType\ItemModelPostType;
use FleetManagement\Models\PostType\LocationPostType;
use FleetManagement\Models\PostType\PagePostType;
use FleetManagement\Models\PrimitiveObserverInterface;

final class SingleUpdatesObserver implements PrimitiveObserverInterface
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
     * For updating single site plugin from 4.3.0 to 5.0.0
     */
    public function do430_UpdateTo500()
    {
        $updated = false;

        // Create mandatory instances
        $objDB_Update = new Update500($this->conf, $this->lang, "IGNORE", $this->conf->getBlogId());

        // We register post types here only because we want to run 'flush_rewrite_rules()' bellow.
        $objPagePostType = new PagePostType($this->conf, $this->lang, $objDB_Update::POST_TYPE_PREFIX.'page');
        $objItemModelPostType = new ItemModelPostType($this->conf, $this->lang, $objDB_Update::POST_TYPE_PREFIX.'item');
        $objLocationPostType = new LocationPostType($this->conf, $this->lang, $objDB_Update::POST_TYPE_PREFIX.'location');

        // Alter the database early structure
        $earlyStructAltered = $objDB_Update->alterDatabaseEarlyStructure();
        $dataUpdated = false;
        $lateStructAltered = false;

        // Process ONLY if the early struct was updated - because what if it crashed in the middle of the process
        if($earlyStructAltered)
        {
            // Update the database data
            $dataUpdated = $objDB_Update->updateDatabaseData();
        }

        // Process ONLY if the data was updated - because what if it crashed in the middle of the process
        if($dataUpdated)
        {
            // Alter the database late structure
            $lateStructAltered = $objDB_Update->alterDatabaseLateStructure();
        }

        // Process ONLY if the late struct was altered - because what if it crashed in the middle of the process
        if($lateStructAltered)
        {
            // Update the database version to 5.0.0
            $updated = $objDB_Update->updateDatabaseSemver();

            // Update roles
            $objDB_Update->updateCustomRoles();
            // Update capabilities
            $objDB_Update->updateCustomCapabilities();
        }

        // Rename uploads folder
        $uploadsDir = wp_upload_dir();
        $oldGalleryPathWithoutEndSlash = str_replace('\\', DIRECTORY_SEPARATOR, $uploadsDir['basedir']).DIRECTORY_SEPARATOR.$objDB_Update::OLD_GALLERY_FOLDER_NAME;
        $newGalleryPathWithoutEndSlash = str_replace('\\', DIRECTORY_SEPARATOR, $uploadsDir['basedir']).DIRECTORY_SEPARATOR.$objDB_Update::GALLERY_FOLDER_NAME;
        // We don't check here is that was successfully or not, as we still allow to process anyway
        StaticFile::renameFolder($oldGalleryPathWithoutEndSlash, $newGalleryPathWithoutEndSlash);

        // There were changes for links - so we need to flush the rewrite rules
        $objPagePostType->register($this->lang->getText('LANG_SETTINGS_DEFAULT_PAGE_URL_SLUG_TEXT'), 95);
        $objItemModelPostType->register($this->lang->getText('LANG_SETTINGS_DEFAULT_ITEM_MODEL_URL_SLUG_TEXT'), 96);
        $objLocationPostType->register($this->lang->getText('LANG_SETTINGS_DEFAULT_LOCATION_URL_SLUG_TEXT'), 97);
        flush_rewrite_rules();

        $this->saveAllMessages(array(
            'debug' => $objDB_Update->getDebugMessages(),
            'okay' => $objDB_Update->getOkayMessages(),
            'error' => $objDB_Update->getErrorMessages(),
        ));

        return $updated;
    }
}