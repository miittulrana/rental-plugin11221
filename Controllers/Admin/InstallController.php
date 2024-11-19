<?php
/**
 * Install controller to handle all install/network install and uninstall procedures
 * Final class cannot be inherited anymore. We use them when creating new instances
 * @package FleetManagement
 * @author Kestutis Matuliauskas
 * @copyright Kestutis Matuliauskas
 * @license See Legal/License.txt for details.
 */
namespace FleetManagement\Controllers\Admin;
use FleetManagement\Models\Assistant\AssistantRole;
use FleetManagement\Models\Configuration\ConfigurationInterface;
use FleetManagement\Models\Install\Install;
use FleetManagement\Models\Language\LanguageInterface;
use FleetManagement\Models\Language\LanguagesObserver;
use FleetManagement\Models\Administrator\AdministratorRole;
use FleetManagement\Models\Manager\ManagerRole;
use FleetManagement\Models\Partner\PartnerRole;
use FleetManagement\Models\PostType\ItemModelPostType;
use FleetManagement\Models\PostType\LocationPostType;
use FleetManagement\Models\PostType\PagePostType;
use FleetManagement\Models\PostType\PostTypesObserver;
use FleetManagement\Models\Status\SingleStatus;
use FleetManagement\Models\Validation\StaticValidator;

final class InstallController
{
    private $conf 	                = null;
    private $lang 		            = null;
    private $blogId 	            = 0;

    public function __construct(ConfigurationInterface &$paramConf, LanguageInterface &$paramLang, $paramBlogId)
    {
        // Set class settings
        $this->conf = $paramConf;
        // Already sanitized before in it's constructor. Too much sanitization will kill the system speed
        $this->lang = $paramLang;

        $this->blogId = intval($paramBlogId);
    }

    /**
     * @throws \Exception
     */
    public function setTables()
    {
        // Create mandatory instances
        $objSingleSiteStatus = new SingleStatus($this->conf, $this->lang, $this->blogId);

        // @Note - even if this is multisite or 2nd or later extension install, tables will be created only once for the main site only (with blog_id = '0' or '1')
        if ($objSingleSiteStatus->checkPluginDB_StructExistsOf($this->conf->getOldestCompatiblePluginSemver()) === false)
        {
            // First - drop all tables if exists any to have a clean install as expected
            foreach(Install::getTableClasses() AS $tableClass)
            {
                if(class_exists($tableClass))
                {
                    $objTable = new $tableClass($this->conf, $this->lang, $this->blogId);
                    if(method_exists($objTable, 'drop') && method_exists($objTable, 'getDebugMessages') && method_exists($objTable, 'getErrorMessages'))
                    {
                        $objTable->drop();
                        $this->processDebug($objTable->getDebugMessages());
                        $this->throwExceptionOnFailure($objTable->getErrorMessages());
                    }
                }
            }

            // Then - create all tables
            foreach(Install::getTableClasses() AS $tableClass)
            {
                if(class_exists($tableClass))
                {
                    $objTable = new $tableClass($this->conf, $this->lang, $this->blogId);
                    if(method_exists($objTable, 'create') && method_exists($objTable, 'getDebugMessages') && method_exists($objTable, 'getErrorMessages'))
                    {
                        $objTable->create();
                        $this->processDebug($objTable->getDebugMessages());
                        $this->throwExceptionOnFailure($objTable->getErrorMessages());
                    }
                }
            }
        }
    }

    /**
     * Note: this method should called only on first activation of plugin in specific blog-id
     */
    public function setCustomRoles()
    {
        // Create mandatory instances
        $objSingleSiteStatus = new SingleStatus($this->conf, $this->lang, $this->blogId);
        $objPartnerRole = new PartnerRole($this->conf, $this->lang);
        $objAssistantRole = new AssistantRole($this->conf, $this->lang);
        $objManagerRole = new ManagerRole($this->conf, $this->lang);

        // Note - the section below is extension-independent, which means that it runs only once per install of first extension, despite how many extensions there are
        if($objSingleSiteStatus->checkPluginDataExistsOf($this->conf->getOldestCompatiblePluginSemver()) === false)
        {
            // First - remove, if exists
            $objPartnerRole->remove();
            $objAssistantRole->remove();
            $objManagerRole->remove();

            // Then - add
            $objPartnerRole->add();
            $objAssistantRole->add();
            $objManagerRole->add();
        }
    }

    /**
     * Note: this method should called only on first activation of plugin in specific blog-id
     */
    public function setCustomCapabilities()
    {
        // Create mandatory instances
        $objSingleSiteStatus = new SingleStatus($this->conf, $this->lang, $this->blogId);
        $objAdministratorRole = new AdministratorRole($this->conf, $this->lang);

        // Note - the section below is extension-independent, which means that it runs only once per install of first extension, despite how many extensions there are
        if($objSingleSiteStatus->checkPluginDataExistsOf($this->conf->getOldestCompatiblePluginSemver()) === false)
        {
            // First - remove, if exists
            $objAdministratorRole->removeCapabilities();

            // Then - add
            $objAdministratorRole->addCapabilities();
        }
    }

    public function setCustomWP_RestAPI_Prefix()
    {
        // NOTE: Do not forget to do the same on install with flush_rewrite_rules(); after it.
        add_filter('rest_url_prefix', function() { return ConfigurationInterface::WP_REST_API_PREFIX; } );

        // NOTE: Do not flush rewrite rules here - they will be flushed with custom post types registration
    }

    public function setCustomPostTypes()
    {
        // Create mandatory instances
        $objSingleSiteStatus = new SingleStatus($this->conf, $this->lang, $this->blogId);

        if($objSingleSiteStatus->checkExtDataExistsOf($this->conf->getOldestCompatiblePluginSemver()) === false)
        {
            // First, we "add" the custom post type via the above written function.
            // Note: "add" is written with quotes, as CPTs don't get added to the DB,
            // They are only referenced in the post_type column with a post entry,
            // when you add a post of this CPT.
            // Note 2: Registering of these post types has to be inside the sql query, because if there is a slug in db, we should use init section instead
            $objPostType = new PagePostType($this->conf, $this->lang, $this->conf->getPostTypePrefix().'page');
            $objPostType->register($this->lang->getText('LANG_SETTINGS_DEFAULT_PAGE_URL_SLUG_TEXT'), 95);

            $objPostType = new ItemModelPostType($this->conf, $this->lang, $this->conf->getPostTypePrefix().'item');
            $objPostType->register($this->lang->getText('LANG_SETTINGS_DEFAULT_ITEM_MODEL_URL_SLUG_TEXT'), 96);

            $objPostType = new LocationPostType($this->conf, $this->lang, $this->conf->getPostTypePrefix().'location');
            $objPostType->register($this->lang->getText('LANG_SETTINGS_DEFAULT_LOCATION_URL_SLUG_TEXT'), 97);

            // Flush rewrite rules for current site, to make new ULRs work. Don't do this for every page load, only for activation.
            // See: https://iandunn.name/2015/04/23/flushing-rewrite-rules-on-all-sites-in-a-multisite-network/
            flush_rewrite_rules();
        }
    }

    /**
     * @throws \Exception
     */
    public function setContent()
    {
        // Create mandatory instances
        $objSingleSiteStatus = new SingleStatus($this->conf, $this->lang, $this->blogId);
        $objSingleSiteInstall = new Install($this->conf, $this->lang, $this->blogId);

        if($objSingleSiteStatus->checkExtDataExistsOf($this->conf->getOldestCompatiblePluginSemver()) === false)
        {
            // Delete any old table content if exists
            foreach(Install::getTableClasses() AS $tableClass)
            {
                if(class_exists($tableClass))
                {
                    $objTable = new $tableClass($this->conf, $this->lang, $this->blogId);
                    if(method_exists($objTable, 'deleteContent') && method_exists($objTable, 'getDebugMessages') && method_exists($objTable, 'getErrorMessages'))
                    {
                        $objTable->deleteContent();
                        $this->processDebug($objTable->getDebugMessages());
                        $this->throwExceptionOnFailure($objTable->getErrorMessages());
                    }
                }
            }
            // Delete any custom type old WP posts if exists
            $objPostTypesObserver = new PostTypesObserver($this->conf, $this->lang);
            $objPostTypesObserver->clearAll();
            $this->processDebug($objPostTypesObserver->getSavedDebugMessages());
            // To void a fatal crash on WordPress page deletion error, so we skip exception raising for them

            // Then insert all content
            $objSingleSiteInstall->insertContent();
            $this->processDebug($objSingleSiteInstall->getDebugMessages());
            $this->throwExceptionOnFailure($objSingleSiteInstall->getErrorMessages());
        }
    }

    /**
     * @throws \Exception
     */
    public function replaceResettableContent()
    {
        // Create mandatory instances
        $objSingleSiteStatus = new SingleStatus($this->conf, $this->lang, $this->blogId);
        $objSingleSiteInstall = new Install($this->conf, $this->lang, $this->blogId);

        // Check if the database is up to date
        if($objSingleSiteStatus->isExtDataUpToDateInDatabase())
        {
            // Then replace resettable content
            $objSingleSiteInstall->resetContent();
            $this->processDebug($objSingleSiteInstall->getDebugMessages());
            $this->throwExceptionOnFailure($objSingleSiteInstall->getErrorMessages());
        }
    }

    public function registerAllForTranslation()
    {
        // Create mandatory instances
        $objLanguagesObserver = new LanguagesObserver($this->conf, $this->lang);
        $objSingleSiteStatus = new SingleStatus($this->conf, $this->lang, $this->blogId);

        // Check if the database is up to date & WPML is enabled
        // Even if the data existed before, having this code out of IF DATA EXISTS scope, means that we allow
        // to re-register language text to WMPL and elsewhere (this will help us to add not-added texts if some is missing)
        if($objSingleSiteStatus->isExtDataUpToDateInDatabase() && $this->lang->canTranslateSQL())
        {
            $objLanguagesObserver->registerAllForTranslation();
        }
    }

    /**
     * Only deletes the content. Does not delete the tables
     * @throws \Exception
     */
    public function deleteContent()
    {
        // Delete any old table content if exists
        foreach(Install::getTableClasses() AS $tableClass)
        {
            if(class_exists($tableClass))
            {
                $objTable = new $tableClass($this->conf, $this->lang, $this->blogId);
                if(method_exists($objTable, 'deleteContent') && method_exists($objTable, 'getDebugMessages') && method_exists($objTable, 'getErrorMessages'))
                {
                    $objTable->deleteContent();
                    $this->processDebug($objTable->getDebugMessages());
                    $this->throwExceptionOnFailure($objTable->getErrorMessages());
                }
            }
        }
        // Delete any custom type old WP posts if exists
        $objPostTypesObserver = new PostTypesObserver($this->conf, $this->lang);
        $objPostTypesObserver->clearAll();
        $this->processDebug($objPostTypesObserver->getSavedDebugMessages());
        // To void a fatal crash on WordPress page deletion error, so we skip exception raising for them
    }

    /**
     * Remove custom roles
     */
    public function removeCustomRoles()
    {
        // Create mandatory instances
        $objPartnerRole = new PartnerRole($this->conf, $this->lang);
        $objAssistantRole = new AssistantRole($this->conf, $this->lang);
        $objManagerRole = new ManagerRole($this->conf, $this->lang);

        // Process actions
        $objPartnerRole->remove();
        $objAssistantRole->remove();
        $objManagerRole->remove();
    }

    /**
     * Removes custom capabilities
     */
    public function removeCustomCapabilities()
    {
        // Create mandatory instances
        $objAdministratorRole = new AdministratorRole($this->conf, $this->lang);

        // Process actions
        $objAdministratorRole->removeCapabilities();
    }

    /**
     * Deletes roles and drops tables
     * @note1 - it drops the tables
     * @note2 - unfortunately it is not possible to delete only extensions folder
     * @throws \Exception
     */
    public function dropTables()
    {
        foreach(Install::getTableClasses() AS $tableClass)
        {
            if(class_exists($tableClass))
            {
                $objTable = new $tableClass($this->conf, $this->lang, $this->blogId);
                if(method_exists($objTable, 'drop') && method_exists($objTable, 'getDebugMessages') && method_exists($objTable, 'getErrorMessages'))
                {
                    $objTable->drop();
                    $this->processDebug($objTable->getDebugMessages());
                    $this->throwExceptionOnFailure($objTable->getErrorMessages());
                }
            }
        }
    }

    /**
     * @param array $paramErrorMessages
     * @throws \Exception
     */
    protected function throwExceptionOnFailure(array $paramErrorMessages)
    {
        $errorMessagesToAdd = array();
        foreach($paramErrorMessages AS $paramErrorMessage)
        {
            $errorMessagesToAdd[] = sanitize_text_field($paramErrorMessage);
        }

        if(sizeof($errorMessagesToAdd) > 0)
        {
            $throwMessage = implode('<br />', $errorMessagesToAdd);
            throw new \Exception($throwMessage);
        }
    }

    /**
     * @param array $paramDebugMessages
     */
    protected function processDebug(array $paramDebugMessages)
    {
        $debugMessagesToAdd = array();
        foreach($paramDebugMessages AS $paramDebugMessage)
        {
            // HTML is allowed here
            $debugMessagesToAdd[] = wp_kses_post($paramDebugMessage);
        }

        if(StaticValidator::inWP_Debug() && sizeof($debugMessagesToAdd) > 0)
        {
            echo '<br />'.implode('<br />', $debugMessagesToAdd);
        }
    }
}